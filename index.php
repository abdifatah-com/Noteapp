<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AI-Powered Notepad</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0f172a;
      --text: #f8fafc;
      --primary: #38bdf8;
      --primary-hover: #0ea5e9;
      --btn-bg: #1e293b;
      --btn-text: #f8fafc;
      --border: #475569;
      --focus: #38bdf8;
      --glass: rgba(255, 255, 255, 0.05);
    }

    * {
      box-sizing: border-box;
      transition: all 0.3s ease;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: var(--text);
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    .left {
      flex: 2;
      display: flex;
      flex-direction: column;
      padding: 1.5rem;
      backdrop-filter: blur(10px);
    }

    .tab-bar {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    .tab {
      display: flex;
      align-items: center;
      padding: 0.5rem 0.8rem;
      background-color: var(--btn-bg);
      border: 1px solid var(--border);
      border-radius: 8px;
      cursor: pointer;
    }

    .tab.active {
      background-color: var(--primary-hover);
      color: white;
      font-weight: bold;
    }

    .tab:hover {
      background-color: var(--primary);
    }

    .tab .close {
      margin-left: 0.5rem;
      font-weight: bold;
      color: red;
      cursor: pointer;
    }

    .controls {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    button {
      padding: 0.7rem 1.2rem;
      background: var(--btn-bg);
      color: var(--btn-text);
      border: 2px solid var(--border);
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
    }

    button:hover {
      background-color: var(--primary-hover);
      color: #fff;
      border-color: var(--primary-hover);
    }

    textarea {
      flex: 1;
      width: 100%;
      background-color: var(--glass);
      border: 2px solid var(--border);
      border-radius: 1rem;
      padding: 1rem;
      font-size: 1rem;
      color: var(--text);
      resize: none;
    }

    .right {
      flex: 1;
      background-color: #1e293b;
      border-left: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 1rem;
      gap: 1rem;
    }

    video {
      width: 100%;
      border-radius: 12px;
      border: 2px solid var(--primary);
    }

    .light-mode {
      background-color: #ffffff;
      color: #0f172a;
    }

    .light-mode textarea {
      background-color: #f1f5f9;
      color: #0f172a;
    }

    .light-mode .tab {
      background-color: #e2e8f0;
      color: #0f172a;
    }

    .light-mode .tab.active {
      background-color: #0ea5e9;
      color: #fff;
    }

    .light-mode .controls button {
      background-color: #cbd5e1;
      color: #1e293b;
    }

    .light-mode .right {
      background-color: #e2e8f0;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
</head>
<body>
  <div class="left">
    <div class="tab-bar" id="tabBar"></div>
    <div class="controls">
      <button onclick="toggleDarkMode()">🌙 Dark Mode</button>
      <button onclick="adjustFontSize(1)">A+</button>
      <button onclick="adjustFontSize(-1)">A-</button>
      <button onclick="toggleVoice()">🎤 Voice</button>
      <button onclick="readAloud()">🔊 Read</button>
      <button onclick="toggleCamera()">🎥 Sing</button>
    </div>
    <textarea id="notepad" placeholder="Start typing or speak..."></textarea>
  </div>
  <div class="right">
    <p>Camera & AI Detection (Optional)</p>
    <video id="video" autoplay muted playsinline></video>
  </div>
  <script>
    let dark = true;
    const body = document.body;
    const textarea = document.getElementById("notepad");
    let currentFontSize = 16;

    function toggleDarkMode() {
      dark = !dark;
      body.classList.toggle('light-mode', !dark);
    }

    function adjustFontSize(change) {
      currentFontSize += change;
      currentFontSize = Math.max(10, Math.min(40, currentFontSize));
      textarea.style.fontSize = `${currentFontSize}px`;
    }

    let tabs = [""];
    let currentTabIndex = 0;

    function renderTabs() {
      const tabBar = document.getElementById("tabBar");
      tabBar.innerHTML = "";
      tabs.forEach((_, i) => {
        const tab = document.createElement("div");
        tab.className = "tab" + (i === currentTabIndex ? " active" : "");
        tab.innerHTML = `Tab ${i + 1} <span class="close" onclick="closeTab(${i}, event)">✖</span>`;
        tab.onclick = () => switchTab(i);
        tabBar.appendChild(tab);
      });
      const addBtn = document.createElement("div");
      addBtn.className = "tab";
      addBtn.textContent = "➕";
      addBtn.onclick = addTab;
      tabBar.appendChild(addBtn);
    }

    function switchTab(index) {
      tabs[currentTabIndex] = textarea.value;
      currentTabIndex = index;
      textarea.value = tabs[currentTabIndex];
      renderTabs();
    }

    function addTab() {
      tabs.push("");
      switchTab(tabs.length - 1);
    }

    function closeTab(index, event) {
      event.stopPropagation();
      tabs.splice(index, 1);
      if (currentTabIndex >= tabs.length) currentTabIndex = tabs.length - 1;
      textarea.value = tabs[currentTabIndex] || "";
      renderTabs();
    }

    textarea.addEventListener("input", () => {
      tabs[currentTabIndex] = textarea.value;
    });

    renderTabs();

    let recognition;
    let listening = false;

    function toggleVoice() {
      if (!('webkitSpeechRecognition' in window)) {
        alert("Speech Recognition not supported");
        return;
      }

      if (!recognition) {
        recognition = new webkitSpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = 'en-GB';

        recognition.onresult = function(event) {
          let transcript = '';
          for (let i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
              transcript += event.results[i][0].transcript + ' ';
            }
          }
          textarea.value += transcript;
          tabs[currentTabIndex] = textarea.value;
        };

        recognition.onerror = (e) => console.error("Speech error", e);
        recognition.onend = () => { if (listening) recognition.start(); };
      }

      if (listening) {
        recognition.stop();
        listening = false;
      } else {
        recognition.start();
        listening = true;
      }
    }

    function readAloud() {
      const msg = new SpeechSynthesisUtterance(textarea.value);
      msg.lang = 'en-GB';
      window.speechSynthesis.speak(msg);
    }

    let cameraStream = null;
    let mpCamera = null;
    const video = document.getElementById("video");

    async function toggleCamera() {
      if (!cameraStream) {
        try {
          cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
          video.srcObject = cameraStream;
          startMediaPipeCamera();
        } catch (err) {
          console.error("Camera error:", err);
        }
      } else {
        if (mpCamera) {
          mpCamera.stop();
          mpCamera = null;
        }
        const tracks = cameraStream.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;
        cameraStream = null;
      }
    }

    function startMediaPipeCamera() {
      if (mpCamera) mpCamera.stop();
      mpCamera = new Camera(video, {
        onFrame: async () => {
          await hands.send({ image: video });
        },
        width: 640,
        height: 480
      });
      mpCamera.start();
    }

    const hands = new Hands({
      locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`
    });

    hands.setOptions({
      maxNumHands: 1,
      modelComplexity: 1,
      minDetectionConfidence: 0.5,
      minTrackingConfidence: 0.5
    });

    hands.onResults(results => {
      console.log('Hand results:', results);
    });

  // Hand on result 
  hands.onResults(results => {
  if (!results.multiHandLandmarks || results.multiHandLandmarks.length === 0) return;

  const landmarks = results.multiHandLandmarks[0];

  // Function to count extended fingers
  function countFingers(landmarks) {
    let count = 0;

    // Thumb
    if (landmarks[4].x < landmarks[3].x) count++; // Left hand (mirror)

    // Index, Middle, Ring, Pinky
    const tips = [8, 12, 16, 20];
    const pips = [6, 10, 14, 18];

    for (let i = 0; i < tips.length; i++) {
      if (landmarks[tips[i]].y < landmarks[pips[i]].y) count++;
    }

    return count;
  }

  const fingerCount = countFingers(landmarks);

  let message = "";

  switch (fingerCount) {
    case 0:
      message = "👋 Goodbye";
      break;
    case 2:
      message = "🙏 Thank you";
      break;
    case 3:
      message = "🤟 I love you";
      break;
    case 5:
      message = "👋 Hello";
      break;
    default:
      return; // Ignore other cases
  }

  // Avoid duplicate messages
  if (!textarea.value.includes(message)) {
    textarea.value += `\n${message}`;
    tabs[currentTabIndex] = textarea.value;
  }
});

  </script>
</body>
</html>