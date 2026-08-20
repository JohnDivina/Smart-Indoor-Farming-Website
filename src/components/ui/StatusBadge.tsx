import React from 'react';

interface StatusBadgeProps {
  status: 'connected' | 'disconnected' | 'online' | 'offline' | 'active' | 'inactive';
  label?: string;
  className?: string;
}

export const StatusBadge: React.FC<StatusBadgeProps> = ({
  status,
  label,
  className = '',
}) => {
  const isOnline = status === 'connected' || status === 'online' || status === 'active';
  const defaultLabel = isOnline ? 'Connected' : 'Disconnected';

  return (
    <span
      className={`status-badge ${className}`}
      style={{
        display: 'inline-flex',
        alignItems: 'center',
        gap: '6px',
        fontSize: '0.75rem',
        fontWeight: 600,
        padding: '4px 10px',
        borderRadius: '999px',
        background: isOnline ? 'rgba(16, 185, 129, 0.15)' : 'rgba(234, 76, 76, 0.15)',
        color: isOnline ? 'var(--accent-success)' : 'var(--accent-danger)',
        border: `1px solid ${isOnline ? 'rgba(16, 185, 129, 0.3)' : 'rgba(234, 76, 76, 0.3)'}`,
        backdropFilter: 'blur(8px)',
      }}
    >
      <span className={isOnline ? 'pulse-dot' : 'pulse-dot pulse-dot-danger'} />
      {label || defaultLabel}
    </span>
  );
};

export default StatusBadge;
