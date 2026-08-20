'use client';

import React from 'react';
import Link from 'next/link';
import styles from './Header.module.css';
import { useTheme } from '@/context/ThemeContext';
import { FaMoon, FaSun, FaBars, FaArrowLeft } from 'react-icons/fa6';
import StatusBadge from '@/components/ui/StatusBadge';

interface HeaderProps {
  title: string;
  subtitle?: string;
  backHref?: string;
  backLabel?: string;
  status?: 'connected' | 'disconnected';
  lastUpdated?: string;
  onMobileMenuToggle?: () => void;
  children?: React.ReactNode;
}

export const Header: React.FC<HeaderProps> = ({
  title,
  subtitle,
  backHref,
  backLabel = 'Back to Dashboard',
  status,
  lastUpdated,
  onMobileMenuToggle,
  children,
}) => {
  const { theme, toggleTheme } = useTheme();

  return (
    <header className={styles.header}>
      <div className={styles.titleArea}>
        {backHref && (
          <Link
            href={backHref}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '6px',
              color: 'var(--accent-primary)',
              fontSize: '0.82rem',
              fontWeight: 700,
              textDecoration: 'none',
              marginBottom: '6px',
              transition: 'transform var(--dur-micro) var(--ease-out)',
            }}
          >
            <FaArrowLeft style={{ fontSize: '11px' }} /> {backLabel}
          </Link>
        )}

        <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
          <h1 className={styles.pageTitle}>{title}</h1>
          {status && <StatusBadge status={status} />}
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flexWrap: 'wrap' }}>
          {subtitle && <p className={styles.pageSubtitle}>{subtitle}</p>}
          {lastUpdated && (
            <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>
              &bull; Last updated: <strong style={{ color: 'var(--text-primary)' }}>{lastUpdated}</strong>
            </span>
          )}
        </div>
      </div>

      <div className={styles.actions}>
        {children}

        {/* Theme Toggle */}
        <button
          type="button"
          className={styles.themeBtn}
          onClick={toggleTheme}
          title={`Switch to ${theme === 'light' ? 'Dark' : 'Light'} Mode`}
          aria-label="Toggle theme"
        >
          {theme === 'light' ? <FaMoon /> : <FaSun style={{ color: '#F2A900' }} />}
        </button>

        {/* Mobile Hamburger Toggle */}
        {onMobileMenuToggle && (
          <button
            type="button"
            className={styles.mobileMenuBtn}
            onClick={onMobileMenuToggle}
            aria-label="Open mobile menu"
          >
            <FaBars />
          </button>
        )}
      </div>
    </header>
  );
};

export default Header;
