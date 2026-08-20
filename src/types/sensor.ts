export interface TempHumidData {
  success: boolean;
  temperature: number;
  humidity: number;
  timestamp: string;
  status: 'connected' | 'disconnected';
}

export interface LightData {
  success: boolean;
  lux: number;
  timestamp: string;
  status: 'connected' | 'disconnected';
}

export interface NpkData {
  success: boolean;
  nitrogen: number;
  phosphorus: number;
  potassium: number;
  timestamp: string;
  status: 'connected' | 'disconnected';
}

export interface ChartDataPoint {
  timestamp: string;
  timeLabel: string;
  value?: number;
  temperature?: number;
  humidity?: number;
  lux?: number;
  nitrogen?: number;
  phosphorus?: number;
  potassium?: number;
}
