'use client';

import React, { useState, useEffect } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import MonthlyAvgChart from '@/components/charts/MonthlyAvgChart';
import AnimatedNumber from '@/components/motion/AnimatedNumber';
import { FaSun, FaChartColumn, FaBolt } from 'react-icons/fa6';

export default function LightInsightsPage() {
  const [data, setData] = useState<any>({ months: [], lux: [] });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/monthly-avg/light')
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
        title="Illumination Trend Analytics"
        subtitle="Historical photoperiod illumination cycles and cumulative lux exposure trends."
      />

      {/* ── Metric Highlights ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '20px' }}>
        <GlassPanel style={{ padding: '24px 28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '8px' }}>
            <FaSun style={{ color: '#F2A900', fontSize: '20px' }} />
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 600 }}>Daily Light Integral (DLI)</span>
          </div>
          <div style={{ fontSize: '2.2rem', fontWeight: 800, color: '#F2A900' }}>
            <AnimatedNumber value={14.2} decimals={1} suffix=" mol/m²/d" />
          </div>
          <span style={{ fontSize: '0.75rem', color: 'var(--accent-success)' }}>High efficiency photosynthetic uptake</span>
        </GlassPanel>

        <GlassPanel style={{ padding: '24px 28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '8px' }}>
            <FaBolt style={{ color: 'var(--accent-primary)', fontSize: '20px' }} />
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 600 }}>Solar Energy Utilization</span>
          </div>
          <div style={{ fontSize: '2.2rem', fontWeight: 800, color: 'var(--accent-primary)' }}>
            <AnimatedNumber value={88.4} decimals={1} suffix="%" />
          </div>
          <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Panel &amp; sensor sync efficiency</span>
        </GlassPanel>
      </div>

      {/* ── Monthly Light Chart ── */}
      <GlassPanel style={{ padding: '28px 32px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '24px' }}>
          <FaChartColumn style={{ fontSize: '22px', color: '#F2A900' }} />
          <div>
            <h2 style={{ fontSize: '1.25rem', fontWeight: 700 }}>6-Month Average Illumination</h2>
            <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
              Monthly aggregated mean lux measurements
            </p>
          </div>
        </div>

        {loading ? (
          <div style={{ height: '320px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)' }}>
            Loading illumination analytics...
          </div>
        ) : (
          <MonthlyAvgChart
            months={data.months}
            datasets={[
              {
                label: 'Mean Illumination (Lux)',
                data: data.lux,
                color: '#F2A900',
                unit: 'Lux',
              },
            ]}
            height={340}
          />
        )}
      </GlassPanel>
    </>
  );
}
