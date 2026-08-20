'use client';

import React, { useState } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import { FaLocationDot, FaEnvelope, FaPhone, FaPaperPlane, FaCheck } from 'react-icons/fa6';

export default function ContactUsPage() {
  const [form, setForm] = useState({ name: '', email: '', subject: '', message: '' });
  const [sent, setSent] = useState(false);
  const [sending, setSending] = useState(false);

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
        title="Contact &amp; Support"
        subtitle="Get in touch with the CLSU Smart Farm technical and agricultural research team."
      />

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '28px', maxWidth: '960px', margin: '0 auto' }}>
        {/* ── Contact Info Panel ── */}
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
                <strong style={{ fontSize: '0.9rem', color: 'var(--text-primary)', display: 'block' }}>Email Inquiries</strong>
                <span style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
                  smartfarm@clsu.edu.ph
                </span>
              </div>
            </div>

            <div style={{ display: 'flex', alignItems: 'flex-start', gap: '14px' }}>
              <FaPhone style={{ color: 'var(--accent-primary)', fontSize: '20px', marginTop: '3px' }} />
              <div>
                <strong style={{ fontSize: '0.9rem', color: 'var(--text-primary)', display: 'block' }}>Office Phone</strong>
                <span style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
                  +63 (44) 456-0107
                </span>
              </div>
            </div>
          </div>

          <div style={{ marginTop: 'auto', padding: '16px', borderRadius: '12px', background: 'var(--glass-bg-subtle)', border: '1px solid var(--glass-border)' }}>
            <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
              Operating Hours: Monday – Friday, 8:00 AM – 5:00 PM (PST)
            </span>
          </div>
        </GlassPanel>

        {/* ── Message Form Panel ── */}
        <GlassPanel style={{ padding: '36px 32px' }}>
          <h3 style={{ fontSize: '1.25rem', fontWeight: 700, marginBottom: '4px' }}>Send Us a Message</h3>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginBottom: '20px' }}>
            Have a question or feedback regarding the greenhouse dashboard?
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
              <FaCheck /> Thank you! Your message has been sent to our team.
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
    </>
  );
}
