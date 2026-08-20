import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log('🌱 Seeding realistic precision telemetry data for CLSU Smart Farm...');

  const now = new Date();

  // Generate 24 hourly readings for today and past 2 days
  const tempHumidReadings = [];
  const lightReadings = [];
  const npkReadings = [];

  for (let dayOffset = 2; dayOffset >= 0; dayOffset--) {
    for (let hour = 0; hour < 24; hour++) {
      const readingTime = new Date(now);
      readingTime.setDate(readingTime.getDate() - dayOffset);
      readingTime.setHours(hour, Math.floor(Math.random() * 60), 0, 0);

      // Temperature curve: cooler at night (23-25°C), warmer at midday (29-33°C)
      const baseTemp = 24 + Math.sin(((hour - 6) / 12) * Math.PI) * 7;
      const temp = Math.max(22, Math.min(34, baseTemp + (Math.random() * 1.5 - 0.75)));

      // Humidity curve: higher at night (75-85%), lower at midday (55-65%)
      const baseHumid = 80 - Math.sin(((hour - 6) / 12) * Math.PI) * 20;
      const humid = Math.max(50, Math.min(90, baseHumid + (Math.random() * 3 - 1.5)));

      tempHumidReadings.push({
        temperature: parseFloat(temp.toFixed(1)),
        humidity: parseFloat(humid.toFixed(1)),
        timestamp: readingTime,
      });

      // Light Intensity curve: 0 at night (before 6am and after 6pm), up to 1800-2200 lux around midday
      let lux = 0;
      if (hour >= 6 && hour <= 18) {
        const peak = Math.sin(((hour - 6) / 12) * Math.PI) * 2000;
        lux = Math.max(0, peak + (Math.random() * 200 - 100));
      }
      lightReadings.push({
        lux: parseFloat(lux.toFixed(0)),
        timestamp: readingTime,
      });

      // NPK Soil Nutrients: periodic readings
      if (hour % 2 === 0) {
        const n = 45 + Math.sin(hour / 4) * 8 + (Math.random() * 4 - 2);
        const p = 30 + Math.cos(hour / 4) * 6 + (Math.random() * 3 - 1.5);
        const k = 55 + Math.sin(hour / 3) * 7 + (Math.random() * 4 - 2);

        npkReadings.push({
          nitrogen: parseFloat(n.toFixed(1)),
          phosphorus: parseFloat(p.toFixed(1)),
          potassium: parseFloat(k.toFixed(1)),
          timestamp: readingTime,
        });
      }
    }
  }

  // Insert TempHumidity
  console.log(`Inserting ${tempHumidReadings.length} temperature/humidity records...`);
  for (const r of tempHumidReadings) {
    await prisma.tempHumiditySensor.create({ data: r });
  }

  // Insert Light Intensity
  console.log(`Inserting ${lightReadings.length} light intensity records...`);
  for (const r of lightReadings) {
    await prisma.lightIntensitySensor.create({ data: r });
  }

  // Insert NPK
  console.log(`Inserting ${npkReadings.length} NPK soil nutrient records...`);
  for (const r of npkReadings) {
    await prisma.npkSensor.create({ data: r });
  }

  // Upsert Status rows
  await prisma.lightStatus.upsert({
    where: { id: 1 },
    update: { status: 'connected', updatedAt: new Date() },
    create: { id: 1, status: 'connected', updatedAt: new Date() },
  });

  await prisma.npkStatus.upsert({
    where: { id: 1 },
    update: { status: 'connected', updatedAt: new Date() },
    create: { id: 1, status: 'connected', updatedAt: new Date() },
  });

  // Upsert Controls
  await prisma.fanControl.upsert({
    where: { id: 1 },
    update: {
      mode: 'manual',
      desiredFanState: 'on',
      espFanState: 'on',
      lastHeartbeat: new Date(),
      lastUpdated: new Date(),
    },
    create: {
      id: 1,
      mode: 'manual',
      desiredFanState: 'on',
      espFanState: 'on',
      lastHeartbeat: new Date(),
      lastUpdated: new Date(),
    },
  });

  await prisma.fertigationControl.upsert({
    where: { id: 1 },
    update: {
      mode: 'manual',
      desiredPumpState: 'off',
      espPumpState: 'off',
      lastHeartbeat: new Date(),
      lastUpdated: new Date(),
    },
    create: {
      id: 1,
      mode: 'manual',
      desiredPumpState: 'off',
      espPumpState: 'off',
      lastHeartbeat: new Date(),
      lastUpdated: new Date(),
    },
  });

  await prisma.solarPanelControl.upsert({
    where: { id: 1 },
    update: {
      mode: 'manual',
      desiredState: 'on',
      actualState: 'on',
      lastHeartbeat: new Date(),
      lastUpdated: new Date(),
    },
    create: {
      id: 1,
      mode: 'manual',
      desiredState: 'on',
      actualState: 'on',
      lastHeartbeat: new Date(),
      lastUpdated: new Date(),
    },
  });

  await prisma.solarPanelStatus.create({
    data: {
      voltage: 13.2,
      current: 2.1,
      power: 27.72,
      timestamp: new Date(),
    },
  });

  console.log('✅ Seeding completed successfully!');
}

main()
  .catch((e) => {
    console.error('Seeding error:', e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
