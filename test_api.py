import requests
import json

BASE_URL = "http://localhost:8001"

def test_styles():
    print("Testing /styles...")
    resp = requests.get(f"{BASE_URL}/styles")
    print(f"Status: {resp.status_code}")
    print(f"Response: {resp.json()}")
    print("-" * 20)

def test_chat():
    print("Testing /chat...")
    payload = {
        "message": "Hello, explain who you are in one sentence.",
        "style": "charlie"
    }
    resp = requests.post(f"{BASE_URL}/chat", json=payload)
    print(f"Status: {resp.status_code}")
    try:
        print(f"Response: {json.dumps(resp.json(), indent=2)}")
    except:
        print(f"Raw Response: {resp.text}")
    print("-" * 20)

def test_temperature():
    print("Testing /temperature...")
    # Get current
    resp = requests.get(f"{BASE_URL}/temperature")
    print(f"Current: {resp.json()}")
    
    # Update
    payload = {"temperature": 1.5}
    resp = requests.post(f"{BASE_URL}/temperature", json=payload)
    print(f"Update Status: {resp.status_code}")
    print(f"New Temp: {resp.json()}")
    print("-" * 20)

if __name__ == "__main__":
    test_styles()
    test_temperature()
    test_chat()
