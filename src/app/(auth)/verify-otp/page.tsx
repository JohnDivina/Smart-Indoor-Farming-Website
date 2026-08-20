'use client';

import React, { useState, useEffect, useRef, Suspense } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import GlassPanel from '@/components/ui/GlassPanel';
import LoadingSpinner from '@/components/ui/LoadingSpinner';
import { FaEnvelopeCircleCheck, FaArrowRotateRight, FaShieldHalved } from 'react-icons/fa6';

function VerifyOtpContent() {
  const router = useRouter();
  const searchParams = useSearchParams();

  const userId = Number(searchParams.get('userId') || '0');
  const email = searchParams.get('email') || '';
  const reason = searchParams.get('reason') || 'account_creation';
  const initialDevOtp = searchParams.get('devOtp') || '';

  const [devOtpCode, setDevOtpCode] = useState(initialDevOtp);
  const [otp, setOtp] = useState(initialDevOtp.length === 6 ? initialDevOtp.split('') : ['', '', '', '', '', '']);
  const [timer, setTimer] = useState(60);
  const [canResend, setCanResend] = useState(false);
  const [loading, setLoading] = useState(false);
  const [resending, setResending] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

  useEffect(() => {
    let interval: NodeJS.Timeout;
    if (timer > 0) {
      interval = setInterval(() => setTimer((prev) => prev - 1), 1000);
    } else {
      setCanResend(true);
    }
    return () => clearInterval(interval);
  }, [timer]);

  const handleChange = (index: number, value: string) => {
    if (!/^\d*$/.test(value)) return;

    const newOtp = [...otp];
    newOtp[index] = value.slice(-1);
    setOtp(newOtp);

    if (value && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }
  };

  const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Backspace' && !otp[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }
  };

  const handlePaste = (e: React.ClipboardEvent<HTMLInputElement>) => {
    e.preventDefault();
    const pasteData = e.clipboardData.getData('text').trim();
    if (/^\d{6}$/.test(pasteData)) {
      const digits = pasteData.split('');
      setOtp(digits);
      inputRefs.current[5]?.focus();
    }
  };

  const handleVerify = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    const fullOtp = otp.join('');
    if (fullOtp.length !== 6) {
      setError('Please enter the full 6-digit verification code.');
      return;
    }

    setError(null);
    setLoading(true);

    try {
      const res = await fetch('/api/auth/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          userId,
          otp: fullOtp,
          reason,
        }),
      });

      const data = await res.json();

      if (!res.ok || !data.success) {
        setError(data.message || 'Verification failed. Please check the code.');
        setLoading(false);
        return;
      }

      setSuccess('Code verified successfully!');

      setTimeout(() => {
        if (reason === 'password_reset') {
          router.push(`/reset-password?userId=${userId}&otp=${fullOtp}`);
        } else {
          router.push('/login?registered=1');
        }
      }, 1000);
    } catch (err: any) {
      setError('An error occurred during verification.');
      setLoading(false);
    }
  };

  const handleResend = async () => {
    if (!canResend || resending) return;
    setError(null);
    setSuccess(null);
    setResending(true);

    try {
      const res = await fetch('/api/auth/verify-otp', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId, reason }),
      });

      const data = await res.json();

      if (res.ok && data.success) {
        setSuccess('A new verification code has been sent to your email.');
        if (data.devOtp) {
          setDevOtpCode(data.devOtp);
          setOtp(data.devOtp.split(''));
        }
        setTimer(60);
        setCanResend(false);
      } else {
        setError(data.message || 'Failed to resend verification code.');
      }
    } catch (err) {
      setError('Unable to resend code right now.');
    } finally {
      setResending(false);
    }
  };

  return (
    <div style={{ maxWidth: '480px', margin: '0 auto' }}>
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
            background: 'rgba(0, 102, 0, 0.12)',
            color: 'var(--accent-primary)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '28px',
            margin: '0 auto 16px',
            boxShadow: '0 4px 16px var(--accent-primary-glow)',
          }}
        >
          <FaEnvelopeCircleCheck />
        </div>

        <h1 style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--text-primary)', marginBottom: '8px' }}>
          Verify Your Code
        </h1>
        <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', lineHeight: 1.5, marginBottom: '24px' }}>
          We sent a 6-digit verification code to <br />
          <strong style={{ color: 'var(--text-primary)' }}>{email || 'your email'}</strong>
        </p>

        {devOtpCode && (
          <div
            style={{
              padding: '10px 14px',
              borderRadius: '10px',
              background: 'rgba(0, 102, 0, 0.08)',
              border: '1px solid rgba(0, 102, 0, 0.2)',
              color: 'var(--accent-primary)',
              fontSize: '0.85rem',
              fontWeight: 600,
              marginBottom: '16px',
            }}
          >
            Verification Code: <strong style={{ fontFamily: 'monospace', letterSpacing: '2px' }}>{devOtpCode}</strong>
          </div>
        )}

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
            }}
          >
            {success}
          </div>
        )}

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

        <form onSubmit={handleVerify}>
          <div
            style={{
              display: 'flex',
              justifyContent: 'center',
              gap: '8px',
              margin: '24px 0',
            }}
          >
            {otp.map((digit, index) => (
              <input
                key={index}
                ref={(el) => {
                  inputRefs.current[index] = el;
                }}
                type="text"
                inputMode="numeric"
                maxLength={1}
                value={digit}
                onChange={(e) => handleChange(index, e.target.value)}
                onKeyDown={(e) => handleKeyDown(index, e)}
                onPaste={handlePaste}
                autoFocus={index === 0}
                style={{
                  width: '46px',
                  height: '56px',
                  borderRadius: '12px',
                  background: 'var(--glass-bg-subtle)',
                  border: '2px solid var(--glass-border)',
                  textAlign: 'center',
                  fontSize: '1.5rem',
                  fontWeight: 700,
                  color: 'var(--text-primary)',
                  outline: 'none',
                  transition: 'all var(--transition-fast)',
                }}
              />
            ))}
          </div>

          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading || otp.join('').length !== 6}
            style={{ width: '100%', height: '48px', fontSize: '1rem', marginBottom: '16px' }}
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
                <FaShieldHalved />
                <span>CONFIRM &amp; PROCEED</span>
              </>
            )}
          </button>
        </form>

        <div style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', marginTop: '16px' }}>
          Didn&apos;t receive the code?{' '}
          {canResend ? (
            <button
              type="button"
              onClick={handleResend}
              disabled={resending}
              style={{
                color: 'var(--accent-primary)',
                fontWeight: 700,
                cursor: 'pointer',
                display: 'inline-flex',
                alignItems: 'center',
                gap: '4px',
              }}
            >
              <FaArrowRotateRight className={resending ? 'animate-spin' : ''} /> Resend Code
            </button>
          ) : (
            <span style={{ color: 'var(--text-muted)' }}>Resend in {timer}s</span>
          )}
        </div>

        <div style={{ marginTop: '24px' }}>
          <Link href="/login" style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
            &larr; Back to Login
          </Link>
        </div>
      </GlassPanel>
    </div>
  );
}

export default function VerifyOtpPage() {
  return (
    <Suspense fallback={<LoadingSpinner fullScreen text="Loading Verification..." />}>
      <VerifyOtpContent />
    </Suspense>
  );
}
