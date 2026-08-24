'use client';

import React, { useEffect, useRef } from 'react';

interface Particle {
  x: number;
  y: number;
  vx: number;
  vy: number;
  radius: number;
  color: string;
  glowColor: string;
  alpha: number;
  baseAlpha: number;
}

interface InteractiveParticlesProps {
  className?: string;
  style?: React.CSSProperties;
  particleCount?: number;
  connectionDistance?: number;
  mouseRadius?: number;
}

export default function InteractiveParticles({
  className,
  style,
  particleCount = 60,
  connectionDistance = 120,
  mouseRadius = 130, // Proximity for connecting lines to cursor
}: InteractiveParticlesProps) {
  const canvasRef = useRef<HTMLCanvasElement | null>(null);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let animationFrameId: number;
    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    // High-DPI support for sharp rendering
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    ctx.scale(dpr, dpr);

    const mouse = {
      x: -9999,
      y: -9999,
      isActive: false,
    };

    // Color palette: Smart Farm Emeralds, Limes & Solar Gold
    const colors = [
      { fill: 'rgba(16, 185, 129, ', glow: 'rgba(16, 185, 129, 0.45)' },
      { fill: 'rgba(34, 197, 94, ', glow: 'rgba(34, 197, 94, 0.45)' },
      { fill: 'rgba(242, 169, 0, ', glow: 'rgba(242, 169, 0, 0.4)' },
    ];

    const actualCount = width < 768 ? Math.floor(particleCount * 0.5) : particleCount;

    const particles: Particle[] = [];
    for (let i = 0; i < actualCount; i++) {
      const palette = colors[Math.floor(Math.random() * colors.length)];
      const baseAlpha = Math.random() * 0.45 + 0.25;
      const x = Math.random() * width;
      const y = Math.random() * height;

      particles.push({
        x,
        y,
        vx: (Math.random() - 0.5) * 0.55,
        vy: (Math.random() - 0.5) * 0.55,
        radius: Math.random() * 1.6 + 1.2,
        color: palette.fill,
        glowColor: palette.glow,
        alpha: baseAlpha,
        baseAlpha,
      });
    }

    const handleResize = () => {
      if (!canvas) return;
      width = window.innerWidth;
      height = window.innerHeight;
      canvas.width = width * dpr;
      canvas.height = height * dpr;
      ctx.scale(dpr, dpr);
    };

    const handleMouseMove = (e: MouseEvent) => {
      const rect = canvas.getBoundingClientRect();
      mouse.x = e.clientX - rect.left;
      mouse.y = e.clientY - rect.top;
      mouse.isActive = true;
    };

    const handleMouseLeave = () => {
      mouse.isActive = false;
      mouse.x = -9999;
      mouse.y = -9999;
    };

    const handleTouchMove = (e: TouchEvent) => {
      if (e.touches.length > 0) {
        const rect = canvas.getBoundingClientRect();
        mouse.x = e.touches[0].clientX - rect.left;
        mouse.y = e.touches[0].clientY - rect.top;
        mouse.isActive = true;
      }
    };

    const handleTouchEnd = () => {
      mouse.isActive = false;
      mouse.x = -9999;
      mouse.y = -9999;
    };

    window.addEventListener('resize', handleResize);
    window.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseleave', handleMouseLeave);
    window.addEventListener('touchmove', handleTouchMove, { passive: true });
    window.addEventListener('touchend', handleTouchEnd);

    // Animation Loop
    let time = 0;
    const render = () => {
      time += 0.015;
      ctx.clearRect(0, 0, width, height);

      // Update & Draw Particles
      for (let i = 0; i < particles.length; i++) {
        const p = particles[i];

        // Organic subtle floating pulse
        p.alpha = p.baseAlpha + Math.sin(time + i * 0.6) * 0.12;

        // Smooth independent drift (particles never get dragged around)
        p.x += p.vx;
        p.y += p.vy;

        // Smooth boundary wrap / bounce
        if (p.x < 0) { p.x = 0; p.vx *= -1; }
        if (p.x > width) { p.x = width; p.vx *= -1; }
        if (p.y < 0) { p.y = 0; p.vy *= -1; }
        if (p.y > height) { p.y = height; p.vy *= -1; }

        // ── Cursor Attachment Web (Attaches connecting lines to cursor on hover) ──
        if (mouse.isActive && mouse.x > 0 && mouse.y > 0) {
          const dx = mouse.x - p.x;
          const dy = mouse.y - p.y;
          const dist = Math.hypot(dx, dy);

          if (dist < mouseRadius) {
            const lineAlpha = (1 - dist / mouseRadius) * 0.42;
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(mouse.x, mouse.y);
            ctx.strokeStyle = `rgba(16, 185, 129, ${lineAlpha})`;
            ctx.lineWidth = 1.0;
            ctx.stroke();
          }
        }

        // Draw particle dot
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
        ctx.fillStyle = `${p.color}${Math.max(0.1, p.alpha)})`;
        ctx.shadowBlur = 6;
        ctx.shadowColor = p.glowColor;
        ctx.fill();
        ctx.shadowBlur = 0;

        // ── Connect Nearby Particles (Constellation mesh) ──
        for (let j = i + 1; j < particles.length; j++) {
          const p2 = particles[j];
          const dist = Math.hypot(p.x - p2.x, p.y - p2.y);

          if (dist < connectionDistance) {
            const lineAlpha = (1 - dist / connectionDistance) * 0.18;
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.strokeStyle = `rgba(16, 185, 129, ${lineAlpha})`;
            ctx.lineWidth = 0.75;
            ctx.stroke();
          }
        }
      }

      animationFrameId = requestAnimationFrame(render);
    };

    render();

    return () => {
      cancelAnimationFrame(animationFrameId);
      window.removeEventListener('resize', handleResize);
      window.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseleave', handleMouseLeave);
      window.removeEventListener('touchmove', handleTouchMove);
      window.removeEventListener('touchend', handleTouchEnd);
    };
  }, [particleCount, connectionDistance, mouseRadius]);

  return (
    <canvas
      ref={canvasRef}
      className={className}
      style={{
        position: 'fixed',
        inset: 0,
        pointerEvents: 'none',
        zIndex: 0,
        ...style,
      }}
    />
  );
}
