'use client';

import React from 'react';
import Sidebar from '@/components/layout/Sidebar';
import MobileNav from '@/components/layout/MobileNav';
import { NavProvider } from '@/context/NavContext';

export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <NavProvider>
      <div className="app-container">
        {/* Desktop Sidebar */}
        <Sidebar />

        {/* Mobile Drawer Navigation */}
        <MobileNav />

        {/* Main Content Area */}
        <main className="main-content">
          {children}
        </main>
      </div>
    </NavProvider>
  );
}
