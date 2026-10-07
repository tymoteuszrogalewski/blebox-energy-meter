-- Miernik energii BleBox — odczyty co kilka sekund.
-- Uzycie: mysql meter < schema.sql
-- ts_utc = czas odczytu w UTC (klucz), ts_local = czas polski. Moc w W / var / VA, napiecie w V, prad w A, czestotliwosc w Hz,
-- energia w kWh (stan licznika — rosnie; zuzycie w okresie = roznica MAX - MIN).

CREATE TABLE IF NOT EXISTS `meter_readings` (
  `ts_utc`         datetime NOT NULL,
  `ts_local`       datetime NOT NULL,
  `power`          decimal(9,1) DEFAULT NULL,   -- moc czynna, suma faz, W (ujemna = oddawanie do sieci)
  `reactive_power` decimal(9,1) DEFAULT NULL,   -- moc bierna, var
  `apparent_power` decimal(9,1) DEFAULT NULL,   -- moc pozorna, VA
  `frequency`      decimal(6,3) DEFAULT NULL,   -- Hz
  `energy_import`  decimal(12,3) DEFAULT NULL,  -- licznik energii pobranej, kWh
  `energy_export`  decimal(12,3) DEFAULT NULL,  -- licznik energii oddanej, kWh
  `l1_power`       decimal(9,1) DEFAULT NULL,
  `l1_voltage`     decimal(5,1) DEFAULT NULL,
  `l1_current`     decimal(6,2) DEFAULT NULL,
  `l2_power`       decimal(9,1) DEFAULT NULL,
  `l2_voltage`     decimal(5,1) DEFAULT NULL,
  `l2_current`     decimal(6,2) DEFAULT NULL,
  `l3_power`       decimal(9,1) DEFAULT NULL,
  `l3_voltage`     decimal(5,1) DEFAULT NULL,
  `l3_current`     decimal(6,2) DEFAULT NULL,
  PRIMARY KEY (`ts_utc`),
  KEY `idx_local` (`ts_local`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Przyklady:
-- zuzycie godzinowe dzis, kWh:
--   SELECT DATE_FORMAT(ts_local, '%H:00') h, MAX(energy_import) - MIN(energy_import) kwh
--   FROM meter_readings WHERE ts_local >= CURDATE() GROUP BY h ORDER BY h;
-- najwyzszy prad na fazach w ostatniej dobie (czy nie zblizamy sie do zabezpieczenia):
--   SELECT MAX(l1_current), MAX(l2_current), MAX(l3_current) FROM meter_readings WHERE ts_local >= NOW() - INTERVAL 1 DAY;
-- napiecie min/max na fazach w ostatniej dobie:
--   SELECT MIN(l1_voltage), MAX(l1_voltage), MIN(l2_voltage), MAX(l2_voltage), MIN(l3_voltage), MAX(l3_voltage)
--   FROM meter_readings WHERE ts_local >= NOW() - INTERVAL 1 DAY;
-- sprzatanie: odczyty starsze niz 90 dni (co 10 s to ok. 3 mln wierszy rocznie):
--   DELETE FROM meter_readings WHERE ts_utc < UTC_TIMESTAMP() - INTERVAL 90 DAY;
