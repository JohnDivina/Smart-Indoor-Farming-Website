'use client';

import React from 'react';
import { motion } from 'framer-motion';

interface PHScaleProps {
  value: number | string;
  min?: number;
  max?: number;
}

export default function PHScale({ value, min = 0, max = 14 }: PHScaleProps) {
  const numericValue = typeof value === 'number' ? value : parseFloat(value);
  const validValue = isNaN(numericValue) ? 7.0 : Math.min(Math.max(numericValue, min), max);
  const percentage = ((validValue - min) / (max - min)) * 100;

  const getPHCategory = (ph: number) => {
    if (ph < 6.0) return { label: 'Acidic Soil', color: '#e74c3c' };
    if (ph <= 7.5) return { label: 'Optimal / Neutral', color: '#2ecc71' };
    return { label: 'Alkaline Soil', color: '#9b59b6' };
  };

  const category = getPHCategory(validValue);

  return (
    <div style={{ width: '100%', padding: '24px 16px' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '28px' }}>
        <div>
          <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.8px', fontWeight: 600 }}>
            Soil pH Assessment
          </span>
          <h4 style={{ fontSize: '1.2rem', fontWeight: 800, color: 'var(--text-primary)', marginTop: '2px' }}>
            {isNaN(numericValue) ? '--' : numericValue.toFixed(1)} pH &bull;{' '}
            <span style={{ color: category.color }}>{category.label}</span>
          </h4>
        </div>
        <span
          style={{
            padding: '4px 12px',
            borderRadius: '999px',
            fontSize: '0.78rem',
            fontWeight: 700,
            background: `${category.color}20`,
            color: category.color,
            border: `1px solid ${category.color}40`,
          }}
        >
          Target: 6.0 - 7.0
        </span>
      </div>

      {/* pH Gradient Track */}
      <div
        style={{
          position: 'relative',
          height: '14px',
          borderRadius: '7px',
          background: 'linear-gradient(to right, #e74c3c 0%, #f39c12 25%, #2ecc71 50%, #3498db 75%, #9b59b6 100%)',
          margin: '36px 0 16px',
        }}
      >
        {/* Animated Position Indicator */}
        <motion.div
          initial={{ left: '50%' }}
          animate={{ left: `${percentage}%` }}
          transition={{ type: 'spring', stiffness: 80, damping: 15 }}
          style={{
            position: 'absolute',
            top: '-34px',
            transform: 'translateX(-50%)',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
          }}
        >
          <div
            style={{
              background: 'var(--glass-bg)',
              backdropFilter: 'blur(10px)',
              border: `2px solid ${category.color}`,
              color: 'var(--text-primary)',
              padding: '2px 8px',
              borderRadius: '6px',
              fontWeight: 800,
              fontSize: '0.85rem',
              boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
              whiteSpace: 'nowrap',
            }}
          >
            {isNaN(numericValue) ? '--' : numericValue.toFixed(1)}
          </div>
          <div
            style={{
              width: 0,
              height: 0,
              borderLeft: '5px solid transparent',
              borderRight: '5px solid transparent',
              borderTop: `6px solid ${category.color}`,
            }}
          />
        </motion.div>
      </div>

      {/* Scale Labels */}
      <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.75rem', fontWeight: 600, color: 'var(--text-muted)' }}>
        <span>0 (Acidic)</span>
        <span>3.5</span>
        <span>7.0 (Neutral)</span>
        <span>10.5</span>
        <span>14 (Alkaline)</span>
      </div>
    </div>
  );
}
