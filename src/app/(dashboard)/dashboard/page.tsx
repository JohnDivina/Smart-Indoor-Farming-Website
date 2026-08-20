'use client';

import React, { useState, Suspense } from 'react';
import Header from '@/components/layout/Header';
import SensorCard from '@/components/ui/SensorCard';
import GlassPanel from '@/components/ui/GlassPanel';
import StatusBadge from '@/components/ui/StatusBadge';
import GuestBanner from '@/components/ui/GuestBanner';
import LoadingSpinner from '@/components/ui/LoadingSpinner';
import Modal from '@/components/ui/Modal';
import useSensorData from '@/hooks/useSensorData';
import useControlStatus from '@/hooks/useControlStatus';
import { useSession } from 'next-auth/react';
import { useSearchParams } from 'next/navigation';
import {
  FaTemperatureHalf,
  FaSun,
  FaFlask,
  FaFan,
  FaDroplet,
  FaSolarPanel,
  FaCloudSun,
  FaLock,
} from 'react-icons/fa6';
import Link from 'next/link';

function DashboardContent() {
  const { data: session } = useSession();
  const searchParams = useSearchParams();
  const guestRestricted = searchParams.get('guest_restricted') === '1';

  const isGuest = session?.user?.isGuest ?? false;

  const [showRestrictedModal, setShowRestrictedModal] = useState(guestRestricted);

  // Poll sensor readings
  const { data: tempHumid } = useSensorData<any>('/api/environment/temp-humid', 6000);
  const { data: lightData } = useSensorData<any>('/api/environment/light', 6000);
  const { data: npkData } = useSensorData<any>('/api/npk/data', 8000);
  const { data: weather } = useSensorData<any>('/api/weather', 60000);

  // Poll controls
  const { data: fanStatus } = useControlStatus<any>('/api/fan/status', 5000);
  const { data: fertigationStatus } = useControlStatus<any>('/api/fertigation/status', 5000);
  const { data: solarStatus } = useControlStatus<any>('/api/solar/status', 5000);

  const handleLockedClick = () => {
    setShowRestrictedModal(true);
  };

  return (
    <>
      <Header
        title="Smart Farm Overview"
        subtitle="Welcome back! Real-time telemetry and automation status for CLSU greenhouse."
      />

      {isGuest && <GuestBanner featureName="hardware controls and system settings" />}

      {/* ── Environmental Sensors Section ── */}
      <div className="section-header">
        <div className="section-bullet" />
        <h2>Environmental Sensors</h2>
      </div>

      <div className="dashboard-grid">
        {/* Temp & Humidity Card */}
        <SensorCard
          title="Temperature & Humidity"
          value={tempHumid?.temperature ?? '--'}
          unit="°C"
          secondaryValue={tempHumid?.humidity ? `${tempHumid.humidity}` : '--'}
          secondaryUnit="%"
          secondaryLabel="Humidity"
          icon={<FaTemperatureHalf />}
          iconBg="rgba(16, 185, 129, 0.12)"
          accentColor="var(--accent-primary)"
          status={tempHumid?.status || 'disconnected'}
          timestamp={tempHumid?.timestamp}
          href="/sensors/temp-humidity"
        />

        {/* Light Intensity Card */}
        <SensorCard
          title="Light Intensity"
          value={lightData?.lux !== undefined ? lightData.lux.toLocaleString() : '--'}
          unit="Lux"
          icon={<FaSun />}
          iconBg="rgba(242, 169, 0, 0.15)"
          accentColor="#F2A900"
          status={lightData?.status || 'disconnected'}
          timestamp={lightData?.timestamp}
          href="/sensors/light-intensity"
        />

        {/* Soil NPK Card */}
        <SensorCard
          title="Soil Nutrients (NPK)"
          value={npkData?.nitrogen ?? '--'}
          unit="N"
          secondaryValue={`${npkData?.phosphorus ?? '--'}P / ${npkData?.potassium ?? '--'}K`}
          secondaryUnit="mg/kg"
          secondaryLabel="P & K"
          icon={<FaFlask />}
          iconBg="rgba(59, 130, 246, 0.15)"
          accentColor="#3b82f6"
          status={npkData?.status || 'disconnected'}
          timestamp={npkData?.timestamp}
          href="/sensors/npk"
        />
      </div>

      {/* ── Automated Hardware Controls Section ── */}
      <div className="section-header" style={{ marginTop: '12px' }}>
        <div className="section-bullet" />
        <h2>Automated Controls</h2>
      </div>

      <div className="dashboard-grid">
        {/* Auxiliary Fan Card */}
        <GlassPanel
          hoverable
          className={isGuest ? 'guest-locked-card' : ''}
          onClick={isGuest ? handleLockedClick : undefined}
          style={{ padding: '24px', display: 'flex', flexDirection: 'column', gap: '16px' }}
        >
          {isGuest && (
            <div className="guest-lock-badge">
              <span>🔒</span> Guest Locked
            </div>
          )}

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
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
                <FaFan />
              </div>
              <div>
                <h3 style={{ fontSize: '1.05rem', fontWeight: 600 }}>Auxiliary Fan</h3>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                  Mode: {fanStatus?.mode || 'manual'}
                </span>
              </div>
            </div>

            <StatusBadge status={fanStatus?.esp_online ? 'connected' : 'disconnected'} />
          </div>

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 'auto' }}>
            <div>
              <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block' }}>
                Operational State
              </span>
              <span
                style={{
                  fontSize: '1.5rem',
                  fontWeight: 800,
                  color: fanStatus?.esp_fan_state === 'on' ? 'var(--accent-success)' : 'var(--text-muted)',
                  textTransform: 'uppercase',
                }}
              >
                {fanStatus?.esp_fan_state || 'OFF'}
              </span>
            </div>

            {!isGuest && (
              <Link href="/controls/fan" className="btn btn-secondary" style={{ fontSize: '0.85rem', padding: '8px 16px' }}>
                Control Fan &rarr;
              </Link>
            )}
          </div>
        </GlassPanel>

        {/* Fertigation Card */}
        <GlassPanel
          hoverable
          className={isGuest ? 'guest-locked-card' : ''}
          onClick={isGuest ? handleLockedClick : undefined}
          style={{ padding: '24px', display: 'flex', flexDirection: 'column', gap: '16px' }}
        >
          {isGuest && (
            <div className="guest-lock-badge">
              <span>🔒</span> Guest Locked
            </div>
          )}

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
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
                <h3 style={{ fontSize: '1.05rem', fontWeight: 600 }}>Fertigation Pump</h3>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                  Mode: {fertigationStatus?.mode || 'manual'}
                </span>
              </div>
            </div>

            <StatusBadge status={fertigationStatus?.esp_online ? 'connected' : 'disconnected'} />
          </div>

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 'auto' }}>
            <div>
              <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block' }}>
                Pump Status
              </span>
              <span
                style={{
                  fontSize: '1.5rem',
                  fontWeight: 800,
                  color: fertigationStatus?.actual_pump_state === 'on' ? 'var(--accent-success)' : 'var(--text-muted)',
                  textTransform: 'uppercase',
                }}
              >
                {fertigationStatus?.actual_pump_state || 'OFF'}
              </span>
            </div>

            {!isGuest && (
              <Link href="/controls/fertigation" className="btn btn-secondary" style={{ fontSize: '0.85rem', padding: '8px 16px' }}>
                Pump Control &rarr;
              </Link>
            )}
          </div>
        </GlassPanel>

        {/* Solar Panel Card */}
        <GlassPanel
          hoverable
          className={isGuest ? 'guest-locked-card' : ''}
          onClick={isGuest ? handleLockedClick : undefined}
          style={{ padding: '24px', display: 'flex', flexDirection: 'column', gap: '16px' }}
        >
          {isGuest && (
            <div className="guest-lock-badge">
              <span>🔒</span> Guest Locked
            </div>
          )}

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
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
                <FaSolarPanel />
              </div>
              <div>
                <h3 style={{ fontSize: '1.05rem', fontWeight: 600 }}>Solar Panels</h3>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                  State: {solarStatus?.desired_state || 'off'}
                </span>
              </div>
            </div>

            <StatusBadge status={solarStatus?.esp_online ? 'connected' : 'disconnected'} />
          </div>

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 'auto' }}>
            <div>
              <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block' }}>
                Energy Output
              </span>
              <span style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--text-primary)' }}>
                {solarStatus?.power ? `${solarStatus.power} W` : 'Active'}
              </span>
            </div>

            {!isGuest && (
              <Link href="/controls/solar" className="btn btn-secondary" style={{ fontSize: '0.85rem', padding: '8px 16px' }}>
                Panel Control &rarr;
              </Link>
            )}
          </div>
        </GlassPanel>
      </div>

      {/* ── Weather & Location Telemetry ── */}
      <div className="section-header" style={{ marginTop: '12px' }}>
        <div className="section-bullet" />
        <h2>External Environment &amp; Weather</h2>
      </div>

      <GlassPanel style={{ padding: '24px 32px' }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '20px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '18px' }}>
            <div
              style={{
                width: '56px',
                height: '56px',
                borderRadius: '16px',
                background: 'linear-gradient(135deg, rgba(242, 169, 0, 0.2) 0%, rgba(0, 102, 0, 0.15) 100%)',
                color: '#F2A900',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '28px',
              }}
            >
              <FaCloudSun />
            </div>
            <div>
              <h3 style={{ fontSize: '1.2rem', fontWeight: 700 }}>
                {weather?.city || 'Science City of Muñoz'}, {weather?.province || 'Nueva Ecija'}
              </h3>
              <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', textTransform: 'capitalize' }}>
                {weather?.description || 'Partly Cloudy'} • Atmospheric Conditions
              </p>
            </div>
          </div>

          <div style={{ display: 'flex', gap: '28px', alignItems: 'center' }}>
            <div>
              <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', display: 'block' }}>Outdoor Temp</span>
              <span style={{ fontSize: '1.7rem', fontWeight: 800, color: 'var(--text-primary)' }}>
                {weather?.temperature ? `${weather.temperature}°C` : '--'}
              </span>
            </div>
            <div>
              <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', display: 'block' }}>Humidity</span>
              <span style={{ fontSize: '1.7rem', fontWeight: 800, color: 'var(--accent-primary)' }}>
                {weather?.humidity ? `${weather.humidity}%` : '--'}
              </span>
            </div>
            <div>
              <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', display: 'block' }}>Wind Speed</span>
              <span style={{ fontSize: '1.7rem', fontWeight: 800, color: '#3b82f6' }}>
                {weather?.windSpeed ? `${weather.windSpeed} m/s` : '3.2 m/s'}
              </span>
            </div>
          </div>
        </div>
      </GlassPanel>

      {/* Guest Mode Restriction Modal */}
      <Modal
        open={showRestrictedModal}
        onClose={() => setShowRestrictedModal(false)}
        title="Guest Mode Restriction"
        description="You are currently browsing the dashboard in Guest Preview mode. Hardware controls, irrigation triggers, and account settings require an authenticated account."
        icon={<FaLock />}
        iconColor="#eab308"
      >
        <div style={{ display: 'flex', gap: '12px', justifyContent: 'center', marginTop: '16px' }}>
          <button
            type="button"
            className="btn btn-secondary"
            onClick={() => setShowRestrictedModal(false)}
          >
            Continue Browsing
          </button>
          <Link href="/login" className="btn btn-primary">
            Sign In for Full Access &rarr;
          </Link>
        </div>
      </Modal>
    </>
  );
}

export default function DashboardPage() {
  return (
    <Suspense fallback={<LoadingSpinner fullScreen text="Loading Dashboard Overview..." />}>
      <DashboardContent />
    </Suspense>
  );
}
