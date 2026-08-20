import React from 'react';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import { FaCircleQuestion, FaMicrochip, FaSliders, FaUserShield, FaWifi } from 'react-icons/fa6';

export default function HelpCenterPage() {
  const faqs = [
    {
      icon: <FaWifi />,
      category: 'IoT Connectivity & Telemetry',
      question: 'How do the ESP32 microcontrollers communicate with the dashboard?',
      answer: 'The microcontrollers poll the RESTful API endpoints at regular intervals (every 2-10 seconds) using HTTP GET/POST with an authenticated API key header. The dashboard displays the status as "Connected" whenever a heartbeat or reading is received within the past 60 seconds.',
    },
    {
      icon: <FaSliders />,
      category: 'Automated Controls & Scheduling',
      question: 'What happens when I toggle the fan or fertigation pump?',
      answer: 'Toggling a switch writes the desired state (ON or OFF) to the database and increments the configuration version. On the next poll cycle, the ESP32 receives the updated command, actuates the corresponding relay, and acknowledges the state change.',
    },
    {
      icon: <FaUserShield />,
      category: 'Guest Mode & Permissions',
      question: 'Why are some controls locked when browsing as a Guest?',
      answer: 'Guest Mode allows visitors to view live telemetry and sensor graphs safely without modifying operational parameters. Actuating relays, changing automated schedules, and modifying account settings require an authenticated user session.',
    },
    {
      icon: <FaMicrochip />,
      category: 'Sensor Accuracy & Calibration',
      question: 'How often are the soil NPK and environmental sensors read?',
      answer: 'Temperature, humidity, and lux sensors stream live data every few seconds. Soil NPK sensors perform periodic sampling and calculate 24-hour averages to maintain reliable macronutrient monitoring.',
    },
  ];

  return (
    <>
      <Header
        title="Help Center &amp; Documentation"
        subtitle="Frequently asked questions, troubleshooting guides, and system documentation."
      />

      <div style={{ maxWidth: '900px', margin: '0 auto', display: 'flex', flexDirection: 'column', gap: '24px' }}>
        <GlassPanel style={{ padding: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '8px' }}>
            <FaCircleQuestion style={{ color: 'var(--accent-primary)', fontSize: '24px' }} />
            <h2 style={{ fontSize: '1.3rem', fontWeight: 800 }}>Frequently Asked Questions</h2>
          </div>
          <p style={{ fontSize: '0.88rem', color: 'var(--text-secondary)' }}>
            Quick answers to common questions about system operation, sensors, and remote controls.
          </p>
        </GlassPanel>

        <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
          {faqs.map((faq, idx) => (
            <GlassPanel key={idx} style={{ padding: '28px 32px' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '8px', color: 'var(--accent-primary)' }}>
                <span style={{ fontSize: '16px' }}>{faq.icon}</span>
                <span style={{ fontSize: '0.78rem', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.5px' }}>
                  {faq.category}
                </span>
              </div>
              <h3 style={{ fontSize: '1.1rem', fontWeight: 700, color: 'var(--text-primary)', marginBottom: '8px' }}>
                {faq.question}
              </h3>
              <p style={{ fontSize: '0.9rem', color: 'var(--text-secondary)', lineHeight: 1.7 }}>
                {faq.answer}
              </p>
            </GlassPanel>
          ))}
        </div>
      </div>
    </>
  );
}
