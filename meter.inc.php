<?php
/**
 * meter.inc.php — biblioteka: odczyt 3-fazowego miernika energii BleBox (np. miernik Pstryk)
 * przez lokalne API HTTP (/state), zapis do MySQL/MariaDB lub CSV.
 */

$cfg = __DIR__ . '/config.php';
if (!file_exists($cfg)) {
    fwrite(STDERR, "Brak config.php — skopiuj config.example.php do config.php i uzupelnij.\n");
    exit(1);
}
require $cfg;

const METER_TZ = 'Europe/Warsaw';

// Kolumny tabeli meter_readings w kolejnosci zapisu (tez kolejnosc kolumn CSV)
const METER_COLS = [
    'power', 'reactive_power', 'apparent_power', 'frequency', 'energy_import', 'energy_export',
    'l1_power', 'l1_voltage', 'l1_current',
    'l2_power', 'l2_voltage', 'l2_current',
    'l3_power', 'l3_voltage', 'l3_current',
];

/**
 * Jeden odczyt miernika. Zwraca [kolumna => wartosc] albo false.
 * Jednostki API (sprawdzone z licznikiem operatora i tozsamoscia S = U * I):
 *   moce: W / var / VA · napiecie: 0,1 V · prad: mA · czestotliwosc: mHz · energia: Wh
 */
function meter_read() {
    $ch = curl_init('http://' . METER_IP . '/state');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $data = json_decode((string)curl_exec($ch), true);
    curl_close($ch);
    if (!isset($data['multiSensor']['sensors'])) return false;

    // id czujnika: 0 = suma / caly obwod, 1-3 = fazy L1-L3
    $map = [
        'activePower'         => ['power', 1],
        'reactivePower'       => ['reactive_power', 1],
        'apparentPower'       => ['apparent_power', 1],
        'voltage'             => ['voltage', 10],
        'current'             => ['current', 1000],
        'frequency'           => ['frequency', 1000],
        'forwardActiveEnergy' => ['energy_import', 1000],
        'reverseActiveEnergy' => ['energy_export', 1000],
    ];
    $all = [];
    foreach ($data['multiSensor']['sensors'] as $s) {
        if (!isset($map[$s['type'] ?? ''])) continue;
        [$name, $div] = $map[$s['type']];
        $prefix = ($s['id'] ?? 0) == 0 ? '' : 'l' . $s['id'] . '_';
        $all[$prefix . $name] = round($s['value'] / $div, 3);
    }

    $row = [];
    foreach (METER_COLS as $c) $row[$c] = $all[$c] ?? null;
    return $row;
}

function meter_db() {
    if (DB_NAME === '') return null;  // tryb CSV
    try {
        return new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    } catch (mysqli_sql_exception $e) {
        fwrite(STDERR, "DB: " . $e->getMessage() . "\n");
        exit(1);
    }
}

// Zapis do tabeli meter_readings albo wypis CSV na stdout, gdy brak bazy.
// Kluczem jest czas UTC — przy zmianie czasu na zimowy godzina 02:00 czasu polskiego wystepuje dwa razy.
function meter_save($db, $row) {
    $utc   = gmdate('Y-m-d H:i:s');
    $local = (new DateTime('now', new DateTimeZone(METER_TZ)))->format('Y-m-d H:i:s');
    if (!$db) {
        echo "$utc;$local;" . implode(';', array_map(fn($v) => $v ?? '', $row)) . "\n";
        return;
    }
    $vals = array_map(fn($v) => $v === null ? 'NULL' : (float)$v, $row);
    $db->query("INSERT IGNORE INTO meter_readings (ts_utc, ts_local, " . implode(', ', array_keys($row)) . ")
                VALUES ('$utc', '$local', " . implode(', ', $vals) . ")");
}
