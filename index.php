<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Gudang Buah (Real-Time IoT)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'dark-bg': '#0D1117', 
                        'card-bg': '#161B22', 
                        'accent-blue': '#6366F1', 
                        'accent-light': '#818CF8', 
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #161B22; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #4B5563; }
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="dark bg-dark-bg text-gray-100 transition-colors duration-300">

    <div id="app" class="min-h-screen flex flex-col">
        
        <header class="bg-card-bg shadow-lg border-b border-gray-700/50 p-4 sticky top-0 z-10">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold text-accent-light flex items-center">
                    <i data-lucide="warehouse" class="w-6 h-6 mr-2 text-accent-blue"></i>
                    Gudang Buah IoT
                </h1>
                <nav>
                    <button onclick="switchTab('dashboard')" id="tab-dashboard" class="px-4 py-2 font-medium text-white border-b-2 border-accent-blue transition duration-200 hover:text-accent-light">
                        Dashboard
                    </button>
                    <button onclick="switchTab('settings')" id="tab-settings" class="px-4 py-2 font-medium text-gray-400 border-b-2 border-transparent transition duration-200 hover:text-accent-light hover:border-accent-blue">
                        Pengaturan
                    </button>
                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto p-6 flex-grow w-full">
            
            <section id="dashboard" class="space-y-8">
                <div class="flex justify-between items-end">
                    <h2 class="text-3xl font-semibold text-gray-200">Monitor Real-Time</h2>
                    <span id="last-update" class="text-xs text-gray-500 font-mono">Syncing...</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50 hover:border-accent-blue/70 transition duration-300 transform hover:scale-[1.01]">
                        <div class="flex items-center justify-between">
                            <i data-lucide="thermometer" class="w-10 h-10 text-red-400"></i>
                            <span class="text-sm font-medium text-gray-400">Suhu Udara</span>
                        </div>
                        <p class="text-5xl font-extrabold mt-3 text-white"><span id="temp-value">--</span>°C</p>
                        <p class="text-sm text-gray-400 mt-1">Target Max: <span id="disp-max-temp">--</span>°C</p>
                    </div>

                    <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50 hover:border-accent-blue/70 transition duration-300 transform hover:scale-[1.01]">
                        <div class="flex items-center justify-between">
                            <i data-lucide="droplets" class="w-10 h-10 text-blue-400"></i>
                            <span class="text-sm font-medium text-gray-400">Kelembaban</span>
                        </div>
                        <p class="text-5xl font-extrabold mt-3 text-white"><span id="humid-value">--</span>%</p>
                        <p class="text-sm text-gray-400 mt-1">Status: Normal</p>
                    </div>

                    <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50 hover:border-accent-blue/70 transition duration-300 transform hover:scale-[1.01]">
                        <div class="flex items-center justify-between">
                            <i data-lucide="cloud-lightning" class="w-10 h-10 text-yellow-400"></i>
                            <span class="text-sm font-medium text-gray-400">Level Gas</span>
                        </div>
                        <p class="text-5xl font-extrabold mt-3 text-white"><span id="gas-value">--</span> PPM</p>
                        <p class="text-sm text-gray-400 mt-1" id="gas-status">Amoniak/Asap</p>
                    </div>
                </div>

                <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-accent-blue/70">
                    <h3 class="text-xl font-semibold mb-4 flex items-center text-accent-light">
                        <i data-lucide="fan" class="w-5 h-5 mr-2"></i>
                        Kontrol Ventilasi DC Fan
                    </h3>
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <p class="text-lg text-gray-300">Status Fan Saat Ini:</p>
                        <div class="flex items-center space-x-4">
                            <span id="fan-status-text" class="px-3 py-1 rounded-full text-sm font-bold bg-gray-600 text-white">LOADING...</span>
                            <button id="fan-toggle-btn" onclick="toggleFan()" disabled class="px-6 py-2 rounded-full font-bold transition duration-300 shadow-lg bg-gray-600 text-white cursor-not-allowed">
                                Loading...
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-4">Fan dikontrol melalui API VPS.</p>
                </div>
                
                <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50">
                    <h3 class="text-xl font-semibold mb-4 text-gray-200">Log Sistem Live</h3>
                    <ul id="log-list" class="space-y-2 text-sm text-gray-400">
                        <li class="flex justify-between items-center">
                            <span><span class="text-accent-light">System</span> - Menunggu data server...</span>
                            <span class="text-yellow-400">WAIT</span>
                        </li>
                    </ul>
                </div>
            </section>

            <section id="settings" class="space-y-6 hidden">
                <h2 class="text-3xl font-semibold text-gray-200 mb-4">Pengaturan Sistem (VPS)</h2>

                <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50">
                    <h3 class="text-xl font-semibold mb-4 text-accent-light">Perangkat Keras & Koneksi</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-700/50">
                            <label class="text-gray-300">Status Koneksi API</label>
                            <span id="api-status" class="px-3 py-1 rounded-full text-sm font-bold bg-green-600 text-white">ONLINE</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-700/50">
                            <label class="text-gray-300">Device ID</label>
                            <span class="text-gray-400 font-mono">ESP32-NODE-01</span>
                        </div>
                    </div>
                </div>

                <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50">
                    <h3 class="text-xl font-semibold mb-4 text-accent-light">Konfigurasi Ambang Batas</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-700/50">
                            <label for="temp-threshold" class="text-gray-300">Suhu Maksimum (°C)</label>
                            <input id="temp-threshold" type="number" class="bg-gray-800 border border-gray-700 rounded-lg p-2 w-24 text-right focus:ring-accent-blue focus:border-accent-blue text-white">
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-700/50">
                            <label for="humid-threshold" class="text-gray-300">Kelembaban Maksimum (%)</label>
                            <input id="humid-threshold" type="number" class="bg-gray-800 border border-gray-700 rounded-lg p-2 w-24 text-right focus:ring-accent-blue focus:border-accent-blue text-white">
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <label for="gas-threshold" class="text-gray-300">Gas Limit (PPM)</label>
                            <input id="gas-threshold" type="number" class="bg-gray-800 border border-gray-700 rounded-lg p-2 w-24 text-right focus:ring-accent-blue focus:border-accent-blue text-white">
                        </div>
                        <button onclick="saveSettings()" class="w-full mt-4 py-3 bg-green-600 rounded-lg text-white font-semibold hover:bg-green-700 transition duration-200 transform active:scale-95">
                            Simpan ke Server
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-card-bg p-4 text-center border-t border-gray-700/50 text-gray-500 text-sm">
            &copy; 2025 Monitoring Gudang Buah IoT. Rakha Project.
        </footer>
    </div>

    <script>
        lucide.createIcons();
        
        // Konfigurasi API
        const API_URL = 'api.php'; 
        
        // State Variables
        let isSettingsLoaded = false; // Agar input tidak tertimpa saat user mengetik
        let currentFanStatus = false;

        // --- 1. Tab Switching Logic ---
        function switchTab(tabId) {
            const tabs = ['dashboard', 'settings'];
            tabs.forEach(id => {
                const section = document.getElementById(id);
                const button = document.getElementById('tab-' + id);
                if (id === tabId) {
                    section.classList.remove('hidden');
                    button.classList.add('border-accent-blue', 'text-white');
                    button.classList.remove('border-transparent', 'text-gray-400');
                } else {
                    section.classList.add('hidden');
                    button.classList.remove('border-accent-blue', 'text-white');
                    button.classList.add('border-transparent', 'text-gray-400');
                }
            });
        }

        // --- 2. Fetch Data dari API (Interval) ---
        async function fetchData() {
            try {
                const response = await fetch(API_URL + '?t=' + new Date().getTime()); // Anti-cache
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();

                // A. Update Dashboard UI
                document.getElementById('temp-value').textContent = parseFloat(data.temperature).toFixed(1);
                document.getElementById('humid-value').textContent = data.humidity;
                document.getElementById('gas-value').textContent = data.gas_level;
                document.getElementById('gas-status').textContent = `Status: ${data.gas_status}`;
                document.getElementById('last-update').textContent = "Last sync: " + data.last_update.split(' ')[1];
                document.getElementById('disp-max-temp').textContent = data.settings.max_temp;

                // B. Update Fan UI
                updateFanUI(data.fan_status);

                // C. Update Settings Input (Hanya sekali saat pertama load agar tidak mengganggu user ngetik)
                if (!isSettingsLoaded && data.settings) {
                    document.getElementById('temp-threshold').value = data.settings.max_temp;
                    document.getElementById('humid-threshold').value = data.settings.max_humid;
                    document.getElementById('gas-threshold').value = data.settings.gas_limit;
                    isSettingsLoaded = true; 
                }

                document.getElementById('api-status').textContent = "ONLINE";
                document.getElementById('api-status').classList.replace('bg-red-600', 'bg-green-600');

            } catch (error) {
                console.error("Fetch Error:", error);
                document.getElementById('api-status').textContent = "OFFLINE";
                document.getElementById('api-status').classList.replace('bg-green-600', 'bg-red-600');
            }
        }

        // --- 3. Fan Control Logic ---
        function updateFanUI(status) {
            currentFanStatus = status;
            const btn = document.getElementById('fan-toggle-btn');
            const statusText = document.getElementById('fan-status-text');

            btn.disabled = false;
            btn.classList.remove('cursor-not-allowed', 'bg-gray-600');

            if (status) {
                statusText.textContent = "AKTIF";
                statusText.className = "px-3 py-1 rounded-full text-sm font-bold bg-green-600 text-white";
                btn.textContent = "Matikan Fan";
                btn.className = "px-6 py-2 rounded-full font-bold transition duration-300 shadow-lg bg-red-600 hover:bg-red-700 text-white transform hover:scale-105";
            } else {
                statusText.textContent = "MATI";
                statusText.className = "px-3 py-1 rounded-full text-sm font-bold bg-red-600 text-white";
                btn.textContent = "Nyalakan Fan";
                btn.className = "px-6 py-2 rounded-full font-bold transition duration-300 shadow-lg bg-green-600 hover:bg-green-700 text-white transform hover:scale-105";
            }
        }

        async function toggleFan() {
            addLog("Mengirim perintah Fan...", "INFO");
            try {
                await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toggle_fan' })
                });
                // Fetch segera untuk update UI
                fetchData();
                addLog("Status Fan berhasil diubah.", "SUCCESS");
            } catch (error) {
                addLog("Gagal mengubah fan.", "ERROR");
            }
        }

        // --- 4. Settings Logic ---
        async function saveSettings() {
            const maxTemp = document.getElementById('temp-threshold').value;
            const maxHumid = document.getElementById('humid-threshold').value;
            const gasLim = document.getElementById('gas-threshold').value;

            addLog("Menyimpan pengaturan...", "INFO");
            showNotification("Menyimpan ke Server...");

            try {
                await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_settings',
                        max_temp: maxTemp,
                        max_humid: maxHumid,
                        gas_limit: gasLim
                    })
                });
                
                showNotification("Pengaturan Tersimpan!");
                addLog("Pengaturan ambang batas diperbarui.", "SUCCESS");
                
                // Refresh data untuk memastikan sinkron
                isSettingsLoaded = false; // Force reload settings text
                fetchData();
            } catch (error) {
                showNotification("Gagal Menyimpan!");
                addLog("Gagal menyimpan pengaturan.", "ERROR");
            }
        }

        // --- 5. Utilities (Log & Notif) ---
        function showNotification(message) {
            let notification = document.createElement('div');
            notification.className = 'fixed bottom-4 right-4 bg-accent-blue text-white p-4 rounded-lg shadow-xl z-50 transition-opacity duration-300';
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function addLog(msg, type) {
            const list = document.getElementById('log-list');
            const li = document.createElement('li');
            li.className = "flex justify-between items-center animate-pulse"; // Animasi kecil saat masuk
            
            let color = "text-gray-400";
            if(type === "SUCCESS") color = "text-green-400";
            if(type === "ERROR") color = "text-red-400";
            if(type === "INFO") color = "text-yellow-400";

            const time = new Date().toLocaleTimeString();
            li.innerHTML = `<span><span class="text-accent-light">${time}</span> - ${msg}</span><span class="${color}">${type}</span>`;
            
            // Limit log items
            list.prepend(li);
            if (list.children.length > 5) list.removeChild(list.lastChild);
            
            setTimeout(() => li.classList.remove('animate-pulse'), 1000);
        }

        // Start App
        document.addEventListener('DOMContentLoaded', () => {
            switchTab('dashboard');
            fetchData();
            setInterval(fetchData, 2000); // Auto refresh tiap 2 detik
        });
    </script>
</body>
</html>