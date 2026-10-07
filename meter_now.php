#!/usr/bin/env php
<?php
/**
 * meter_now.php — jeden odczyt miernika, czytelnie na ekranie. Do sprawdzenia, czy wszystko dziala.
 * Uzycie: php meter_now.php
 */

require __DIR__ . '/meter.inc.php';

$r = meter_read();
if ($r === false) { fwrite(STDERR, "Brak odpowiedzi z miernika " . METER_IP . " — sprawdz METER_IP\n"); exit(1); }

printf("Moc:        %7.0f W   (bierna %.0f var, pozorna %.0f VA)\n", $r['power'], $r['reactive_power'], $r['apparent_power']);
printf("Czestotl.:  %7.2f Hz\n", $r['frequency']);
printf("Energia:    %7.3f kWh pobrana, %.3f kWh oddana\n", $r['energy_import'], $r['energy_export']);
foreach ([1, 2, 3] as $l) {
    printf("L%d:         %7.0f W   %5.1f V   %5.2f A\n", $l, $r["l{$l}_power"], $r["l{$l}_voltage"], $r["l{$l}_current"]);
}
