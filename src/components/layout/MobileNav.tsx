'use client';

import React from 'react';
import Sidebar from './Sidebar';
import { useNav } from '@/context/NavContext';

interface MobileNavProps {
  isOpen?: boolean;
  onClose?: () => void;
}

export const MobileNav: React.FC<MobileNavProps> = ({ isOpen, onClose }) => {
  const nav = useNav();
  const activeIsOpen = isOpen !== undefined ? isOpen : nav.isMobileNavOpen;
  const handleClose = onClose || nav.closeMobileNav;

  if (!activeIsOpen) return null;

  return (
    <>
      {/* Backdrop */}
      <div
        onClick={handleClose}
        style={{
          position: 'fixed',
          inset: 0,
          background: 'rgba(0, 0, 0, 0.55)',
          backdropFilter: 'blur(4px)',
          WebkitBackdropFilter: 'blur(4px)',
          zIndex: 99,
          animation: 'fadeIn 0.2s ease-out',
        }}
      />
      <Sidebar isOpen={activeIsOpen} onClose={handleClose} />
    </>
  );
};

export default MobileNav;
