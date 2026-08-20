'use client';

import React, { useEffect } from 'react';
import GlassPanel from './GlassPanel';
import { FaXmark } from 'react-icons/fa6';

interface ModalProps {
  open: boolean;
  onClose: () => void;
  title: string;
  description?: string;
  children?: React.ReactNode;
  icon?: React.ReactNode;
  iconColor?: string;
}

export const Modal: React.FC<ModalProps> = ({
  open,
  onClose,
  title,
  description,
  children,
  icon,
  iconColor,
}) => {
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && open) {
        onClose();
      }
    };
    if (open) {
      document.body.style.overflow = 'hidden';
      window.addEventListener('keydown', handleKeyDown);
    }
    return () => {
      document.body.style.overflow = '';
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [open, onClose]);

  if (!open) return null;

  return (
    <div
      style={{
        position: 'fixed',
        inset: 0,
        zIndex: 9999,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '20px',
      }}
    >
      {/* Backdrop */}
      <div
        onClick={onClose}
        style={{
          position: 'absolute',
          inset: 0,
          background: 'rgba(0, 0, 0, 0.55)',
          backdropFilter: 'blur(8px)',
          WebkitBackdropFilter: 'blur(8px)',
        }}
      />

      {/* Dialog */}
      <GlassPanel
        style={{
          position: 'relative',
          width: '100%',
          maxWidth: '480px',
          padding: '32px',
          borderRadius: '24px',
          boxShadow: '0 24px 64px rgba(0, 0, 0, 0.3)',
          border: '1px solid var(--glass-border-hover)',
          zIndex: 1,
          animation: 'fadeIn 0.25s ease-out',
        }}
      >
        <button
          type="button"
          onClick={onClose}
          aria-label="Close"
          style={{
            position: 'absolute',
            top: '20px',
            right: '20px',
            width: '32px',
            height: '32px',
            borderRadius: '50%',
            background: 'var(--glass-bg-subtle)',
            border: '1px solid var(--glass-border)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: 'var(--text-secondary)',
            fontSize: '14px',
            cursor: 'pointer',
          }}
        >
          <FaXmark />
        </button>

        {icon && (
          <div
            style={{
              fontSize: '44px',
              color: iconColor || 'var(--accent-primary)',
              marginBottom: '16px',
              textAlign: 'center',
            }}
          >
            {icon}
          </div>
        )}

        <h3
          style={{
            fontSize: '1.35rem',
            fontWeight: 700,
            color: 'var(--text-primary)',
            textAlign: 'center',
            marginBottom: '8px',
          }}
        >
          {title}
        </h3>

        {description && (
          <p
            style={{
              fontSize: '0.95rem',
              color: 'var(--text-secondary)',
              textAlign: 'center',
              marginBottom: '24px',
              lineHeight: 1.5,
            }}
          >
            {description}
          </p>
        )}

        {children}
      </GlassPanel>
    </div>
  );
};

export default Modal;
