# -*- coding: utf-8 -*-
"""
Simple script to send a daily SMS using the same GSM modem as receive_sms.py
Schedule this with Windows Task Scheduler at 2:00 PM daily.
"""
import time
import sys
import atexit
import serial

# CONFIG
PORT = "COM11"         # Change if your modem is on a different COM port
BAUDRATE = 115200
TIMEOUT = 2
RECIPIENTS = [
    "+639950246872",
]
MESSAGE = "Good day Farmer! please check all components and crops before leaving the premises. Thank you!"

ser = None


def close_ser():
    try:
        if ser and ser.is_open:
            ser.close()
    except Exception:
        pass


def send_command(cmd: str, wait_time: float = 3.0) -> str:
    """Write an AT command and read until OK/ERROR or timeout."""
    ser.write((cmd + "\r\n").encode("utf-8"))
    end = time.time() + max(wait_time, 0.5)
    buf = ""
    while time.time() < end:
        time.sleep(0.2)
        try:
            chunk = ser.read(ser.in_waiting or 1).decode("utf-8", errors="ignore")
        except Exception:
            chunk = ""
        if chunk:
            buf += chunk
            if "\r\nOK\r\n" in buf or "\nOK\n" in buf or "ERROR" in buf:
                break
    return buf.strip()


def format_phone_number(phone_number: str) -> str:
    if phone_number.startswith('+'):
        return phone_number
    if phone_number.startswith('0') and len(phone_number) >= 10:
        return "+63" + phone_number[1:]
    return phone_number


def split_gsm(text: str, single_limit: int = 160, multi_limit: int = 153):
    if len(text) <= single_limit:
        return [text]
    parts = []
    idx = 0
    reserved = 8  # space for (n/n)
    while idx < len(text):
        remaining = text[idx:]
        cap = max(1, multi_limit - reserved)
        chunk = remaining[:cap]
        if len(remaining) > cap:
            last_nl = chunk.rfind("\n")
            last_sp = chunk.rfind(" ")
            cut = max(last_nl, last_sp)
            if cut >= 20:
                chunk = chunk[:cut]
        parts.append(chunk)
        idx += len(chunk)
    total = len(parts)
    return [f"({i+1}/{total}) {p}" for i, p in enumerate(parts)]


def send_sms(number: str, message: str) -> None:
    number = format_phone_number(number)

    # Basic init
    print("AT =>\n" + send_command("AT", 3))
    print("ATE0 =>\n" + send_command("ATE0", 3))
    print("AT+CMEE=2 =>\n" + send_command("AT+CMEE=2", 3))
    print("AT+CSCS=\"GSM\" =>\n" + send_command("AT+CSCS=\"GSM\"", 3))
    print("AT+CMGF=1 =>\n" + send_command("AT+CMGF=1", 3))
    print("AT+CSCA? =>\n" + send_command("AT+CSCA?", 3))

    # Clear buffers
    try:
        ser.reset_input_buffer()
    except Exception:
        pass

    segments = split_gsm(message)
    for i, seg in enumerate(segments):
        ser.write(f'AT+CMGS="{number}"\r\n'.encode('utf-8'))
        # wait for '>'
        prompt = ""
        start = time.time()
        while time.time() - start < 10:
            time.sleep(0.2)
            chunk = ser.read(ser.in_waiting or 1).decode('utf-8', errors='ignore')
            if chunk:
                prompt += chunk
                if '>' in prompt:
                    break
        print(f"Prompt (part {i+1}/{len(segments)}): {prompt}")
        if '>' not in prompt:
            print("No prompt '>' received; aborting.")
            return
        ser.write((seg + chr(26)).encode('utf-8'))
        # wait for result
        result = ""
        start2 = time.time()
        while time.time() - start2 < 90:
            time.sleep(0.5)
            n = ser.in_waiting
            chunk2 = ser.read(n if n else 64).decode('utf-8', errors='ignore')
            if chunk2:
                result += chunk2
                if '+CMGS' in result or '\r\nOK\r\n' in result or 'ERROR' in result:
                    break
        print(f"Result (part {i+1}/{len(segments)}): {result}")
        time.sleep(1.5)


def main():
    global ser
    try:
        ser = serial.Serial(PORT, BAUDRATE, timeout=TIMEOUT)
        time.sleep(2)
    except Exception as e:
        print(f"Failed to open serial port {PORT}: {e}")
        sys.exit(1)
    atexit.register(close_ser)

    for num in RECIPIENTS:
        send_sms(num, MESSAGE)


if __name__ == "__main__":
    main()
