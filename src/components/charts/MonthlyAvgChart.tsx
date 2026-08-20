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

interface MonthlyAvgDataset {
  label: string;
  data: number[];
  color: string;
  unit?: string;
}

interface MonthlyAvgChartProps {
  months: string[];
  datasets: MonthlyAvgDataset[];
  height?: number;
}

export const MonthlyAvgChart: React.FC<MonthlyAvgChartProps> = ({
  months,
  datasets,
  height = 320,
}) => {
  const { theme } = useTheme();
  const isDark = theme === 'dark';

  const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.05)';
  const textColor = isDark ? '#94a3ab' : '#64748b';

  const chartData = {
    labels: months,
    datasets: datasets.map((ds) => ({
      label: ds.label,
      data: ds.data,
      backgroundColor: `${ds.color}cc`,
      borderColor: ds.color,
      borderWidth: 1.5,
      borderRadius: 6,
    })),
  };

  const options: ChartOptions<'bar'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
        labels: {
          color: textColor,
          font: { family: 'Outfit, sans-serif', size: 12, weight: 600 },
          padding: 16,
        },
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
          label: (context) => {
            const ds = datasets[context.datasetIndex];
            return ` ${context.dataset.label}: ${context.parsed.y} ${ds?.unit || ''}`;
          },
        },
      },
    },
    scales: {
      x: {
        grid: { color: gridColor },
        ticks: { color: textColor },
      },
      y: {
        grid: { color: gridColor },
        ticks: { color: textColor },
      },
    },
  };

  return (
    <div style={{ height: `${height}px`, width: '100%', position: 'relative' }}>
      <Bar data={chartData} options={options} />
    </div>
  );
};

export default MonthlyAvgChart;
