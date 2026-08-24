'use client';

import React, { useEffect } from 'react';
import { useSpring, useTransform, motion } from 'framer-motion';

interface AnimatedNumberProps {
  value: number | string;
  decimals?: number;
  className?: string;
  style?: React.CSSProperties;
  prefix?: string;
  suffix?: string;
}

export default function AnimatedNumber({
  value,
  decimals = 1,
  className,
  style,
  prefix = '',
  suffix = '',
}: AnimatedNumberProps) {
  const numericValue = typeof value === 'number' ? value : parseFloat(String(value));
  const isInvalid = isNaN(numericValue);

  // Hallmark Number Tick / Reveal Spring Physics
  const spring = useSpring(0, {
    stiffness: 75,
    damping: 16,
    mass: 0.8,
  });

  useEffect(() => {
    if (!isInvalid) {
      spring.set(numericValue);
    }
  }, [numericValue, isInvalid, spring]);

  const display = useTransform(spring, (current) => {
    if (isInvalid) return typeof value === 'string' ? value : '--';
    const formatted = decimals === 0 ? Math.round(current).toString() : current.toFixed(decimals);
    return `${prefix}${formatted}${suffix}`;
  });

  if (isInvalid) {
    return (
      <span className={className} style={style} aria-live="polite">
        {typeof value === 'string' ? value : '--'}
      </span>
    );
  }

  return (
    <motion.span className={className} style={style} aria-live="polite">
      {display}
    </motion.span>
  );
}
