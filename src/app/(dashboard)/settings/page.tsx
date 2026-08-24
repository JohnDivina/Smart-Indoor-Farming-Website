'use client';

import React, { useState, useEffect } from 'react';
import Image from 'next/image';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import Modal from '@/components/ui/Modal';
import { useSession, signOut } from 'next-auth/react';
import {
  FaGear,
  FaUser,
  FaKey,
  FaShieldHalved,
  FaTrashCan,
  FaCheck,
  FaCircleExclamation,
  FaQrcode,
  FaLock,
} from 'react-icons/fa6';

export default function SettingsPage() {
  const { data: session } = useSession();

  // Profile State
  const [profile, setProfile] = useState<{
    username: string;
    email: string;
    phonenumber: string;
    role?: string;
    authProvider?: string;
    totpEnabled: boolean;
    createdAt: string;
  }>({ username: '', email: '', phonenumber: '', totpEnabled: false, createdAt: '' });
  const [loadingProfile, setLoadingProfile] = useState(true);
  const [profileMessage, setProfileMessage] = useState<string | null>(null);
  const [savingProfile, setSavingProfile] = useState(false);

  // Password State
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [passwordMessage, setPasswordMessage] = useState<{ text: string; isError: boolean } | null>(null);
  const [savingPassword, setSavingPassword] = useState(false);

  const isGoogleLinked = profile.authProvider === 'google';

  // 2FA State
  const [totpSetup, setTotpSetup] = useState<{ secret: string; qrCode: string } | null>(null);
  const [totpCode, setTotpCode] = useState('');
  const [totpMessage, setTotpMessage] = useState<{ text: string; isError: boolean } | null>(null);
  const [settingUp2FA, setSettingUp2FA] = useState(false);

  // Delete Account State
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteOtp, setDeleteOtp] = useState('');
  const [otpSent, setOtpSent] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [deleteError, setDeleteError] = useState<string | null>(null);

  // Fetch profile on mount
  useEffect(() => {
    fetch('/api/user/profile')
      .then((res) => res.json())
      .then((data) => {
        if (data.success && data.user) {
          setProfile(data.user);
        }
      })
      .catch(console.error)
      .finally(() => setLoadingProfile(false));
  }, []);

  // Update Profile
  const handleUpdateProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setSavingProfile(true);
    setProfileMessage(null);

    try {
      const res = await fetch('/api/user/profile', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          username: profile.username,
          email: profile.email,
          phonenumber: profile.phonenumber,
        }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        setProfileMessage('Profile information saved successfully!');
        setTimeout(() => setProfileMessage(null), 3000);
      } else {
        setProfileMessage(data.message || 'Failed to update profile.');
      }
    } catch (err) {
      setProfileMessage('An error occurred.');
    } finally {
      setSavingProfile(false);
    }
  };

  // Change Password
  const handleChangePassword = async (e: React.FormEvent) => {
    e.preventDefault();
    setPasswordMessage(null);

    if (newPassword !== confirmPassword) {
      setPasswordMessage({ text: 'New passwords do not match.', isError: true });
      return;
    }

    setSavingPassword(true);
    try {
      const res = await fetch('/api/user/password', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          currentPassword: isGoogleLinked ? undefined : currentPassword,
          newPassword,
          confirmPassword,
        }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        setPasswordMessage({
          text: data.message || 'Password updated successfully!',
          isError: false,
        });
        setCurrentPassword('');
        setNewPassword('');
        setConfirmPassword('');
      } else {
        setPasswordMessage({ text: data.message || 'Failed to change password.', isError: true });
      }
    } catch (err) {
      setPasswordMessage({ text: 'An error occurred.', isError: true });
    } finally {
      setSavingPassword(false);
    }
  };

  // Start 2FA Setup
  const handleStart2FA = async () => {
    setSettingUp2FA(true);
    setTotpMessage(null);
    try {
      const res = await fetch('/api/user/totp', { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        setTotpSetup({ secret: data.secret, qrCode: data.qrCode });
      }
    } catch (err) {
      setTotpMessage({ text: 'Error starting 2FA setup', isError: true });
    } finally {
      setSettingUp2FA(false);
    }
  };

  // Verify and Confirm 2FA
  const handleConfirm2FA = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!totpSetup) return;

    try {
      const res = await fetch('/api/user/totp', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: totpCode, secret: totpSetup.secret }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        setProfile((prev) => ({ ...prev, totpEnabled: true }));
        setTotpSetup(null);
        setTotpCode('');
        setTotpMessage({ text: 'Two-Factor Authentication is now ENABLED!', isError: false });
      } else {
        setTotpMessage({ text: data.message || 'Invalid code.', isError: true });
      }
    } catch (err) {
      setTotpMessage({ text: 'Verification error', isError: true });
    }
  };

  // Disable 2FA
  const handleDisable2FA = async () => {
    if (!confirm('Are you sure you want to disable Two-Factor Authentication?')) return;
    try {
      const res = await fetch('/api/user/totp', { method: 'DELETE' });
      const data = await res.json();
      if (res.ok && data.success) {
        setProfile((prev) => ({ ...prev, totpEnabled: false }));
        setTotpMessage({ text: '2FA has been disabled.', isError: false });
      }
    } catch (err) {
      setTotpMessage({ text: 'Error disabling 2FA', isError: true });
    }
  };

  // Send Account Deletion OTP
  const handleSendDeleteOtp = async () => {
    setDeleteError(null);
    try {
      const res = await fetch('/api/user/send-delete-otp', { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        setOtpSent(true);
      } else {
        setDeleteError(data.message || 'Failed to send OTP.');
      }
    } catch (err) {
      setDeleteError('Error sending OTP.');
    }
  };

  // Permanently Delete Account
  const handleConfirmDelete = async () => {
    if (!deleteOtp || deleteOtp.length !== 6) {
      setDeleteError('Please enter the 6-digit confirmation code.');
      return;
    }

    setDeleting(true);
    setDeleteError(null);

    try {
      const res = await fetch('/api/user/delete', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ otp: deleteOtp }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        await signOut({ callbackUrl: '/login' });
      } else {
        setDeleteError(data.message || 'Failed to delete account.');
        setDeleting(false);
      }
    } catch (err) {
      setDeleteError('An error occurred.');
      setDeleting(false);
    }
  };

  const getRoleTitle = (role?: string) => {
    if (role === 'admin') return '👑 Master Administrator';
    if (role === 'farm_manager') return '🛡️ Farm Manager';
    if (role === 'farmer') return '🌾 Station Farmer';
    return '👁️ Station Viewer';
  };

  return (
    <>
      <Header
        title="Account &amp; System Settings"
        subtitle="Manage your operator profile, security credentials, and two-factor authentication."
        backHref="/dashboard"
      />

      <div style={{ maxWidth: '840px', margin: '0 auto', display: 'flex', flexDirection: 'column', gap: '24px' }}>
        {/* ── Hero Profile Overview Banner ── */}
        <div
          style={{
            padding: '28px 32px',
            borderRadius: '20px',
            background: 'linear-gradient(135deg, var(--accent-primary) 0%, #008000 60%, var(--accent-secondary) 100%)',
            color: '#ffffff',
            display: 'flex',
            alignItems: 'center',
            gap: '24px',
            boxShadow: '0 8px 32px var(--accent-primary-glow)',
          }}
        >
          <div
            style={{
              width: '64px',
              height: '64px',
              borderRadius: '50%',
              background: 'rgba(255, 255, 255, 0.2)',
              backdropFilter: 'blur(8px)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '28px',
              flexShrink: 0,
            }}
          >
            <FaUser />
          </div>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
              <h2 style={{ fontSize: '1.4rem', fontWeight: 800, color: '#ffffff', marginBottom: '2px' }}>
                {profile.username || 'System Administrator'}
              </h2>
              <span style={{ fontSize: '0.78rem', padding: '3px 10px', borderRadius: '999px', background: 'rgba(0,0,0,0.25)', color: '#ffffff', fontWeight: 700 }}>
                {getRoleTitle(profile.role)}
              </span>
            </div>
            <p style={{ fontSize: '0.88rem', color: 'rgba(255, 255, 255, 0.85)' }}>
              {profile.email} • Registered {profile.createdAt || 'CLSU Smart Farm'}
            </p>
          </div>
        </div>

        {/* ── Profile Information Card ── */}
        <GlassPanel style={{ padding: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px' }}>
            <FaUser style={{ color: 'var(--accent-primary)', fontSize: '20px' }} />
            <div>
              <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Personal Information</h3>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Update your contact details and username</p>
            </div>
          </div>

          {profileMessage && (
            <div
              style={{
                padding: '10px 16px',
                borderRadius: '8px',
                background: 'rgba(16, 185, 129, 0.12)',
                color: 'var(--accent-success)',
                fontSize: '0.85rem',
                fontWeight: 600,
                marginBottom: '16px',
              }}
            >
              {profileMessage}
            </div>
          )}

          <form onSubmit={handleUpdateProfile}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '16px' }}>
              <div className="form-group">
                <label className="form-label" htmlFor="settingsUsername">
                  Username
                </label>
                <input
                  id="settingsUsername"
                  type="text"
                  className="form-input"
                  value={profile.username}
                  onChange={(e) => setProfile({ ...profile, username: e.target.value })}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="settingsEmail">
                  Email Address
                </label>
                <input
                  id="settingsEmail"
                  type="email"
                  className="form-input"
                  value={profile.email}
                  onChange={(e) => setProfile({ ...profile, email: e.target.value })}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="settingsPhone">
                  Phone Number
                </label>
                <input
                  id="settingsPhone"
                  type="tel"
                  className="form-input"
                  value={profile.phonenumber || ''}
                  onChange={(e) => setProfile({ ...profile, phonenumber: e.target.value })}
                />
              </div>
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              disabled={savingProfile}
              style={{ marginTop: '12px', padding: '10px 24px', fontSize: '0.9rem' }}
            >
              {savingProfile ? 'Saving...' : 'UPDATE PROFILE'}
            </button>
          </form>
        </GlassPanel>

        {/* ── Change Password Card ── */}
        <GlassPanel style={{ padding: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px' }}>
            <FaKey style={{ color: '#F2A900', fontSize: '20px' }} />
            <div>
              <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>
                {isGoogleLinked ? 'Set Account Password (Google Linked)' : 'Security & Password'}
              </h3>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
                {isGoogleLinked
                  ? 'Your account is linked to Google OAuth. Set a password below if you want to also log in directly via email.'
                  : 'Ensure your account uses a strong password.'}
              </p>
            </div>
          </div>

          {passwordMessage && (
            <div
              style={{
                padding: '10px 16px',
                borderRadius: '8px',
                background: passwordMessage.isError ? 'rgba(234, 76, 76, 0.12)' : 'rgba(16, 185, 129, 0.12)',
                color: passwordMessage.isError ? 'var(--accent-danger)' : 'var(--accent-success)',
                fontSize: '0.85rem',
                fontWeight: 600,
                marginBottom: '16px',
              }}
            >
              {passwordMessage.text}
            </div>
          )}

          <form onSubmit={handleChangePassword}>
            {!isGoogleLinked && (
              <div className="form-group">
                <label className="form-label" htmlFor="currentPassword">
                  Current Password
                </label>
                <input
                  id="currentPassword"
                  type="password"
                  className="form-input"
                  value={currentPassword}
                  onChange={(e) => setCurrentPassword(e.target.value)}
                  required
                />
              </div>
            )}

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
              <div className="form-group">
                <label className="form-label" htmlFor="newPassword">
                  {isGoogleLinked ? 'Create New Password' : 'New Password'}
                </label>
                <input
                  id="newPassword"
                  type="password"
                  className="form-input"
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  required
                  minLength={6}
                  placeholder="Min 6 characters"
                />
              </div>

              <div className="form-group">
                <label className="form-label" htmlFor="confirmNewPassword">
                  Confirm Password
                </label>
                <input
                  id="confirmNewPassword"
                  type="password"
                  className="form-input"
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  required
                  placeholder="Repeat password"
                />
              </div>
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              disabled={savingPassword}
              style={{ marginTop: '12px', padding: '10px 24px', fontSize: '0.9rem' }}
            >
              {savingPassword ? 'Saving...' : isGoogleLinked ? 'SET ACCOUNT PASSWORD' : 'CHANGE PASSWORD'}
            </button>
          </form>
        </GlassPanel>

        {/* ── Two-Factor Authentication (2FA) ── */}
        <GlassPanel style={{ padding: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '16px', marginBottom: '20px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              <FaShieldHalved style={{ color: 'var(--accent-primary)', fontSize: '22px' }} />
              <div>
                <h3 style={{ fontSize: '1.15rem', fontWeight: 700 }}>Two-Factor Authentication (2FA)</h3>
                <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
                  Protect your account with Google Authenticator or Authy TOTP codes
                </p>
              </div>
            </div>

            <span
              style={{
                padding: '6px 14px',
                borderRadius: '999px',
                fontSize: '0.8rem',
                fontWeight: 700,
                background: profile.totpEnabled ? 'rgba(16, 185, 129, 0.15)' : 'rgba(234, 179, 8, 0.15)',
                color: profile.totpEnabled ? 'var(--accent-success)' : '#eab308',
                border: `1px solid ${profile.totpEnabled ? 'rgba(16, 185, 129, 0.3)' : 'rgba(234, 179, 8, 0.3)'}`,
              }}
            >
              {profile.totpEnabled ? '2FA ENABLED' : '2FA DISABLED'}
            </span>
          </div>

          {totpMessage && (
            <div
              style={{
                padding: '10px 16px',
                borderRadius: '8px',
                background: totpMessage.isError ? 'rgba(234, 76, 76, 0.12)' : 'rgba(16, 185, 129, 0.12)',
                color: totpMessage.isError ? 'var(--accent-danger)' : 'var(--accent-success)',
                fontSize: '0.85rem',
                fontWeight: 600,
                marginBottom: '16px',
              }}
            >
              {totpMessage.text}
            </div>
          )}

          {!profile.totpEnabled ? (
            !totpSetup ? (
              <button
                type="button"
                className="btn btn-primary"
                onClick={handleStart2FA}
                disabled={settingUp2FA}
                style={{ padding: '10px 24px', fontSize: '0.9rem' }}
              >
                <FaQrcode />
                <span>{settingUp2FA ? 'Generating QR Code...' : 'ENABLE TWO-FACTOR AUTH'}</span>
              </button>
            ) : (
              <div style={{ background: 'var(--glass-bg-subtle)', padding: '24px', borderRadius: '16px', border: '1px solid var(--glass-border)' }}>
                <h4 style={{ fontSize: '1rem', fontWeight: 700, marginBottom: '12px' }}>
                  1. Scan this QR Code with Google Authenticator
                </h4>
                <div style={{ width: '180px', height: '180px', margin: '0 auto 16px', background: '#ffffff', padding: '10px', borderRadius: '12px' }}>
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={totpSetup.qrCode} alt="2FA QR Code" style={{ width: '100%', height: '100%' }} />
                </div>
                <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)', textAlign: 'center', marginBottom: '16px' }}>
                  Or enter key manually: <strong style={{ color: 'var(--text-primary)', fontFamily: 'monospace' }}>{totpSetup.secret}</strong>
                </p>

                <h4 style={{ fontSize: '1rem', fontWeight: 700, marginBottom: '8px' }}>
                  2. Enter 6-digit verification code
                </h4>
                <form onSubmit={handleConfirm2FA} style={{ display: 'flex', gap: '12px', maxWidth: '360px' }}>
                  <input
                    type="text"
                    maxLength={6}
                    className="form-input"
                    placeholder="000000"
                    value={totpCode}
                    onChange={(e) => setTotpCode(e.target.value.replace(/\D/g, ''))}
                    required
                    style={{ textAlign: 'center', fontSize: '1.2rem', letterSpacing: '4px', fontFamily: 'monospace' }}
                  />
                  <button type="submit" className="btn btn-primary" style={{ padding: '10px 20px', whiteSpace: 'nowrap' }}>
                    CONFIRM &amp; ACTIVATE
                  </button>
                </form>
              </div>
            )
          ) : (
            <div>
              <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', marginBottom: '16px' }}>
                Your account is protected by Two-Factor Authentication. A 6-digit authenticator code will be required during login.
              </p>
              <button
                type="button"
                className="btn btn-secondary"
                onClick={handleDisable2FA}
                style={{ color: 'var(--accent-danger)', borderColor: 'rgba(234, 76, 76, 0.3)' }}
              >
                Disable 2FA
              </button>
            </div>
          )}
        </GlassPanel>

        {/* ── Danger Zone ── */}
        <GlassPanel style={{ padding: '32px', border: '1.5px solid rgba(234, 76, 76, 0.3)' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '12px' }}>
            <FaCircleExclamation style={{ color: 'var(--accent-danger)', fontSize: '22px' }} />
            <div>
              <h3 style={{ fontSize: '1.15rem', fontWeight: 700, color: 'var(--accent-danger)' }}>Danger Zone</h3>
              <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Permanently delete your account and associated session history</p>
            </div>
          </div>

          <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', marginBottom: '16px' }}>
            Deleting your account will erase your profile information and revoke access to all automated hardware controls. This action cannot be undone.
          </p>

          <button
            type="button"
            className="btn btn-danger"
            onClick={() => {
              setShowDeleteModal(true);
              handleSendDeleteOtp();
            }}
            style={{ padding: '10px 20px', fontSize: '0.9rem' }}
          >
            <FaTrashCan /> DELETE ACCOUNT
          </button>
        </GlassPanel>
      </div>

      {/* Account Deletion OTP Modal */}
      <Modal
        open={showDeleteModal}
        onClose={() => setShowDeleteModal(false)}
        title="Confirm Account Deletion"
        description="We have sent a 6-digit confirmation code to your email. Enter it below to permanently delete your account."
        icon={<FaTrashCan />}
        iconColor="var(--accent-danger)"
      >
        {deleteError && (
          <div
            style={{
              padding: '10px 14px',
              borderRadius: '8px',
              background: 'rgba(234, 76, 76, 0.12)',
              color: 'var(--accent-danger)',
              fontSize: '0.85rem',
              fontWeight: 600,
              marginBottom: '16px',
              textAlign: 'center',
            }}
          >
            {deleteError}
          </div>
        )}

        <div style={{ display: 'flex', flexDirection: 'column', gap: '16px', marginTop: '16px' }}>
          <input
            type="text"
            maxLength={6}
            className="form-input"
            placeholder="Enter 6-digit code"
            value={deleteOtp}
            onChange={(e) => setDeleteOtp(e.target.value.replace(/\D/g, ''))}
            style={{ textAlign: 'center', fontSize: '1.4rem', letterSpacing: '6px', fontFamily: 'monospace' }}
          />

          <div style={{ display: 'flex', gap: '12px', justifyContent: 'center' }}>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => setShowDeleteModal(false)}
            >
              Cancel
            </button>
            <button
              type="button"
              className="btn btn-danger"
              onClick={handleConfirmDelete}
              disabled={deleting || deleteOtp.length !== 6}
            >
              {deleting ? 'Deleting...' : 'PERMANENTLY DELETE'}
            </button>
          </div>
        </div>
      </Modal>
    </>
  );
}
