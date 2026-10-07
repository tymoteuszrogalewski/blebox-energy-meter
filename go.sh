#!/bin/bash
# Jeden odczyt miernika na ekranie — sprawdzenie, czy wszystko dziala.
php "$(dirname "$0")/meter_now.php" "$@"
