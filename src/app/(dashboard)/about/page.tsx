import React from 'react';
import Image from 'next/image';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import { FaGraduationCap, FaSeedling, FaMicrochip, FaUsers, FaAward } from 'react-icons/fa6';

export default function AboutUsPage() {
  const teamMembers = [
    {
      name: 'Project Leadership & Agronomy',
      role: 'Central Luzon State University',
      desc: 'Precision crop phenotyping, micro-climate optimization, and nutrient formula research.',
    },
    {
      name: 'Hardware & IoT Engineering',
      role: 'Embedded Systems & Automation',
      desc: 'ESP32 microcontroller integration, Modbus RS485 soil sensing, and solar photovoltaic systems.',
    },
    {
      name: 'Software & Telemetry Architecture',
      role: 'Cloud & Dashboard Development',
      desc: 'Full-stack Next.js web application, real-time database synchronizer, and responsive glassmorphism UI.',
    },
  ];

  return (
    <>
      <Header
        title="About CLSU Smart Farm"
        subtitle="Central Luzon State University Precision Indoor Agriculture & Automated Smart Greenhouse Project."
      />

      <div style={{ maxWidth: '960px', margin: '0 auto', display: 'flex', flexDirection: 'column', gap: '28px' }}>
        {/* ── Mission Hero Card ── */}
        <GlassPanel style={{ padding: '36px 40px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '20px', marginBottom: '24px' }}>
            <div style={{ position: 'relative', width: '64px', height: '64px' }}>
              <Image
                src="/assets/clsu-official-logo.png"
                alt="CLSU Official Seal"
                fill
                style={{ objectFit: 'contain' }}
              />
            </div>
            <div>
              <h2 style={{ fontSize: '1.45rem', fontWeight: 800, color: 'var(--text-primary)' }}>
                Precision Agriculture for Sustainable Food Security
              </h2>
              <p style={{ fontSize: '0.88rem', color: 'var(--text-muted)' }}>
                Science City of Muñoz, Nueva Ecija, Philippines
              </p>
            </div>
          </div>

          <p style={{ fontSize: '0.98rem', color: 'var(--text-secondary)', lineHeight: 1.8, marginBottom: '20px' }}>
            The <strong>CLSU Smart Indoor Farm</strong> is an advanced research and technological demonstrator developed to optimize closed-environment crop cultivation. By combining Internet of Things (IoT) sensors, automated nutrient fertigation, auxiliary air exchange, and solar renewable energy, the facility ensures maximum photosynthetic yield while conserving water and macronutrients.
          </p>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '16px', marginTop: '24px' }}>
            <div style={{ padding: '16px', borderRadius: '12px', background: 'var(--glass-bg-subtle)', border: '1px solid var(--glass-border)' }}>
              <FaMicrochip style={{ color: 'var(--accent-primary)', fontSize: '24px', marginBottom: '8px' }} />
              <h4 style={{ fontSize: '0.95rem', fontWeight: 700 }}>Active IoT Telemetry</h4>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '4px' }}>
                Continuous sensor stream of temperature, humidity, lux, and soil NPK values.
              </p>
            </div>

            <div style={{ padding: '16px', borderRadius: '12px', background: 'var(--glass-bg-subtle)', border: '1px solid var(--glass-border)' }}>
              <FaSeedling style={{ color: 'var(--accent-secondary)', fontSize: '24px', marginBottom: '8px' }} />
              <h4 style={{ fontSize: '0.95rem', fontWeight: 700 }}>Closed Hydroponics</h4>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '4px' }}>
                Automated fertigation dosing pumps synchronized via cloud schedules.
              </p>
            </div>

            <div style={{ padding: '16px', borderRadius: '12px', background: 'var(--glass-bg-subtle)', border: '1px solid var(--glass-border)' }}>
              <FaAward style={{ color: '#3b82f6', fontSize: '24px', marginBottom: '8px' }} />
              <h4 style={{ fontSize: '0.95rem', fontWeight: 700 }}>CLSU Innovation</h4>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '4px' }}>
                Advancing Philippine high-value crop farming techniques and sustainability.
              </p>
            </div>
          </div>
        </GlassPanel>

        {/* ── Multidisciplinary Research Team ── */}
        <GlassPanel style={{ padding: '36px 40px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '24px' }}>
            <FaUsers style={{ color: 'var(--accent-primary)', fontSize: '22px' }} />
            <div>
              <h3 style={{ fontSize: '1.25rem', fontWeight: 700 }}>Multidisciplinary Project Team</h3>
              <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Faculty, researchers, and engineers</p>
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))', gap: '20px' }}>
            {teamMembers.map((m, idx) => (
              <div
                key={idx}
                style={{
                  padding: '24px',
                  borderRadius: '16px',
                  background: 'var(--glass-bg-subtle)',
                  border: '1px solid var(--glass-border)',
                }}
              >
                <h4 style={{ fontSize: '1.05rem', fontWeight: 700, color: 'var(--text-primary)', marginBottom: '4px' }}>
                  {m.name}
                </h4>
                <span style={{ fontSize: '0.8rem', color: 'var(--accent-primary)', fontWeight: 600, display: 'block', marginBottom: '10px' }}>
                  {m.role}
                </span>
                <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', lineHeight: 1.6 }}>
                  {m.desc}
                </p>
              </div>
            ))}
          </div>
        </GlassPanel>
      </div>
    </>
  );
}
