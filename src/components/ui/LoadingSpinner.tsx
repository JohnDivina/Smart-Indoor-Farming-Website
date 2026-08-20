import React from 'react';
import { FaLeaf } from 'react-icons/fa6';

interface LoadingSpinnerProps {
  text?: string;
  fullScreen?: boolean;
  size?: 'sm' | 'md' | 'lg';
}

export const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({
  text = 'Loading Smart Farm Data...',
  fullScreen = false,
  size = 'md',
}) => {
  const containerSize = size === 'sm' ? 60 : size === 'lg' ? 120 : 80;
  const iconSize = size === 'sm' ? 20 : size === 'lg' ? 44 : 28;

  const content = (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        gap: '20px',
        padding: '24px',
      }}
    >
      <div
        style={{
          position: 'relative',
          width: `${containerSize}px`,
          height: `${containerSize}px`,
        }}
      >
        {/* Ring 1 */}
        <div
          style={{
            position: 'absolute',
            inset: 0,
            border: '3px solid transparent',
            borderTopColor: 'var(--accent-primary)',
            borderRightColor: 'var(--accent-primary)',
            borderRadius: '50%',
            animation: 'spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite',
          }}
        />
        {/* Ring 2 */}
        <div
          style={{
            position: 'absolute',
            inset: '8%',
            border: '3px solid transparent',
            borderTopColor: 'var(--accent-secondary)',
            borderRightColor: 'var(--accent-secondary)',
            borderRadius: '50%',
            animation: 'spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) -0.3s infinite',
          }}
        />
        {/* Center Plant Icon */}
        <div
          style={{
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            color: 'var(--accent-primary)',
            fontSize: `${iconSize}px`,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            filter: 'drop-shadow(0 2px 8px var(--accent-primary-glow))',
            animation: 'pulseGlow 2s infinite ease-in-out',
          }}
        >
          <FaLeaf />
        </div>
      </div>

      {text && (
        <span
          style={{
            fontSize: '0.95rem',
            fontWeight: 600,
            color: 'var(--accent-primary)',
            letterSpacing: '0.3px',
          }}
        >
          {text}
        </span>
      )}
    </div>
  );

  if (fullScreen) {
    return (
      <div
        style={{
          position: 'fixed',
          inset: 0,
          background: 'var(--glass-bg)',
          backdropFilter: 'blur(12px)',
          WebkitBackdropFilter: 'blur(12px)',
          zIndex: 9999,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
        }}
      >
        {content}
      </div>
    );
  }

  return content;
};

export default LoadingSpinner;
