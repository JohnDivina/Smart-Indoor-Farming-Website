'use client';

import React, { useState, useEffect } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import MonthlyAvgChart from '@/components/charts/MonthlyAvgChart';
import { FaChartColumn, FaTemperatureHalf, FaDroplet, FaLightbulb } from 'react-icons/fa6';

export default function TempHumidityInsightsPage() {
  const [data, setData] = useState<any>({ months: [], temperature: [], humidity: [] });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/monthly-avg/temp-humid')
      .then((res) => res.json())
      .then((json) => {
        if (json.success) setData(json);
      })
      .catch(console.error)
      .finally(() => setLoading(false));
  }, []);

  return (
    <>
      <Header
        title="Climate Trend Analytics"
        subtitle="Longitudinal 6-month monthly aggregations and micro-climate stability patterns."
      />

      {/* ── Key Statistics ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))', gap: '20px' }}>
        <GlassPanel style={{ padding: '24px 28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '8px' }}>
            <FaTemperatureHalf style={{ color: 'var(--accent-primary)', fontSize: '20px' }} />
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 600 }}>Avg Seasonal Temp</span>
          </div>
          <div style={{ fontSize: '2.2rem', fontWeight: 800, color: 'var(--accent-primary)' }}>
            26.8°C
          </div>
          <span style={{ fontSize: '0.75rem', color: 'var(--accent-success)' }}>± 1.4°C Micro-climate Variance</span>
        </GlassPanel>

        <GlassPanel style={{ padding: '24px 28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '8px' }}>
            <FaDroplet style={{ color: '#3b82f6', fontSize: '20px' }} />
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 600 }}>Avg Relative Humidity</span>
          </div>
          <div style={{ fontSize: '2.2rem', fontWeight: 800, color: '#3b82f6' }}>
            68.5%
          </div>
          <span style={{ fontSize: '0.75rem', color: 'var(--accent-success)' }}>Optimal Transpiration Zone</span>
        </GlassPanel>

        <GlassPanel style={{ padding: '24px 28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '8px' }}>
            <FaLightbulb style={{ color: '#F2A900', fontSize: '20px' }} />
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 600 }}>Vapor Pressure Deficit (VPD)</span>
          </div>
          <div style={{ fontSize: '2.2rem', fontWeight: 800, color: '#F2A900' }}>
            1.05 kPa
          </div>
          <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Healthy stomatal opening</span>
        </GlassPanel>
      </div>

      {/* ── Monthly Bar Chart ── */}
      <GlassPanel style={{ padding: '28px 32px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '24px' }}>
          <FaChartColumn style={{ fontSize: '22px', color: 'var(--accent-primary)' }} />
          <div>
            <h2 style={{ fontSize: '1.25rem', fontWeight: 700 }}>6-Month Climate Aggregation</h2>
            <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
              Monthly aggregated mean temperature vs humidity
            </p>
          </div>
        </div>

        {loading ? (
          <div style={{ height: '320px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)' }}>
            Loading trend insights...
          </div>
        ) : (
          <MonthlyAvgChart
            months={data.months}
            datasets={[
              {
                label: 'Mean Temperature (°C)',
                data: data.temperature,
                color: '#009639',
                unit: '°C',
              },
              {
                label: 'Mean Humidity (%)',
                data: data.humidity,
                color: '#3b82f6',
                unit: '%',
              },
            ]}
            height={340}
          />
        )}
      </GlassPanel>
    </>
  );
}
