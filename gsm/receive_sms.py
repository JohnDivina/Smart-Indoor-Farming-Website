# -*- coding: utf-8 -*-
"""Read SMS commands from the USR-GM3 and reply once per invocation.

This script is intentionally a short-lived, single-instance worker.  Keep the
USR-GM3 in SMS/AT mode; do not send the transparent-mode escape sequence (+++).
"""
import datetime as dt
import os
import re
import sys
import time
import msvcrt

import mysql.connector
import serial

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "smartfarm",
    "charset": "utf8mb4",
}
PORT = "COM5"
BAUDRATE = 115200
SERIAL_TIMEOUT = 1
LOCK_PATH = os.path.join(os.path.dirname(__file__), "receive_sms.py.lock")
CMGL_HEADER = re.compile(
    r'^\+CMGL:\s*(\d+),"([^"]*)","([^"]*)",(?:"[^"]*")?,"([^"]*)"', re.MULTILINE
)


class WorkerAlreadyRunning(RuntimeError):
    pass


class WorkerLock:
    def __enter__(self):
        self.file = open(LOCK_PATH, "a+b")
        if os.path.getsize(LOCK_PATH) == 0:
            self.file.write(b"0")
            self.file.flush()
        self.file.seek(0)
        try:
            msvcrt.locking(self.file.fileno(), msvcrt.LK_NBLCK, 1)
        except OSError as exc:
            self.file.close()
            raise WorkerAlreadyRunning("SMS worker is already running") from exc
        return self

    def __exit__(self, *_):
        try:
            self.file.seek(0)
            msvcrt.locking(self.file.fileno(), msvcrt.LK_UNLCK, 1)
        finally:
            self.file.close()


class Modem:
    def __init__(self):
        self.ser = serial.Serial(PORT, BAUDRATE, timeout=SERIAL_TIMEOUT, write_timeout=5)
        self.in_serial_at_mode = False
        time.sleep(1)
        self.ser.reset_input_buffer()

    def close(self):
        if self.ser and self.ser.is_open:
            self.ser.close()

    def _read_until(self, timeout, complete):
        end = time.monotonic() + timeout
        response = ""
        while time.monotonic() < end:
            waiting = self.ser.in_waiting
            data = self.ser.read(waiting or 1).decode("utf-8", errors="replace")
            if data:
                response += data
                if complete(response):
                    break
        return response.strip()

    def command(self, command, timeout=5):
        self.ser.reset_input_buffer()
        self.ser.write((command + "\r\n").encode("ascii"))
        self.ser.flush()
        return self._read_until(timeout, lambda text: "\r\nOK" in text or "\r\nERROR" in text or "+CME ERROR" in text or "+ok" in text.lower() or "+err" in text.lower())

    def enter_serial_at_mode(self):
        """Enter the USR-GM3 serial AT session.

        The GM3 setup application uses this exact +++ escape before it can
        issue GSM AT commands.  Guard times are required; do not append CR/LF.
        """
        self.ser.reset_input_buffer()
        time.sleep(1.2)
        self.ser.write(b"+++")
        self.ser.flush()
        # Do not stop just because an unsolicited modem notification contains
        # the letter "a" (for example: +CIEV: "MESSAGE",1).  The first
        # stage of the GM3 handshake is a *standalone* `a` line.
        response = self._read_until(
            4,
            lambda text: bool(re.search(r"(?:^|[\r\n])a(?:[\r\n]|$)", text, re.IGNORECASE))
            or "ERROR" in text,
        )
        # A prior run can have finished while the GM3 remained in serial AT
        # mode.  In that state +++ is correctly rejected as an invalid command.
        if "cme error" in response.lower() and "invalid command" in response.lower():
            self.in_serial_at_mode = True
            print("GM3 was already in serial AT mode")
            return
        # After a successful SMS submit, some GM3 firmware revisions remain in
        # the GSM AT session but only echo a later +++.  Continue with the SMS
        # commands; they provide the real health check and avoid leaving new
        # inbound messages unread.
        if response.strip() in {"", "+++"}:
            self.in_serial_at_mode = True
            print("GM3 serial AT escape was silent; continuing in existing session")
            return
        if not re.search(r"(?:^|[\r\n])a(?:[\r\n]|$)", response, re.IGNORECASE) or "ERROR" in response:
            raise RuntimeError(f"GM3 did not acknowledge +++: {response}")
        # USR serial-AT handshake: +++ -> a, then immediately a -> +ok.
        self.ser.write(b"a")
        self.ser.flush()
        confirmation = self._read_until(4, lambda text: "+ok" in text.lower() or "+err" in text.lower() or "ERROR" in text)
        print(f"Enter serial AT mode: {response} / {confirmation}")
        if "+ok" not in confirmation.lower():
            raise RuntimeError(f"GM3 rejected serial AT mode entry: {confirmation}")
        self.in_serial_at_mode = True

    def exit_serial_at_mode(self):
        if not self.in_serial_at_mode:
            return
        response = self.command("AT+ENTM", timeout=3)
        print(f"Exit serial AT mode: {response}")
        self.in_serial_at_mode = False

    def reset_module(self):
        """Reset the USR DTU so its SMS storage is usable on the next poll."""
        try:
            self.ser.reset_input_buffer()
            self.ser.write(b"AT+Z\r\n")
            self.ser.flush()
            response = self._read_until(3, lambda text: "+ok" in text.lower() or "OK" in text or "ERROR" in text)
            print(f"Reset GM3 for next poll: {response}")
        except Exception as exc:
            print(f"Warning: could not reset GM3: {exc}")

    def initialize(self):
        self.enter_serial_at_mode()
        # Bare AT/ATE0 are not valid USR serial-AT configuration commands and
        # produce +CME ERROR:58.  Use the supported GSM SMS commands directly.
        required = ("AT+CMEE=2", "AT+CPIN?", "AT+CREG?", "AT+CSQ", 'AT+CSCS="GSM"', "AT+CMGF=1", 'AT+CPMS="SM","SM","SM"', "AT+CNMI=2,0,0,0,0")
        for command in required:
            response = self.command(command)
            print(f"{command}: {response}")
            # The GM3 occasionally rejects a repeated CMGF/CSCS setting even
            # though the prior value remains usable. Retry once, then keep
            # polling rather than losing an incoming SMS.
            if command in {'AT+CSCS="GSM"', "AT+CMGF=1"} and "ERROR" in response:
                time.sleep(1)
                response = self.command(command)
                print(f"{command} retry: {response}")
            if command == "AT+CPIN?" and "READY" not in response:
                raise RuntimeError(f"Modem initialization failed at {command}: {response}")

    def list_messages(self):
        return self.command('AT+CMGL="ALL"', timeout=8)

    def send_sms(self, recipient, message):
        # GSM text mode is deliberately ASCII here.  This avoids corrupting degree symbols.
        message = message.replace("°", "deg ").encode("ascii", "replace").decode("ascii")
        pieces = split_message(message)
        for piece in pieces:
            self.ser.reset_input_buffer()
            self.ser.write(f'AT+CMGS="{recipient}"\r\n'.encode("ascii"))
            self.ser.flush()
            prompt = self._read_until(5, lambda text: ">" in text or "ERROR" in text)
            if ">" not in prompt:
                print(f"SMS prompt failed for {recipient}: {prompt}")
                return False
            self.ser.write(piece.encode("ascii", "replace") + b"\x1a")
            self.ser.flush()
            result = self._read_until(30, lambda text: "\r\nOK" in text or "ERROR" in text or "+CME ERROR" in text)
            print(f"SMS result for {recipient}: {result}")
            if "+CMGS:" not in result or "OK" not in result or "ERROR" in result:
                return False
        return True

    def delete_message(self, index):
        response = self.command(f"AT+CMGD={index}")
        print(f"Delete SIM message {index}: {response}")
        return "OK" in response and "ERROR" not in response


def format_phone_number(number):
    number = number.strip()
    if number.startswith("0") and len(number) >= 10:
        return "+63" + number[1:]
    return number


def split_message(message):
    if len(message) <= 160:
        return [message]
    chunk_size = 145
    raw = [message[i:i + chunk_size] for i in range(0, len(message), chunk_size)]
    return [f"({i}/{len(raw)}) {part}" for i, part in enumerate(raw, 1)]


def parse_timestamp(value):
    # GM3 firmware versions emit either yy/MM/dd or yyyy/MM/dd.  Keep the
    # module's local time; its +08 suffix is not consistently encoded as the
    # 3GPP quarter-hour offset across firmware versions.
    match = re.fullmatch(r"(\d{2,4}/\d{2}/\d{2},\d{2}:\d{2}:\d{2})([+-]\d{2})", value)
    if not match:
        raise ValueError(f"Unexpected modem timestamp: {value}")
    date_format = "%Y/%m/%d,%H:%M:%S" if len(match.group(1).split("/", 1)[0]) == 4 else "%y/%m/%d,%H:%M:%S"
    return dt.datetime.strptime(match.group(1), date_format)


def parse_messages(response):
    matches = list(CMGL_HEADER.finditer(response))
    messages = []
    for pos, match in enumerate(matches):
        end = matches[pos + 1].start() if pos + 1 < len(matches) else len(response)
        body = response[match.end():end].strip("\r\n ")
        body_lines = [line.strip() for line in body.splitlines() if line.strip() not in {"OK", "ERROR"}]
        messages.append({
            "id": int(match.group(1)),
            "status": match.group(2),
            "sender": format_phone_number(match.group(3)),
            "received_at": parse_timestamp(match.group(4)),
            "message": "\n".join(body_lines).strip(),
        })
    return messages


def latest(cursor, query):
    cursor.execute(query)
    return cursor.fetchone()


def format_live_light_average(row):
    """Return the average of the available live light-sensor readings."""
    readings = [row.get(f"sensor{i}") for i in range(1, 5)]
    readings = [float(reading) for reading in readings if reading is not None]
    if not readings:
        return "N/A"
    return f"{sum(readings) / len(readings):,.2f}".rstrip("0").rstrip(".")


def build_reply(cursor, message):
    command = message.strip().lower()
    if "smartfarm" in command:
        return "SmartFarm commands: LIGHT, LIVE, NPK, STATUS, TEMPERATURE, or HUMIDITY."
    if "status" in command:
        light = latest(cursor, "SELECT sensor1, sensor2, sensor3, sensor4, timestamp FROM live_light_readings ORDER BY timestamp DESC LIMIT 1") or {}
        npk = latest(cursor, "SELECT n, p, k, moist, ph, timestamp FROM npksensor ORDER BY timestamp DESC LIMIT 1") or {}
        th = latest(cursor, "SELECT temperature, humidity, timestamp FROM temphumiditysensor ORDER BY timestamp DESC LIMIT 1") or {}
        return ("STATUS\n"
                f"Light average: {format_live_light_average(light)} lux\n"
                f"N/P/K: {npk.get('n', 'N/A')}/{npk.get('p', 'N/A')}/{npk.get('k', 'N/A')}\n"
                f"Moisture/pH: {npk.get('moist', 'N/A')}/{npk.get('ph', 'N/A')}\n"
                f"Temp/Humidity: {th.get('temperature', 'N/A')} deg C / {th.get('humidity', 'N/A')}%")
    if "live" in command:
        row = latest(cursor, "SELECT sensor1, sensor2, sensor3, sensor4, timestamp FROM live_light_readings ORDER BY timestamp DESC LIMIT 1")
        return "No live light data found." if not row else f"Live light average: {format_live_light_average(row)} lux. {row['timestamp']}"
    if "light" in command:
        row = latest(cursor, "SELECT hourlyAverage, timestamp FROM lightintensitysensor ORDER BY timestamp DESC LIMIT 1")
        return "No light data found." if not row else f"Light average: {row['hourlyAverage']} lux. {row['timestamp']}"
    if "npk" in command:
        row = latest(cursor, "SELECT temp, moist, ph, ec, n, p, k, timestamp FROM npksensor ORDER BY timestamp DESC LIMIT 1")
        return "No NPK data found." if not row else f"NPK: N {row['n']}, P {row['p']}, K {row['k']}; temp {row['temp']}, moisture {row['moist']}, pH {row['ph']}, EC {row['ec']}. {row['timestamp']}"
    if "temperature" in command or "humidity" in command:
        row = latest(cursor, "SELECT temperature, humidity, timestamp FROM temphumiditysensor ORDER BY timestamp DESC LIMIT 1")
        return "No temperature/humidity data found." if not row else f"Temperature: {row['temperature']} deg C; humidity: {row['humidity']}%. {row['timestamp']}"
    # Do not reply to carrier promotions, shortcode notifications, or other
    # non-command SMS messages.  They are still saved and then removed safely.
    return None


def process_messages(modem, connection):
    response = modem.list_messages()
    print("AT+CMGL response:\n" + response)
    if "+CMS ERROR" in response or "+CME ERROR" in response or "\nERROR" in response:
        raise RuntimeError(f"Could not list SIM messages: {response}")
    messages = parse_messages(response)
    cursor = connection.cursor(dictionary=True)
    replies_sent = 0
    try:
        for message in messages:
            if not message["message"]:
                print(f"Skipping empty SIM message {message['id']}")
                continue
            cursor.execute("SELECT id FROM sms_messages WHERE sender=%s AND received_at=%s AND message=%s LIMIT 1", (message["sender"], message["received_at"], message["message"]))
            existing = cursor.fetchone()
            if not existing:
                cursor.execute("INSERT INTO sms_messages (sender, received_at, message) VALUES (%s, %s, %s)", (message["sender"], message["received_at"], message["message"]))
                connection.commit()
                print(f"Saved SIM message {message['id']} from {message['sender']}")
            else:
                print(f"SIM message {message['id']} was already saved as database row {existing['id']}")
            reply = build_reply(cursor, message["message"])
            if reply is None:
                modem.delete_message(message["id"])
            elif modem.send_sms(message["sender"], reply):
                modem.delete_message(message["id"])
                replies_sent += 1
            else:
                print(f"Keeping SIM message {message['id']} for retry because reply failed")
    finally:
        cursor.close()
    return replies_sent


def main():
    try:
        with WorkerLock():
            modem = None
            connection = None
            try:
                modem = Modem()
                modem.initialize()
                connection = mysql.connector.connect(**DB_CONFIG)
                process_messages(modem, connection)
            except Exception:
                raise
            finally:
                if connection and connection.is_connected():
                    connection.close()
                if modem:
                    modem.close()
    except WorkerAlreadyRunning as exc:
        print(str(exc))
        return 2
    except Exception as exc:
        print(f"ERROR: {type(exc).__name__}: {exc}")
        return 1
    return 0


def run_daemon():
    """Run one long-lived worker; GM3 firmware is unreliable when reopened often."""
    modem = None
    connection = None
    try:
        with WorkerLock():
            while True:
                try:
                    if modem is None:
                        modem = Modem()
                        modem.initialize()
                        print("SMS daemon connected", flush=True)
                    if connection is None or not connection.is_connected():
                        connection = mysql.connector.connect(**DB_CONFIG)
                    replies_sent = process_messages(modem, connection)
                    if replies_sent:
                        # This GM3 revision can lock SIM storage after SMS
                        # submission. Reboot before the next poll instead of
                        # repeatedly issuing CMGL against a bad session.
                        modem.reset_module()
                        modem.close()
                        modem = None
                        time.sleep(60)
                        continue
                except KeyboardInterrupt:
                    break
                except Exception as exc:
                    print(f"Daemon error: {type(exc).__name__}: {exc}", flush=True)
                    if connection and connection.is_connected():
                        connection.close()
                    connection = None
                    if modem:
                        modem.reset_module()
                        modem.close()
                    modem = None
                    # Allow GSM registration and SIM storage to settle after
                    # the DTU reset before opening the port again.
                    time.sleep(60)
                    continue
                time.sleep(10)
    except WorkerAlreadyRunning as exc:
        print(str(exc), flush=True)
        return 2
    finally:
        try:
            if connection and connection.is_connected():
                connection.close()
        except Exception:
            pass
        try:
            if modem:
                modem.close()
        except Exception:
            pass
    return 0


if __name__ == "__main__":
    sys.exit(run_daemon() if "--daemon" in sys.argv else main())
