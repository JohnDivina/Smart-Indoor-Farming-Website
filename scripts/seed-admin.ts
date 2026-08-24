import prisma from '../src/lib/prisma';

async function main() {
  const adminEmail = 'johnrey_divina@clsu.edu.ph';
  
  const user = await prisma.user.findFirst({
    where: { email: { equals: adminEmail, mode: 'insensitive' } },
  });

  if (user) {
    await prisma.user.update({
      where: { id: user.id },
      data: {
        role: 'admin',
        approved: true,
        emailVerified: 1,
      },
    });
    console.log(`Updated user ${user.username} (${user.email}) as Master Administrator (approved=true, role=admin)`);
  } else {
    console.log(`User ${adminEmail} not found in DB yet. Will be auto-assigned admin role upon initial Google / credentials sign in.`);
  }

  // Also approve existing demo admin users if any
  const adminUser = await prisma.user.findFirst({
    where: { username: { equals: 'admin', mode: 'insensitive' } },
  });
  if (adminUser) {
    await prisma.user.update({
      where: { id: adminUser.id },
      data: {
        role: 'admin',
        approved: true,
      },
    });
    console.log(`Updated user 'admin' as Master Administrator`);
  }
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
