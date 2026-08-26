'use client';

import React, { useState, useEffect, useCallback } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import Modal from '@/components/ui/Modal';
import LoadingSpinner from '@/components/ui/LoadingSpinner';
import { useSession } from 'next-auth/react';
import { format } from 'date-fns';
import {
  FaUserShield,
  FaUsersGear,
  FaUserCheck,
  FaUserClock,
  FaTrashCan,
  FaShieldHalved,
  FaCheck,
  FaXmark,
  FaGoogle,
  FaEnvelope,
  FaSliders,
  FaCircleExclamation,
  FaArrowRotateRight,
  FaCrown,
  FaTractor,
  FaEye,
  FaCircleCheck,
} from 'react-icons/fa6';

interface UserRecord {
  id: number;
  username: string;
  email: string;
  phonenumber: string;
  role: 'admin' | 'farm_manager' | 'farmer' | 'viewer';
  approved: boolean;
  authProvider: 'credentials' | 'google';
  emailVerified: boolean;
  createdAt: string;
  lastActivity: string | null;
  currentPage: string;
}

export default function MasterControlPage() {
  const { data: session } = useSession();
  const [activeTab, setActiveTab] = useState<'pending' | 'directory' | 'matrix'>('pending');
  const [users, setUsers] = useState<UserRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);
  const [toastMessage, setToastMessage] = useState<{ text: string; isError?: boolean } | null>(null);

  // Modal Delete state
  const [deleteTarget, setDeleteTarget] = useState<UserRecord | null>(null);
  const [deleting, setDeleting] = useState(false);

  // Selected roles for pending users
  const [pendingRoles, setPendingRoles] = useState<Record<number, string>>({});

  const currentUserRole = session?.user?.role || 'viewer';
  const isMasterAdmin = currentUserRole === 'admin';

  const showToast = (text: string, isError = false) => {
    setToastMessage({ text, isError });
    setTimeout(() => setToastMessage(null), 4000);
  };

  const fetchUsers = useCallback(async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/admin/users');
      const data = await res.json();
      if (data.success && Array.isArray(data.users)) {
        setUsers(data.users);
      } else {
        showToast(data.message || 'Failed to fetch user list.', true);
      }
    } catch (err) {
      showToast('Error connecting to operator management service.', true);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchUsers();
  }, [fetchUsers]);

  const pendingUsers = users.filter((u) => !u.approved && u.role !== 'admin');
  const approvedUsers = users.filter((u) => u.approved || u.role === 'admin');
  const farmManagersCount = users.filter((u) => u.role === 'farm_manager' && u.approved).length;
  const farmersCount = users.filter((u) => u.role === 'farmer' && u.approved).length;

  // Handle Approve with specific role
  const handleApprove = async (user: UserRecord) => {
    const assignedRole = pendingRoles[user.id] || user.role || 'farmer';
    setActionLoading(user.id);

    try {
      const res = await fetch('/api/admin/users', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          userId: user.id,
          approved: true,
          role: assignedRole,
        }),
      });

      const data = await res.json();
      if (res.ok && data.success) {
        showToast(`Approved ${user.username} as ${assignedRole.replace('_', ' ').toUpperCase()}!`);
        fetchUsers();
      } else {
        showToast(data.message || 'Failed to approve user.', true);
      }
    } catch (err) {
      showToast('Error processing user approval.', true);
    } finally {
      setActionLoading(null);
    }
  };

  // Handle Role Change
  const handleRoleChange = async (userId: number, newRole: string) => {
    setActionLoading(userId);
    try {
      const res = await fetch('/api/admin/users', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId, role: newRole }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        showToast(`Role updated to ${newRole.replace('_', ' ').toUpperCase()}!`);
        fetchUsers();
      } else {
        showToast(data.message || 'Failed to update role.', true);
      }
    } catch (err) {
      showToast('Error updating role.', true);
    } finally {
      setActionLoading(null);
    }
  };

  // Handle Revoke Approval
  const handleRevokeApproval = async (userId: number, username: string) => {
    if (!confirm(`Are you sure you want to suspend/revoke approval for ${username}? They will not be able to log in.`)) return;
    setActionLoading(userId);
    try {
      const res = await fetch('/api/admin/users', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId, approved: false }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        showToast(`Access revoked for ${username}. Account is now in pending queue.`);
        fetchUsers();
      } else {
        showToast(data.message || 'Failed to revoke access.', true);
      }
    } catch (err) {
      showToast('Error revoking access.', true);
    } finally {
      setActionLoading(null);
    }
  };

  // Handle Delete Account
  const handleConfirmDelete = async () => {
    if (!deleteTarget) return;
    setDeleting(true);

    try {
      const res = await fetch('/api/admin/users', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId: deleteTarget.id }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        showToast(`Account ${deleteTarget.username} was permanently deleted.`);
        setDeleteTarget(null);
        fetchUsers();
      } else {
        showToast(data.message || 'Failed to delete account.', true);
      }
    } catch (err) {
      showToast('Error deleting account.', true);
    } finally {
      setDeleting(false);
    }
  };

  const getRoleBadge = (role: string) => {
    switch (role) {
      case 'admin':
        return (
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '4px 10px', borderRadius: '8px', fontSize: '0.78rem', fontWeight: 700, background: 'rgba(168, 85, 247, 0.18)', color: '#c084fc', border: '1px solid rgba(168, 85, 247, 0.3)' }}>
            <FaCrown style={{ color: '#fbbf24' }} /> Master Admin
          </span>
        );
      case 'farm_manager':
        return (
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '4px 10px', borderRadius: '8px', fontSize: '0.78rem', fontWeight: 700, background: 'rgba(16, 185, 129, 0.18)', color: 'var(--accent-primary)', border: '1px solid rgba(16, 185, 129, 0.3)' }}>
            <FaUserShield /> Farm Manager
          </span>
        );
      case 'farmer':
        return (
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '4px 10px', borderRadius: '8px', fontSize: '0.78rem', fontWeight: 700, background: 'rgba(59, 130, 246, 0.18)', color: '#60a5fa', border: '1px solid rgba(59, 130, 246, 0.3)' }}>
            <FaTractor /> Farmer
          </span>
        );
      default:
        return (
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '4px 10px', borderRadius: '8px', fontSize: '0.78rem', fontWeight: 600, background: 'rgba(148, 163, 184, 0.15)', color: '#94a3b8', border: '1px solid rgba(148, 163, 184, 0.25)' }}>
            <FaEye /> Viewer
          </span>
        );
    }
  };

  return (
    <>
      <Header
        title="Master Control &amp; RBAC"
        subtitle="Operator role assignments, registration approvals, and hardware control privileges."
        backHref="/dashboard"
      />

      {/* Toast Notification Banner */}
      {toastMessage && (
        <div
          style={{
            position: 'fixed',
            top: '24px',
            right: '24px',
            zIndex: 9999,
            padding: '14px 22px',
            borderRadius: '12px',
            background: toastMessage.isError ? 'rgba(234, 76, 76, 0.95)' : 'rgba(16, 185, 129, 0.95)',
            color: '#ffffff',
            fontWeight: 700,
            fontSize: '0.9rem',
            boxShadow: '0 10px 30px rgba(0,0,0,0.3)',
            display: 'flex',
            alignItems: 'center',
            gap: '10px',
            backdropFilter: 'blur(8px)',
          }}
        >
          {toastMessage.isError ? <FaCircleExclamation /> : <FaCircleCheck />}
          <span>{toastMessage.text}</span>
        </div>
      )}

      {/* ── Metric Highlights ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '16px', marginBottom: '24px' }}>
        <GlassPanel style={{ padding: '20px 24px', display: 'flex', alignItems: 'center', gap: '16px' }}>
          <div style={{ width: '48px', height: '48px', borderRadius: '12px', background: 'rgba(234, 179, 8, 0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#eab308', fontSize: '22px' }}>
            <FaUserClock />
          </div>
          <div>
            <div style={{ fontSize: '1.6rem', fontWeight: 800, color: pendingUsers.length > 0 ? '#eab308' : 'var(--text-primary)' }}>
              {pendingUsers.length}
            </div>
            <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', fontWeight: 600 }}>Pending Approvals</div>
          </div>
        </GlassPanel>

        <GlassPanel style={{ padding: '20px 24px', display: 'flex', alignItems: 'center', gap: '16px' }}>
          <div style={{ width: '48px', height: '48px', borderRadius: '12px', background: 'rgba(16, 185, 129, 0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--accent-primary)', fontSize: '22px' }}>
            <FaUserShield />
          </div>
          <div>
            <div style={{ fontSize: '1.6rem', fontWeight: 800, color: 'var(--accent-primary)' }}>
              {farmManagersCount}
            </div>
            <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', fontWeight: 600 }}>Farm Managers (Controls Active)</div>
          </div>
        </GlassPanel>

        <GlassPanel style={{ padding: '20px 24px', display: 'flex', alignItems: 'center', gap: '16px' }}>
          <div style={{ width: '48px', height: '48px', borderRadius: '12px', background: 'rgba(59, 130, 246, 0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#60a5fa', fontSize: '22px' }}>
            <FaTractor />
          </div>
          <div>
            <div style={{ fontSize: '1.6rem', fontWeight: 800, color: '#60a5fa' }}>
              {farmersCount}
            </div>
            <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', fontWeight: 600 }}>Active Farmers</div>
          </div>
        </GlassPanel>

        <GlassPanel style={{ padding: '20px 24px', display: 'flex', alignItems: 'center', gap: '16px' }}>
          <div style={{ width: '48px', height: '48px', borderRadius: '12px', background: 'rgba(168, 85, 247, 0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#c084fc', fontSize: '22px' }}>
            <FaUsersGear />
          </div>
          <div>
            <div style={{ fontSize: '1.6rem', fontWeight: 800, color: 'var(--text-primary)' }}>
              {approvedUsers.length}
            </div>
            <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', fontWeight: 600 }}>Total Authorized Accounts</div>
          </div>
        </GlassPanel>
      </div>

      {/* ── Navigation Tabs ── */}
      <div style={{ display: 'flex', gap: '12px', marginBottom: '20px', flexWrap: 'wrap' }}>
        <button
          type="button"
          className={`btn ${activeTab === 'pending' ? 'btn-primary' : 'btn-secondary'}`}
          onClick={() => setActiveTab('pending')}
          style={{ padding: '10px 20px', fontSize: '0.9rem', display: 'flex', alignItems: 'center', gap: '8px' }}
        >
          <FaUserClock />
          <span>Pending Approvals</span>
          {pendingUsers.length > 0 && (
            <span style={{ padding: '2px 8px', borderRadius: '999px', background: '#eab308', color: '#000', fontSize: '0.75rem', fontWeight: 800 }}>
              {pendingUsers.length}
            </span>
          )}
        </button>

        <button
          type="button"
          className={`btn ${activeTab === 'directory' ? 'btn-primary' : 'btn-secondary'}`}
          onClick={() => setActiveTab('directory')}
          style={{ padding: '10px 20px', fontSize: '0.9rem', display: 'flex', alignItems: 'center', gap: '8px' }}
        >
          <FaUsersGear />
          <span>Authorized Users Directory ({approvedUsers.length})</span>
        </button>

        <button
          type="button"
          className={`btn ${activeTab === 'matrix' ? 'btn-primary' : 'btn-secondary'}`}
          onClick={() => setActiveTab('matrix')}
          style={{ padding: '10px 20px', fontSize: '0.9rem', display: 'flex', alignItems: 'center', gap: '8px' }}
        >
          <FaShieldHalved />
          <span>Permissions Reference</span>
        </button>

        <button
          type="button"
          className="btn btn-secondary"
          onClick={fetchUsers}
          disabled={loading}
          style={{ marginLeft: 'auto', padding: '10px 16px' }}
          title="Refresh List"
        >
          <FaArrowRotateRight className={loading ? 'fa-spin' : ''} />
        </button>
      </div>

      {/* ── TAB 1: Pending Approvals ── */}
      {activeTab === 'pending' && (
        <GlassPanel style={{ padding: '28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '20px' }}>
            <div>
              <h2 style={{ fontSize: '1.2rem', fontWeight: 700 }}>Pending Account Registrations</h2>
              <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                Newly registered operators require approval from the Master Administrator before they can access the station.
              </p>
            </div>
          </div>

          {loading ? (
            <div style={{ padding: '40px', textAlign: 'center', color: 'var(--text-muted)' }}>
              <LoadingSpinner size="md" />
              <p style={{ marginTop: '12px' }}>Loading pending accounts...</p>
            </div>
          ) : pendingUsers.length === 0 ? (
            <div style={{ padding: '48px 24px', textAlign: 'center', color: 'var(--text-muted)' }}>
              <FaCircleCheck style={{ fontSize: '48px', color: 'var(--accent-primary)', marginBottom: '12px' }} />
              <h3 style={{ fontSize: '1.1rem', fontWeight: 700, color: 'var(--text-primary)', marginBottom: '6px' }}>
                All Caught Up!
              </h3>
              <p style={{ fontSize: '0.88rem' }}>There are currently no new accounts waiting for confirmation.</p>
            </div>
          ) : (
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left', fontSize: '0.88rem' }}>
                <thead>
                  <tr style={{ borderBottom: '1px solid var(--glass-border)', color: 'var(--text-muted)', fontSize: '0.78rem', textTransform: 'uppercase', letterSpacing: '1px' }}>
                    <th style={{ padding: '12px 16px' }}>Operator</th>
                    <th style={{ padding: '12px 16px' }}>Auth Method</th>
                    <th style={{ padding: '12px 16px' }}>Registered On</th>
                    <th style={{ padding: '12px 16px' }}>Assign Role</th>
                    <th style={{ padding: '12px 16px', textAlign: 'right' }}>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {pendingUsers.map((user) => {
                    const selectedRole = pendingRoles[user.id] || 'farmer';
                    const isProcessing = actionLoading === user.id;

                    return (
                      <tr key={user.id} style={{ borderBottom: '1px solid var(--glass-border-subtle)' }}>
                        <td style={{ padding: '16px' }}>
                          <div style={{ fontWeight: 700, color: 'var(--text-primary)' }}>{user.username}</div>
                          <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>{user.email}</div>
                          {user.phonenumber !== '--' && (
                            <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{user.phonenumber}</div>
                          )}
                        </td>

                        <td style={{ padding: '16px' }}>
                          <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', padding: '4px 10px', borderRadius: '8px', fontSize: '0.75rem', fontWeight: 700, background: user.authProvider === 'google' ? 'rgba(234, 67, 53, 0.12)' : 'rgba(59, 130, 246, 0.12)', color: user.authProvider === 'google' ? '#f87171' : '#60a5fa' }}>
                            {user.authProvider === 'google' ? <FaGoogle /> : <FaEnvelope />}
                            {user.authProvider === 'google' ? 'Google OAuth' : 'Credentials'}
                          </span>
                        </td>

                        <td style={{ padding: '16px', color: 'var(--text-secondary)' }}>
                          {format(new Date(user.createdAt), 'MMM d, yyyy h:mm a')}
                        </td>

                        <td style={{ padding: '16px' }}>
                          <select
                            className="form-input"
                            value={selectedRole}
                            onChange={(e) => setPendingRoles({ ...pendingRoles, [user.id]: e.target.value })}
                            style={{ padding: '6px 12px', fontSize: '0.85rem', width: 'auto', minWidth: '160px' }}
                          >
                            <option value="farmer">🌾 Farmer (Monitoring Only)</option>
                            <option value="farm_manager">🛡️ Farm Manager (Controls &amp; Deletion)</option>
                            <option value="viewer">👁️ Viewer (Read-Only)</option>
                          </select>
                        </td>

                        <td style={{ padding: '16px', textAlign: 'right' }}>
                          <div style={{ display: 'flex', gap: '8px', justifyContent: 'flex-end' }}>
                            <button
                              type="button"
                              className="btn btn-primary"
                              disabled={isProcessing}
                              onClick={() => handleApprove(user)}
                              style={{ padding: '6px 14px', fontSize: '0.82rem', display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                            >
                              <FaCheck />
                              <span>{isProcessing ? 'Saving...' : 'Approve & Activate'}</span>
                            </button>

                            <button
                              type="button"
                              className="btn btn-secondary"
                              disabled={isProcessing}
                              onClick={() => setDeleteTarget(user)}
                              style={{ padding: '6px 12px', fontSize: '0.82rem', color: 'var(--accent-danger)', borderColor: 'rgba(234, 76, 76, 0.3)' }}
                              title="Reject and delete this registration"
                            >
                              <FaXmark />
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </GlassPanel>
      )}

      {/* ── TAB 2: Authorized Users Directory ── */}
      {activeTab === 'directory' && (
        <GlassPanel style={{ padding: '28px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '20px' }}>
            <div>
              <h2 style={{ fontSize: '1.2rem', fontWeight: 700 }}>Authorized Station Operators</h2>
              <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                Active users with assigned roles. Master Administrators can re-assign roles or revoke access at any time.
              </p>
            </div>
          </div>

          {loading ? (
            <div style={{ padding: '40px', textAlign: 'center', color: 'var(--text-muted)' }}>
              <LoadingSpinner size="md" />
              <p style={{ marginTop: '12px' }}>Loading authorized users...</p>
            </div>
          ) : (
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left', fontSize: '0.88rem' }}>
                <thead>
                  <tr style={{ borderBottom: '1px solid var(--glass-border)', color: 'var(--text-muted)', fontSize: '0.78rem', textTransform: 'uppercase', letterSpacing: '1px' }}>
                    <th style={{ padding: '12px 16px' }}>Operator</th>
                    <th style={{ padding: '12px 16px' }}>Current Role</th>
                    <th style={{ padding: '12px 16px' }}>Auth Method</th>
                    <th style={{ padding: '12px 16px' }}>Last Activity</th>
                    <th style={{ padding: '12px 16px', textAlign: 'right' }}>Management</th>
                  </tr>
                </thead>
                <tbody>
                  {approvedUsers.map((user) => {
                    const isSelf = String(user.id) === String(session?.user?.id);
                    const isMasterAdminAccount = user.email.toLowerCase() === 'johnrey_divina@clsu.edu.ph' || user.role === 'admin';
                    const isProcessing = actionLoading === user.id;

                    // Can this logged-in user delete this account?
                    // Master Admin: can delete anyone except Master Admin
                    // Farm Manager: can delete Farmers and Viewers
                    const canDelete =
                      !isMasterAdminAccount &&
                      !isSelf &&
                      (isMasterAdmin || (currentUserRole === 'farm_manager' && (user.role === 'farmer' || user.role === 'viewer')));

                    return (
                      <tr key={user.id} style={{ borderBottom: '1px solid var(--glass-border-subtle)' }}>
                        <td style={{ padding: '16px' }}>
                          <div style={{ fontWeight: 700, color: 'var(--text-primary)', display: 'flex', alignItems: 'center', gap: '8px' }}>
                            <span>{user.username}</span>
                            {isSelf && (
                              <span style={{ fontSize: '0.7rem', padding: '2px 6px', borderRadius: '4px', background: 'rgba(255,255,255,0.1)', color: 'var(--text-muted)' }}>
                                YOU
                              </span>
                            )}
                          </div>
                          <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>{user.email}</div>
                        </td>

                        <td style={{ padding: '16px' }}>
                          {isMasterAdmin && !isMasterAdminAccount ? (
                            <select
                              className="form-input"
                              value={user.role}
                              disabled={isProcessing}
                              onChange={(e) => handleRoleChange(user.id, e.target.value)}
                              style={{ padding: '4px 10px', fontSize: '0.82rem', width: 'auto' }}
                            >
                              <option value="admin">👑 Master Admin</option>
                              <option value="farm_manager">🛡️ Farm Manager</option>
                              <option value="farmer">🌾 Farmer</option>
                              <option value="viewer">👁️ Viewer</option>
                            </select>
                          ) : (
                            getRoleBadge(user.role)
                          )}
                        </td>

                        <td style={{ padding: '16px' }}>
                          <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '0.78rem', color: 'var(--text-secondary)' }}>
                            {user.authProvider === 'google' ? <FaGoogle style={{ color: '#ea4335' }} /> : <FaEnvelope style={{ color: '#3b82f6' }} />}
                            <span>{user.authProvider === 'google' ? 'Google' : 'Credentials'}</span>
                          </span>
                        </td>

                        <td style={{ padding: '16px', color: 'var(--text-secondary)', fontSize: '0.82rem' }}>
                          {user.lastActivity ? (
                            <div>
                              <div>{format(new Date(user.lastActivity), 'MMM d, h:mm a')}</div>
                              <div style={{ fontSize: '0.72rem', color: 'var(--accent-primary)' }}>{user.currentPage}</div>
                            </div>
                          ) : (
                            <span style={{ color: 'var(--text-muted)' }}>No recent activity</span>
                          )}
                        </td>

                        <td style={{ padding: '16px', textAlign: 'right' }}>
                          <div style={{ display: 'flex', gap: '8px', justifyContent: 'flex-end' }}>
                            {isMasterAdmin && !isMasterAdminAccount && (
                              <button
                                type="button"
                                className="btn btn-secondary"
                                disabled={isProcessing}
                                onClick={() => handleRevokeApproval(user.id, user.username)}
                                style={{ padding: '6px 12px', fontSize: '0.78rem' }}
                                title="Revoke authorization and move back to pending queue"
                              >
                                Revoke
                              </button>
                            )}

                            {canDelete && (
                              <button
                                type="button"
                                className="btn btn-secondary"
                                disabled={isProcessing}
                                onClick={() => setDeleteTarget(user)}
                                style={{ padding: '6px 10px', fontSize: '0.78rem', color: 'var(--accent-danger)', borderColor: 'rgba(234, 76, 76, 0.3)' }}
                                title="Delete account"
                              >
                                <FaTrashCan />
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </GlassPanel>
      )}

      {/* ── TAB 3: Permissions Matrix ── */}
      {activeTab === 'matrix' && (
        <GlassPanel style={{ padding: '32px' }}>
          <h2 style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '6px' }}>Role-Based Access Matrix</h2>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginBottom: '24px' }}>
            System capabilities and authorization gates enforced across the station.
          </p>

          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.88rem' }}>
              <thead>
                <tr style={{ borderBottom: '1.5px solid var(--glass-border)', color: 'var(--text-primary)' }}>
                  <th style={{ padding: '12px', textAlign: 'left' }}>Capability</th>
                  <th style={{ padding: '12px', textAlign: 'center' }}>👑 Master Admin</th>
                  <th style={{ padding: '12px', textAlign: 'center' }}>🛡️ Farm Manager</th>
                  <th style={{ padding: '12px', textAlign: 'center' }}>🌾 Farmer</th>
                  <th style={{ padding: '12px', textAlign: 'center' }}>👁️ Viewer</th>
                </tr>
              </thead>
              <tbody>
                {[
                  { name: 'Live Sensor Telemetry & Historical Trends', admin: true, manager: true, farmer: true, viewer: true },
                  { name: 'Export Sensor Datasets (Excel & CSV)', admin: true, manager: true, farmer: true, viewer: true },
                  { name: 'Auxiliary Cooling Fan Controls & Schedules', admin: true, manager: true, farmer: false, viewer: false },
                  { name: 'Fertigation & Irrigation Pumps Control', admin: true, manager: true, farmer: false, viewer: false },
                  { name: 'Solar Panels Tracking Motor & Timers', admin: true, manager: true, farmer: false, viewer: false },
                  { name: 'Account Profile & 2FA Settings', admin: true, manager: true, farmer: true, viewer: true },
                  { name: 'Approve New Operator Registrations', admin: true, manager: false, farmer: false, viewer: false },
                  { name: 'Assign & Re-assign Operator Roles', admin: true, manager: false, farmer: false, viewer: false },
                  { name: 'Delete Farmer & Viewer Accounts', admin: true, manager: true, farmer: false, viewer: false },
                  { name: 'Delete Farm Manager Accounts', admin: true, manager: false, farmer: false, viewer: false },
                ].map((row, idx) => (
                  <tr key={idx} style={{ borderBottom: '1px solid var(--glass-border-subtle)' }}>
                    <td style={{ padding: '14px 12px', fontWeight: 600, color: 'var(--text-primary)' }}>{row.name}</td>
                    <td style={{ padding: '14px 12px', textAlign: 'center' }}>
                      {row.admin ? <FaCheck style={{ color: 'var(--accent-primary)' }} /> : <FaXmark style={{ color: 'var(--text-muted)' }} />}
                    </td>
                    <td style={{ padding: '14px 12px', textAlign: 'center' }}>
                      {row.manager ? <FaCheck style={{ color: 'var(--accent-primary)' }} /> : <FaXmark style={{ color: 'var(--text-muted)' }} />}
                    </td>
                    <td style={{ padding: '14px 12px', textAlign: 'center' }}>
                      {row.farmer ? <FaCheck style={{ color: 'var(--accent-primary)' }} /> : <FaXmark style={{ color: 'var(--text-muted)' }} />}
                    </td>
                    <td style={{ padding: '14px 12px', textAlign: 'center' }}>
                      {row.viewer ? <FaCheck style={{ color: 'var(--accent-primary)' }} /> : <FaXmark style={{ color: 'var(--text-muted)' }} />}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </GlassPanel>
      )}

      {/* Account Deletion Confirmation Modal */}
      <Modal
        open={Boolean(deleteTarget)}
        onClose={() => setDeleteTarget(null)}
        title="Confirm Account Deletion"
        description={`Are you sure you want to permanently delete the account for ${deleteTarget?.username} (${deleteTarget?.email})? This action immediately revokes access.`}
        icon={<FaTrashCan />}
        iconColor="var(--accent-danger)"
      >
        <div style={{ display: 'flex', gap: '12px', justifyContent: 'flex-end', marginTop: '20px' }}>
          <button type="button" className="btn btn-secondary" onClick={() => setDeleteTarget(null)}>
            Cancel
          </button>
          <button
            type="button"
            className="btn btn-danger"
            onClick={handleConfirmDelete}
            disabled={deleting}
          >
            {deleting ? 'Deleting...' : 'Permanently Delete'}
          </button>
        </div>
      </Modal>
    </>
  );
}
