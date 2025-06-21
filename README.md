# 📝 PHP + Electron AI Notepad

An AI-powered notepad built using PHP (backend) and Electron (desktop wrapper). This app features voice input, hand gesture detection, dark/light mode toggle, font resizing, and multiple tabs — all wrapped into a sleek Windows app.

---

## ⚙️ Features

- 🎤 Voice-to-text using Web Speech API
- ✋ Hand gesture detection with MediaPipe
- 🌗 Dark & Light Mode toggle
- 🔠 Font size adjust (A+ / A-)
- ➕ Multi-tab note system
- 🎥 Optional camera integration
- 💻 Packaged as a Windows `.exe` using Electron

---

## 📁 Folder Structure

```bash
noteapp/
├── index.php         # Main PHP/HTML file
├── main.js           # Electron entry script
├── package.json      # Node dependencies and config
├── README.md         # You’re reading it 😉
└── dist/             # Output folder for .exe build
