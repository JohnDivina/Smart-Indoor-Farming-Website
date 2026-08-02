# Active User Presence Tracking - Integration Guide

## Overview

This system tracks active users in real-time using polling (no WebSockets) and displays them in the sidebar with initials and hover tooltips.

---

## ✅ Setup Complete

The following files have been created:

### Database
- ✅ `active_users` table updated with `current_page` column

### Backend API
- ✅ `api/heartbeat.php` - Updates user activity every 15 seconds
- ✅ `api/get_active_users.php` - Returns list of active users

### Frontend JavaScript
- ✅ `js/user-presence.js` - Heartbeat with idle detection
- ✅ `js/active-users-display.js` - Sidebar display and polling

### Styles
- ✅ `css/active-users.css` - Avatar and tooltip styles

---

## 🔧 Integration Steps

### Step 1: Include Scripts in Your Pages

Add these lines to the `<head>` section of your dashboard pages (e.g., `index.php`, `temp-humidity-data-insight.php`, etc.):

```html
<!-- Active Users Presence Tracking -->
<link rel="stylesheet" href="css/active-users.css">
<script src="js/user-presence.js"></script>
<script src="js/active-users-display.js"></script>
```

**Important:** These scripts should be included on ALL authenticated pages where you want to track user presence.

---

### Step 2: Verify Sidebar Structure

The active users display will automatically insert itself into your sidebar. It looks for:
- An element with class `sidebar` or containing "sidebar" in the class name
- Preferably after the "About Us" link

If your sidebar has a different structure, you may need to adjust the `createSidebarContainer()` function in `js/active-users-display.js`.

---

## 📋 How It Works

### Heartbeat System
1. **Every 15 seconds**: Sends POST request to `api/heartbeat.php` with current page name
2. **Idle Detection**: Stops heartbeat after 60 seconds of inactivity
3. **Activity Resume**: Automatically resumes when user moves mouse or types
4. **Page Unload**: Stops heartbeat when user closes tab

### Active Users Display
1. **Every 15 seconds**: Fetches active users from `api/get_active_users.php`
2. **30-Second Window**: Only shows users active within last 30 seconds
3. **Smooth Animations**: Fade in/out when users appear/disappear
4. **Tooltips**: Show on hover with full name and current page

---

## 🎨 Visual Example

**Sidebar Display:**
```
┌─────────────────┐
│ Dashboard       │
│ About Us        │
├─────────────────┤
│ ACTIVE USERS    │
│ [JD] [MR] [AL]  │  ← Circular avatars with initials
└─────────────────┘
```

**Hover Tooltip:**
```
[JD] ──→  ┌─────────────────────┐
          │ Juan Dela Cruz      │
          │ Viewing: Dashboard  │
          └─────────────────────┘
```

---

## 🧪 Testing

### Test 1: Heartbeat
1. Open browser DevTools (F12) → Network tab
2. Navigate to dashboard
3. Filter by "heartbeat"
4. You should see POST requests every 15 seconds

### Test 2: Idle Detection
1. Stay on dashboard page
2. Don't move mouse or type for 60 seconds
3. Heartbeat requests should stop
4. Move mouse → heartbeat should resume

### Test 3: Active Users Display
1. Open dashboard in two different browsers (or incognito)
2. Log in as different users
3. Both users should see each other in the sidebar
4. Hover over avatar to see tooltip

### Test 4: User Disappears
1. Close one browser tab
2. Within 30 seconds, that user should disappear from the other user's sidebar

---

## 🔒 Security Features

✅ **Session Validation**: All API endpoints check for valid session
✅ **No User IDs Exposed**: Frontend only receives names and initials
✅ **SQL Injection Prevention**: All queries use prepared statements
✅ **XSS Protection**: User input is escaped before display
✅ **Activity Timeout**: Users automatically removed after 30 seconds

---

## ⚙️ Configuration

### Adjust Timing

Edit `js/user-presence.js`:
```javascript
const HEARTBEAT_INTERVAL = 15000; // Change heartbeat frequency
const IDLE_TIMEOUT = 60000;       // Change idle timeout
```

Edit `js/active-users-display.js`:
```javascript
const POLL_INTERVAL = 15000; // Change polling frequency
```

Edit `api/get_active_users.php`:
```sql
WHERE au.last_activity >= DATE_SUB(NOW(), INTERVAL 30 SECOND)
-- Change 30 to different number of seconds
```

### Customize Appearance

Edit `css/active-users.css`:
- Avatar size: `.user-avatar { width: 36px; height: 36px; }`
- Colors: `background: linear-gradient(135deg, #009639, #87b237);`
- Tooltip position and style

---

## 🐛 Troubleshooting

### Users not appearing in sidebar

**Check:**
1. Are both users logged in?
2. Are the scripts included in the page?
3. Check browser console for errors
4. Check Network tab for API calls
5. Verify `active_users` table has records:
   ```sql
   SELECT * FROM active_users;
   ```

### Heartbeat not sending

**Check:**
1. Browser console for errors
2. Verify session is active
3. Check if user is idle (move mouse to resume)
4. Verify API endpoint path is correct

### Tooltip not showing

**Check:**
1. CSS file is loaded
2. Hover over avatar (not clicking)
3. Check z-index conflicts with other elements

---

## 📊 Database Queries

### View Active Users
```sql
SELECT u.username, au.last_activity, au.current_page 
FROM active_users au 
JOIN users u ON au.user_id = u.id 
WHERE au.last_activity >= DATE_SUB(NOW(), INTERVAL 30 SECOND);
```

### Clear Stale Records
```sql
DELETE FROM active_users 
WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE);
```

### View All Activity
```sql
SELECT u.username, au.* 
FROM active_users au 
JOIN users u ON au.user_id = u.id 
ORDER BY au.last_activity DESC;
```

---

## 🚀 Next Steps

1. ✅ Run database migration: `http://localhost/smartfarm2/migrate_active_users.php`
2. ✅ Add scripts to your dashboard pages (see Step 1 above)
3. ✅ Test with multiple users
4. ✅ Customize appearance if needed
5. ✅ Monitor performance and adjust timing if necessary

---

## 📝 Notes

- **No WebSockets**: Uses simple HTTP polling for maximum compatibility
- **Low Overhead**: 15-second intervals are efficient and responsive
- **Automatic Cleanup**: Inactive users removed automatically
- **Mobile Friendly**: Responsive design works on all screen sizes
- **Graceful Degradation**: Works even if some users have old browsers

---

## Summary

The active user presence tracking system is now ready to use. Simply include the CSS and JavaScript files in your dashboard pages, and users will automatically appear in the sidebar when they're active!
