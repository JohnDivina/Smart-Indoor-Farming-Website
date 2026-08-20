'use client';

import React from 'react';

interface ControlToggleProps {
  isOn: boolean;
  onToggle: () => void;
  disabled?: boolean;
  loading?: boolean;
  labelOn?: string;
  labelOff?: string;
  size?: 'sm' | 'md' | 'lg';
}

export const ControlToggle: React.FC<ControlToggleProps> = ({
  isOn,
  onToggle,
  disabled = false,
  loading = false,
  labelOn = 'ON',
  labelOff = 'OFF',
  size = 'md',
}) => {
  const dimensions = {
    sm: { width: 56, height: 28, dot: 22, offset: 28 },
    md: { width: 72, height: 36, dot: 28, offset: 36 },
    lg: { width: 88, height: 44, dot: 36, offset: 44 },
  }[size];

  return (
    <button
      type="button"
      role="switch"
      aria-checked={isOn}
      disabled={disabled || loading}
      onClick={onToggle}
      style={{
        width: `${dimensions.width}px`,
        height: `${dimensions.height}px`,
        borderRadius: '999px',
        background: isOn
          ? 'linear-gradient(135deg, var(--accent-primary) 0%, #009933 100%)'
          : 'var(--glass-bg-subtle)',
        border: `2px solid ${isOn ? 'var(--accent-primary)' : 'var(--glass-border)'}`,
        position: 'relative',
        cursor: disabled || loading ? 'not-allowed' : 'pointer',
        opacity: disabled ? 0.6 : 1,
        transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
        boxShadow: isOn ? '0 0 16px var(--accent-primary-glow)' : 'none',
        display: 'inline-flex',
        alignItems: 'center',
        padding: '2px',
      }}
    >
      <div
        style={{
          width: `${dimensions.dot}px`,
          height: `${dimensions.dot}px`,
          borderRadius: '50%',
          background: '#ffffff',
          boxShadow: '0 2px 8px rgba(0, 0, 0, 0.2)',
          transform: isOn ? `translateX(${dimensions.offset}px)` : 'translateX(0px)',
          transition: 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '10px',
          fontWeight: 800,
          color: isOn ? 'var(--accent-primary)' : 'var(--text-muted)',
        }}
      >
        {loading ? (
          <span
            style={{
              width: '12px',
              height: '12px',
              border: '2px solid var(--accent-primary)',
              borderTopColor: 'transparent',
              borderRadius: '50%',
              animation: 'spin 0.8s linear infinite',
            }}
          />
        ) : isOn ? (
          labelOn
        ) : (
          labelOff
        )}
      </div>
    </button>
  );
};

export default ControlToggle;
