import { z } from 'zod';

export const loginSchema = z.object({
  identifier: z.string().min(1, 'Username, email, or phone number is required'),
  password: z.string().min(1, 'Password is required'),
  totpCode: z.string().optional(),
});

export const registerSchema = z.object({
  username: z
    .string()
    .min(3, 'Username must be at least 3 characters')
    .max(50, 'Username cannot exceed 50 characters')
    .regex(/^[a-zA-Z0-9_-]+$/, 'Username can only contain letters, numbers, underscores, and hyphens'),
  email: z.string().email('Please enter a valid email address'),
  phonenumber: z
    .string()
    .min(7, 'Phone number must be at least 7 digits')
    .max(15, 'Phone number cannot exceed 15 digits')
    .regex(/^\+?[0-9]+$/, 'Phone number must contain digits only'),
  password: z
    .string()
    .min(6, 'Password must be at least 6 characters')
    .regex(/(?=.*[0-9!@#$%^&*(),.?":{}|<>])/, 'Password must contain at least one number or special symbol'),
  confirmPassword: z.string().min(1, 'Please confirm your password'),
}).refine((data) => data.password === data.confirmPassword, {
  message: 'Passwords do not match',
  path: ['confirmPassword'],
});

export const otpVerifySchema = z.object({
  userId: z.number().int().positive(),
  otp: z.string().length(6, 'OTP must be exactly 6 digits').regex(/^[0-9]{6}$/, 'OTP must contain numbers only'),
  reason: z.enum(['account_creation', 'password_reset', 'login', 'account_deletion']),
});

export const forgotPasswordSchema = z.object({
  identifier: z.string().min(1, 'Email or username is required'),
});

export const resetPasswordSchema = z.object({
  userId: z.number().int().positive(),
  otp: z.string().length(6),
  newPassword: z
    .string()
    .min(6, 'Password must be at least 6 characters')
    .regex(/(?=.*[0-9!@#$%^&*(),.?":{}|<>])/, 'Password must contain at least one number or special symbol'),
  confirmPassword: z.string(),
}).refine((data) => data.newPassword === data.confirmPassword, {
  message: 'Passwords do not match',
  path: ['confirmPassword'],
});

export const updateProfileSchema = z.object({
  username: z.string().min(3).max(50).optional(),
  email: z.string().email().optional(),
  phonenumber: z.string().regex(/^\+?[0-9]+$/).optional(),
});

export const changePasswordSchema = z.object({
  currentPassword: z.string().optional(),
  newPassword: z
    .string()
    .min(6, 'New password must be at least 6 characters')
    .regex(/(?=.*[0-9!@#$%^&*(),.?":{}|<>])/, 'Password must contain at least one number or special symbol'),
  confirmPassword: z.string(),
}).refine((data) => data.newPassword === data.confirmPassword, {
  message: 'Passwords do not match',
  path: ['confirmPassword'],
});

export const totpVerifySchema = z.object({
  code: z.string().length(6, 'Code must be 6 digits').regex(/^[0-9]{6}$/),
});

export const esp32HeartbeatSchema = z.object({
  device: z.enum(['fan', 'fertigation', 'solar', 'temp-humid', 'light', 'npk', 'all']),
});

export const fanControlSchema = z.object({
  action: z.enum(['on', 'off']),
});

export const fanScheduleSchema = z.object({
  scheduleTime: z.string().regex(/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/, 'Invalid time format (HH:MM)'),
  scheduleStopTime: z.string().regex(/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/, 'Invalid time format (HH:MM)').optional().nullable(),
  durationMinutes: z.number().int().min(1).max(720).optional(),
});

export const fertigationControlSchema = z.object({
  action: z.enum(['on', 'off']),
});

export const fertigationModeSchema = z.object({
  mode: z.enum(['manual', 'schedule', 'auto']),
});

export const solarCommandSchema = z.object({
  action: z.enum(['on', 'off']),
});

export const solarModeSchema = z.object({
  mode: z.enum(['manual', 'schedule', 'auto']),
});
