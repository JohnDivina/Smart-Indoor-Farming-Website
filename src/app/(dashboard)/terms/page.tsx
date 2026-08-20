'use client';

import React from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import { useRouter } from 'next/navigation';
import { FaFileContract, FaArrowLeft } from 'react-icons/fa6';

export default function TermsOfServicePage() {
  const router = useRouter();

  return (
    <>
      <Header
        title="Terms of Service"
        subtitle="Operational regulations, access conditions, and acceptable use guidelines."
      />

      <div style={{ maxWidth: '860px', margin: '0 auto' }}>
        <button
          type="button"
          onClick={() => router.back()}
          className="btn btn-secondary"
          style={{ marginBottom: '16px', display: 'inline-flex', alignItems: 'center', gap: '8px', fontSize: '0.85rem' }}
        >
          <FaArrowLeft /> Back
        </button>

        <GlassPanel style={{ padding: '40px 48px', display: 'flex', flexDirection: 'column', gap: '20px', lineHeight: 1.8 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', borderBottom: '1px solid var(--glass-border)', paddingBottom: '16px' }}>
            <FaFileContract style={{ color: 'var(--accent-primary)', fontSize: '24px' }} />
            <div>
              <h2 style={{ fontSize: '1.25rem', fontWeight: 800 }}>Terms of Service Agreement</h2>
              <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Updated: August 2026</span>
            </div>
          </div>

          <h3 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '8px' }}>
            1. Acceptance of Terms
          </h3>
          <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
            By accessing or operating the CLSU Smart Indoor Farming telemetry dashboard, you agree to comply with these Terms of Service, all applicable university IT policies, and relevant precision agriculture safety standards.
          </p>

          <h3 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '8px' }}>
            2. Authorized Use &amp; Account Responsibility
          </h3>
          <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
            Access to physical hardware actuations (fans, irrigation pumps, and solar relays) is restricted to authorized operators. You are responsible for safeguarding your login credentials and 2FA authentication tokens. Any actions initiated under your authenticated session are your operational responsibility.
          </p>

          <h3 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '8px' }}>
            3. Automated Hardware Safety &amp; Overrides
          </h3>
          <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
            Automated schedules and sensor-based thresholds should be configured with appropriate agronomic safety margins to avoid over-irrigation or thermal stress. Manual hardware overrides located on physical ESP32 enclosures take precedence during emergencies.
          </p>

          <h3 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '8px' }}>
            4. Modifications &amp; System Maintenance
          </h3>
          <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
            The CLSU Precision Agriculture Research Laboratory reserves the right to deploy firmware updates, adjust telemetry endpoints, or perform scheduled system maintenance to ensure operational stability.
          </p>
        </GlassPanel>
      </div>
    </>
  );
}
