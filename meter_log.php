#!/usr/bin/env php
<?php
/**
 * meter_log.php — zapisuje odczyty co METER_INTERVAL sekund, bez konca (Ctrl+C konczy).
 * Uzycie: php meter_log.php           (najlepiej jako usluga systemd — patrz README)
 */

require __DIR__ . '/meter.inc.php';

$db   = meter_db();
$fail = 0;
while (true) {
    $t   = microtime(true);
    $row = meter_read();
    if ($row === false) {
        if (++$fail === 3) fwrite(STDERR, date('H:i:s') . " miernik " . METER_IP . " nie odpowiada\n");
    } else {
        if ($fail >= 3) fwrite(STDERR, date('H:i:s') . " miernik znow odpowiada\n");
        $fail = 0;
        if ($db && !$db->ping()) $db = meter_db();  // baza zerwala polaczenie (np. restart)
        meter_save($db, $row);
    }
    usleep(max(0, (int)((METER_INTERVAL - (microtime(true) - $t)) * 1e6)));
}
