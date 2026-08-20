'use client';

import React from 'react';
import styles from './Header.module.css';
import { useTheme } from '@/context/ThemeContext';
import { FaMoon, FaSun, FaBars } from 'react-icons/fa6';

interface HeaderProps {
  title: string;
  subtitle?: string;
  onMobileMenuToggle?: () => void;
  children?: React.ReactNode;
}

export const Header: React.FC<HeaderProps> = ({
  title,
  subtitle,
  onMobileMenuToggle,
  children,
}) => {
  const { theme, toggleTheme } = useTheme();

  return (
    <header className={styles.header}>
      <div className={styles.titleArea}>
        <h1 className={styles.pageTitle}>{title}</h1>
        {subtitle && <p className={styles.pageSubtitle}>{subtitle}</p>}
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
