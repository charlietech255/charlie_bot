from fastapi import FastAPI, HTTPException, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Optional
import os
import nm as nm
import traceback
from dotenv import load_dotenv

# Load variables from .env file
load_dotenv()

app = FastAPI(title="Charlie Bot API")

# Security Configuration - Load Allowed Origins
origins_str = os.getenv("ALLOWED_ORIGINS", "https://dsnonline.store")
allowed_origins = [o.strip() for o in origins_str.split(",")]

app.add_middleware(
    CORSMiddleware,
    allow_origins=allowed_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Configuration - Loaded once at startup
GROQ_KEY = os.getenv("GROQ_API_KEY")
if GROQ_KEY:
    nm.GROQ_API_KEY = GROQ_KEY
    # CRITICAL: Update HEADERS in nm module as it's defined at load time
    nm.HEADERS["Authorization"] = f"Bearer {GROQ_KEY}"
else:
    print("WARNING: GROQ_API_KEY not found in environment!")

# Initialize MODEL
try:
    if not hasattr(nm, 'MODEL') or nm.MODEL is None:
        nm.MODEL = nm.get_model()
except Exception as e:
    print(f"Model init error: {e}")

class ChatRequest(BaseModel):
    message: str
    style: Optional[str] = "charlie"
    use_interpreter: Optional[bool] = False
    history: Optional[List[dict]] = None

class ChatResponse(BaseModel):
    response: str
    messages: List[dict]

@app.get("/styles")
async def get_styles():
    try:
        return {"styles": list(nm.get_jailbreak_styles().keys())}
    except Exception:
        raise HTTPException(status_code=500, detail="Could not fetch styles")

@app.post("/transcribe")
async def transcribe_voice(file: UploadFile = File(...)):
    """Receives audio file from frontend and returns transcribed text."""
    try:
        audio_data = await file.read()
        if not audio_data:
            raise HTTPException(status_code=400, detail="Empty audio file")
            
        text = nm.transcribe_audio(audio_data)
        return {"text": text}
    except Exception as e:
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/chat", response_model=ChatResponse)
async def chat(request: ChatRequest):
    try:
        # Ensure model is initialized
        if not nm.MODEL:
            nm.MODEL = nm.get_model()
        
        # 1. Handle History/Context
        if not request.history:
            msgs = nm.create_conversation(request.style)
        else:
            msgs = request.history

        # 2. DIRECT JAILBREAK MODE - NO INTERPRETER, NO REFUSALS
        # Send straight to the jailbreak model exactly like machine.py direct mode
        msgs.append({"role": "user", "content": request.message})
        reply = nm.stream_response(msgs, temperature=1.2, print_output=False)
        msgs.append({"role": "assistant", "content": reply})

        # 3. Context Window Management (Keep system prompt + last 11 messages)
        if len(msgs) > 12:
            msgs = [msgs[0]] + msgs[-11:]

        return {"response": msgs[-1]["content"], "messages": msgs}

    except Exception as e:
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    import sys

    print("\n" + "="*50)
    print("--- CHARLIE BOT API IS STARTING ---")
    print(f"URL: http://localhost:8001")
    print("To stop the server, press: CTRL + C")
    print("="*50 + "\n")

    try:
        # Render/Cloud deployment usually provides a PORT env variable
        port = int(os.getenv("PORT", 8001))
        uvicorn.run(app, host="0.0.0.0", port=port, log_level="info")
    except Exception as e:
        # Check for port conflict errors
        if "10048" in str(e) or "addr in use" in str(e).lower():
            print("\n" + "!"*60)
            print("ERROR: Port 8001 is already being used by another process.")
            print("Run this command in PowerShell to kill the old process:")
            print("Get-Process -Id (Get-NetTCPConnection -LocalPort 8001).OwningProcess | Stop-Process -Force")
            print("Then try running 'python api.py' again.")
            print("!"*60 + "\n")
        else:
            print(f"Unexpected error: {str(e)}")
            traceback.print_exc()
