# Charlie Bot | Premium AI-Chat

A high-performance, glassmorphism-inspired chatbot powered by FastAPI and Groq's LLMs. Highlighting advanced jailbreak styles, voice-to-text integration, and interactive markdown rendering.

## 🚀 Key Features

- **Advanced Voice Messaging**: Seamless voice-to-text integration using Groq's Whisper v3 API.
- **Glassmorphism UI**: Beautifully designed dark-mode interface with smooth animations and responsive drawer.
- **Always-on Interpreter**: Intelligent reasoning provided by the internal interpreter module.
- **Premium Markdown Support**: Full support for headers, tables, nested lists, and bold text via `marked.js`.
- **Bot Style Personalities**: Select between 7 unique bot styles (Charlie, Kissu, Maria, Suzzie, Silas, Amina, Anna).
- **Interactive Responses**: Instant 'Copy' and 'Share' actions for every response.

## 🛠️ Setup Instructions

### 1. Prerequisites
- Python 3.8+
- [Groq API Key](https://console.groq.com/)

### 2. Installation
Clone the repository:
```bash
git clone <your-repo-url>
cd <repo-name>
```

Install dependencies:
```bash
pip install -r requirements.txt
```

### 3. Configuration
Rename `.env.example` to `.env` and add your Groq API Key:
```
GROQ_API_KEY=your_key_here
```

### 4. Running the Application
Start the backend server:
```bash
python api.py
```
*The API will run on `http://localhost:8001`.*

Finally, open `index.html` in your browser to start chatting!

## 🔐 Security & Deployment
- Ensure `.env` is **never committed** to version control (pre-configured in `.gitignore`).
- For production deployment, review CORS settings in `api.py`.
