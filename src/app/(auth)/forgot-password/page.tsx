'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import GlassPanel from '@/components/ui/GlassPanel';
import { FaKey, FaPaperPlane } from 'react-icons/fa6';

export default function ForgotPasswordPage() {
  const router = useRouter();
  const [identifier, setIdentifier] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      const res = await fetch('/api/auth/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ identifier }),
      });

      const data = await res.json();

      if (res.ok && data.success) {
        router.push(
          `/verify-otp?userId=${data.userId || 0}&email=${encodeURIComponent(data.email || identifier)}&reason=password_reset`
        );
      } else {
        setError(data.message || 'Failed to send password reset code.');
        setLoading(false);
      }
    } catch (err: any) {
      setError('An error occurred. Please try again.');
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: '460px', margin: '0 auto' }}>
      <GlassPanel
        style={{
          padding: '40px 32px',
          borderRadius: '28px',
          boxShadow: '0 20px 60px rgba(0, 102, 0, 0.12)',
          border: '1px solid var(--glass-border-hover)',
          textAlign: 'center',
        }}
      >
        <div
          style={{
            width: '64px',
            height: '64px',
            borderRadius: '50%',
            background: 'rgba(242, 169, 0, 0.15)',
            color: '#F2A900',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '26px',
            margin: '0 auto 16px',
          }}
        >
          <FaKey />
        </div>

        <h1 style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--text-primary)', marginBottom: '8px' }}>
          Reset Password
        </h1>
        <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', lineHeight: 1.5, marginBottom: '24px' }}>
          Enter your registered email address or username and we will send you a 6-digit recovery code.
        </p>

        {error && (
          <div
            style={{
              padding: '12px 16px',
              borderRadius: '10px',
              background: 'rgba(234, 76, 76, 0.12)',
              color: 'var(--accent-danger)',
              border: '1px solid rgba(234, 76, 76, 0.25)',
              fontSize: '0.85rem',
              fontWeight: 600,
              marginBottom: '20px',
            }}
          >
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="form-group" style={{ textAlign: 'left' }}>
            <label className="form-label" htmlFor="identifier">
              Email Address or Username
            </label>
            <input
              id="identifier"
              type="text"
              className="form-input"
              placeholder="Enter username or email"
              value={identifier}
              onChange={(e) => setIdentifier(e.target.value)}
              required
              autoFocus
            />
          </div>

          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading}
            style={{ width: '100%', height: '48px', fontSize: '1rem', marginTop: '12px' }}
          >
            {loading ? (
              <span
                style={{
                  width: '20px',
                  height: '20px',
                  border: '2px solid #ffffff',
                  borderTopColor: 'transparent',
                  borderRadius: '50%',
                  animation: 'spin 0.8s linear infinite',
                }}
              />
            ) : (
              <>
                <FaPaperPlane />
                <span>SEND RECOVERY CODE</span>
              </>
            )}
          </button>
        </form>

        <div style={{ marginTop: '24px' }}>
          <Link href="/login" style={{ fontSize: '0.88rem', color: 'var(--text-secondary)' }}>
            &larr; Return to Sign In
          </Link>
        </div>
      </GlassPanel>
    </div>
  );
}
