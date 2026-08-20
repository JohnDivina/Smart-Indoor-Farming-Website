import React from 'react';
import Link from 'next/link';
import Image from 'next/image';
import GlassPanel from '@/components/ui/GlassPanel';
import { FaCompass, FaHouse } from 'react-icons/fa6';

export default function NotFound() {
  return (
    <div
      style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '24px',
        textAlign: 'center',
      }}
    >
      <GlassPanel style={{ padding: '48px 40px', maxWidth: '480px', width: '100%' }}>
        <div
          style={{
            width: '72px',
            height: '72px',
            borderRadius: '50%',
            background: 'rgba(234, 179, 8, 0.15)',
            color: '#eab308',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '32px',
            margin: '0 auto 16px',
          }}
        >
          <FaCompass />
        </div>

        <h1 style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--text-primary)', marginBottom: '8px' }}>
          404
        </h1>
        <h2 style={{ fontSize: '1.2rem', fontWeight: 700, color: 'var(--text-primary)', marginBottom: '12px' }}>
          Page Not Found
        </h2>
        <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)', marginBottom: '24px', lineHeight: 1.6 }}>
          The requested Smart Farm page or telemetry endpoint does not exist or has been moved.
        </p>

        <Link href="/dashboard" className="btn btn-primary" style={{ width: '100%', height: '46px' }}>
          <FaHouse />
          <span>Return to Dashboard</span>
        </Link>
      </GlassPanel>
    </div>
  );
}
