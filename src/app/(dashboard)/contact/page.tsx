'use client';

import React, { useState } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import MotionWrapper from '@/components/motion/MotionWrapper';
import {
  FaLocationDot,
  FaEnvelope,
  FaPhone,
  FaPaperPlane,
  FaCheck,
  FaUserTie,
  FaMicrochip,
  FaSeedling,
} from 'react-icons/fa6';

export default function ContactUsPage() {
  const [form, setForm] = useState({ name: '', email: '', subject: '', message: '' });
  const [sent, setSent] = useState(false);
  const [sending, setSending] = useState(false);

  const contacts = [
    {
      name: 'Dr. Milagros R. Campos',
      title: 'Project Leader & Principal Investigator',
      email: 'mrcampos@clsu.edu.ph',
      icon: <FaSeedling style={{ color: 'var(--accent-primary)' }} />,
      focus: 'Agronomic Research & Crop Science',
    },
    {
      name: 'Engr. Roldan T. Quitos',
      title: 'Co-Project Leader / Engineering',
      email: 'rtquitos@clsu.edu.ph',
      icon: <FaMicrochip style={{ color: '#3b82f6' }} />,
      focus: 'Agricultural Mechanization & Embedded Systems',
    },
    {
      name: 'Sylvester A. Badua, PhD',
      title: 'Co-Project Leader / Controls',
      email: 'sbadua@clsu.edu.ph',
      icon: <FaUserTie style={{ color: '#F2A900' }} />,
      focus: 'Instrumentation & Automated Sensor Networks',
    },
    {
      name: 'John Rey L. Divina',
      title: 'Project Technical Assistant',
      email: 'johnrey_divina@clsu.edu.ph',
      icon: <FaMicrochip style={{ color: 'var(--accent-primary)' }} />,
      focus: 'Full-Stack Architecture & Field Telemetry Operations',
    },
  ];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSending(true);
    setTimeout(() => {
      setSending(false);
      setSent(true);
      setForm({ name: '', email: '', subject: '', message: '' });
    }, 1200);
  };

  return (
    <>
      <Header
        title="Contact &amp; Research Support"
        subtitle="Get in touch with the Central Luzon State University precision agriculture research team."
      />

      <div style={{ display: 'flex', flexDirection: 'column', gap: '32px', maxWidth: '1100px', margin: '0 auto' }}>
        {/* ── Key Project Contacts ── */}
        <MotionWrapper direction="up" delay={0.05}>
          <div className="section-header" style={{ marginBottom: '16px' }}>
            <div className="section-bullet" />
            <div>
              <h2 style={{ fontSize: '1.25rem', fontWeight: 800 }}>Project Leadership Directory</h2>
              <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Direct contacts for research inquiries and system telemetry</p>
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '16px' }}>
            {contacts.map((c, idx) => (
              <GlassPanel
                key={idx}
                hoverable
                style={{
                  padding: '20px',
                  borderRadius: '16px',
                  display: 'flex',
                  flexDirection: 'column',
                  gap: '8px',
                  transition: 'transform var(--dur-short) var(--ease-out), box-shadow var(--dur-short) var(--ease-out)',
                }}
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                  <div style={{ fontSize: '20px' }}>{c.icon}</div>
                  <div>
                    <h4 style={{ fontSize: '0.95rem', fontWeight: 800, color: 'var(--text-primary)' }}>{c.name}</h4>
                    <span style={{ fontSize: '0.75rem', color: 'var(--accent-primary)', fontWeight: 600 }}>{c.title}</span>
                  </div>
                </div>
                <p style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '4px' }}>{c.focus}</p>
                <a
                  href={`mailto:${c.email}`}
                  style={{
                    fontSize: '0.8rem',
                    color: 'var(--accent-primary)',
                    fontWeight: 700,
                    textDecoration: 'none',
                    marginTop: 'auto',
                    paddingTop: '8px',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '6px',
                  }}
                >
                  <FaEnvelope style={{ fontSize: '12px' }} />
                  {c.email}
                </a>
              </GlassPanel>
            ))}
          </div>
        </MotionWrapper>

        {/* ── Contact Info & Message Form Grid ── */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '28px' }}>
          {/* Contact Info Panel */}
          <GlassPanel style={{ padding: '36px 32px', display: 'flex', flexDirection: 'column', gap: '24px' }}>
            <div>
              <h3 style={{ fontSize: '1.25rem', fontWeight: 700, marginBottom: '8px' }}>CLSU Project Center</h3>
              <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', lineHeight: 1.6 }}>
                Central Luzon State University precision agriculture laboratory and indoor smart farming facility.
              </p>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '18px' }}>
              <div style={{ display: 'flex', alignItems: 'flex-start', gap: '14px' }}>
                <FaLocationDot style={{ color: 'var(--accent-primary)', fontSize: '20px', marginTop: '3px' }} />
                <div>
                  <strong style={{ fontSize: '0.9rem', color: 'var(--text-primary)', display: 'block' }}>Location</strong>
                  <span style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
                    Maharlika Highway, Science City of Muñoz, 3120 Nueva Ecija, Philippines
                  </span>
                </div>
              </div>

              <div style={{ display: 'flex', alignItems: 'flex-start', gap: '14px' }}>
                <FaEnvelope style={{ color: 'var(--accent-primary)', fontSize: '20px', marginTop: '3px' }} />
                <div>
                  <strong style={{ fontSize: '0.9rem', color: 'var(--text-primary)', display: 'block' }}>General Inquiries</strong>
                  <span style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
                    smartfarm@clsu.edu.ph
                  </span>
                </div>
              </div>

              <div style={{ display: 'flex', alignItems: 'flex-start', gap: '14px' }}>
                <FaPhone style={{ color: 'var(--accent-primary)', fontSize: '20px', marginTop: '3px' }} />
                <div>
                  <strong style={{ fontSize: '0.9rem', color: 'var(--text-primary)', display: 'block' }}>Campus Office Phone</strong>
                  <span style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
                    +63 (44) 456-0107
                  </span>
                </div>
              </div>
            </div>

            <div style={{ marginTop: 'auto', padding: '16px', borderRadius: '12px', background: 'var(--glass-bg-subtle)', border: '1px solid var(--glass-border)' }}>
              <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
                Facility Hours: Monday – Friday, 8:00 AM – 5:00 PM (Philippine Standard Time)
              </span>
            </div>
          </GlassPanel>

          {/* Message Form Panel */}
          <GlassPanel style={{ padding: '36px 32px' }}>
            <h3 style={{ fontSize: '1.25rem', fontWeight: 700, marginBottom: '4px' }}>Send Direct Message</h3>
            <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginBottom: '20px' }}>
              Inquire about research telemetry or request access
            </p>

            {sent && (
              <div
                style={{
                  padding: '12px 16px',
                  borderRadius: '10px',
                  background: 'rgba(16, 185, 129, 0.12)',
                  color: 'var(--accent-success)',
                  fontSize: '0.85rem',
                  fontWeight: 600,
                  marginBottom: '16px',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '8px',
                }}
              >
                <FaCheck /> Thank you! Your message has been sent to our research team.
              </div>
            )}

            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label className="form-label" htmlFor="contactName">Your Name</label>
                <input
                  id="contactName"
                  type="text"
                  className="form-input"
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="contactEmail">Email Address</label>
                <input
                  id="contactEmail"
                  type="email"
                  className="form-input"
                  value={form.email}
                  onChange={(e) => setForm({ ...form, email: e.target.value })}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="contactSubject">Subject</label>
                <input
                  id="contactSubject"
                  type="text"
                  className="form-input"
                  value={form.subject}
                  onChange={(e) => setForm({ ...form, subject: e.target.value })}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="contactMessage">Message</label>
                <textarea
                  id="contactMessage"
                  rows={4}
                  className="form-input"
                  value={form.message}
                  onChange={(e) => setForm({ ...form, message: e.target.value })}
                  required
                  style={{ resize: 'vertical' }}
                />
              </div>

              <button
                type="submit"
                className="btn btn-primary"
                disabled={sending}
                style={{ width: '100%', height: '46px', marginTop: '10px' }}
              >
                <FaPaperPlane />
                <span>{sending ? 'Sending...' : 'SUBMIT MESSAGE'}</span>
              </button>
            </form>
          </GlassPanel>
        </div>
      </div>
    </>
  );
}
