<?php
// File: api.php
// Penulis: Rakha Taufiqurrahman Faisal Aziz
// Update: Menambahkan fitur penyimpanan Settings (Threshold)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$file_db = 'iot_data.json';

// 1. Inisialisasi Data Default (Termasuk Settings)
if (!file_exists($file_db)) {
    $initial_data = [
        "temperature" => 0,
        "humidity" => 0,
        "gas_level" => 0,
        "gas_status" => "SAFE",
        "fan_status" => false, 
        // Data Settings (Ambang Batas)
        "settings" => [
            "max_temp" => 28,
            "max_humid" => 75,
            "gas_limit" => 500
        ],
        "last_update" => date("Y-m-d H:i:s")
    ];
    file_put_contents($file_db, json_encode($initial_data));
}

// Baca data saat ini
$current_data = json_decode(file_get_contents($file_db), true);

// 2. Handle Request POST
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
        }
        
        // B. Aksi dari Website
        if (isset($input['action'])) {
            // Toggle Fan
            if ($input['action'] == 'toggle_fan') {
                $current_data['fan_status'] = !$current_data['fan_status'];
            }
            
            // Simpan Settings Baru
            if ($input['action'] == 'save_settings') {
                $current_data['settings']['max_temp'] = $input['max_temp'];
                $current_data['settings']['max_humid'] = $input['max_humid'];
                $current_data['settings']['gas_limit'] = $input['gas_limit'];
            }
        }

        // Simpan ke file
        file_put_contents($file_db, json_encode($current_data));
    }
}

// 3. Output Data
echo json_encode($current_data);
?>