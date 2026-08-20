'use client';

import React from 'react';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ChartOptions,
} from 'chart.js';
import { Bar } from 'react-chartjs-2';
import { useTheme } from '@/context/ThemeContext';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

interface NPKChartProps {
  nitrogen: number;
  phosphorus: number;
  potassium: number;
  height?: number;
}

export const NPKChart: React.FC<NPKChartProps> = ({
  nitrogen,
  phosphorus,
  potassium,
  height = 280,
}) => {
  const { theme } = useTheme();
  const isDark = theme === 'dark';

  const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.05)';
  const textColor = isDark ? '#94a3ab' : '#64748b';

  const chartData = {
    labels: ['Nitrogen (N)', 'Phosphorus (P)', 'Potassium (K)'],
    datasets: [
      {
        label: 'Current Soil Concentration (mg/kg)',
        data: [nitrogen, phosphorus, potassium],
        backgroundColor: [
          'rgba(59, 130, 246, 0.75)', // Blue for Nitrogen
          'rgba(242, 169, 0, 0.75)',  // Yellow/Gold for Phosphorus
          'rgba(16, 185, 129, 0.75)', // Green for Potassium
        ],
        borderColor: [
          '#3b82f6',
          '#F2A900',
          '#10b981',
        ],
        borderWidth: 2,
        borderRadius: 8,
      },
    ],
  };

  const options: ChartOptions<'bar'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false,
      },
      tooltip: {
        backgroundColor: isDark ? 'rgba(18, 25, 32, 0.92)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: isDark ? '#ffffff' : '#1a232c',
        bodyColor: isDark ? '#cbd5e1' : '#4a5568',
        borderColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.08)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 10,
        callbacks: {
          label: (context) => ` Value: ${context.parsed.y} mg/kg (ppm)`,
        },
      },
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: {
          color: textColor,
          font: { family: 'Outfit, sans-serif', size: 12, weight: 600 },
        },
      },
      y: {
        grid: { color: gridColor },
        ticks: {
          color: textColor,
          font: { family: 'Inter, sans-serif', size: 11 },
        },
      },
    },
  };

  return (
    <div style={{ height: `${height}px`, width: '100%', position: 'relative' }}>
      <Bar data={chartData} options={options} />
    </div>
  );
};

export default NPKChart;
