'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import Image from 'next/image';
import { useSession, signOut } from 'next-auth/react';
import styles from './Sidebar.module.css';
import Modal from '../ui/Modal';
import {
  FaShapes,
  FaTemperatureHalf,
  FaSun,
  FaFlask,
  FaFan,
  FaDroplet,
  FaSolarPanel,
  FaCircleInfo,
  FaGear,
  FaLock,
  FaRightFromBracket,
  FaUser,
  FaUserClock,
  FaUserShield,
  FaCrown,
  FaTractor,
  FaEye,
} from 'react-icons/fa6';

interface SidebarProps {
  isOpen?: boolean;
  onClose?: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ isOpen = false, onClose }) => {
  const pathname = usePathname();
  const router = useRouter();
  const { data: session } = useSession();
  const [showLogoutModal, setShowLogoutModal] = useState(false);

  const isGuest = session?.user?.isGuest ?? false;
  const userRole = (session?.user?.role as string) || (isGuest ? 'guest' : 'viewer');
  const isAdmin = userRole === 'admin';
  const canControlHardware = isAdmin || userRole === 'farm_manager';

  const username = session?.user?.name || session?.user?.username || session?.user?.email?.split('@')[0] || (isGuest ? 'Guest User' : 'Operator');

  const getRoleLabel = () => {
    if (isGuest) return 'Guest Mode';
    if (userRole === 'admin') return '👑 Master Admin';
    if (userRole === 'farm_manager') return '🛡️ Farm Manager';
    if (userRole === 'farmer') return '🌾 Farmer';
    return '👁️ Viewer';
  };

  const navLinks = [
    {
      type: 'link',
      label: 'Dashboard',
      href: '/dashboard',
      icon: <FaShapes />,
      locked: false,
    },
    { type: 'section', label: 'Sensors' },
    {
      type: 'link',
      label: 'Temp & Humidity',
      href: '/sensors/temp-humidity',
      icon: <FaTemperatureHalf />,
      locked: false,
    },
    {
      type: 'link',
      label: 'Light Intensity',
      href: '/sensors/light-intensity',
      icon: <FaSun />,
      locked: false,
    },
    {
      type: 'link',
      label: 'Soil NPK',
      href: '/sensors/npk',
      icon: <FaFlask />,
      locked: false,
    },
    { type: 'section', label: 'Controls' },
    {
      type: 'link',
      label: 'Auxiliary Fan',
      href: '/controls/fan',
      icon: <FaFan />,
      locked: !canControlHardware,
    },
    {
      type: 'link',
      label: 'Fertigation',
      href: '/controls/fertigation',
      icon: <FaDroplet />,
      locked: !canControlHardware,
    },
    {
      type: 'link',
      label: 'Solar Panels',
      href: '/controls/solar',
      icon: <FaSolarPanel />,
      locked: !canControlHardware,
    },
    { type: 'section', label: 'System' },
    {
      type: 'link',
      label: 'About Us',
      href: '/about',
      icon: <FaCircleInfo />,
      locked: false,
    },
    {
      type: 'link',
      label: 'Contact Us',
      href: '/contact',
      icon: <FaCircleInfo />,
      locked: false,
    },
    {
      type: 'link',
      label: 'Help & Docs',
      href: '/help',
      icon: <FaCircleInfo />,
      locked: false,
    },
    ...(isAdmin
      ? [
          {
            type: 'link',
            label: 'Master Control',
            href: '/master-control',
            icon: <FaUserShield />,
            locked: false,
          },
        ]
      : []),
    {
      type: 'link',
      label: 'Settings',
      href: '/settings',
      icon: <FaGear />,
      locked: isGuest,
    },
  ];

  const handleNavClick = (href: string, locked: boolean) => {
    if (locked) {
      router.push('/dashboard?restricted=controls_only');
    } else {
      router.push(href);
    }
    if (onClose) onClose();
  };

  const handleLogout = async () => {
    try {
      await signOut({ redirect: false });
    } catch (e) {
      console.warn('Signout error:', e);
    }
    window.location.href = '/login';
  };

  return (
    <>
      <aside className={`${styles.sidebar} ${isOpen ? styles.sidebarOpen : ''}`}>
        {/* Brand Header */}
        <div className={styles.brand}>
          <Image
            src="/assets/clsu-official-logo.png"
            alt="CLSU Official Logo"
            width={44}
            height={44}
            className={styles.brandLogo}
            priority
          />
          <div className={styles.brandText}>
            <div className={styles.brandTitle}>
              CLSU <span className={styles.brandHighlight}>Smart Farm</span>
            </div>
            <div className={styles.brandSub}>Indoor Dashboard</div>
          </div>
        </div>

        {/* Navigation */}
        <nav className={styles.navContainer}>
          {navLinks.map((item, index) => {
            if (item.type === 'section') {
              return (
                <div key={`section-${index}`} className={styles.sectionLabel}>
                  {item.label}
                </div>
              );
            }

            const isActive = pathname === item.href || (item.href !== '/dashboard' && pathname.startsWith(item.href || ''));

            return (
              <button
                key={`link-${index}`}
                type="button"
                className={`${styles.navItem} ${isActive ? styles.navItemActive : ''}`}
                onClick={() => handleNavClick(item.href!, item.locked!)}
                style={item.locked ? { opacity: 0.75 } : {}}
              >
                <span className={styles.navIcon}>{item.icon}</span>
                <span>{item.label}</span>
                {item.locked && <FaLock className={styles.lockIcon} />}
              </button>
            );
          })}
        </nav>

        {/* User Profile */}
        <div className={styles.userSection}>
          <div className={`${styles.avatar} ${isGuest ? styles.avatarGuest : ''}`}>
            {isGuest ? <FaUserClock /> : userRole === 'admin' ? <FaCrown style={{ color: '#fbbf24' }} /> : <FaUser />}
          </div>
          <div className={styles.userInfo}>
            <div className={styles.userName}>{username}</div>
            <div className={styles.userStatus} style={{ fontSize: '0.75rem', marginTop: '2px' }}>
              <span className={isGuest ? 'pulse-dot pulse-dot-danger' : 'pulse-dot'} />
              <span style={{ fontWeight: 600 }}>{getRoleLabel()}</span>
            </div>
          </div>
          <button
            type="button"
            className={styles.logoutBtn}
            title={isGuest ? 'Exit Guest Mode' : 'Log Out'}
            onClick={() => setShowLogoutModal(true)}
            aria-label="Logout"
          >
            <FaRightFromBracket />
          </button>
        </div>
      </aside>

      {/* Logout Confirmation Modal */}
      <Modal
        open={showLogoutModal}
        onClose={() => setShowLogoutModal(false)}
        title="Confirm Logout"
        description="Are you sure you want to sign out of the Smart Farm dashboard?"
        icon={<FaRightFromBracket />}
        iconColor="var(--accent-danger)"
      >
        <div style={{ display: 'flex', gap: '12px', justifyContent: 'center', marginTop: '16px' }}>
          <button
            type="button"
            className="btn btn-secondary"
            onClick={() => setShowLogoutModal(false)}
          >
            Cancel
          </button>
          <button
            type="button"
            className="btn btn-danger"
            onClick={handleLogout}
          >
            <FaRightFromBracket /> Sign Out
          </button>
        </div>
      </Modal>
    </>
  );
};

export default Sidebar;
