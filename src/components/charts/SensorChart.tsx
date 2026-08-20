'use client';

import React from 'react';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  ChartOptions,
} from 'chart.js';
import { Line } from 'react-chartjs-2';
import { useTheme } from '@/context/ThemeContext';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

interface DatasetConfig {
  label: string;
  data: number[];
  color: string;
  fill?: boolean;
  unit?: string;
}

interface SensorChartProps {
  labels: string[];
  datasets: DatasetConfig[];
  height?: number;
  yAxisLabel?: string;
}

export const SensorChart: React.FC<SensorChartProps> = ({
  labels,
  datasets,
  height = 320,
  yAxisLabel,
}) => {
  const { theme } = useTheme();
  const isDark = theme === 'dark';

  const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.05)';
  const textColor = isDark ? '#94a3ab' : '#64748b';

  const chartData = {
    labels,
    datasets: datasets.map((ds) => ({
      label: ds.label,
      data: ds.data,
      borderColor: ds.color,
      backgroundColor: (context: any) => {
        const chart = context.chart;
        const { ctx, chartArea } = chart;
        if (!chartArea) return 'transparent';
        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        gradient.addColorStop(0, `${ds.color}33`); // 20% opacity
        gradient.addColorStop(1, `${ds.color}00`); // 0% opacity
        return gradient;
      },
      fill: ds.fill ?? true,
      tension: 0.35,
      pointRadius: labels.length > 50 ? 0 : 3,
      pointHoverRadius: 6,
      pointBackgroundColor: ds.color,
      borderWidth: 2.5,
    })),
  };

  const options: ChartOptions<'line'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
        labels: {
          color: textColor,
          font: { family: 'Outfit, sans-serif', size: 12, weight: 600 },
          usePointStyle: true,
          pointStyle: 'circle',
          boxWidth: 8,
          boxHeight: 8,
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
        boxPadding: 6,
        usePointStyle: true,
        cornerRadius: 10,
        titleFont: { family: 'Outfit, sans-serif', size: 13, weight: 700 },
        bodyFont: { family: 'Inter, sans-serif', size: 12 },
        callbacks: {
          label: (context) => {
            const label = context.dataset.label || '';
            const value = context.parsed.y;
            const ds = datasets[context.datasetIndex];
            return ` ${label}: ${value} ${ds?.unit || ''}`;
          },
        },
      },
    },
    scales: {
      x: {
        grid: { color: gridColor },
        ticks: {
          color: textColor,
          font: { family: 'Inter, sans-serif', size: 11 },
          maxRotation: 0,
          autoSkip: true,
          maxTicksLimit: 12,
        },
      },
      y: {
        grid: { color: gridColor },
        title: yAxisLabel ? { display: true, text: yAxisLabel, color: textColor } : undefined,
        ticks: {
          color: textColor,
          font: { family: 'Inter, sans-serif', size: 11 },
        },
      },
    },
    interaction: {
      mode: 'index',
      intersect: false,
    },
  };

  return (
    <div style={{ height: `${height}px`, width: '100%', position: 'relative' }}>
      <Line data={chartData} options={options} />
    </div>
  );
};

export default SensorChart;
