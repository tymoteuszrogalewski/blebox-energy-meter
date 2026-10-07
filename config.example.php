<?php
// Skopiuj do config.php i uzupelnij. config.php jest w .gitignore.

define('METER_IP',       '192.168.1.50');  // adres miernika w sieci domowej (aplikacja BleBox / router)
define('METER_INTERVAL', 10);              // co ile sekund zapisywac odczyt (meter_log.php)

// Baza MySQL / MariaDB. DB_NAME = '' -> zamiast zapisu do bazy wypisuje CSV na ekran.
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'meter');
define('DB_PASS', 'password');
define('DB_NAME', 'meter');
