import type { Metadata } from 'next';
import './globals.css';
import { ThemeProvider } from '@/context/ThemeContext';
import { SessionProvider } from '@/context/SessionProvider';

export const metadata: Metadata = {
  title: 'CLSU Smart Indoor Farming Dashboard',
  description: 'Smart indoor precision agriculture monitoring, sensor tracking, and environmental automated controls for CLSU.',
  icons: {
    icon: [
      { url: '/assets/clsu-official-logo.png', type: 'image/png' },
      { url: '/favicon.ico' },
    ],
    shortcut: '/assets/clsu-official-logo.png',
    apple: '/assets/clsu-official-logo.png',
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <head>
        <link rel="icon" type="image/png" href="/assets/clsu-official-logo.png" />
        <link rel="shortcut icon" href="/assets/clsu-official-logo.png" />
        <link rel="apple-touch-icon" href="/assets/clsu-official-logo.png" />
      </head>
      <body>
        <SessionProvider>
          <ThemeProvider>
            {children}
          </ThemeProvider>
        </SessionProvider>
      </body>
    </html>
  );
}
