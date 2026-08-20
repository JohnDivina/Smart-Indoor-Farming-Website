'use client';

import React, { useEffect } from 'react';
import { useSpring, useTransform, motion } from 'framer-motion';

interface AnimatedNumberProps {
  value: number | string;
  decimals?: number;
  className?: string;
  prefix?: string;
  suffix?: string;
}

export default function AnimatedNumber({
  value,
  decimals = 1,
  className,
  prefix = '',
  suffix = '',
}: AnimatedNumberProps) {
  const numericValue = typeof value === 'number' ? value : parseFloat(value);
  const isInvalid = isNaN(numericValue);

  const spring = useSpring(0, {
    stiffness: 70,
    damping: 18,
    mass: 0.8,
  });

  useEffect(() => {
    if (!isInvalid) {
      spring.set(numericValue);
    }
  }, [numericValue, isInvalid, spring]);

  const display = useTransform(spring, (current) => {
    if (isInvalid) return typeof value === 'string' ? value : '--';
    return `${prefix}${current.toFixed(decimals)}${suffix}`;
  });

  if (isInvalid) {
    return <span className={className}>{typeof value === 'string' ? value : '--'}</span>;
  }

  return <motion.span className={className}>{display}</motion.span>;
}
