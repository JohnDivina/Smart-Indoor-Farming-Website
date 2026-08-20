export interface FanStatusResponse {
  success: boolean;
  esp_fan_state: 'on' | 'off';
  mode: 'manual' | 'schedule';
  schedule_time: string | null;
  schedule_stop_time: string | null;
  esp_online: boolean;
  message?: string;
}

export interface FertigationStatusResponse {
  success: boolean;
  mode: 'manual' | 'schedule' | 'auto';
  desired_pump_state: 'on' | 'off';
  actual_pump_state: 'on' | 'off';
  esp_pump_state: 'on' | 'off';
  esp_online: boolean;
  schedule_time: string | null;
  schedule_stop_time: string | null;
  config_version: number;
  ack_config_version: number;
  error_message?: string;
}

export interface SolarStatusResponse {
  success: boolean;
  mode: 'manual' | 'schedule';
  desired_state: 'on' | 'off';
  actual_state: 'on' | 'off';
  voltage?: number;
  current?: number;
  power?: number;
  esp_online: boolean;
  schedule_time: string | null;
  schedule_stop_time: string | null;
}
