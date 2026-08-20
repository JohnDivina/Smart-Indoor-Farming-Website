'use client';

import React from 'react';
import Image from 'next/image';
import Header from '@/components/layout/Header';
import GlassPanel from '@/components/ui/GlassPanel';
import MotionWrapper from '@/components/motion/MotionWrapper';
import {
  FaSeedling,
  FaMicrochip,
  FaUsers,
  FaAward,
  FaGears,
  FaDatabase,
  FaVial,
  FaCode,
  FaBolt,
  FaWrench,
  FaEarthAmericas,
  FaWater,
  FaStore,
} from 'react-icons/fa6';

export default function AboutUsPage() {
  const teamMembers = [
    {
      name: 'Dr. Milagros R. Campos',
      role: 'Project Leader / Associate Professor V',
      image: '/assets/images/team/project1.png',
      expertise: [
        { label: 'Agriculture', icon: <FaSeedling /> },
        { label: 'Crop Science', icon: <FaSeedling /> },
      ],
      bio: 'Leading the development of smart farming solutions with over 15 years of experience in agricultural technology and IoT integration.',
    },
    {
      name: 'Engr. Roldan T. Quitos',
      role: 'Associate Professor I',
      image: '/assets/images/team/project2.png',
      expertise: [
        { label: 'Agr. Mechanization', icon: <FaGears /> },
        { label: 'Electronics', icon: <FaMicrochip /> },
      ],
      bio: 'Specializes in designing and implementing sensor networks and control systems for precision agriculture applications.',
    },
    {
      name: 'Sylvester A. Badua, PhD',
      role: 'Associate Professor V',
      image: '/assets/images/team/project3.png',
      expertise: [
        { label: 'Agr. Mechanization', icon: <FaGears /> },
        { label: 'Instr. & Controls', icon: <FaDatabase /> },
      ],
      bio: 'Develops intuitive dashboards and data visualization tools to help farmers make informed decisions based on real-time sensor data.',
    },
    {
      name: 'Engr. John Vincent A. Nate',
      role: 'Former Faculty',
      image: '/assets/images/team/project4.png',
      expertise: [
        { label: 'Bio-processing', icon: <FaVial /> },
        { label: 'Soils & Water', icon: <FaWater /> },
      ],
      bio: 'Provides expertise in crop management and optimal growing conditions for hot pepper and tomato production in controlled environments.',
    },
    {
      name: 'Ivan Christian L. Salinas',
      role: 'Instructor I',
      image: '/assets/images/team/project5.png',
      expertise: [
        { label: 'Programming', icon: <FaCode /> },
        { label: 'Networking', icon: <FaMicrochip /> },
      ],
      bio: 'Analyzes system performance and develops automation algorithms to optimize resource usage and maximize crop yield.',
    },
    {
      name: 'John Rey L. Divina',
      role: 'Project Technical Assistant',
      image: '/assets/images/team/project6.png',
      expertise: [
        { label: 'Electronics', icon: <FaBolt /> },
        { label: 'Automation', icon: <FaMicrochip /> },
        { label: 'Data Comm.', icon: <FaDatabase /> },
      ],
      bio: 'Conducts field experiments and maintains comprehensive documentation of system performance and crop growth patterns.',
    },
    {
      name: 'Alcris R. Dumale',
      role: 'Project Laborer I',
      image: '/assets/images/team/projectx.png',
      expertise: [
        { label: 'Tech Support', icon: <FaWrench /> },
        { label: 'Maintenance', icon: <FaGears /> },
      ],
      bio: 'Provides crucial on-site technical support and routine system maintenance to assure uninterrupted operational capacity.',
    },
    {
      name: 'Emelie C. Ablaza, PhD',
      role: 'Associate Professor V',
      image: '/assets/images/team/project8.png',
      expertise: [
        { label: 'Environmental', icon: <FaEarthAmericas /> },
        { label: 'Impact Assmt.', icon: <FaAward /> },
      ],
      bio: 'Evaluates the environmental sustainability and long-term societal impacts of deployed smart farming protocols in the local sector.',
    },
    {
      name: 'Jeannie-Rose G. Fabulla, PhD',
      role: 'Associate Professor II',
      image: '/assets/images/team/project7.png',
      expertise: [
        { label: 'Soils & Water', icon: <FaWater /> },
        { label: 'Impact Assmt.', icon: <FaAward /> },
      ],
      bio: 'Researches ideal soil configurations and hydrodynamic modeling to tailor precision irrigation to specific plant cultivars.',
    },
    {
      name: 'Marilou M. Sarong, PhD',
      role: 'Associate Professor IV',
      image: '/assets/images/team/project9.png',
      expertise: [
        { label: 'Soil Fertility', icon: <FaSeedling /> },
        { label: 'Soil Physics', icon: <FaEarthAmericas /> },
      ],
      bio: 'Specialist in soil nutrient management, analyzing optimal NPK thresholds for greenhouse-based crop production.',
    },
    {
      name: 'Cesar V. Ortinero, PhD',
      role: 'Professor III',
      image: '/assets/images/team/project12.png',
      expertise: [
        { label: 'Environmental', icon: <FaEarthAmericas /> },
        { label: 'Impact Assmt.', icon: <FaAward /> },
      ],
      bio: 'Collaborates on strategic environmental assessments and sustainability frameworks for integrated indoor farming ventures.',
    },
    {
      name: 'Katherine DA. Bautista',
      role: 'Assistant Professor I',
      image: '/assets/images/team/project10.png',
      expertise: [
        { label: 'Environmental', icon: <FaEarthAmericas /> },
        { label: 'Assessment', icon: <FaVial /> },
      ],
      bio: 'Research focus on ecological monitoring and environmental compliance within the smart indoor agriculture framework.',
    },
    {
      name: 'Fernando P. Ferrer',
      role: 'Assistant Professor II',
      image: '/assets/images/team/project11.jpg',
      expertise: [
        { label: 'Marketing', icon: <FaStore /> },
        { label: 'Cost Control', icon: <FaAward /> },
      ],
      bio: 'Manages the economic feasibility and marketing strategies for distributed greenhouse products in domestic markets.',
    },
  ];

  return (
    <>
      <Header
        title="About CLSU Smart Farm"
        subtitle="Central Luzon State University Precision Indoor Agriculture & Automated Smart Greenhouse Project."
      />

      <div style={{ maxWidth: '1200px', margin: '0 auto', display: 'flex', flexDirection: 'column', gap: '32px' }}>
        {/* ── Mission Hero Card ── */}
        <MotionWrapper direction="up" delay={0.05}>
          <GlassPanel style={{ padding: '36px 40px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '20px', marginBottom: '24px', flexWrap: 'wrap' }}>
              <div style={{ position: 'relative', width: '72px', height: '72px', flexShrink: 0 }}>
                <Image
                  src="/assets/clsu-official-logo.png"
                  alt="CLSU Official Seal"
                  fill
                  style={{ objectFit: 'contain' }}
                  priority
                />
              </div>
              <div>
                <h2 style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
                  Precision Agriculture for Sustainable Food Security
                </h2>
                <p style={{ fontSize: '0.9rem', color: 'var(--text-muted)', marginTop: '2px' }}>
                  Central Luzon State University &bull; Science City of Muñoz, Nueva Ecija, Philippines
                </p>
              </div>
            </div>

            <p style={{ fontSize: '1rem', color: 'var(--text-secondary)', lineHeight: 1.8, marginBottom: '24px' }}>
              The <strong>CLSU Smart Indoor Farm</strong> is an advanced research and technological demonstrator developed to optimize closed-environment crop cultivation. By combining Internet of Things (IoT) sensors, automated nutrient fertigation, auxiliary air exchange, and solar renewable energy, the facility ensures maximum photosynthetic yield while conserving water and macronutrients.
            </p>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '16px' }}>
              <div style={{ padding: '18px', borderRadius: '14px', background: 'var(--glass-bg-subtle)', border: '1px solid var(--glass-border)' }}>
                <FaMicrochip style={{ color: 'var(--accent-primary)', fontSize: '24px', marginBottom: '10px' }} />
                <h4 style={{ fontSize: '1rem', fontWeight: 700, color: 'var(--text-primary)' }}>Active IoT Telemetry</h4>
                <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', marginTop: '4px', lineHeight: 1.5 }}>
                  Continuous sensor stream of temperature, humidity, lux, and soil NPK values.
                </p>
              </div>

              <div style={{ padding: '18px', borderRadius: '14px', background: 'var(--glass-bg-subtle)', border: '1px solid var(--glass-border)' }}>
                <FaSeedling style={{ color: 'var(--accent-secondary)', fontSize: '24px', marginBottom: '10px' }} />
                <h4 style={{ fontSize: '1rem', fontWeight: 700, color: 'var(--text-primary)' }}>Closed Hydroponics</h4>
                <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', marginTop: '4px', lineHeight: 1.5 }}>
                  Automated fertigation dosing pumps synchronized via cloud schedules.
                </p>
              </div>

              <div style={{ padding: '18px', borderRadius: '14px', background: 'var(--glass-bg-subtle)', border: '1px solid var(--glass-border)' }}>
                <FaAward style={{ color: '#3b82f6', fontSize: '24px', marginBottom: '10px' }} />
                <h4 style={{ fontSize: '1rem', fontWeight: 700, color: 'var(--text-primary)' }}>CLSU Innovation</h4>
                <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', marginTop: '4px', lineHeight: 1.5 }}>
                  Advancing Philippine high-value crop farming techniques and sustainability.
                </p>
              </div>
            </div>
          </GlassPanel>
        </MotionWrapper>

        {/* ── Multidisciplinary Research Team ── */}
        <MotionWrapper direction="up" delay={0.15}>
          <div className="section-header" style={{ marginBottom: '20px' }}>
            <div className="section-bullet" />
            <div>
              <h2 style={{ fontSize: '1.4rem', fontWeight: 800, color: 'var(--text-primary)' }}>
                Multidisciplinary Project Team
              </h2>
              <p style={{ fontSize: '0.88rem', color: 'var(--text-muted)' }}>
                Faculty researchers, engineers, and technical assistants driving innovation
              </p>
            </div>
          </div>

          <div
            style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))',
              gap: '24px',
            }}
          >
            {teamMembers.map((member, idx) => (
              <GlassPanel
                key={idx}
                style={{
                  padding: '24px',
                  borderRadius: '20px',
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  textAlign: 'center',
                  position: 'relative',
                  overflow: 'hidden',
                  transition: 'transform var(--dur-short) var(--ease-out), box-shadow var(--dur-short) var(--ease-out)',
                }}
              >
                {/* Avatar */}
                <div
                  style={{
                    position: 'relative',
                    width: '100px',
                    height: '100px',
                    borderRadius: '50%',
                    overflow: 'hidden',
                    marginBottom: '16px',
                    border: '3px solid var(--accent-primary)',
                    boxShadow: '0 4px 16px var(--accent-primary-glow)',
                    background: 'var(--glass-bg-subtle)',
                  }}
                >
                  <Image
                    src={member.image}
                    alt={member.name}
                    fill
                    sizes="100px"
                    style={{ objectFit: 'cover' }}
                  />
                </div>

                {/* Info */}
                <h3 style={{ fontSize: '1.1rem', fontWeight: 800, color: 'var(--text-primary)', marginBottom: '4px' }}>
                  {member.name}
                </h3>
                <p style={{ fontSize: '0.82rem', fontWeight: 600, color: 'var(--accent-primary)', marginBottom: '12px' }}>
                  {member.role}
                </p>

                {/* Expertise Badges */}
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '6px', justifyContent: 'center', marginBottom: '14px' }}>
                  {member.expertise.map((exp, expIdx) => (
                    <span
                      key={expIdx}
                      style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '5px',
                        padding: '4px 10px',
                        borderRadius: '999px',
                        fontSize: '0.75rem',
                        fontWeight: 600,
                        background: 'rgba(0, 102, 0, 0.08)',
                        color: 'var(--accent-primary)',
                        border: '1px solid rgba(0, 102, 0, 0.15)',
                      }}
                    >
                      {exp.icon}
                      {exp.label}
                    </span>
                  ))}
                </div>

                {/* Bio */}
                <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', lineHeight: 1.6, marginTop: 'auto' }}>
                  {member.bio}
                </p>
              </GlassPanel>
            ))}
          </div>
        </MotionWrapper>
      </div>
    </>
  );
}
