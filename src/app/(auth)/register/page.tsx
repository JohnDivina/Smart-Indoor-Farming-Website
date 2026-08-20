'use client';

import React, { useState } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import GlassPanel from '@/components/ui/GlassPanel';
import { FaUserPlus, FaEye, FaEyeSlash, FaCheck } from 'react-icons/fa6';

export default function RegisterPage() {
  const router = useRouter();

  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [phonenumber, setPhonenumber] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [agreeTerms, setAgreeTerms] = useState(false);

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    if (password !== confirmPassword) {
      setError('Passwords do not match.');
      return;
    }

    if (!agreeTerms) {
      setError('Please agree to the Terms of Service & Privacy Policy.');
      return;
    }

    setLoading(true);

    try {
      const res = await fetch('/api/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          username,
          email,
          phonenumber,
          password,
          confirmPassword,
        }),
      });

      const data = await res.json();

      if (!res.ok || !data.success) {
        setError(data.message || 'Registration failed. Please check your details.');
        setLoading(false);
        return;
      }

      // Forward to OTP verification page
      const devParam = data.devOtp ? `&devOtp=${encodeURIComponent(data.devOtp)}` : '';
      router.push(`/verify-otp?userId=${data.userId}&email=${encodeURIComponent(email)}&reason=account_creation${devParam}`);
    } catch (err: any) {
      setError(err?.message || 'A network error occurred. Please try again.');
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: '580px', margin: '0 auto' }}>
      <GlassPanel
        style={{
          padding: '40px 36px',
          borderRadius: '28px',
          boxShadow: '0 20px 60px rgba(0, 102, 0, 0.12)',
          border: '1px solid var(--glass-border-hover)',
        }}
      >
        <div style={{ textAlign: 'center', marginBottom: '24px' }}>
          <div style={{ position: 'relative', width: '72px', height: '72px', margin: '0 auto 12px' }}>
            <Image
              src="/assets/clsu-official-logo.png"
              alt="CLSU Logo"
              fill
              style={{ objectFit: 'contain' }}
              priority
            />
          </div>
          <h1 style={{ fontSize: '1.6rem', fontWeight: 800, color: 'var(--text-primary)' }}>
            Create Your Account
          </h1>
          <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', marginTop: '4px' }}>
            Join CLSU Smart Indoor Precision Farming System
          </p>
        </div>

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
              textAlign: 'center',
            }}
          >
            {error}
          </div>
        )}

        <form onSubmit={handleRegister}>
          <div className="form-group">
            <label className="form-label" htmlFor="username">
              Username
            </label>
            <input
              id="username"
              type="text"
              className="form-input"
              placeholder="e.g. john_doe"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
              minLength={3}
            />
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
            <div className="form-group">
              <label className="form-label" htmlFor="email">
                Email Address
              </label>
              <input
                id="email"
                type="email"
                className="form-input"
                placeholder="name@example.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
            </div>
            <div className="form-group">
              <label className="form-label" htmlFor="phonenumber">
                Phone Number
              </label>
              <input
                id="phonenumber"
                type="tel"
                className="form-input"
                placeholder="09123456789"
                value={phonenumber}
                onChange={(e) => setPhonenumber(e.target.value)}
                required
              />
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
            <div className="form-group">
              <label className="form-label" htmlFor="password">
                Password
              </label>
              <div style={{ position: 'relative' }}>
                <input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  className="form-input"
                  placeholder="Min. 6 chars with #/symbol"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  minLength={6}
                  style={{ paddingRight: '38px' }}
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  aria-label="Toggle password visibility"
                  style={{
                    position: 'absolute',
                    right: '10px',
                    top: '50%',
                    transform: 'translateY(-50%)',
                    color: 'var(--text-muted)',
                    fontSize: '14px',
                  }}
                >
                  {showPassword ? <FaEyeSlash /> : <FaEye />}
                </button>
              </div>
            </div>

            <div className="form-group">
              <label className="form-label" htmlFor="confirmPassword">
                Confirm Password
              </label>
              <input
                id="confirmPassword"
                type={showPassword ? 'text' : 'password'}
                className="form-input"
                placeholder="Re-enter password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                required
              />
            </div>
          </div>

          <div style={{ margin: '16px 0 20px', display: 'flex', alignItems: 'flex-start', gap: '10px' }}>
            <input
              id="agreeTerms"
              type="checkbox"
              checked={agreeTerms}
              onChange={(e) => setAgreeTerms(e.target.checked)}
              style={{ marginTop: '4px', cursor: 'pointer' }}
              required
            />
            <label htmlFor="agreeTerms" style={{ fontSize: '0.82rem', color: 'var(--text-secondary)', cursor: 'pointer' }}>
              I agree to the{' '}
              <Link href="/terms" target="_blank" style={{ color: 'var(--accent-primary)', fontWeight: 600 }}>
                Terms of Service
              </Link>{' '}
              and{' '}
              <Link href="/privacy" target="_blank" style={{ color: 'var(--accent-primary)', fontWeight: 600 }}>
                Privacy Policy
              </Link>
              .
            </label>
          </div>

          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading}
            style={{ width: '100%', height: '48px', fontSize: '1rem' }}
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
                <FaUserPlus />
                <span>CREATE ACCOUNT</span>
              </>
            )}
          </button>
        </form>

        <div style={{ textAlign: 'center', marginTop: '24px', fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
          Already have an account?{' '}
          <Link href="/login" style={{ color: 'var(--accent-primary)', fontWeight: 700 }}>
            Log In
          </Link>
        </div>
      </GlassPanel>
    </div>
  );
}
