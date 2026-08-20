'use client';

import React, { useState, useEffect } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import StatusBadge from '@/components/ui/StatusBadge';
import SensorChart from '@/components/charts/SensorChart';
import useSensorData from '@/hooks/useSensorData';
import { format } from 'date-fns';
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';
import {
  FaTemperatureHalf,
  FaDroplet,
  FaFileExcel,
  FaCalendarDays,
  FaArrowRotateRight,
  FaChartLine,
} from 'react-icons/fa6';

export default function TempHumidityPage() {
  const [selectedDate, setSelectedDate] = useState<string>(format(new Date(), 'yyyy-MM-dd'));
  const [chartData, setChartData] = useState<any>({ labels: [], temperature: [], humidity: [], raw: [] });
  const [chartLoading, setChartLoading] = useState(true);
  const [exporting, setExporting] = useState(false);

  // Real-time live readings (polled every 5s)
  const { data: liveData, refetch: refetchLive } = useSensorData<any>('/api/environment/temp-humid', 5000);

  // Fetch chart history
  const fetchChart = async (date?: string) => {
    setChartLoading(true);
    try {
      const url = date ? `/api/environment/temp-humid-chart?date=${date}` : '/api/environment/temp-humid-chart';
      const res = await fetch(url);
      const data = await res.json();
      if (data.success) {
        setChartData(data);
      }
    } catch (err) {
      console.error('Error fetching chart data:', err);
    } finally {
      setChartLoading(false);
    }
  };

  useEffect(() => {
    fetchChart(selectedDate);
  }, [selectedDate]);

  // Export data to Excel
  const handleExportExcel = async () => {
    if (!chartData?.raw || chartData.raw.length === 0) {
      alert('No sensor data available for export on this date.');
      return;
    }

    setExporting(true);
    try {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Temp & Humidity');

      // Add Headers
      worksheet.columns = [
        { header: 'Reading ID', key: 'id', width: 14 },
        { header: 'Timestamp', key: 'timestamp', width: 24 },
        { header: 'Temperature (°C)', key: 'temperature', width: 18 },
        { header: 'Humidity (%)', key: 'humidity', width: 18 },
      ];

      // Style Header Row
      worksheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
      worksheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF006600' },
      };

      // Add Data Rows
      chartData.raw.forEach((item: any) => {
        worksheet.addRow({
          id: item.id,
          timestamp: item.timestamp,
          temperature: item.temperature,
          humidity: item.humidity,
        });
      });

      const buffer = await workbook.xlsx.writeBuffer();
      const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
      saveAs(blob, `CLSU_SmartFarm_TempHumidity_${selectedDate}.xlsx`);
    } catch (err) {
      console.error('Export Excel error:', err);
      alert('Failed to generate Excel file.');
    } finally {
      setExporting(false);
    }
  };

  return (
    <>
      <Header
        title="Temperature &amp; Humidity"
        subtitle="Live environmental telemetry and 24-hour climate trends."
      />

      {/* ── Live Gauges / Cards ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '20px' }}>
        {/* Temperature Card */}
        <GlassPanel style={{ padding: '28px 32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              <div
                style={{
                  width: '48px',
                  height: '48px',
                  borderRadius: '14px',
                  background: 'rgba(0, 102, 0, 0.12)',
                  color: 'var(--accent-primary)',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: '22px',
                }}
              >
                <FaTemperatureHalf />
              </div>
              <div>
                <h3 style={{ fontSize: '1.1rem', fontWeight: 700 }}>Ambient Temperature</h3>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Target: 22.0°C – 28.0°C</span>
              </div>
            </div>
            <StatusBadge status={liveData?.status || 'disconnected'} />
          </div>

          <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px' }}>
            <span style={{ fontSize: '3.4rem', fontWeight: 800, color: 'var(--accent-primary)', lineHeight: 1 }}>
              {liveData?.temperature !== undefined ? liveData.temperature : '--'}
            </span>
            <span style={{ fontSize: '1.4rem', fontWeight: 700, color: 'var(--text-secondary)' }}>°C</span>
          </div>

          <div style={{ marginTop: '16px', fontSize: '0.8rem', color: 'var(--text-muted)' }}>
            Last reading: {liveData?.timestamp || 'Waiting for sensor stream...'}
          </div>
        </GlassPanel>

        {/* Humidity Card */}
        <GlassPanel style={{ padding: '28px 32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              <div
                style={{
                  width: '48px',
                  height: '48px',
                  borderRadius: '14px',
                  background: 'rgba(59, 130, 246, 0.12)',
                  color: '#3b82f6',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: '22px',
                }}
              >
                <FaDroplet />
              </div>
              <div>
                <h3 style={{ fontSize: '1.1rem', fontWeight: 700 }}>Relative Humidity</h3>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Optimal: 60% – 75%</span>
              </div>
            </div>
            <StatusBadge status={liveData?.status || 'disconnected'} />
          </div>

          <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px' }}>
            <span style={{ fontSize: '3.4rem', fontWeight: 800, color: '#3b82f6', lineHeight: 1 }}>
              {liveData?.humidity !== undefined ? liveData.humidity : '--'}
            </span>
            <span style={{ fontSize: '1.4rem', fontWeight: 700, color: 'var(--text-secondary)' }}>%</span>
          </div>

          <div style={{ marginTop: '16px', fontSize: '0.8rem', color: 'var(--text-muted)' }}>
            Last reading: {liveData?.timestamp || 'Waiting for sensor stream...'}
          </div>
        </GlassPanel>
      </div>

      {/* ── Historical Chart Section ── */}
      <GlassPanel style={{ padding: '28px 32px' }}>
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            flexWrap: 'wrap',
            gap: '16px',
            marginBottom: '24px',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
            <FaChartLine style={{ fontSize: '20px', color: 'var(--accent-primary)' }} />
            <div>
              <h2 style={{ fontSize: '1.25rem', fontWeight: 700 }}>Historical Climate Graph</h2>
              <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                Comparing ambient temperature (°C) and relative humidity (%)
              </p>
            </div>
          </div>

          {/* Controls: Date Picker + Excel Export */}
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <FaCalendarDays style={{ color: 'var(--text-muted)', fontSize: '14px' }} />
              <input
                type="date"
                className="form-input"
                value={selectedDate}
                onChange={(e) => setSelectedDate(e.target.value)}
                style={{ padding: '8px 12px', fontSize: '0.85rem', width: 'auto' }}
              />
            </div>

            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => fetchChart(selectedDate)}
              title="Refresh Graph"
              style={{ padding: '8px 14px', fontSize: '0.85rem' }}
            >
              <FaArrowRotateRight />
            </button>

            <button
              type="button"
              className="btn btn-primary"
              onClick={handleExportExcel}
              disabled={exporting || chartData.labels.length === 0}
              style={{ padding: '8px 18px', fontSize: '0.85rem' }}
            >
              <FaFileExcel />
              <span>{exporting ? 'Exporting...' : 'Export Excel'}</span>
            </button>
          </div>
        </div>

        {/* Line Chart */}
        {chartLoading ? (
          <div style={{ height: '320px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ color: 'var(--text-muted)' }}>Loading climate chart data...</span>
          </div>
        ) : chartData.labels.length > 0 ? (
          <SensorChart
            labels={chartData.labels}
            datasets={[
              {
                label: 'Temperature (°C)',
                data: chartData.temperature,
                color: '#009639',
                unit: '°C',
              },
              {
                label: 'Humidity (%)',
                data: chartData.humidity,
                color: '#3b82f6',
                unit: '%',
              },
            ]}
            height={340}
          />
        ) : (
          <div
            style={{
              height: '240px',
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              justifyContent: 'center',
              color: 'var(--text-muted)',
              gap: '8px',
            }}
          >
            <p>No sensor records found for {selectedDate}.</p>
            <span style={{ fontSize: '0.85rem' }}>Select a different date or verify sensor connection.</span>
          </div>
        )}
      </GlassPanel>
    </>
  );
}
