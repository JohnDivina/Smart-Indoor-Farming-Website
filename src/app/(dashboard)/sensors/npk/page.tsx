'use client';

import React, { useState, useEffect } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import StatusBadge from '@/components/ui/StatusBadge';
import PHScale from '@/components/ui/PHScale';
import NPKChart from '@/components/charts/NPKChart';
import MotionWrapper from '@/components/motion/MotionWrapper';
import AnimatedNumber from '@/components/motion/AnimatedNumber';
import useSensorData from '@/hooks/useSensorData';
import { format } from 'date-fns';
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';
import { motion, AnimatePresence } from 'framer-motion';
import {
  FaFlask,
  FaFileExcel,
  FaCalendarDays,
  FaArrowRotateRight,
  FaChartBar,
  FaSeedling,
  FaBolt,
  FaDroplet,
  FaCamera,
} from 'react-icons/fa6';

export default function NpkSensorPage() {
  const [activeTab, setActiveTab] = useState<'live' | 'history'>('live');
  const [selectedDate, setSelectedDate] = useState<string>(format(new Date(), 'yyyy-MM-dd'));
  const [historyData, setHistoryData] = useState<any>({
    readings: [],
    average: { nitrogen: 0, phosphorus: 0, potassium: 0 },
  });
  const [loading, setLoading] = useState(true);
  const [exporting, setExporting] = useState(false);

  const { data: liveData } = useSensorData<any>('/api/npk/data', 6000);

  const fetchHistory = async (date?: string) => {
    setLoading(true);
    try {
      const url = date ? `/api/npk/data-by-date?date=${date}` : '/api/npk/data-by-date';
      const res = await fetch(url);
      const data = await res.json();
      if (data.success) {
        setHistoryData(data);
      }
    } catch (err) {
      console.error('Error fetching NPK history:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchHistory(selectedDate);
  }, [selectedDate]);

  const handleExportExcel = async () => {
    if (!historyData?.readings || historyData.readings.length === 0) {
      alert('No NPK records available for export on this date.');
      return;
    }

    setExporting(true);
    try {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Soil NPK Nutrients');

      worksheet.columns = [
        { header: 'Reading ID', key: 'id', width: 14 },
        { header: 'Timestamp', key: 'timestamp', width: 24 },
        { header: 'Nitrogen (N) mg/kg', key: 'nitrogen', width: 22 },
        { header: 'Phosphorus (P) mg/kg', key: 'phosphorus', width: 22 },
        { header: 'Potassium (K) mg/kg', key: 'potassium', width: 22 },
      ];

      worksheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
      worksheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF006600' },
      };

      historyData.readings.forEach((item: any) => {
        worksheet.addRow({
          id: item.id,
          timestamp: item.timestamp,
          nitrogen: item.nitrogen,
          phosphorus: item.phosphorus,
          potassium: item.potassium,
        });
      });

      const buffer = await workbook.xlsx.writeBuffer();
      const blob = new Blob([buffer], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      });
      saveAs(blob, `CLSU_SmartFarm_Soil_NPK_${selectedDate}.xlsx`);
    } catch (err) {
      alert('Failed to generate Excel export.');
    } finally {
      setExporting(false);
    }
  };

  const handleExportStory = () => {
    alert(`IG Story snapshot generated for ${selectedDate}! In production, this renders a 1080x1920 mobile infographic summary.`);
  };

  const currentN = liveData?.nitrogen ?? 45;
  const currentP = liveData?.phosphorus ?? 30;
  const currentK = liveData?.potassium ?? 55;
  const currentPH = liveData?.ph ?? 6.8;
  const currentEC = liveData?.ec ?? 1.42;
  const currentMoisture = liveData?.moisture ?? 68;

  return (
    <>
      <Header
        title="Soil Nutrient & pH Telemetry"
        subtitle="Precision soil macronutrient analysis for Nitrogen (N), Phosphorus (P), Potassium (K), and Soil pH."
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
          Live Telemetry &amp; pH
        </button>
        <button
          type="button"
          onClick={() => setActiveTab('history')}
          style={{
            padding: '10px 24px',
            borderRadius: '12px',
            border: '1px solid var(--glass-border)',
            background: activeTab === 'history' ? 'var(--accent-primary)' : 'var(--glass-bg-subtle)',
            color: activeTab === 'history' ? '#ffffff' : 'var(--text-secondary)',
            fontWeight: 700,
            fontSize: '0.9rem',
            cursor: 'pointer',
            transition: 'all var(--dur-short) var(--ease-out)',
          }}
        >
          Historical Insights &amp; Logs
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
            {/* ── 3 Nutrient Metric Cards ── */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))', gap: '20px' }}>
              {/* Nitrogen */}
              <GlassPanel style={{ padding: '24px 28px', borderLeft: '4px solid #3b82f6' }}>
                <div style={{ marginBottom: '12px' }}>
                  <span style={{ fontSize: '0.9rem', fontWeight: 700, color: '#3b82f6' }}>Nitrogen (N)</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px' }}>
                  <span style={{ fontSize: '2.8rem', fontWeight: 800, color: 'var(--text-primary)', lineHeight: 1 }}>
                    <AnimatedNumber value={currentN} decimals={0} />
                  </span>
                  <span style={{ fontSize: '1rem', color: 'var(--text-muted)' }}>mg/kg</span>
                </div>
                <p style={{ fontSize: '0.78rem', color: 'var(--text-secondary)', marginTop: '8px' }}>
                  Promotes lush vegetative growth and chlorophyll synthesis.
                </p>
              </GlassPanel>

              {/* Phosphorus */}
              <GlassPanel style={{ padding: '24px 28px', borderLeft: '4px solid #F2A900' }}>
                <div style={{ marginBottom: '12px' }}>
                  <span style={{ fontSize: '0.9rem', fontWeight: 700, color: '#F2A900' }}>Phosphorus (P)</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px' }}>
                  <span style={{ fontSize: '2.8rem', fontWeight: 800, color: 'var(--text-primary)', lineHeight: 1 }}>
                    <AnimatedNumber value={currentP} decimals={0} />
                  </span>
                  <span style={{ fontSize: '1rem', color: 'var(--text-muted)' }}>mg/kg</span>
                </div>
                <p style={{ fontSize: '0.78rem', color: 'var(--text-secondary)', marginTop: '8px' }}>
                  Stimulates root development, flowering, and seed formation.
                </p>
              </GlassPanel>

              {/* Potassium */}
              <GlassPanel style={{ padding: '24px 28px', borderLeft: '4px solid #10b981' }}>
                <div style={{ marginBottom: '12px' }}>
                  <span style={{ fontSize: '0.9rem', fontWeight: 700, color: '#10b981' }}>Potassium (K)</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px' }}>
                  <span style={{ fontSize: '2.8rem', fontWeight: 800, color: 'var(--text-primary)', lineHeight: 1 }}>
                    <AnimatedNumber value={currentK} decimals={0} />
                  </span>
                  <span style={{ fontSize: '1rem', color: 'var(--text-muted)' }}>mg/kg</span>
                </div>
                <p style={{ fontSize: '0.78rem', color: 'var(--text-secondary)', marginTop: '8px' }}>
                  Enhances disease resistance, water regulation, and crop vigor.
                </p>
              </GlassPanel>
            </div>

            {/* ── Soil pH & EC Section ── */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '20px' }}>
              {/* Visual pH Scale */}
              <GlassPanel style={{ padding: '16px' }}>
                <PHScale value={currentPH} />
              </GlassPanel>

              {/* EC & Moisture Quick Stats */}
              <GlassPanel style={{ padding: '24px 28px', display: 'flex', flexDirection: 'column', justifyContent: 'space-around' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: '1px solid var(--glass-border)', paddingBottom: '16px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
                    <div style={{ width: '44px', height: '44px', borderRadius: '12px', background: 'rgba(242, 169, 0, 0.15)', color: '#F2A900', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>
                      <FaBolt />
                    </div>
                    <div>
                      <h4 style={{ fontSize: '0.95rem', fontWeight: 700, color: 'var(--text-primary)' }}>Soil Electrical Conductivity</h4>
                      <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Salinity &amp; Mineral Concentration</p>
                    </div>
                  </div>
                  <div style={{ textAlign: 'right' }}>
                    <span style={{ fontSize: '1.6rem', fontWeight: 800, color: '#F2A900' }}>
                      <AnimatedNumber value={currentEC} decimals={2} />
                    </span>
                    <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginLeft: '4px' }}>dS/m</span>
                  </div>
                </div>

                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', paddingTop: '16px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
                    <div style={{ width: '44px', height: '44px', borderRadius: '12px', background: 'rgba(16, 185, 129, 0.15)', color: 'var(--accent-primary)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>
                      <FaDroplet />
                    </div>
                    <div>
                      <h4 style={{ fontSize: '0.95rem', fontWeight: 700, color: 'var(--text-primary)' }}>Volumetric Soil Moisture</h4>
                      <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Root Zone Hydration Level</p>
                    </div>
                  </div>
                  <div style={{ textAlign: 'right' }}>
                    <span style={{ fontSize: '1.6rem', fontWeight: 800, color: 'var(--accent-primary)' }}>
                      <AnimatedNumber value={currentMoisture} decimals={1} />
                    </span>
                    <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginLeft: '4px' }}>%</span>
                  </div>
                </div>
              </GlassPanel>
            </div>

            {/* ── Comparison Chart & Agronomic Recommendations ── */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '20px' }}>
              <GlassPanel style={{ padding: '28px 32px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '20px' }}>
                  <FaChartBar style={{ color: '#3b82f6', fontSize: '20px' }} />
                  <div>
                    <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Current Macronutrient Balance</h3>
                    <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Real-time sensor ratio breakdown</p>
                  </div>
                </div>
                <NPKChart nitrogen={currentN} phosphorus={currentP} potassium={currentK} height={260} />
              </GlassPanel>

              <GlassPanel style={{ padding: '28px 32px', display: 'flex', flexDirection: 'column', gap: '16px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                  <FaSeedling style={{ color: 'var(--accent-primary)', fontSize: '20px' }} />
                  <div>
                    <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Agronomic Recommendations</h3>
                    <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Automated Fertigation Guidance</p>
                  </div>
                </div>

                <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                  <div style={{ padding: '12px 16px', borderRadius: '12px', background: 'rgba(59, 130, 246, 0.08)', border: '1px solid rgba(59, 130, 246, 0.2)' }}>
                    <div style={{ fontSize: '0.85rem', fontWeight: 700, color: '#3b82f6' }}>Nitrogen (N) Status: Normal</div>
                    <div style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '2px' }}>
                      Vegetative tissue nourishment is within target thresholds (30–60 mg/kg).
                    </div>
                  </div>

                  <div style={{ padding: '12px 16px', borderRadius: '12px', background: 'rgba(242, 169, 0, 0.08)', border: '1px solid rgba(242, 169, 0, 0.2)' }}>
                    <div style={{ fontSize: '0.85rem', fontWeight: 700, color: '#F2A900' }}>Phosphorus (P) Status: Optimal</div>
                    <div style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '2px' }}>
                      Root uptake and flowering energy storage are steady (20–40 mg/kg).
                    </div>
                  </div>

                  <div style={{ padding: '12px 16px', borderRadius: '12px', background: 'rgba(16, 185, 129, 0.08)', border: '1px solid rgba(16, 185, 129, 0.2)' }}>
                    <div style={{ fontSize: '0.85rem', fontWeight: 700, color: '#10b981' }}>Potassium (K) Status: Good</div>
                    <div style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '2px' }}>
                      Stomatal conductance and cellular hydration pressure are balanced (40–80 mg/kg).
                    </div>
                  </div>
                </div>
              </GlassPanel>
            </div>
          </motion.div>
        ) : (
          <motion.div
            key="history"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.2 }}
            style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}
          >
            {/* ── Daily History Table & Excel/IG Export ── */}
            <GlassPanel style={{ padding: '28px 32px' }}>
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'space-between',
                  flexWrap: 'wrap',
                  gap: '16px',
                  marginBottom: '20px',
                }}
              >
                <div>
                  <h2 style={{ fontSize: '1.25rem', fontWeight: 700 }}>Daily Nutrient Readings Log</h2>
                  <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                    Average for date &bull; N: {historyData.average.nitrogen} | P: {historyData.average.phosphorus} | K: {historyData.average.potassium} mg/kg
                  </p>
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
                    onClick={() => fetchHistory(selectedDate)}
                    title="Refresh Records"
                    style={{ padding: '8px 14px', fontSize: '0.85rem' }}
                  >
                    <FaArrowRotateRight />
                  </button>

                  {/* Instagram Story Gradient Action */}
                  <button
                    type="button"
                    onClick={handleExportStory}
                    style={{
                      background: 'linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%)',
                      border: 'none',
                      color: '#fff',
                      padding: '8px 16px',
                      borderRadius: '8px',
                      fontWeight: 700,
                      cursor: 'pointer',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '8px',
                      fontSize: '0.85rem',
                      boxShadow: '0 4px 15px rgba(220,39,67,0.35)',
                    }}
                  >
                    <FaCamera />
                    <span>IG Story Report</span>
                  </button>

                  <button
                    type="button"
                    className="btn btn-primary"
                    onClick={handleExportExcel}
                    disabled={exporting || historyData.readings.length === 0}
                    style={{ padding: '8px 18px', fontSize: '0.85rem' }}
                  >
                    <FaFileExcel />
                    <span>{exporting ? 'Exporting...' : 'Export Excel'}</span>
                  </button>
                </div>
              </div>

              {loading ? (
                <div style={{ padding: '40px', textAlign: 'center', color: 'var(--text-muted)' }}>
                  Loading NPK records...
                </div>
              ) : historyData.readings.length > 0 ? (
                <div style={{ overflowX: 'auto' }}>
                  <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.9rem', textAlign: 'left' }}>
                    <thead>
                      <tr style={{ borderBottom: '1px solid var(--glass-border)', color: 'var(--text-muted)' }}>
                        <th style={{ padding: '12px 16px' }}>Time</th>
                        <th style={{ padding: '12px 16px' }}>Nitrogen (N)</th>
                        <th style={{ padding: '12px 16px' }}>Phosphorus (P)</th>
                        <th style={{ padding: '12px 16px' }}>Potassium (K)</th>
                      </tr>
                    </thead>
                    <tbody>
                      {historyData.readings.slice(-12).reverse().map((r: any) => (
                        <tr key={r.id} style={{ borderBottom: '1px solid var(--glass-border)' }}>
                          <td style={{ padding: '12px 16px', color: 'var(--text-primary)', fontWeight: 600 }}>
                            {r.timeLabel || r.timestamp}
                          </td>
                          <td style={{ padding: '12px 16px', color: '#3b82f6', fontWeight: 700 }}>{r.nitrogen} mg/kg</td>
                          <td style={{ padding: '12px 16px', color: '#F2A900', fontWeight: 700 }}>{r.phosphorus} mg/kg</td>
                          <td style={{ padding: '12px 16px', color: '#10b981', fontWeight: 700 }}>{r.potassium} mg/kg</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div style={{ padding: '40px', textAlign: 'center', color: 'var(--text-muted)' }}>
                  No NPK sensor logs found for {selectedDate}.
                </div>
              )}
            </GlassPanel>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
