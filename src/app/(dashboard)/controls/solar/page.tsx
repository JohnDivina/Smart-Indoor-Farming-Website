'use client';

import React, { useState } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import StatusBadge from '@/components/ui/StatusBadge';
import ControlToggle from '@/components/ui/ControlToggle';
import GuestBanner from '@/components/ui/GuestBanner';
import useControlStatus from '@/hooks/useControlStatus';
import { useSession } from 'next-auth/react';
import {
  FaSolarPanel,
  FaClock,
  FaBolt,
  FaSliders,
  FaBatteryFull,
  FaCheck,
} from 'react-icons/fa6';

export default function SolarPanelPage() {
  const { data: session } = useSession();
  const isGuest = session?.user?.isGuest ?? false;

  const { data: solarStatus, refetch } = useControlStatus<any>('/api/solar/status', 3000);

  const [toggling, setToggling] = useState(false);
  const [scheduleTime, setScheduleTime] = useState('06:00');
  const [scheduleStopTime, setScheduleStopTime] = useState('18:00');
  const [savingSchedule, setSavingSchedule] = useState(false);
  const [scheduleSaved, setScheduleSaved] = useState(false);

  const isSolarOn = solarStatus?.desired_state === 'on';
  const isOnline = solarStatus?.esp_online ?? false;
  const currentMode = solarStatus?.mode || 'manual';

  const handleToggleSolar = async () => {
    if (isGuest || toggling) return;
    const nextAction = isSolarOn ? 'off' : 'on';
    setToggling(true);

    try {
      const res = await fetch('/api/solar/command', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: nextAction }),
      });
      const data = await res.json();
      if (data.success) {
        refetch();
      }
    } catch (err) {
      console.error('Toggle solar error:', err);
    } finally {
      setToggling(false);
    }
  };

  const handleSaveSchedule = async (e: React.FormEvent) => {
    e.preventDefault();
    if (isGuest || savingSchedule) return;
    setSavingSchedule(true);
    setScheduleSaved(false);

    try {
      const res = await fetch('/api/solar/schedule', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          scheduleTime,
          scheduleStopTime,
        }),
      });
      const data = await res.json();
      if (data.success) {
        setScheduleSaved(true);
        refetch();
        setTimeout(() => setScheduleSaved(false), 3000);
      }
    } catch (err) {
      console.error('Save solar schedule error:', err);
    } finally {
      setSavingSchedule(false);
    }
  };

  const voltage = solarStatus?.voltage ?? 12.8;
  const current = solarStatus?.current ?? 1.9;
  const power = solarStatus?.power ?? (voltage * current);

  return (
    <>
      <Header
        title="Solar Panel Energy Management"
        subtitle="Photovoltaic generation tracking, relay switches, and energy harvesting telemetry."
        backHref="/dashboard"
      />

      {isGuest && <GuestBanner featureName="Solar Panel power switches and automation" />}

      {/* ── 3 Electrical Metric Meters ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '20px' }}>
        {/* Voltage */}
        <GlassPanel style={{ padding: '24px 28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '8px' }}>
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 600 }}>Panel Voltage</span>
            <FaBolt style={{ color: '#F2A900', fontSize: '18px' }} />
          </div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px' }}>
            <span style={{ fontSize: '2.8rem', fontWeight: 800, color: 'var(--text-primary)', lineHeight: 1 }}>
              {voltage.toFixed(1)}
            </span>
            <span style={{ fontSize: '1.2rem', fontWeight: 700, color: 'var(--text-secondary)' }}>V DC</span>
          </div>
          <span style={{ fontSize: '0.75rem', color: 'var(--accent-success)' }}>Nominal PV Operating Voltage</span>
        </GlassPanel>

        {/* Current */}
        <GlassPanel style={{ padding: '24px 28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '8px' }}>
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 600 }}>Output Current</span>
            <FaBatteryFull style={{ color: 'var(--accent-primary)', fontSize: '18px' }} />
          </div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px' }}>
            <span style={{ fontSize: '2.8rem', fontWeight: 800, color: 'var(--accent-primary)', lineHeight: 1 }}>
              {current.toFixed(2)}
            </span>
            <span style={{ fontSize: '1.2rem', fontWeight: 700, color: 'var(--text-secondary)' }}>Amperes</span>
          </div>
          <span style={{ fontSize: '0.75rem', color: 'var(--accent-success)' }}>Steady current flow</span>
        </GlassPanel>

        {/* Generated Power */}
        <GlassPanel style={{ padding: '24px 28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '8px' }}>
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 600 }}>Instantaneous Power</span>
            <FaSolarPanel style={{ color: '#3b82f6', fontSize: '18px' }} />
          </div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px' }}>
            <span style={{ fontSize: '2.8rem', fontWeight: 800, color: '#3b82f6', lineHeight: 1 }}>
              {power.toFixed(1)}
            </span>
            <span style={{ fontSize: '1.2rem', fontWeight: 700, color: 'var(--text-secondary)' }}>Watts</span>
          </div>
          <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>P = V × I harvest</span>
        </GlassPanel>
      </div>

      {/* ── Control Grid ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '24px' }}>
        {/* Switch Card */}
        <GlassPanel style={{ padding: '32px', display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center' }}>
          <div style={{ alignSelf: 'flex-end', marginBottom: '8px' }}>
            <StatusBadge
              status={isOnline ? 'connected' : 'disconnected'}
              label={isOnline ? 'ESP32 Solar Node Online' : 'ESP32 Offline'}
            />
          </div>

          <div
            style={{
              width: '100px',
              height: '100px',
              borderRadius: '50%',
              background: isSolarOn
                ? 'linear-gradient(135deg, rgba(242, 169, 0, 0.25) 0%, rgba(0, 102, 0, 0.15) 100%)'
                : 'var(--glass-bg-subtle)',
              border: `2px solid ${isSolarOn ? '#F2A900' : 'var(--glass-border)'}`,
              color: isSolarOn ? '#F2A900' : 'var(--text-muted)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '44px',
              marginBottom: '16px',
              boxShadow: isSolarOn ? '0 0 32px var(--accent-secondary-glow)' : 'none',
              transition: 'all 0.3s ease',
            }}
          >
            <FaSolarPanel />
          </div>

          <h2 style={{ fontSize: '1.4rem', fontWeight: 800, marginBottom: '4px' }}>
            Solar Relay: <span style={{ color: isSolarOn ? 'var(--accent-success)' : 'var(--text-muted)' }}>{isSolarOn ? 'POWERED (ON)' : 'ISOLATED (OFF)'}</span>
          </h2>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginBottom: '24px' }}>
            Current Mode: <strong style={{ textTransform: 'capitalize', color: 'var(--text-primary)' }}>{currentMode}</strong>
          </p>

          <ControlToggle
            isOn={isSolarOn}
            onToggle={handleToggleSolar}
            disabled={isGuest}
            loading={toggling}
            size="lg"
            labelOn="ON"
            labelOff="OFF"
          />
        </GlassPanel>

        {/* Schedule Form */}
        <GlassPanel style={{ padding: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px' }}>
            <FaClock style={{ fontSize: '22px', color: '#F2A900' }} />
            <div>
              <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Solar Generation Window</h3>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Set automatic sunrise activation hours</p>
            </div>
          </div>

          {scheduleSaved && (
            <div
              style={{
                padding: '10px 16px',
                borderRadius: '8px',
                background: 'rgba(16, 185, 129, 0.12)',
                color: 'var(--accent-success)',
                border: '1px solid rgba(16, 185, 129, 0.25)',
                fontSize: '0.85rem',
                fontWeight: 600,
                marginBottom: '16px',
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
              }}
            >
              <FaCheck /> Solar schedule saved successfully!
            </div>
          )}

          <form onSubmit={handleSaveSchedule}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
              <div className="form-group">
                <label className="form-label" htmlFor="solarScheduleTime">
                  Activation Time
                </label>
                <input
                  id="solarScheduleTime"
                  type="time"
                  className="form-input"
                  value={scheduleTime}
                  onChange={(e) => setScheduleTime(e.target.value)}
                  disabled={isGuest}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="solarScheduleStopTime">
                  Deactivation Time
                </label>
                <input
                  id="solarScheduleStopTime"
                  type="time"
                  className="form-input"
                  value={scheduleStopTime}
                  onChange={(e) => setScheduleStopTime(e.target.value)}
                  disabled={isGuest}
                />
              </div>
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              disabled={isGuest || savingSchedule}
              style={{ width: '100%', height: '46px', marginTop: '16px' }}
            >
              {savingSchedule ? 'Saving...' : 'SAVE SOLAR SCHEDULE'}
            </button>
          </form>
        </GlassPanel>
      </div>
    </>
  );
}
