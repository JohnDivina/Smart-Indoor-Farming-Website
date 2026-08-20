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
  FaSun,
  FaFileExcel,
  FaCalendarDays,
  FaArrowRotateRight,
  FaChartLine,
  FaLightbulb,
} from 'react-icons/fa6';

export default function LightIntensityPage() {
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
      const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
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
      />

      {/* ── Live Lux Panel ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '20px' }}>
        <GlassPanel style={{ padding: '28px 32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              <div
                style={{
                  width: '48px',
                  height: '48px',
                  borderRadius: '14px',
                  background: 'rgba(242, 169, 0, 0.15)',
                  color: '#F2A900',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: '22px',
                }}
              >
                <FaSun />
              </div>
              <div>
                <h3 style={{ fontSize: '1.1rem', fontWeight: 700 }}>Photosynthetic Lux</h3>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Target: 800 – 1,800 Lux</span>
              </div>
            </div>
            <StatusBadge status={liveData?.status || 'disconnected'} />
          </div>

          <div style={{ display: 'flex', alignItems: 'baseline', gap: '8px' }}>
            <span style={{ fontSize: '3.4rem', fontWeight: 800, color: '#F2A900', lineHeight: 1 }}>
              {liveData?.lux !== undefined ? liveData.lux.toLocaleString() : '--'}
            </span>
            <span style={{ fontSize: '1.4rem', fontWeight: 700, color: 'var(--text-secondary)' }}>Lux</span>
          </div>

          {/* Illumination Progress Meter */}
          <div style={{ marginTop: '20px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.8rem', color: 'var(--text-muted)', marginBottom: '6px' }}>
              <span>Canopy Illumination Level</span>
              <span>{sunlightPercent}% of max</span>
            </div>
            <div style={{ height: '8px', background: 'var(--glass-bg-subtle)', borderRadius: '999px', overflow: 'hidden' }}>
              <div
                style={{
                  height: '100%',
                  width: `${sunlightPercent}%`,
                  background: 'linear-gradient(90deg, #F2A900 0%, #ff8c00 100%)',
                  borderRadius: '999px',
                  transition: 'width 0.6s cubic-bezier(0.4, 0, 0.2, 1)',
                }}
              />
            </div>
          </div>
        </GlassPanel>

        <GlassPanel style={{ padding: '28px 32px', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '14px', marginBottom: '12px' }}>
            <FaLightbulb style={{ fontSize: '24px', color: 'var(--accent-primary)' }} />
            <h3 style={{ fontSize: '1.05rem', fontWeight: 700 }}>Agronomic Photoperiod Guide</h3>
          </div>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', lineHeight: 1.6 }}>
            Indoor crops like leafy greens and tomatoes thrive with 12–16 hours of 1,000+ Lux illumination. When natural light falls below 600 Lux, supplementary grow lights are recommended.
          </p>
          <div style={{ marginTop: '16px', fontSize: '0.8rem', color: 'var(--text-muted)' }}>
            Sensor: BH1750 / Photodiode Array • High Precision
          </div>
        </GlassPanel>
      </div>

      {/* ── Historical Graph ── */}
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
            <FaChartLine style={{ fontSize: '20px', color: '#F2A900' }} />
            <div>
              <h2 style={{ fontSize: '1.25rem', fontWeight: 700 }}>Historical Illumination Graph</h2>
              <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                24-Hour lux measurement stream over time
              </p>
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

        {chartLoading ? (
          <div style={{ height: '320px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ color: 'var(--text-muted)' }}>Loading light intensity chart...</span>
          </div>
        ) : chartData.labels.length > 0 ? (
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
            <p>No light records found for {selectedDate}.</p>
          </div>
        )}
      </GlassPanel>
    </>
  );
}
