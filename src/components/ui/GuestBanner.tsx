import React from 'react';
import { FaLock, FaArrowRight } from 'react-icons/fa6';
import Link from 'next/link';
import GlassPanel from './GlassPanel';

interface GuestBannerProps {
  featureName?: string;
}

export const GuestBanner: React.FC<GuestBannerProps> = ({ featureName = 'this feature' }) => {
  return (
    <GlassPanel
      style={{
        padding: '18px 24px',
        borderLeft: '4px solid var(--accent-warning)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        flexWrap: 'wrap',
        gap: '14px',
        background: 'rgba(234, 179, 8, 0.08)',
      }}
    >
      <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
        <div
          style={{
            width: '40px',
            height: '40px',
            borderRadius: '10px',
            background: 'rgba(234, 179, 8, 0.2)',
            color: '#eab308',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '18px',
          }}
        >
          <FaLock />
        </div>
        <div>
          <h4 style={{ fontSize: '0.95rem', fontWeight: 700, color: 'var(--text-primary)' }}>
            Guest Mode Active
          </h4>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
            Control actions and system settings for {featureName} are disabled in guest mode.
          </p>
        </div>
      </div>

      <Link
        href="/login"
        className="btn btn-primary"
        style={{
          fontSize: '0.85rem',
          padding: '8px 18px',
          borderRadius: '999px',
        }}
      >
        <span>Sign In for Full Access</span>
        <FaArrowRight />
      </Link>
    </GlassPanel>
  );
};

export default GuestBanner;
