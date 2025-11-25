<?php
// File: api.php
// Penulis: Rakha Taufiqurrahman Faisal Aziz
// Update: Menambahkan Fitur Mode Selector (Manual vs Auto)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$file_db = 'iot_data.json';

// 1. Inisialisasi Data Default
if (!file_exists($file_db)) {
    $initial_data = [
        "temperature" => 0,
        "humidity" => 0,
        "gas_level" => 0,
        "gas_status" => "SAFE",
        "fan_status" => false, 
        "system_mode" => "AUTO", // Opsi: 'AUTO' atau 'MANUAL'
        "settings" => [
            "max_temp" => 28,
            "max_humid" => 75,
            "gas_limit" => 500
        ],
        "last_update" => date("Y-m-d H:i:s")
    ];
    file_put_contents($file_db, json_encode($initial_data));
}

$current_data = json_decode(file_get_contents($file_db), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    if ($input) {
        // A. Update Sensor (Dari ESP32)
        if (isset($input['temperature'])) {
            $current_data['temperature'] = $input['temperature'];
            $current_data['humidity'] = $input['humidity'];
            $current_data['gas_level'] = $input['gas_level'];
            $current_data['gas_status'] = $input['gas_status'];
            $current_data['last_update'] = date("Y-m-d H:i:s");

            // --- LOGIKA UTAMA ---
            // Hanya jalankan logika otomatis jika Mode == AUTO
            if ($current_data['system_mode'] === 'AUTO') {
                $max_t = $current_data['settings']['max_temp'];
                $max_g = $current_data['settings']['gas_limit'];

                // Jika Panas ATAU Gas Bahaya -> Fan NYALA
                if ($input['temperature'] >= $max_t || $input['gas_level'] >= $max_g) {
                    $current_data['fan_status'] = true;
                } else {
                    $current_data['fan_status'] = false;
                }
            }
            // Jika Mode == MANUAL, server diam saja (fan_status tetap sesuai input user terakhir)
        }
        
        // B. Aksi dari Website
        if (isset($input['action'])) {
            
            // 1. Ganti Mode (Manual / Auto)
            if ($input['action'] == 'set_mode') {
                $current_data['system_mode'] = $input['mode']; // 'MANUAL' or 'AUTO'
            }

            // 2. Toggle Fan (Hanya berfungsi jika Mode MANUAL)
            // Namun kita validasi di PHP juga biar aman
            if ($input['action'] == 'toggle_fan') {
                if ($current_data['system_mode'] === 'MANUAL') {
                    $current_data['fan_status'] = !$current_data['fan_status'];
                }
            }
            
            // 3. Simpan Settings (Hanya berfungsi jika Mode AUTO/Allowed)
            if ($input['action'] == 'save_settings') {
                $current_data['settings']['max_temp'] = $input['max_temp'];
                $current_data['settings']['max_humid'] = $input['max_humid'];
                $current_data['settings']['gas_limit'] = $input['gas_limit'];
            }
        }

        file_put_contents($file_db, json_encode($current_data));
    }
}

echo json_encode($current_data);
?>