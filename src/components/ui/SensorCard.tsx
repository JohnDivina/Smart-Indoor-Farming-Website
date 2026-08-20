import React from 'react';
import Link from 'next/link';
import GlassPanel from './GlassPanel';
import StatusBadge from './StatusBadge';
import AnimatedNumber from '@/components/motion/AnimatedNumber';
import { FaChevronRight } from 'react-icons/fa6';

interface SensorCardProps {
  title: string;
  value: string | number;
  unit?: string;
  secondaryValue?: string;
  secondaryUnit?: string;
  secondaryLabel?: string;
  icon: React.ReactNode;
  iconBg?: string;
  status?: 'connected' | 'disconnected';
  timestamp?: string;
  href?: string;
  isGuestLocked?: boolean;
  onLockedClick?: () => void;
  accentColor?: string;
  decimals?: number;
}

export const SensorCard: React.FC<SensorCardProps> = ({
  title,
  value,
  unit,
  secondaryValue,
  secondaryUnit,
  secondaryLabel,
  icon,
  iconBg,
  status,
  timestamp,
  href,
  isGuestLocked = false,
  onLockedClick,
  accentColor,
  decimals = 1,
}) => {
  const isNumeric = typeof value === 'number' || (!isNaN(parseFloat(value as string)) && isFinite(value as any));

  const content = (
    <GlassPanel
      hoverable
      className={`sensor-card ${isGuestLocked ? 'guest-locked-card' : ''}`}
      style={{
        padding: '24px',
        display: 'flex',
        flexDirection: 'column',
        gap: '16px',
        height: '100%',
        position: 'relative',
        cursor: 'pointer',
        transition: 'transform var(--dur-short) var(--ease-out), box-shadow var(--dur-short) var(--ease-out), border-color var(--dur-short) var(--ease-out)',
      }}
      onClick={isGuestLocked ? onLockedClick : undefined}
    >
      {isGuestLocked && (
        <div className="guest-lock-badge">
          <span>🔒</span> Guest Locked
        </div>
      )}

      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
          <div
            style={{
              width: '48px',
              height: '48px',
              borderRadius: '14px',
              background: iconBg || 'rgba(0, 102, 0, 0.12)',
              color: accentColor || 'var(--accent-primary)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '22px',
              boxShadow: '0 4px 12px rgba(0,0,0,0.04)',
            }}
          >
            {icon}
          </div>
          <div>
            <h3 style={{ fontSize: '1.05rem', fontWeight: 600, color: 'var(--text-primary)' }}>{title}</h3>
            {timestamp && (
              <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{timestamp}</span>
            )}
          </div>
        </div>

        {status && <StatusBadge status={status} />}
      </div>

      <div style={{ display: 'flex', alignItems: 'baseline', gap: '8px', marginTop: 'auto' }}>
        <span
          style={{
            fontSize: '2.4rem',
            fontWeight: 800,
            letterSpacing: '-0.03em',
            color: accentColor || 'var(--text-primary)',
            lineHeight: 1,
          }}
        >
          {isNumeric && typeof value !== 'string' ? (
            <AnimatedNumber value={value} decimals={decimals} />
          ) : (
            value
          )}
        </span>
        {unit && (
          <span style={{ fontSize: '1.1rem', fontWeight: 600, color: 'var(--text-secondary)' }}>
            {unit}
          </span>
        )}

        {secondaryValue !== undefined && (
          <div style={{ marginLeft: 'auto', textAlign: 'right' }}>
            {secondaryLabel && (
              <span style={{ fontSize: '0.7rem', color: 'var(--text-muted)', display: 'block' }}>
                {secondaryLabel}
              </span>
            )}
            <span style={{ fontSize: '1.6rem', fontWeight: 700, color: 'var(--text-primary)' }}>
              {secondaryValue}
            </span>
            {secondaryUnit && (
              <span style={{ fontSize: '0.9rem', color: 'var(--text-secondary)', marginLeft: '4px' }}>
                {secondaryUnit}
              </span>
            )}
          </div>
        )}
      </div>
    </GlassPanel>
  );

  if (href && !isGuestLocked) {
    return <Link href={href} style={{ textDecoration: 'none' }}>{content}</Link>;
  }

  return content;
};

export default SensorCard;
