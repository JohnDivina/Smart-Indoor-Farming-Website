import serial
import time

PORT = "COM5"       # change if needed
BAUDRATE = 115200
NUMBER = "+639950246872"
MESSAGE = "Test from SmartFarm GPRS module"

ser = serial.Serial(PORT, BAUDRATE, timeout=2, write_timeout=5)
time.sleep(1)

def send(cmd, wait=1):
    ser.write((cmd + "\r\n").encode())
    time.sleep(wait)
    resp = ser.read(ser.in_waiting).decode("utf-8", errors="ignore")
    print(f">> {cmd}\n<< {resp.strip()}\n")
    return resp

send("AT", 1)
send("ATE0", 1)
send("AT+CPIN?", 1)
send("AT+CREG?", 1)
send("AT+CMGF=1", 1)   # text mode

# Send SMS
ser.write(f'AT+CMGS="{NUMBER}"\r\n'.encode())
time.sleep(2)
prompt = ser.read(ser.in_waiting).decode("utf-8", errors="ignore")
print(f"Prompt: {prompt.strip()}")

if ">" in prompt:
    ser.write((MESSAGE + chr(26)).encode())  # chr(26) = Ctrl+Z
    time.sleep(10)
    result = ser.read(ser.in_waiting).decode("utf-8", errors="ignore")
    print(f"Result: {result.strip()}")
    if "+CMGS" in result:
        print("\n✅ SMS SENT SUCCESSFULLY!")
    else:
        print("\n❌ SMS send failed or timed out")
else:
    print("❌ No > prompt received - module may not be registered")

ser.close()
