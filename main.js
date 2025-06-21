const { app, BrowserWindow } = require('electron');

let win;

function createWindow() {
  win = new BrowserWindow({
    width: 1000,
    height: 800,
    backgroundColor: '#0f172a',  // match your dark bg
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
    }
  });

  win.loadURL('http://localhost:8080/notepad-app/notepad-app/index.php');  // <-- YOUR XAMPP PHP app URL
}

app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});

