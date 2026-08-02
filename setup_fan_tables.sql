-- setup_fan_tables.sql
-- Run this in phpMyAdmin or MySQL CLI to set up/update the Auxiliary Fan tables.
-- Safe to run multiple times (uses IF NOT EXISTS / ADD COLUMN IF NOT EXISTS patterns).

-- ───────────────────────────────────────────────────────────────────────────
-- fan_state: single-row control table (id always = 1)
-- ───────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS fan_state (
    id                       INT          NOT NULL DEFAULT 1,
    mode                     ENUM('manual','scheduled','auto') NOT NULL DEFAULT 'manual',

    -- Schedule configuration
    schedule_time            TIME         DEFAULT NULL COMMENT 'HH:MM:SS start time',
    schedule_stop_time       TIME         DEFAULT NULL COMMENT 'HH:MM:SS stop time',
    schedule_duration_minutes INT         NOT NULL DEFAULT 30 COMMENT 'Duration in minutes (computed from start-stop)',

    -- Desired state (set by server / schedule)
    desired_fan_state        ENUM('on','off') NOT NULL DEFAULT 'off',

    -- Last ESP32 reported state (set by ESP32 via heartbeat)
    esp_fan_state            ENUM('on','off') NOT NULL DEFAULT 'off',
    esp_mode                 VARCHAR(20)  NOT NULL DEFAULT 'manual',

    -- Heartbeat & timestamps
    last_heartbeat           DATETIME     DEFAULT NULL,
    last_updated             DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_schedule_run        DATETIME     DEFAULT NULL,
    config_version           INT          NOT NULL DEFAULT 0,
    ack_config_version       INT          NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
    CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initialise the single control row
INSERT INTO fan_state (id) VALUES (1) ON DUPLICATE KEY UPDATE id = 1;

-- Add new columns if upgrading from an older schema:
ALTER TABLE fan_state
    ADD COLUMN IF NOT EXISTS mode                    ENUM('manual','scheduled','auto') NOT NULL DEFAULT 'manual',
    ADD COLUMN IF NOT EXISTS schedule_stop_time      TIME         DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS esp_fan_state           ENUM('on','off') NOT NULL DEFAULT 'off',
    ADD COLUMN IF NOT EXISTS esp_mode                VARCHAR(20)  NOT NULL DEFAULT 'manual',
    ADD COLUMN IF NOT EXISTS last_heartbeat          DATETIME     DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS last_updated            DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS last_schedule_run       DATETIME     DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS schedule_duration_minutes INT NOT NULL DEFAULT 30,
    ADD COLUMN IF NOT EXISTS config_version           INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS ack_config_version       INT NOT NULL DEFAULT 0;

-- ───────────────────────────────────────────────────────────────────────────
-- fan_log: event log table (START / STOP records)
-- ───────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS fan_log (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    action    VARCHAR(10)  NOT NULL COMMENT 'START or STOP',
    timestamp DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action           (action),
    INDEX idx_timestamp        (timestamp),
    INDEX idx_action_timestamp (action, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
