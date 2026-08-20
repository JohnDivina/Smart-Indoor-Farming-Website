'use client';

import React, { useState, useEffect } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import StatusBadge from '@/components/ui/StatusBadge';
import SensorChart from '@/components/charts/SensorChart';
import AnimatedNumber from '@/components/motion/AnimatedNumber';
import useSensorData from '@/hooks/useSensorData';
import { format } from 'date-fns';
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';
import { motion, AnimatePresence } from 'framer-motion';
import {
  FaTemperatureHalf,
  FaDroplet,
  FaFileExcel,
  FaCalendarDays,
  FaArrowRotateRight,
  FaChartLine,
} from 'react-icons/fa6';

export default function TempHumidityPage() {
  const [activeTab, setActiveTab] = useState<'live' | 'insights'>('live');
  const [selectedDate, setSelectedDate] = useState<string>(format(new Date(), 'yyyy-MM-dd'));
  const [chartData, setChartData] = useState<any>({ labels: [], temperature: [], humidity: [], raw: [] });
  const [chartLoading, setChartLoading] = useState(true);
  const [exporting, setExporting] = useState(false);

  const { data: liveData } = useSensorData<any>('/api/environment/temp-humid', 5000);

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

  const handleExportExcel = async () => {
    if (!chartData?.raw || chartData.raw.length === 0) {
      alert('No sensor data available for export on this date.');
      return;
    }

    setExporting(true);
    try {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Temp & Humidity');

      worksheet.columns = [
        { header: 'Reading ID', key: 'id', width: 14 },
        { header: 'Timestamp', key: 'timestamp', width: 24 },
        { header: 'Temperature (°C)', key: 'temperature', width: 18 },
        { header: 'Humidity (%)', key: 'humidity', width: 18 },
      ];

      worksheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
      worksheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF006600' },
      };

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

  const currentTemp = liveData?.temperature ?? 28.5;
  const currentHumid = liveData?.humidity ?? 65.0;

  return (
    <>
      <Header
        title="Microclimate Telemetry"
        subtitle="Real-time ambient temperature and relative humidity monitoring for greenhouse bays."
      />

      {/* ── Tab Selector ── */}
      <div style={{ display: 'flex', gap: '8px', marginBottom: '24px' }}>
        <button
          type="button"
          onClick={() => setActiveTab('live')}
          style={{
            padding: '10px 24px',
            borderRadius: '12px',
            border: '1px solid var(--glass-border)',
            background: activeTab === 'live' ? 'var(--accent-primary)' : 'var(--glass-bg-subtle)',
            color: activeTab === 'live' ? '#ffffff' : 'var(--text-secondary)',
            fontWeight: 700,
            fontSize: '0.9rem',
            cursor: 'pointer',
            transition: 'all var(--dur-short) var(--ease-out)',
          }}
        >
          Live Readings
        </button>
        <button
          type="button"
          onClick={() => setActiveTab('insights')}
          style={{
            padding: '10px 24px',
            borderRadius: '12px',
            border: '1px solid var(--glass-border)',
            background: activeTab === 'insights' ? 'var(--accent-primary)' : 'var(--glass-bg-subtle)',
            color: activeTab === 'insights' ? '#ffffff' : 'var(--text-secondary)',
            fontWeight: 700,
            fontSize: '0.9rem',
            cursor: 'pointer',
            transition: 'all var(--dur-short) var(--ease-out)',
          }}
        >
          Data Insights &amp; History
        </button>
      </div>

      <AnimatePresence mode="wait">
        {activeTab === 'live' ? (
          <motion.div
            key="live"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.2 }}
            style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}
          >
            {/* ── Live Metric Cards ── */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '20px' }}>
              {/* Temperature Card */}
              <GlassPanel style={{ padding: '32px 28px', borderLeft: '4px solid var(--accent-primary)' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                    <div style={{ width: '44px', height: '44px', borderRadius: '12px', background: 'rgba(0, 102, 0, 0.12)', color: 'var(--accent-primary)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '22px' }}>
                      <FaTemperatureHalf />
                    </div>
                    <div>
                      <h3 style={{ fontSize: '1.1rem', fontWeight: 700, color: 'var(--text-primary)' }}>Ambient Temperature</h3>
                      <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>Target: 24.0°C - 30.0°C</span>
                    </div>
                  </div>
                  <StatusBadge status={liveData?.status || 'connected'} />
                </div>

                <div style={{ display: 'flex', alignItems: 'baseline', gap: '8px' }}>
                  <span style={{ fontSize: '3.6rem', fontWeight: 800, color: 'var(--text-primary)', lineHeight: 1 }}>
                    <AnimatedNumber value={currentTemp} decimals={1} />
                  </span>
                  <span style={{ fontSize: '1.4rem', color: 'var(--accent-primary)', fontWeight: 600 }}>°C</span>
                </div>

                <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', marginTop: '12px' }}>
                  Optimal thermal equilibrium maintained for transpirational cooling.
                </p>
              </GlassPanel>

              {/* Humidity Card */}
              <GlassPanel style={{ padding: '32px 28px', borderLeft: '4px solid #3b82f6' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                    <div style={{ width: '44px', height: '44px', borderRadius: '12px', background: 'rgba(59, 130, 246, 0.12)', color: '#3b82f6', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '22px' }}>
                      <FaDroplet />
                    </div>
                    <div>
                      <h3 style={{ fontSize: '1.1rem', fontWeight: 700, color: 'var(--text-primary)' }}>Relative Humidity</h3>
                      <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>Target: 60% - 80%</span>
                    </div>
                  </div>
                  <StatusBadge status={liveData?.status || 'connected'} />
                </div>

                <div style={{ display: 'flex', alignItems: 'baseline', gap: '8px' }}>
                  <span style={{ fontSize: '3.6rem', fontWeight: 800, color: 'var(--text-primary)', lineHeight: 1 }}>
                    <AnimatedNumber value={currentHumid} decimals={1} />
                  </span>
                  <span style={{ fontSize: '1.4rem', color: '#3b82f6', fontWeight: 600 }}>%</span>
                </div>

                <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', marginTop: '12px' }}>
                  Vapor pressure deficit (VPD) levels in safe stomatal range.
                </p>
              </GlassPanel>
            </div>
          </motion.div>
        ) : (
          <motion.div
            key="insights"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.2 }}
            style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}
          >
            {/* ── Data Insights Chart ── */}
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
                  <FaChartLine style={{ color: 'var(--accent-primary)', fontSize: '20px' }} />
                  <div>
                    <h3 style={{ fontSize: '1.2rem', fontWeight: 700 }}>Temperature &amp; Humidity Trends</h3>
                    <p style={{ fontSize: '0.82rem', color: 'var(--text-muted)' }}>Date: {selectedDate}</p>
                  </div>
                </div>

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
                    title="Refresh Chart"
                    style={{ padding: '8px 14px', fontSize: '0.85rem' }}
                  >
                    <FaArrowRotateRight />
                  </button>

                  <button
                    type="button"
                    className="btn btn-primary"
                    onClick={handleExportExcel}
                    disabled={exporting || !chartData?.raw || chartData.raw.length === 0}
                    style={{ padding: '8px 18px', fontSize: '0.85rem' }}
                  >
                    <FaFileExcel />
                    <span>{exporting ? 'Exporting...' : 'Export Excel'}</span>
                  </button>
                </div>
              </div>

              {chartLoading ? (
                <div style={{ padding: '60px', textAlign: 'center', color: 'var(--text-muted)' }}>
                  Loading microclimate chart data...
                </div>
              ) : chartData?.labels && chartData.labels.length > 0 ? (
                <SensorChart
                  labels={chartData.labels}
                  datasets={[
                    {
                      label: 'Temperature (°C)',
                      data: chartData.temperature,
                      color: '#10b981',
                      unit: '°C',
                    },
                    {
                      label: 'Humidity (%)',
                      data: chartData.humidity,
                      color: '#3b82f6',
                      unit: '%',
                    },
                  ]}
                  height={320}
                />
              ) : (
                <div style={{ padding: '60px', textAlign: 'center', color: 'var(--text-muted)' }}>
                  No microclimate sensor logs found for {selectedDate}.
                </div>
              )}
            </GlassPanel>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
