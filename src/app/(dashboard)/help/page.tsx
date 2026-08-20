'use client';

import React, { useState } from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import MotionWrapper from '@/components/motion/MotionWrapper';
import { motion, AnimatePresence } from 'framer-motion';
import {
  FaCircleQuestion,
  FaMicrochip,
  FaSliders,
  FaUserShield,
  FaWifi,
  FaChevronDown,
  FaBookOpen,
  FaEnvelope,
} from 'react-icons/fa6';
import Link from 'next/link';

export default function HelpCenterPage() {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  const toggleAccordion = (index: number) => {
    setOpenIndex(openIndex === index ? null : index);
  };

  const faqs = [
    {
      icon: <FaWifi />,
      category: 'IoT Connectivity & Telemetry',
      question: 'How do the ESP32 microcontrollers communicate with the dashboard?',
      answer:
        'The ESP32 microcontrollers poll the RESTful API endpoints at regular intervals (every 2-10 seconds) using HTTP GET/POST with an authenticated API key header. The dashboard displays the status as "Connected" whenever a heartbeat or sensor packet is received within the past 60 seconds.',
    },
    {
      icon: <FaSliders />,
      category: 'Automated Controls & Scheduling',
      question: 'What happens when I toggle the fan or fertigation pump?',
      answer:
        'Toggling a switch in the dashboard writes the desired state (ON or OFF) to the PostgreSQL database and increments the hardware configuration version. On the subsequent polling cycle, the ESP32 pulls the new state, actuates the relay, and acknowledges the state change back to the cloud.',
    },
    {
      icon: <FaUserShield />,
      category: 'Guest Mode & Permissions',
      question: 'Why are some controls locked when browsing as a Guest?',
      answer:
        'Guest Mode allows visitors to view live telemetry and sensor graphs safely without modifying operational parameters. Actuating relays, changing automated schedules, and modifying account settings require an authenticated user session.',
    },
    {
      icon: <FaMicrochip />,
      category: 'Sensor Accuracy & Calibration',
      question: 'How often are the soil NPK and environmental sensors read?',
      answer:
        'Temperature, humidity, and lux sensors stream live data every few seconds. Soil NPK sensors perform periodic sampling and calculate 24-hour averages to maintain reliable macronutrient monitoring.',
    },
    {
      icon: <FaBookOpen />,
      category: 'Excel & Data Reporting',
      question: 'How do I download historical telemetry records?',
      answer:
        'Navigate to any sensor page (NPK, Temperature, or Light Intensity), switch to the "Data Insights & History" tab, select your desired calendar date, and click "Export Excel". An formatted .xlsx spreadsheet will be generated instantly.',
    },
  ];

  return (
    <>
      <Header
        title="Help Center &amp; Documentation"
        subtitle="Frequently asked questions, troubleshooting guides, and system documentation."
      />

      <div style={{ maxWidth: '960px', margin: '0 auto', display: 'flex', flexDirection: 'column', gap: '24px' }}>
        {/* ── Banner ── */}
        <MotionWrapper direction="up" delay={0.05}>
          <GlassPanel style={{ padding: '32px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '14px', marginBottom: '8px' }}>
              <div style={{ width: '48px', height: '48px', borderRadius: '14px', background: 'rgba(0, 102, 0, 0.12)', color: 'var(--accent-primary)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }}>
                <FaCircleQuestion />
              </div>
              <div>
                <h2 style={{ fontSize: '1.35rem', fontWeight: 800 }}>Frequently Asked Questions</h2>
                <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)' }}>
                  Quick answers to common questions about system operation, sensors, and remote controls.
                </p>
              </div>
            </div>
          </GlassPanel>
        </MotionWrapper>

        {/* ── Accordion FAQ List ── */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
          {faqs.map((faq, idx) => {
            const isOpen = openIndex === idx;

            return (
              <GlassPanel
                key={idx}
                style={{
                  padding: '20px 24px',
                  cursor: 'pointer',
                  borderRadius: '16px',
                  transition: 'border-color var(--dur-short) var(--ease-out)',
                }}
                onClick={() => toggleAccordion(idx)}
              >
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                    <span style={{ color: 'var(--accent-primary)', fontSize: '18px' }}>{faq.icon}</span>
                    <div>
                      <span style={{ fontSize: '0.72rem', fontWeight: 700, color: 'var(--accent-primary)', textTransform: 'uppercase', letterSpacing: '0.6px', display: 'block' }}>
                        {faq.category}
                      </span>
                      <h3 style={{ fontSize: '1.02rem', fontWeight: 700, color: 'var(--text-primary)', marginTop: '2px' }}>
                        {faq.question}
                      </h3>
                    </div>
                  </div>

                  <motion.div
                    animate={{ rotate: isOpen ? 180 : 0 }}
                    transition={{ duration: 0.2 }}
                    style={{ color: 'var(--text-muted)', fontSize: '14px', flexShrink: 0, marginLeft: '12px' }}
                  >
                    <FaChevronDown />
                  </motion.div>
                </div>

                <AnimatePresence>
                  {isOpen && (
                    <motion.div
                      initial={{ opacity: 0, height: 0 }}
                      animate={{ opacity: 1, height: 'auto' }}
                      exit={{ opacity: 0, height: 0 }}
                      transition={{ duration: 0.25, ease: [0.16, 1, 0.3, 1] }}
                      style={{ overflow: 'hidden' }}
                    >
                      <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)', lineHeight: 1.7, marginTop: '16px', paddingTop: '16px', borderTop: '1px solid var(--glass-border)' }}>
                        {faq.answer}
                      </p>
                    </motion.div>
                  )}
                </AnimatePresence>
              </GlassPanel>
            );
          })}
        </div>

        {/* ── Support CTA ── */}
        <GlassPanel style={{ padding: '28px 32px', textAlign: 'center' }}>
          <h3 style={{ fontSize: '1.15rem', fontWeight: 800, color: 'var(--text-primary)', marginBottom: '6px' }}>
            Still have questions?
          </h3>
          <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)', marginBottom: '18px' }}>
            Our CLSU research and technical team is ready to assist.
          </p>
          <Link href="/contact" className="btn btn-primary" style={{ display: 'inline-flex', alignItems: 'center', gap: '8px' }}>
            <FaEnvelope /> Contact the Team &rarr;
          </Link>
        </GlassPanel>
      </div>
    </>
  );
}
