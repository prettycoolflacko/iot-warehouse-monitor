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
        /* Kelas untuk input disabled agar terlihat jelas mati */
        input:disabled { opacity: 0.5; cursor: not-allowed; background-color: #374151; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
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
                    <div>
                        <h2 class="text-3xl font-semibold text-gray-200">Monitor Real-Time</h2>
                        <p id="mode-badge-dash" class="text-xs font-bold mt-1 text-accent-blue tracking-wide">MODE: LOADING...</p>
                    </div>
                    <span id="last-update" class="text-xs text-gray-500 font-mono">Syncing...</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50 hover:border-accent-blue/70 transition duration-300">
                        <div class="flex items-center justify-between">
                            <i data-lucide="thermometer" class="w-10 h-10 text-red-400"></i>
                            <span class="text-sm font-medium text-gray-400">Suhu Udara</span>
                        </div>
                        <p class="text-5xl font-extrabold mt-3 text-white"><span id="temp-value">--</span>°C</p>
                        <p class="text-sm text-gray-400 mt-1">Target Max: <span id="disp-max-temp">--</span>°C</p>
                    </div>

                    <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50 hover:border-accent-blue/70 transition duration-300">
                        <div class="flex items-center justify-between">
                            <i data-lucide="droplets" class="w-10 h-10 text-blue-400"></i>
                            <span class="text-sm font-medium text-gray-400">Kelembaban</span>
                        </div>
                        <p class="text-5xl font-extrabold mt-3 text-white"><span id="humid-value">--</span>%</p>
                        <p class="text-sm text-gray-400 mt-1">Status: Normal</p>
                    </div>

                    <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50 hover:border-accent-blue/70 transition duration-300">
                        <div class="flex items-center justify-between">
                            <i data-lucide="cloud-lightning" class="w-10 h-10 text-yellow-400"></i>
                            <span class="text-sm font-medium text-gray-400">Level Gas</span>
                        </div>
                        <p class="text-5xl font-extrabold mt-3 text-white"><span id="gas-value">--</span> PPM</p>
                        <p class="text-sm text-gray-400 mt-1" id="gas-status">--</p>
                    </div>
                </div>

                <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-accent-blue/70">
                    <h3 class="text-xl font-semibold mb-4 flex items-center text-accent-light">
                        <i data-lucide="fan" class="w-5 h-5 mr-2"></i>
                        Kontrol Ventilasi (Fan)
                    </h3>
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <div>
                            <p class="text-lg text-gray-300">Status Fan Saat Ini:</p>
                            <p id="fan-control-msg" class="text-xs text-gray-500 mt-1">--</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span id="fan-status-text" class="px-3 py-1 rounded-full text-sm font-bold bg-gray-600 text-white">LOADING...</span>
                            <button id="fan-toggle-btn" onclick="toggleFan()" disabled class="px-6 py-2 rounded-full font-bold transition duration-300 shadow-lg bg-gray-600 text-white cursor-not-allowed">
                                Loading...
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50">
                    <h3 class="text-xl font-semibold mb-4 text-gray-200">Log Sistem Live</h3>
                    <ul id="log-list" class="space-y-2 text-sm text-gray-400"></ul>
                </div>
            </section>

            <section id="settings" class="space-y-6 hidden">
                <h2 class="text-3xl font-semibold text-gray-200 mb-4">Pengaturan Sistem</h2>

                <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50">
                    <h3 class="text-xl font-semibold mb-4 text-accent-light">Mode Operasi</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="setMode('AUTO')" id="btn-mode-auto" class="p-4 rounded-lg border border-gray-600 text-center hover:bg-gray-700 transition">
                            <i data-lucide="cpu" class="w-8 h-8 mx-auto mb-2 text-blue-400"></i>
                            <div class="font-bold">OTOMATIS</div>
                            <div class="text-xs text-gray-400 mt-1">Fan dikontrol sensor. Setting Aktif.</div>
                        </button>
                        
                        <button onclick="setMode('MANUAL')" id="btn-mode-manual" class="p-4 rounded-lg border border-gray-600 text-center hover:bg-gray-700 transition">
                            <i data-lucide="hand" class="w-8 h-8 mx-auto mb-2 text-green-400"></i>
                            <div class="font-bold">MANUAL</div>
                            <div class="text-xs text-gray-400 mt-1">Fan dikontrol user. Setting Terkunci.</div>
                        </button>
                    </div>
                </div>

                <div class="bg-card-bg p-6 rounded-xl shadow-2xl border border-gray-700/50">
                    <h3 class="text-xl font-semibold mb-4 text-accent-light">Konfigurasi Ambang Batas (Auto)</h3>
                    <p id="setting-lock-msg" class="text-xs text-red-400 mb-4 hidden">⚠️ Mode MANUAL aktif. Ubah ke OTOMATIS untuk mengedit pengaturan ini.</p>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-700/50">
                            <label class="text-gray-300">Suhu Maksimum (°C)</label>
                            <input id="temp-threshold" type="number" class="bg-gray-800 border border-gray-700 rounded-lg p-2 w-24 text-right focus:ring-accent-blue focus:border-accent-blue text-white transition-colors">
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-700/50">
                            <label class="text-gray-300">Kelembaban Maksimum (%)</label>
                            <input id="humid-threshold" type="number" class="bg-gray-800 border border-gray-700 rounded-lg p-2 w-24 text-right focus:ring-accent-blue focus:border-accent-blue text-white transition-colors">
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <label class="text-gray-300">Gas Limit (PPM)</label>
                            <input id="gas-threshold" type="number" class="bg-gray-800 border border-gray-700 rounded-lg p-2 w-24 text-right focus:ring-accent-blue focus:border-accent-blue text-white transition-colors">
                        </div>
                        <button id="btn-save-settings" onclick="saveSettings()" class="w-full mt-4 py-3 bg-green-600 rounded-lg text-white font-semibold hover:bg-green-700 transition duration-200">
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
        const API_URL = 'api.php'; 
        
        let isSettingsLoaded = false;
        let currentSystemMode = "AUTO";

        function switchTab(tabId) {
            const tabs = ['dashboard', 'settings'];
            tabs.forEach(id => {
                document.getElementById(id).classList.toggle('hidden', id !== tabId);
                const btn = document.getElementById('tab-' + id);
                if(id === tabId) {
                    btn.classList.add('border-accent-blue', 'text-white');
                    btn.classList.remove('border-transparent', 'text-gray-400');
                } else {
                    btn.classList.remove('border-accent-blue', 'text-white');
                    btn.classList.add('border-transparent', 'text-gray-400');
                }
            });
        }

        async function fetchData() {
            try {
                const response = await fetch(API_URL + '?t=' + new Date().getTime());
                const data = await response.json();

                // 1. Update Data Sensor
                document.getElementById('temp-value').textContent = parseFloat(data.temperature).toFixed(1);
                document.getElementById('humid-value').textContent = data.humidity;
                document.getElementById('gas-value').textContent = data.gas_level;
                document.getElementById('gas-status').textContent = `Status: ${data.gas_status}`;
                document.getElementById('last-update').textContent = "Last sync: " + data.last_update.split(' ')[1];
                document.getElementById('disp-max-temp').textContent = data.settings.max_temp;

                // 2. Handle System Mode UI
                currentSystemMode = data.system_mode || "AUTO";
                updateModeUI(currentSystemMode);

                // 3. Update Fan UI
                updateFanUI(data.fan_status, currentSystemMode);

                // 4. Isi Form Settings (Sekali saja saat awal/reload)
                if (!isSettingsLoaded && data.settings) {
                    document.getElementById('temp-threshold').value = data.settings.max_temp;
                    document.getElementById('humid-threshold').value = data.settings.max_humid;
                    document.getElementById('gas-threshold').value = data.settings.gas_limit;
                    isSettingsLoaded = true; 
                }

            } catch (error) {
                console.error("Fetch Error:", error);
            }
        }

        // --- UI LOGIC UNTUK MODE (PENTING) ---
        function updateModeUI(mode) {
            const btnAuto = document.getElementById('btn-mode-auto');
            const btnManual = document.getElementById('btn-mode-manual');
            const badge = document.getElementById('mode-badge-dash');
            
            // Elemen yang akan dilock/unlock
            const inputTemp = document.getElementById('temp-threshold');
            const inputHumid = document.getElementById('humid-threshold');
            const inputGas = document.getElementById('gas-threshold');
            const btnSave = document.getElementById('btn-save-settings');
            const lockMsg = document.getElementById('setting-lock-msg');

            if (mode === 'AUTO') {
                // Style Tombol Mode
                btnAuto.className = "p-4 rounded-lg border-2 border-accent-blue bg-accent-blue/10 text-center cursor-default";
                btnManual.className = "p-4 rounded-lg border border-gray-600 text-center hover:bg-gray-700 transition cursor-pointer";
                badge.textContent = "MODE: OTOMATIS (SENSOR)";
                badge.className = "text-xs font-bold mt-1 text-blue-400 tracking-wide";

                // UNLOCK SETTINGS
                inputTemp.disabled = false;
                inputHumid.disabled = false;
                inputGas.disabled = false;
                btnSave.disabled = false;
                btnSave.classList.replace('bg-gray-600', 'bg-green-600');
                lockMsg.classList.add('hidden');

            } else { // MANUAL
                // Style Tombol Mode
                btnAuto.className = "p-4 rounded-lg border border-gray-600 text-center hover:bg-gray-700 transition cursor-pointer";
                btnManual.className = "p-4 rounded-lg border-2 border-green-500 bg-green-500/10 text-center cursor-default";
                badge.textContent = "MODE: MANUAL (USER)";
                badge.className = "text-xs font-bold mt-1 text-green-400 tracking-wide";

                // LOCK SETTINGS (SESUAI REQUEST)
                inputTemp.disabled = true;
                inputHumid.disabled = true;
                inputGas.disabled = true;
                btnSave.disabled = true;
                btnSave.classList.replace('bg-green-600', 'bg-gray-600'); // Jadi abu-abu
                lockMsg.classList.remove('hidden');
            }
        }

        function updateFanUI(status, mode) {
            const btn = document.getElementById('fan-toggle-btn');
            const statusText = document.getElementById('fan-status-text');
            const msg = document.getElementById('fan-control-msg');

            // Text Status
            if (status) {
                statusText.textContent = "ON";
                statusText.className = "px-3 py-1 rounded-full text-sm font-bold bg-green-600 text-white";
                btn.textContent = "Matikan Fan";
                btn.className = "px-6 py-2 rounded-full font-bold transition duration-300 shadow-lg bg-red-600 hover:bg-red-700 text-white transform hover:scale-105";
            } else {
                statusText.textContent = "OFF";
                statusText.className = "px-3 py-1 rounded-full text-sm font-bold bg-red-600 text-white";
                btn.textContent = "Nyalakan Fan";
                btn.className = "px-6 py-2 rounded-full font-bold transition duration-300 shadow-lg bg-green-600 hover:bg-green-700 text-white transform hover:scale-105";
            }

            // LOGIKA DISABLE TOMBOL FAN
            if (mode === 'AUTO') {
                btn.disabled = true; // Di Auto, user tidak boleh tekan
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.classList.remove('hover:scale-105');
                msg.textContent = "Dikontrol Sistem Otomatis";
            } else {
                btn.disabled = false; // Di Manual, user BOLEH tekan
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.classList.add('hover:scale-105');
                msg.textContent = "Kontrol Manual Aktif";
            }
        }

        // --- ACTIONS ---
        async function setMode(newMode) {
            if(newMode === currentSystemMode) return; // Klik tombol yg sama

            addLog("Mengubah Mode ke " + newMode + "...", "INFO");
            try {
                await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'set_mode', mode: newMode })
                });
                fetchData(); // Refresh UI segera
            } catch (e) { console.error(e); }
        }

        async function toggleFan() {
            if(currentSystemMode === 'AUTO') {
                alert("Ubah ke Mode Manual dulu di Pengaturan!");
                return;
            }
            // Logic sama seperti sebelumnya
            try {
                await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toggle_fan' })
                });
                fetchData();
            } catch (e) { console.error(e); }
        }

        async function saveSettings() {
            if(currentSystemMode === 'MANUAL') return; // Double protection

            const maxTemp = document.getElementById('temp-threshold').value;
            const maxHumid = document.getElementById('humid-threshold').value;
            const gasLim = document.getElementById('gas-threshold').value;

            addLog("Menyimpan Config...", "INFO");
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
                addLog("Config Tersimpan!", "SUCCESS");
                isSettingsLoaded = false;
                fetchData();
            } catch (e) { addLog("Gagal Simpan", "ERROR"); }
        }

        function addLog(msg, type) {
            const list = document.getElementById('log-list');
            const li = document.createElement('li');
            let color = type === "SUCCESS" ? "text-green-400" : (type === "ERROR" ? "text-red-400" : "text-yellow-400");
            li.innerHTML = `<span><span class="text-accent-light">${new Date().toLocaleTimeString()}</span> - ${msg}</span><span class="${color}">${type}</span>`;
            li.className = "flex justify-between items-center";
            list.prepend(li);
            if (list.children.length > 5) list.removeChild(list.lastChild);
        }

        document.addEventListener('DOMContentLoaded', () => {
            switchTab('dashboard');
            fetchData();
            setInterval(fetchData, 2000);
        });
    </script>
</body>
</html>