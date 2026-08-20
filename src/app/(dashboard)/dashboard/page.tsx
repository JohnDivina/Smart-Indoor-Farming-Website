'use client';

import React, { useState, useEffect, Suspense } from 'react';
import Header from '@/components/layout/Header';
import SensorCard from '@/components/ui/SensorCard';
import GlassPanel from '@/components/ui/GlassPanel';
import StatusBadge from '@/components/ui/StatusBadge';
import GuestBanner from '@/components/ui/GuestBanner';
import LoadingSpinner from '@/components/ui/LoadingSpinner';
import Modal from '@/components/ui/Modal';
import SensorChart from '@/components/charts/SensorChart';
import NPKChart from '@/components/charts/NPKChart';
import useSensorData from '@/hooks/useSensorData';
import useControlStatus from '@/hooks/useControlStatus';
import { useSession } from 'next-auth/react';
import { useSearchParams } from 'next/navigation';
import { format } from 'date-fns';
import {
  FaTemperatureHalf,
  FaSun,
  FaFlask,
  FaFan,
  FaDroplet,
  FaSolarPanel,
  FaCloudSun,
  FaLock,
  FaBolt,
  FaChartLine,
  FaCalendarDays,
  FaLocationDot,
  FaArrowRotateRight,
} from 'react-icons/fa6';
import Link from 'next/link';

function DashboardContent() {
  const { data: session } = useSession();
  const searchParams = useSearchParams();
  const guestRestricted = searchParams.get('guest_restricted') === '1';

  const isGuest = session?.user?.isGuest ?? false;

  const [showRestrictedModal, setShowRestrictedModal] = useState(guestRestricted);

  // Poll live sensor readings
  const { data: tempHumid } = useSensorData<any>('/api/environment/temp-humid', 5000);
  const { data: lightData } = useSensorData<any>('/api/environment/light', 5000);
  const { data: npkData } = useSensorData<any>('/api/npk/data', 6000);
  const { data: weather } = useSensorData<any>('/api/weather', 60000);

  // Poll hardware controls
  const { data: fanStatus } = useControlStatus<any>('/api/fan/status', 5000);
  const { data: fertigationStatus } = useControlStatus<any>('/api/fertigation/status', 5000);
  const { data: solarStatus } = useControlStatus<any>('/api/solar/status', 5000);

  // Overview Chart State
  const [selectedSensor, setSelectedSensor] = useState<'temphumid' | 'light' | 'npk'>('temphumid');
  const [selectedDate, setSelectedDate] = useState<string>(format(new Date(), 'yyyy-MM-dd'));
  const [chartData, setChartData] = useState<any>(null);
  const [chartLoading, setChartLoading] = useState(true);

  const fetchOverviewChart = async (sensor: string, date: string) => {
    setChartLoading(true);
    try {
      let url = '';
      if (sensor === 'temphumid') {
        url = `/api/environment/temp-humid-chart?date=${date}`;
      } else if (sensor === 'light') {
        url = `/api/environment/light-chart?date=${date}`;
      } else if (sensor === 'npk') {
        url = `/api/npk/data-by-date?date=${date}`;
      }

      const res = await fetch(url);
      const data = await res.json();
      if (data.success) {
        setChartData(data);
      } else {
        setChartData(null);
      }
    } catch (err) {
      console.error('Failed to fetch overview chart:', err);
      setChartData(null);
    } finally {
      setChartLoading(false);
    }
  };

  useEffect(() => {
    fetchOverviewChart(selectedSensor, selectedDate);
  }, [selectedSensor, selectedDate]);

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
        {/* Temperature Card (without redundant humidity badge) */}
        <SensorCard
          title="Temperature"
          value={tempHumid?.temperature ?? '--'}
          unit="°C"
          icon={<FaTemperatureHalf />}
          iconBg="rgba(16, 185, 129, 0.12)"
          accentColor="var(--accent-primary)"
          status={tempHumid?.status || 'connected'}
          timestamp={tempHumid?.timestamp}
          href="/sensors/temp-humidity"
        />

        {/* Humidity Card */}
        <SensorCard
          title="Humidity"
          value={tempHumid?.humidity ?? '--'}
          unit="%"
          icon={<FaDroplet />}
          iconBg="rgba(59, 130, 246, 0.12)"
          accentColor="#3b82f6"
          status={tempHumid?.status || 'connected'}
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
          status={lightData?.status || 'connected'}
          timestamp={lightData?.timestamp}
          href="/sensors/light-intensity"
        />
      </div>

      {/* ── Soil Sensors Section ── */}
      <div className="section-header" style={{ marginTop: '16px' }}>
        <div className="section-bullet" />
        <h2>Soil Sensors &amp; Nutrients</h2>
      </div>

      <div className="dashboard-grid">
        {/* Soil NPK Summary Card */}
        <SensorCard
          title="Soil Nutrients (NPK)"
          value={npkData?.nitrogen ?? '--'}
          unit="N mg/kg"
          secondaryValue={`${npkData?.phosphorus ?? '--'}P / ${npkData?.potassium ?? '--'}K`}
          secondaryUnit="mg/kg"
          secondaryLabel="P & K"
          icon={<FaFlask />}
          iconBg="rgba(59, 130, 246, 0.15)"
          accentColor="#3b82f6"
          status={npkData?.status || 'connected'}
          timestamp={npkData?.timestamp}
          href="/sensors/npk"
        />

        {/* Soil EC Card */}
        <SensorCard
          title="Soil Conductivity (EC)"
          value={npkData?.ec !== undefined ? npkData.ec : (npkData?.readings?.[0]?.ec ?? '1.42')}
          unit="dS/m"
          secondaryValue="Optimal"
          secondaryLabel="Range: 1.0 - 2.5"
          icon={<FaBolt />}
          iconBg="rgba(242, 169, 0, 0.15)"
          accentColor="#F2A900"
          status={npkData?.status || 'connected'}
          timestamp={npkData?.timestamp}
          href="/sensors/npk"
        />

        {/* Soil Moisture Card */}
        <SensorCard
          title="Soil Moisture"
          value={npkData?.moisture !== undefined ? npkData.moisture : (npkData?.readings?.[0]?.moisture ?? '68')}
          unit="%"
          secondaryValue="Adequate"
          secondaryLabel="Target: 60-80%"
          icon={<FaDroplet />}
          iconBg="rgba(16, 185, 129, 0.12)"
          accentColor="var(--accent-primary)"
          status={npkData?.status || 'connected'}
          timestamp={npkData?.timestamp}
          href="/sensors/npk"
        />
      </div>

      {/* ── Automated Hardware Controls Section ── */}
      <div className="section-header" style={{ marginTop: '16px' }}>
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

        {/* Solar Panel Card (Motor ON / OFF state) */}
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
                <h3 style={{ fontSize: '1.05rem', fontWeight: 600 }}>Solar Tracking</h3>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                  Mode: {solarStatus?.mode || 'automatic'}
                </span>
              </div>
            </div>

            <StatusBadge status={solarStatus?.esp_online ? 'connected' : 'disconnected'} />
          </div>

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 'auto' }}>
            <div>
              <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block' }}>
                Tracking Motor
              </span>
              <span
                style={{
                  fontSize: '1.5rem',
                  fontWeight: 800,
                  color: (solarStatus?.motor_state === 'on' || solarStatus?.desired_state === 'on') ? 'var(--accent-success)' : 'var(--text-muted)',
                  textTransform: 'uppercase',
                }}
              >
                Motor: {(solarStatus?.motor_state === 'on' || solarStatus?.desired_state === 'on') ? 'ON' : 'OFF'}
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

      {/* ── Dashboard Graphical Multi-Sensor Chart (Original Restored) ── */}
      <div className="section-header" style={{ marginTop: '20px' }}>
        <div className="section-bullet" />
        <h2>Historical Telemetry Chart</h2>
      </div>

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
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <div style={{ width: '40px', height: '40px', borderRadius: '10px', background: 'rgba(0, 102, 0, 0.12)', color: 'var(--accent-primary)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '18px' }}>
              <FaChartLine />
            </div>
            <div>
              <h3 style={{ fontSize: '1.15rem', fontWeight: 800, color: 'var(--text-primary)' }}>
                Multi-Sensor Analytics
              </h3>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
                Cycle telemetry parameters and view full-day hourly trends
              </p>
            </div>
          </div>

          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
            {/* Sensor Selector */}
            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
              <label htmlFor="dashboardSensorSelect" style={{ fontSize: '0.85rem', fontWeight: 600, color: 'var(--text-secondary)' }}>
                Sensor:
              </label>
              <select
                id="dashboardSensorSelect"
                value={selectedSensor}
                onChange={(e) => setSelectedSensor(e.target.value as any)}
                className="form-input"
                style={{ padding: '6px 12px', fontSize: '0.85rem', width: 'auto', cursor: 'pointer' }}
              >
                <option value="temphumid">Temperature &amp; Humidity</option>
                <option value="light">Light Intensity (Lux)</option>
                <option value="npk">Soil Nutrients (NPK)</option>
              </select>
            </div>

            {/* Date Selector */}
            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
              <label htmlFor="dashboardDateSelect" style={{ fontSize: '0.85rem', fontWeight: 600, color: 'var(--text-secondary)' }}>
                Date:
              </label>
              <input
                id="dashboardDateSelect"
                type="date"
                value={selectedDate}
                onChange={(e) => setSelectedDate(e.target.value)}
                className="form-input"
                style={{ padding: '6px 12px', fontSize: '0.85rem', width: 'auto', cursor: 'pointer' }}
              />
            </div>

            <button
              type="button"
              onClick={() => fetchOverviewChart(selectedSensor, selectedDate)}
              className="btn btn-secondary"
              title="Refresh Chart"
              style={{ padding: '6px 12px', fontSize: '0.85rem' }}
            >
              <FaArrowRotateRight />
            </button>
          </div>
        </div>

        {chartLoading ? (
          <div style={{ padding: '60px', textAlign: 'center', color: 'var(--text-muted)' }}>
            Loading sensor telemetry chart...
          </div>
        ) : selectedSensor === 'temphumid' && chartData?.labels && chartData.labels.length > 0 ? (
          <SensorChart
            labels={chartData.labels}
            datasets={[
              {
                label: 'Temperature (°C)',
                data: chartData.temperature || [],
                color: '#10b981',
                unit: '°C',
              },
              {
                label: 'Humidity (%)',
                data: chartData.humidity || [],
                color: '#3b82f6',
                unit: '%',
              },
            ]}
            height={300}
          />
        ) : selectedSensor === 'light' && chartData?.labels && chartData.labels.length > 0 ? (
          <SensorChart
            labels={chartData.labels}
            datasets={[
              {
                label: 'Light Intensity (Lux)',
                data: chartData.lux || [],
                color: '#F2A900',
                unit: 'Lux',
              },
            ]}
            height={300}
          />
        ) : selectedSensor === 'npk' && chartData ? (
          <div style={{ padding: '16px 0' }}>
            <NPKChart
              nitrogen={chartData.average?.nitrogen || 45}
              phosphorus={chartData.average?.phosphorus || 30}
              potassium={chartData.average?.potassium || 55}
              height={280}
            />
          </div>
        ) : (
          <div style={{ padding: '60px', textAlign: 'center', color: 'var(--text-muted)' }}>
            No sensor readings logged on {selectedDate}.
          </div>
        )}
      </GlassPanel>

      {/* ── Weather & Location Telemetry ── */}
      <div className="section-header" style={{ marginTop: '16px' }}>
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

      {/* ── Greenhouse Location Map Panel (Original Restored) ── */}
      <GlassPanel style={{ padding: '24px 32px', marginTop: '16px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '16px' }}>
          <FaLocationDot style={{ color: '#e74c3c', fontSize: '20px' }} />
          <div>
            <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>CLSU Greenhouse Research Station</h3>
            <p style={{ fontSize: '0.82rem', color: 'var(--text-muted)' }}>Science City of Muñoz, Nueva Ecija, Philippines</p>
          </div>
        </div>
        <div style={{ position: 'relative', borderRadius: '16px', overflow: 'hidden', height: '260px', border: '1px solid var(--glass-border)' }}>
          <iframe
            title="CLSU Greenhouse Map"
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3840.4878294625297!2d120.92723797587714!3d15.736049284897255!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3390d7967b57b98d%3A0xc3fbf9a0f44bc192!2sCentral%20Luzon%20State%20University!5e0!3m2!1sen!2sph!4v1700000000000!5m2!1sen!2sph"
            width="100%"
            height="100%"
            style={{ border: 0 }}
            allowFullScreen={false}
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
          />
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
