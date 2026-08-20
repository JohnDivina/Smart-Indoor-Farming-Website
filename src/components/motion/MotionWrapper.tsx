'use client';

import React from 'react';
import { motion, HTMLMotionProps } from 'framer-motion';

interface MotionWrapperProps extends HTMLMotionProps<'div'> {
  children: React.ReactNode;
  delay?: number;
  duration?: number;
  direction?: 'up' | 'down' | 'left' | 'right' | 'none';
  stagger?: boolean;
}

export default function MotionWrapper({
  children,
  delay = 0,
  duration = 0.35,
  direction = 'up',
  stagger = false,
  className,
  style,
  ...props
}: MotionWrapperProps) {
  const getInitialY = () => {
    if (direction === 'up') return 12;
    if (direction === 'down') return -12;
    return 0;
  };

  const getInitialX = () => {
    if (direction === 'left') return 12;
    if (direction === 'right') return -12;
    return 0;
  };

  const variants = stagger
    ? {
        hidden: { opacity: 0 },
        visible: {
          opacity: 1,
          transition: {
            staggerChildren: 0.08,
            delayChildren: delay,
          },
        },
      }
    : {
        hidden: {
          opacity: 0,
          y: getInitialY(),
          x: getInitialX(),
        },
        visible: {
          opacity: 1,
          y: 0,
          x: 0,
          transition: {
            duration,
            delay,
            ease: [0.16, 1, 0.3, 1], // --ease-out
          },
        },
      };

  return (
    <motion.div
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: '-20px' }}
      variants={variants}
      className={className}
      style={style}
      {...props}
    >
      {children}
    </motion.div>
  );
}
