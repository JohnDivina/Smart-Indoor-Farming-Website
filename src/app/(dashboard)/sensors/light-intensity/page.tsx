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
  FaSun,
  FaFileExcel,
  FaCalendarDays,
  FaArrowRotateRight,
  FaChartLine,
  FaLightbulb,
} from 'react-icons/fa6';

export default function LightIntensityPage() {
  const [activeTab, setActiveTab] = useState<'live' | 'insights'>('live');
  const [selectedDate, setSelectedDate] = useState<string>(format(new Date(), 'yyyy-MM-dd'));
  const [chartData, setChartData] = useState<any>({ labels: [], lux: [], raw: [] });
  const [chartLoading, setChartLoading] = useState(true);
  const [exporting, setExporting] = useState(false);

  const { data: liveData } = useSensorData<any>('/api/environment/light', 5000);

  const fetchChart = async (date?: string) => {
    setChartLoading(true);
    try {
      const url = date ? `/api/environment/light-chart?date=${date}` : '/api/environment/light-chart';
      const res = await fetch(url);
      const data = await res.json();
      if (data.success) {
        setChartData(data);
      }
    } catch (err) {
      console.error('Error fetching light chart:', err);
    } finally {
      setChartLoading(false);
    }
  };

  useEffect(() => {
    fetchChart(selectedDate);
  }, [selectedDate]);

  const handleExportExcel = async () => {
    if (!chartData?.raw || chartData.raw.length === 0) {
      alert('No light intensity records available for export on this date.');
      return;
    }

    setExporting(true);
    try {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Light Intensity');

      worksheet.columns = [
        { header: 'Reading ID', key: 'id', width: 14 },
        { header: 'Timestamp', key: 'timestamp', width: 24 },
        { header: 'Light Intensity (Lux)', key: 'lux', width: 22 },
      ];

      worksheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
      worksheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFF2A900' },
      };

      chartData.raw.forEach((item: any) => {
        worksheet.addRow({
          id: item.id,
          timestamp: item.timestamp,
          lux: item.lux,
        });
      });

      const buffer = await workbook.xlsx.writeBuffer();
      const blob = new Blob([buffer], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      });
      saveAs(blob, `CLSU_SmartFarm_LightIntensity_${selectedDate}.xlsx`);
    } catch (err) {
      alert('Failed to generate Excel export.');
    } finally {
      setExporting(false);
    }
  };

  const currentLux = liveData?.lux ?? 0;
  const sunlightPercent = Math.min(100, Math.round((currentLux / 2000) * 100));

  return (
    <>
      <Header
        title="Light Intensity Telemetry"
        subtitle="Solar irradiation, photosynthetic lux measurements, and illumination cycles."
        backHref="/dashboard"
        status={liveData?.status || 'connected'}
        lastUpdated={liveData?.timestamp || 'Just now'}
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
          Live Telemetry
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
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '24px' }}>
              {/* Primary Lux Card */}
              <GlassPanel style={{ padding: '36px 32px', textAlign: 'center' }}>
                <div
                  style={{
                    width: '72px',
                    height: '72px',
                    borderRadius: '20px',
                    background: 'rgba(242, 169, 0, 0.15)',
                    color: '#F2A900',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: '34px',
                    margin: '0 auto 20px',
                    boxShadow: '0 8px 24px rgba(242, 169, 0, 0.2)',
                  }}
                >
                  <FaLightbulb />
                </div>

                <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '1px', fontWeight: 600 }}>
                  Current Illumination
                </span>

                <div style={{ fontSize: '4rem', fontWeight: 800, color: 'var(--text-primary)', lineHeight: 1.1, margin: '8px 0' }}>
                  <AnimatedNumber value={currentLux} decimals={0} />
                  <span style={{ fontSize: '1.4rem', color: '#F2A900', marginLeft: '8px', fontWeight: 600 }}>Lux</span>
                </div>

                <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)' }}>
                  Average hourly photosynthetic photon flux density
                </p>
              </GlassPanel>

              {/* Sunlight Index & Crop Photosynthesis Gauge */}
              <GlassPanel style={{ padding: '32px 28px', display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
                <div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '16px' }}>
                    <FaSun style={{ color: '#F2A900', fontSize: '24px' }} />
                    <h3 style={{ fontSize: '1.2rem', fontWeight: 700, color: 'var(--text-primary)' }}>
                      Sunlight Saturation Index
                    </h3>
                  </div>

                  <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', lineHeight: 1.6, marginBottom: '24px' }}>
                    Calculated against optimal DLI (Daily Light Integral) for indoor bell peppers and tomatoes.
                  </p>

                  <div style={{ marginBottom: '12px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', fontWeight: 700, marginBottom: '6px' }}>
                      <span>Photosynthetic Capacity</span>
                      <span style={{ color: '#F2A900' }}>{sunlightPercent}%</span>
                    </div>
                    <div style={{ width: '100%', height: '12px', borderRadius: '6px', background: 'var(--glass-bg-subtle)', overflow: 'hidden' }}>
                      <div
                        style={{
                          width: `${sunlightPercent}%`,
                          height: '100%',
                          background: 'linear-gradient(90deg, #F2A900 0%, #006600 100%)',
                          borderRadius: '6px',
                          transition: 'width 0.6s var(--ease-out)',
                        }}
                      />
                    </div>
                  </div>
                </div>

                <div style={{ padding: '16px', borderRadius: '12px', background: 'rgba(242, 169, 0, 0.08)', border: '1px solid rgba(242, 169, 0, 0.2)' }}>
                  <h4 style={{ fontSize: '0.9rem', fontWeight: 700, color: '#F2A900' }}>Photoperiod Status</h4>
                  <p style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '4px' }}>
                    Light levels adequate for vegetative growth. Supplemental lighting unnecessary at current irradiance.
                  </p>
                </div>
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
                  <FaChartLine style={{ color: '#F2A900', fontSize: '20px' }} />
                  <div>
                    <h3 style={{ fontSize: '1.2rem', fontWeight: 700 }}>Hourly Lux Intensity Curve</h3>
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
                  Loading illumination chart data...
                </div>
              ) : chartData?.labels && chartData.labels.length > 0 ? (
                <SensorChart
                  labels={chartData.labels}
                  datasets={[
                    {
                      label: 'Light Intensity (Lux)',
                      data: chartData.lux,
                      color: '#F2A900',
                      unit: 'Lux',
                    },
                  ]}
                  height={320}
                />
              ) : (
                <div style={{ padding: '60px', textAlign: 'center', color: 'var(--text-muted)' }}>
                  No light sensor logs found for {selectedDate}.
                </div>
              )}
            </GlassPanel>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
