#!/bin/bash
# Zapisuje odczyty co METER_INTERVAL sekund (do bazy albo CSV). Ctrl+C konczy. Na stale: usluga systemd (README).
php "$(dirname "$0")/meter_log.php" "$@"
