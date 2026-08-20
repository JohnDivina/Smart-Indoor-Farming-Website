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
  FaDroplet,
  FaClock,
  FaSliders,
  FaListCheck,
  FaCheck,
  FaWandMagicSparkles,
  FaHand,
} from 'react-icons/fa6';

export default function FertigationPage() {
  const { data: session } = useSession();
  const isGuest = session?.user?.isGuest ?? false;

  const { data: pumpStatus, refetch } = useControlStatus<any>('/api/fertigation/status', 3000);
  const { data: lastIrrigation, refetch: refetchLastIrrigation } = useControlStatus<any>('/api/fertigation/last-irrigation', 6000);
  const { data: logsData, refetch: refetchLogs } = useControlStatus<any>('/api/fertigation/log', 6000);

  const [toggling, setToggling] = useState(false);
  const [scheduleTime, setScheduleTime] = useState('06:30');
  const [scheduleStopTime, setScheduleStopTime] = useState('07:00');
  const [duration, setDuration] = useState(30);
  const [savingSchedule, setSavingSchedule] = useState(false);
  const [scheduleSaved, setScheduleSaved] = useState(false);
  const [settingMode, setSettingMode] = useState(false);

  const isPumpOn = pumpStatus?.actual_pump_state === 'on';
  const isOnline = pumpStatus?.esp_online ?? false;
  const currentMode = pumpStatus?.mode || 'manual';

  const handleTogglePump = async () => {
    if (isGuest || toggling) return;
    const nextAction = isPumpOn ? 'off' : 'on';
    setToggling(true);

    try {
      const res = await fetch('/api/fertigation/manual-control', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: nextAction }),
      });
      const data = await res.json();
      if (data.success) {
        refetch();
        refetchLastIrrigation();
        refetchLogs();
      }
    } catch (err) {
      console.error('Toggle pump error:', err);
    } finally {
      setToggling(false);
    }
  };

  const handleModeChange = async (mode: 'manual' | 'schedule' | 'auto') => {
    if (isGuest || settingMode) return;
    setSettingMode(true);
    try {
      const res = await fetch('/api/fertigation/mode', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode }),
      });
      if (res.ok) refetch();
    } catch (err) {
      console.error('Set mode error:', err);
    } finally {
      setSettingMode(false);
    }
  };

  const handleSaveSchedule = async (e: React.FormEvent) => {
    e.preventDefault();
    if (isGuest || savingSchedule) return;
    setSavingSchedule(true);
    setScheduleSaved(false);

    try {
      const res = await fetch('/api/fertigation/schedule', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          scheduleTime,
          scheduleStopTime,
          durationMinutes: Number(duration),
        }),
      });
      const data = await res.json();
      if (data.success) {
        setScheduleSaved(true);
        refetch();
        setTimeout(() => setScheduleSaved(false), 3000);
      }
    } catch (err) {
      console.error('Save schedule error:', err);
    } finally {
      setSavingSchedule(false);
    }
  };

  return (
    <>
      <Header
        title="Fertigation &amp; Irrigation Control"
        subtitle="Automated nutrient dosing pumps, water circulation, and scheduled hydroponic irrigation."
        backHref="/dashboard"
      />

      {isGuest && <GuestBanner featureName="Fertigation pump controls and scheduling" />}

      {/* ── Mode Selection Pills ── */}
      <GlassPanel style={{ padding: '16px 24px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '16px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
          <FaSliders style={{ color: '#3b82f6', fontSize: '18px' }} />
          <span style={{ fontSize: '0.9rem', fontWeight: 700 }}>Operating Mode:</span>
        </div>

        <div style={{ display: 'flex', gap: '8px' }}>
          {(['manual', 'schedule', 'auto'] as const).map((mode) => (
            <button
              key={mode}
              type="button"
              onClick={() => handleModeChange(mode)}
              disabled={isGuest || settingMode}
              style={{
                padding: '8px 18px',
                borderRadius: '999px',
                fontSize: '0.85rem',
                fontWeight: 700,
                textTransform: 'capitalize',
                background: currentMode === mode ? '#3b82f6' : 'var(--glass-bg-subtle)',
                color: currentMode === mode ? '#ffffff' : 'var(--text-secondary)',
                border: `1px solid ${currentMode === mode ? '#3b82f6' : 'var(--glass-border)'}`,
                cursor: isGuest ? 'not-allowed' : 'pointer',
                transition: 'all 0.2s ease',
              }}
            >
              {mode}
            </button>
          ))}
        </div>
      </GlassPanel>

      {/* ── Control Panel Grid ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '24px' }}>
        {/* Pump Toggle */}
        <GlassPanel style={{ padding: '32px', display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center' }}>
          <div style={{ alignSelf: 'flex-end', marginBottom: '8px' }}>
            <StatusBadge
              status={isOnline ? 'connected' : 'disconnected'}
              label={isOnline ? 'ESP32 Pump Node Online' : 'ESP32 Offline'}
            />
          </div>

          <div
            style={{
              width: '100px',
              height: '100px',
              borderRadius: '50%',
              background: isPumpOn
                ? 'linear-gradient(135deg, rgba(59, 130, 246, 0.25) 0%, rgba(16, 185, 129, 0.15) 100%)'
                : 'var(--glass-bg-subtle)',
              border: `2px solid ${isPumpOn ? '#3b82f6' : 'var(--glass-border)'}`,
              color: isPumpOn ? '#3b82f6' : 'var(--text-muted)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '44px',
              marginBottom: '16px',
              boxShadow: isPumpOn ? '0 0 32px rgba(59, 130, 246, 0.35)' : 'none',
              animation: isPumpOn ? 'pulseGlow 2s infinite ease-in-out' : 'none',
            }}
          >
            <FaDroplet />
          </div>

          <h2 style={{ fontSize: '1.4rem', fontWeight: 800, marginBottom: '4px' }}>
            Pump State: <span style={{ color: isPumpOn ? 'var(--accent-success)' : 'var(--text-muted)' }}>{isPumpOn ? 'PUMPING (ON)' : 'IDLE (OFF)'}</span>
          </h2>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginBottom: '24px' }}>
            Mode: <strong style={{ textTransform: 'capitalize', color: 'var(--text-primary)' }}>{currentMode}</strong>
          </p>

          <ControlToggle
            isOn={isPumpOn}
            onToggle={handleTogglePump}
            disabled={isGuest}
            loading={toggling}
            size="lg"
            labelOn="ON"
            labelOff="OFF"
          />

          <div style={{ marginTop: '24px', fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
            Last Irrigation: <strong>{lastIrrigation?.last_irrigation || 'No irrigation logged today'}</strong>
          </div>
        </GlassPanel>

        {/* Schedule Form */}
        <GlassPanel style={{ padding: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px' }}>
            <FaClock style={{ fontSize: '22px', color: '#3b82f6' }} />
            <div>
              <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Fertigation Schedule</h3>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Set scheduled irrigation start and duration</p>
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
              <FaCheck /> Schedule saved and pushed to ESP32!
            </div>
          )}

          <form onSubmit={handleSaveSchedule}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
              <div className="form-group">
                <label className="form-label" htmlFor="fertScheduleTime">
                  Start Time
                </label>
                <input
                  id="fertScheduleTime"
                  type="time"
                  className="form-input"
                  value={scheduleTime}
                  onChange={(e) => setScheduleTime(e.target.value)}
                  disabled={isGuest}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="fertScheduleStopTime">
                  Stop Time
                </label>
                <input
                  id="fertScheduleStopTime"
                  type="time"
                  className="form-input"
                  value={scheduleStopTime}
                  onChange={(e) => setScheduleStopTime(e.target.value)}
                  disabled={isGuest}
                />
              </div>
            </div>

            <div className="form-group">
              <label className="form-label" htmlFor="fertDuration">
                Irrigation Duration (Minutes)
              </label>
              <input
                id="fertDuration"
                type="number"
                min={1}
                max={180}
                className="form-input"
                value={duration}
                onChange={(e) => setDuration(Number(e.target.value))}
                disabled={isGuest}
              />
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              disabled={isGuest || savingSchedule}
              style={{ width: '100%', height: '46px', marginTop: '10px' }}
            >
              {savingSchedule ? 'Saving Schedule...' : 'SAVE & APPLY SCHEDULE'}
            </button>
          </form>
        </GlassPanel>
      </div>

      {/* ── Irrigation Logs Table ── */}
      <GlassPanel style={{ padding: '28px 32px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '20px' }}>
          <FaListCheck style={{ fontSize: '20px', color: '#3b82f6' }} />
          <div>
            <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Irrigation History Logs</h3>
            <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Historical dosing events and cycle executions</p>
          </div>
        </div>

        {logsData?.logs && logsData.logs.length > 0 ? (
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.9rem', textAlign: 'left' }}>
              <thead>
                <tr style={{ borderBottom: '1px solid var(--glass-border)', color: 'var(--text-muted)' }}>
                  <th style={{ padding: '12px 16px' }}>Event ID</th>
                  <th style={{ padding: '12px 16px' }}>Action</th>
                  <th style={{ padding: '12px 16px' }}>Trigger Source</th>
                  <th style={{ padding: '12px 16px' }}>Timestamp</th>
                </tr>
              </thead>
              <tbody>
                {logsData.logs.map((log: any) => (
                  <tr key={log.id} style={{ borderBottom: '1px solid var(--glass-border)' }}>
                    <td style={{ padding: '12px 16px', color: 'var(--text-muted)' }}>#{log.id}</td>
                    <td style={{ padding: '12px 16px' }}>
                      <span
                        style={{
                          padding: '4px 10px',
                          borderRadius: '6px',
                          fontSize: '0.75rem',
                          fontWeight: 700,
                          background: log.action === 'START' ? 'rgba(59, 130, 246, 0.15)' : 'rgba(234, 76, 76, 0.15)',
                          color: log.action === 'START' ? '#3b82f6' : 'var(--accent-danger)',
                        }}
                      >
                        {log.action}
                      </span>
                    </td>
                    <td style={{ padding: '12px 16px', color: 'var(--text-secondary)' }}>{log.source}</td>
                    <td style={{ padding: '12px 16px', color: 'var(--text-primary)', fontWeight: 500 }}>
                      {log.timestamp}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div style={{ padding: '32px', textAlign: 'center', color: 'var(--text-muted)' }}>
            No irrigation activity recorded yet.
          </div>
        )}
      </GlassPanel>
    </>
  );
}
