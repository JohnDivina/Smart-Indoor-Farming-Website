import React from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import { FaShieldHalved } from 'react-icons/fa6';

export default function PrivacyPolicyPage() {
  return (
    <>
      <Header
        title="Privacy Policy"
        subtitle="Data governance, telemetry retention, and user privacy guidelines for CLSU Smart Farm."
      />

      <div style={{ maxWidth: '860px', margin: '0 auto' }}>
        <GlassPanel style={{ padding: '40px 48px', display: 'flex', flexDirection: 'column', gap: '20px', lineHeight: 1.8 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', borderBottom: '1px solid var(--glass-border)', paddingBottom: '16px' }}>
            <FaShieldHalved style={{ color: 'var(--accent-primary)', fontSize: '24px' }} />
            <div>
              <h2 style={{ fontSize: '1.25rem', fontWeight: 800 }}>Information Privacy Commitment</h2>
              <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Effective Date: August 2026</span>
            </div>
          </div>

          <h3 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '8px' }}>
            1. Information We Collect
          </h3>
          <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
            We collect personal information necessary to manage user authentication and system access, including your username, email address, and phone number. Environmental sensor readings (temperature, humidity, lux, soil NPK) and hardware operational logs (fan and irrigation cycle events) are recorded for agronomic research and system telemetry.
          </p>

          <h3 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '8px' }}>
            2. How Information is Used
          </h3>
          <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
            Collected data is utilized to: (a) authenticate greenhouse operators, (b) calculate 24-hour agricultural averages and micro-climate graphs, (c) execute scheduled hardware actuation routines, and (d) maintain security logs and prevent unauthorized system access.
          </p>

          <h3 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '8px' }}>
            3. Data Security &amp; Encryption
          </h3>
          <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
            User passwords are encrypted using industry-standard bcrypt hashing. Multi-factor authentication secrets are securely generated via RFC 6238 TOTP algorithms. Telemetry transmissions between IoT edge devices and the central database are authenticated via private API keys.
          </p>

          <h3 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '8px' }}>
            4. User Rights &amp; Data Deletion
          </h3>
          <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
            Registered operators retain full rights to update their personal information or request permanent account deletion at any time through the Account Settings portal. Deletion removes all operator credentials and authentication associations.
          </p>
        </GlassPanel>
      </div>
    </>
  );
}
