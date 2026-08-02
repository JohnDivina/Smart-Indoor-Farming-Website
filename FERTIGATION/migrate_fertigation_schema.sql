-- Migration: add schedule_stop_time and last_heartbeat to fertigation_control
-- Run in phpMyAdmin or MySQL CLI. Safe to run multiple times.

ALTER TABLE fertigation_control
    ADD COLUMN IF NOT EXISTS schedule_stop_time        TIME     DEFAULT NULL COMMENT 'HH:MM:SS schedule stop time',
    ADD COLUMN IF NOT EXISTS schedule_duration_minutes  INT      NOT NULL DEFAULT 30 COMMENT 'Duration in minutes (computed)',
    ADD COLUMN IF NOT EXISTS last_heartbeat             DATETIME DEFAULT NULL COMMENT 'Last ESP32 heartbeat timestamp',
    ADD COLUMN IF NOT EXISTS esp_pump_state             VARCHAR(5)  NOT NULL DEFAULT 'off' COMMENT 'ESP32 reported pump state',
    ADD COLUMN IF NOT EXISTS config_version             INT         NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS ack_config_version         INT         NOT NULL DEFAULT 0;

-- Confirm row exists
INSERT INTO fertigation_control (id) VALUES (1) ON DUPLICATE KEY UPDATE id = 1;
