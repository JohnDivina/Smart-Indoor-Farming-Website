'use client';

import React, { useState, Suspense } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { signIn } from 'next-auth/react';
import GlassPanel from '@/components/ui/GlassPanel';
import LoadingSpinner from '@/components/ui/LoadingSpinner';
import { FaEye, FaEyeSlash, FaGoogle, FaUserSecret, FaRightToBracket, FaShieldHalved } from 'react-icons/fa6';

function LoginFormContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const callbackUrl = searchParams.get('callbackUrl') || '/dashboard';
  const registered = searchParams.get('registered');

  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [totpCode, setTotpCode] = useState('');
  const [require2FA, setRequire2FA] = useState(false);
  const [loading, setLoading] = useState(false);
  const [guestLoading, setGuestLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(
    registered ? 'Account verified successfully! Please sign in below.' : null
  );

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSuccess(null);
    setLoading(true);

    try {
      const res = await signIn('credentials', {
        identifier,
        password,
        totpCode: require2FA ? totpCode : undefined,
        redirect: false,
      });

      if (res?.error) {
        if (res.error.startsWith('UNVERIFIED_EMAIL:')) {
          const parts = res.error.split(':');
          const userId = parts[1];
          const email = decodeURIComponent(parts[2] || '');
          router.push(`/verify-otp?userId=${userId}&email=${encodeURIComponent(email)}&reason=account_creation`);
          return;
        }

        if (res.error.startsWith('REQUIRE_2FA:')) {
          setRequire2FA(true);
          setError('Two-Factor Authentication is enabled. Please enter your 6-digit authenticator code.');
          setLoading(false);
          return;
        }

        setError(res.error);
        setLoading(false);
      } else if (res?.ok) {
        router.push(callbackUrl);
        router.refresh();
      }
    } catch (err: any) {
      setError(err?.message || 'Login failed. Please try again.');
      setLoading(false);
    }
  };

  const handleGuestLogin = async () => {
    setError(null);
    setGuestLoading(true);
    try {
      const res = await signIn('credentials', {
        isGuest: 'true',
        identifier: '__guest__',
        password: '__guest__',
        redirect: false,
      });

      if (res?.ok) {
        router.push('/dashboard');
        router.refresh();
      } else {
        setError('Unable to initiate guest session.');
        setGuestLoading(false);
      }
    } catch (err: any) {
      setError('Guest mode login error.');
      setGuestLoading(false);
    }
  };

  const handleGoogleLogin = async () => {
    try {
      await signIn('google', { callbackUrl });
    } catch (err) {
      setError('Failed to connect with Google.');
    }
  };

  return (
    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(340px, 1fr))', gap: '32px', alignItems: 'center' }}>
      {/* Left Branding Panel */}
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center', padding: '20px' }}>
        <div style={{ position: 'relative', width: '180px', height: '180px', marginBottom: '20px' }}>
          <Image
            src="/assets/clsu-official-logo.png"
            alt="CLSU Logo"
            fill
            style={{ objectFit: 'contain' }}
            priority
          />
        </div>
        <h1 style={{ fontSize: '2rem', fontWeight: 800, color: 'var(--text-primary)', marginBottom: '8px' }}>
          CLSU <span style={{ color: 'var(--accent-primary)' }}>Smart Farm</span>
        </h1>
        <p style={{ fontSize: '1rem', color: 'var(--text-secondary)', maxWidth: '420px', lineHeight: 1.6 }}>
          Automated precision agriculture, environmental climate control, and real-time indoor telemetry dashboard.
        </p>
      </div>

      {/* Right Login Card */}
      <GlassPanel
        style={{
          padding: '40px 36px',
          borderRadius: '28px',
          boxShadow: '0 20px 60px rgba(0, 102, 0, 0.12)',
          border: '1px solid var(--glass-border-hover)',
        }}
      >
        <div style={{ textAlign: 'center', marginBottom: '28px' }}>
          <h2 style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
            {require2FA ? '2-Step Verification' : 'Welcome Back'}
          </h2>
          <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', marginTop: '4px' }}>
            {require2FA
              ? 'Enter the 6-digit code from Google Authenticator'
              : 'Sign in to access sensor readings & farm controls'}
          </p>
        </div>

        {/* Success Alert */}
        {success && (
          <div
            style={{
              padding: '12px 16px',
              borderRadius: '10px',
              background: 'rgba(16, 185, 129, 0.12)',
              color: 'var(--accent-success)',
              border: '1px solid rgba(16, 185, 129, 0.25)',
              fontSize: '0.85rem',
              fontWeight: 600,
              marginBottom: '20px',
              textAlign: 'center',
            }}
          >
            {success}
          </div>
        )}

        {/* Error Alert */}
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

        <form onSubmit={handleLogin}>
          {!require2FA ? (
            <>
              <div className="form-group">
                <label className="form-label" htmlFor="identifier">
                  Username / Email / Mobile
                </label>
                <input
                  id="identifier"
                  type="text"
                  className="form-input"
                  placeholder="Enter your username, email, or number"
                  value={identifier}
                  onChange={(e) => setIdentifier(e.target.value)}
                  required
                  autoComplete="username"
                />
              </div>

              <div className="form-group" style={{ marginBottom: '10px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <label className="form-label" htmlFor="password">
                    Password
                  </label>
                  <Link
                    href="/forgot-password"
                    style={{ fontSize: '0.8rem', color: 'var(--accent-primary)', fontWeight: 600 }}
                  >
                    Forgot Password?
                  </Link>
                </div>
                <div style={{ position: 'relative' }}>
                  <input
                    id="password"
                    type={showPassword ? 'text' : 'password'}
                    className="form-input"
                    placeholder="Enter your password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                    autoComplete="current-password"
                    style={{ paddingRight: '44px' }}
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    aria-label="Toggle password visibility"
                    style={{
                      position: 'absolute',
                      right: '12px',
                      top: '50%',
                      transform: 'translateY(-50%)',
                      color: 'var(--text-muted)',
                      fontSize: '16px',
                      padding: '4px',
                    }}
                  >
                    {showPassword ? <FaEyeSlash /> : <FaEye />}
                  </button>
                </div>
              </div>
            </>
          ) : (
            <div className="form-group">
              <label className="form-label" htmlFor="totpCode">
                <FaShieldHalved style={{ marginRight: '6px', color: 'var(--accent-primary)' }} />
                6-Digit Authenticator Code
              </label>
              <input
                id="totpCode"
                type="text"
                maxLength={6}
                className="form-input"
                placeholder="000000"
                value={totpCode}
                onChange={(e) => setTotpCode(e.target.value.replace(/\D/g, ''))}
                required
                autoFocus
                style={{
                  textAlign: 'center',
                  fontSize: '1.5rem',
                  letterSpacing: '8px',
                  fontFamily: 'monospace',
                }}
              />
            </div>
          )}

          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading || guestLoading}
            style={{ width: '100%', marginTop: '16px', height: '48px', fontSize: '1rem' }}
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
                <FaRightToBracket />
                <span>{require2FA ? 'VERIFY & SIGN IN' : 'SIGN IN'}</span>
              </>
            )}
          </button>
        </form>

        {!require2FA && (
          <>
            <div
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '12px',
                margin: '20px 0',
                color: 'var(--text-muted)',
                fontSize: '0.8rem',
                textTransform: 'uppercase',
                letterSpacing: '1px',
              }}
            >
              <div style={{ flex: 1, height: '1px', background: 'var(--glass-border)' }} />
              <span>OR</span>
              <div style={{ flex: 1, height: '1px', background: 'var(--glass-border)' }} />
            </div>

            <button
              type="button"
              className="btn btn-secondary"
              onClick={handleGuestLogin}
              disabled={loading || guestLoading}
              style={{
                width: '100%',
                height: '46px',
                border: '1.5px solid rgba(234, 179, 8, 0.4)',
                background: 'rgba(234, 179, 8, 0.08)',
                color: '#eab308',
                fontWeight: 700,
                marginBottom: '10px',
              }}
            >
              {guestLoading ? (
                <span
                  style={{
                    width: '18px',
                    height: '18px',
                    border: '2px solid #eab308',
                    borderTopColor: 'transparent',
                    borderRadius: '50%',
                    animation: 'spin 0.8s linear infinite',
                  }}
                />
              ) : (
                <>
                  <FaUserSecret />
                  <span>VIEW AS GUEST (PREVIEW)</span>
                </>
              )}
            </button>

            <button
              type="button"
              className="btn btn-secondary"
              onClick={handleGoogleLogin}
              style={{ width: '100%', height: '46px', gap: '10px', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}
            >
              <FaGoogle style={{ color: '#ea4335' }} />
              <span>Continue with Google</span>
            </button>

            <div style={{ textAlign: 'center', marginTop: '24px', fontSize: '0.9rem', color: 'var(--text-secondary)' }}>
              Don&apos;t have an account?{' '}
              <Link href="/register" style={{ color: 'var(--accent-primary)', fontWeight: 700 }}>
                Sign Up
              </Link>
            </div>
          </>
        )}
      </GlassPanel>
    </div>
  );
}

export default function LoginPage() {
  return (
    <Suspense fallback={<LoadingSpinner fullScreen text="Loading Login..." />}>
      <LoginFormContent />
    </Suspense>
  );
}
