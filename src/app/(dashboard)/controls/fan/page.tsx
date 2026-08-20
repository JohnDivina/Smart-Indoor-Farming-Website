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
  FaFan,
  FaClock,
  FaSliders,
  FaPowerOff,
  FaListCheck,
  FaCalendarDays,
  FaCheck,
} from 'react-icons/fa6';

export default function AuxiliaryFanPage() {
  const { data: session } = useSession();
  const isGuest = session?.user?.isGuest ?? false;

  const { data: fanStatus, refetch } = useControlStatus<any>('/api/fan/status', 3000);
  const { data: lastRunData, refetch: refetchLastRun } = useControlStatus<any>('/api/fan/last-run', 6000);
  const { data: logsData, refetch: refetchLogs } = useControlStatus<any>('/api/fan/log', 6000);

  const [toggling, setToggling] = useState(false);
  const [scheduleTime, setScheduleTime] = useState('08:00');
  const [scheduleStopTime, setScheduleStopTime] = useState('08:30');
  const [duration, setDuration] = useState(30);
  const [savingSchedule, setSavingSchedule] = useState(false);
  const [scheduleSaved, setScheduleSaved] = useState(false);

  const isFanOn = fanStatus?.esp_fan_state === 'on';
  const isOnline = fanStatus?.esp_online ?? false;
  const currentMode = fanStatus?.mode || 'manual';

  // Toggle Fan Power
  const handleToggleFan = async () => {
    if (isGuest || toggling) return;
    const nextAction = isFanOn ? 'off' : 'on';
    setToggling(true);

    try {
      const res = await fetch('/api/fan/manual-control', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: nextAction }),
      });
      const data = await res.json();
      if (data.success) {
        refetch();
        refetchLastRun();
        refetchLogs();
      }
    } catch (err) {
      console.error('Toggle fan error:', err);
    } finally {
      setToggling(false);
    }
  };

  // Save Schedule
  const handleSaveSchedule = async (e: React.FormEvent) => {
    e.preventDefault();
    if (isGuest || savingSchedule) return;
    setSavingSchedule(true);
    setScheduleSaved(false);

    try {
      const res = await fetch('/api/fan/schedule', {
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
        title="Auxiliary Ventilation Fan"
        subtitle="Greenhouse airflow management, temperature dissipation, and scheduled circulation."
        backHref="/dashboard"
      />

      {isGuest && <GuestBanner featureName="Auxiliary Fan controls and scheduling" />}

      {/* ── Main Control Panel Grid ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '24px' }}>
        {/* Power Toggle Card */}
        <GlassPanel style={{ padding: '32px', display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center' }}>
          <div style={{ alignSelf: 'flex-end', marginBottom: '8px' }}>
            <StatusBadge
              status={isOnline ? 'connected' : 'disconnected'}
              label={isOnline ? 'ESP32 Connected' : 'ESP32 Offline'}
            />
          </div>

          <div
            style={{
              width: '100px',
              height: '100px',
              borderRadius: '50%',
              background: isFanOn
                ? 'linear-gradient(135deg, rgba(0, 102, 0, 0.2) 0%, rgba(242, 169, 0, 0.15) 100%)'
                : 'var(--glass-bg-subtle)',
              border: `2px solid ${isFanOn ? 'var(--accent-primary)' : 'var(--glass-border)'}`,
              color: isFanOn ? 'var(--accent-primary)' : 'var(--text-muted)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '44px',
              marginBottom: '16px',
              boxShadow: isFanOn ? '0 0 32px var(--accent-primary-glow)' : 'none',
              animation: isFanOn ? 'spin 4s linear infinite' : 'none',
              transition: 'all 0.3s ease',
            }}
          >
            <FaFan />
          </div>

          <h2 style={{ fontSize: '1.4rem', fontWeight: 800, marginBottom: '4px' }}>
            Fan State: <span style={{ color: isFanOn ? 'var(--accent-success)' : 'var(--text-muted)' }}>{isFanOn ? 'ACTIVE (ON)' : 'STANDBY (OFF)'}</span>
          </h2>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginBottom: '24px' }}>
            Current Mode: <strong style={{ textTransform: 'capitalize', color: 'var(--text-primary)' }}>{currentMode}</strong>
          </p>

          <ControlToggle
            isOn={isFanOn}
            onToggle={handleToggleFan}
            disabled={isGuest}
            loading={toggling}
            size="lg"
            labelOn="ON"
            labelOff="OFF"
          />

          <div style={{ marginTop: '24px', fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
            Last Run: <strong>{lastRunData?.last_run || 'No recorded run today'}</strong>
          </div>
        </GlassPanel>

        {/* Schedule Configuration Card */}
        <GlassPanel style={{ padding: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px' }}>
            <FaClock style={{ fontSize: '22px', color: 'var(--accent-primary)' }} />
            <div>
              <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Automated Schedule</h3>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Configure automated daily runtime intervals</p>
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
              <FaCheck /> Schedule updated and synced to ESP32!
            </div>
          )}

          <form onSubmit={handleSaveSchedule}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
              <div className="form-group">
                <label className="form-label" htmlFor="scheduleTime">
                  Start Time
                </label>
                <input
                  id="scheduleTime"
                  type="time"
                  className="form-input"
                  value={scheduleTime}
                  onChange={(e) => setScheduleTime(e.target.value)}
                  disabled={isGuest}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="scheduleStopTime">
                  Stop Time
                </label>
                <input
                  id="scheduleStopTime"
                  type="time"
                  className="form-input"
                  value={scheduleStopTime}
                  onChange={(e) => setScheduleStopTime(e.target.value)}
                  disabled={isGuest}
                />
              </div>
            </div>

            <div className="form-group">
              <label className="form-label" htmlFor="duration">
                Run Duration (Minutes)
              </label>
              <input
                id="duration"
                type="number"
                min={1}
                max={360}
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

      {/* ── Activity History Log Table ── */}
      <GlassPanel style={{ padding: '28px 32px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '20px' }}>
          <FaListCheck style={{ fontSize: '20px', color: 'var(--accent-primary)' }} />
          <div>
            <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Recent Fan Activity Logs</h3>
            <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Live operational history and start/stop timestamps</p>
          </div>
        </div>

        {logsData?.logs && logsData.logs.length > 0 ? (
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.9rem', textAlign: 'left' }}>
              <thead>
                <tr style={{ borderBottom: '1px solid var(--glass-border)', color: 'var(--text-muted)' }}>
                  <th style={{ padding: '12px 16px' }}>Event ID</th>
                  <th style={{ padding: '12px 16px' }}>Action Trigger</th>
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
                          background: log.action === 'START' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(234, 76, 76, 0.15)',
                          color: log.action === 'START' ? 'var(--accent-success)' : 'var(--accent-danger)',
                        }}
                      >
                        {log.action}
                      </span>
                    </td>
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
            No fan activity recorded yet.
          </div>
        )}
      </GlassPanel>
    </>
  );
}
