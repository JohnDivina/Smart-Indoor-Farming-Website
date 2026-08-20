import React from 'react';

interface GlassPanelProps extends React.HTMLAttributes<HTMLDivElement> {
  children: React.ReactNode;
  hoverable?: boolean;
  className?: string;
  glow?: boolean;
}

export const GlassPanel: React.FC<GlassPanelProps> = ({
  children,
  hoverable = false,
  className = '',
  glow = false,
  style,
  ...props
}) => {
  return (
    <div
      className={`glass-panel ${hoverable ? 'glass-panel-hoverable' : ''} ${className}`}
      style={{
        ...(glow ? { boxShadow: 'var(--card-glow)' } : {}),
        ...style,
      }}
      {...props}
    >
      {children}
    </div>
  );
};

export default GlassPanel;
