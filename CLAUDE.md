> Agent kit: c:\AGProjects\.agent\
> Inherits: c:\AGProjects\CLAUDE.md

# Smart Farm Dashboard

Modern, glassmorphic rebuild of the CLSU smart indoor farming web dashboard. 

## Stack
- PHP 8 (Backend API, rendering)
- Vanilla HTML, JS
- Vanilla CSS (No Bootstrap)
- MySQL

## Key Directives
- **Design:** Build a stunning, UI-focused interface with glassmorphism, glowing accents, and smooth transitions. Give it a WOW factor.
- **Controls Architecture:** The Fertigation and Auxiliary Fan controls operate via server-side polling and MySQL state tables `fertigation_state` and `fan_state`, NOT via direct ESP32 LAN IP calls. 
- **Agents:** Primary agents applied here are `@frontend-specialist` (UI/UX) and `@backend-specialist` (PHP APIs).
