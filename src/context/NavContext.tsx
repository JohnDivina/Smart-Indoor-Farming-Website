'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';
import { usePathname } from 'next/navigation';

interface NavContextType {
  isMobileNavOpen: boolean;
  toggleMobileNav: () => void;
  openMobileNav: () => void;
  closeMobileNav: () => void;
}

const NavContext = createContext<NavContextType | undefined>(undefined);

export const NavProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [isMobileNavOpen, setIsMobileNavOpen] = useState(false);
  const pathname = usePathname();

  // Automatically close mobile nav when route changes
  useEffect(() => {
    setIsMobileNavOpen(false);
  }, [pathname]);

  const toggleMobileNav = () => setIsMobileNavOpen((prev) => !prev);
  const openMobileNav = () => setIsMobileNavOpen(true);
  const closeMobileNav = () => setIsMobileNavOpen(false);

  return (
    <NavContext.Provider
      value={{
        isMobileNavOpen,
        toggleMobileNav,
        openMobileNav,
        closeMobileNav,
      }}
    >
      {children}
    </NavContext.Provider>
  );
};

export const useNav = (): NavContextType => {
  const context = useContext(NavContext);
  if (!context) {
    // Return a fallback so components outside NavProvider don't crash
    return {
      isMobileNavOpen: false,
      toggleMobileNav: () => {},
      openMobileNav: () => {},
      closeMobileNav: () => {},
    };
  }
  return context;
};

export default NavContext;
