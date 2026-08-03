# ASAAS STUDIO - Premium Digital Agency Platform

## Quick Setup

1. **Database**: Import `database/schema.sql` into MySQL
2. **Config**: Edit `config/database.php` with your DB credentials
3. **Serve**: Point Apache to the project root
4. **Access**: Visit `http://localhost/sas_studio/`

### Default Credentials

- **Admin**: admin@asaas-studio.tech / admin123
- **User**: user@asaas-studio.tech / admin123

## Structure

| Directory | Purpose |
|-----------|---------|
| `public/` | Website pages (Home, Services, Portfolio, Blog, About, Contact) |
| `auth/` | Login, Register, Forgot Password |
| `user/` | User dashboard (Profile, Messages, Notifications, Ratings, Settings) |
| `admin/` | Full admin panel (Analytics, Users, Posts, Portfolio, Media, Messages, Activity Logs, Settings) |
| `api/` | JSON API endpoints |
| `config/` | Database, app config, core functions |
| `assets/` | CSS (4 files), JS (4 files) |
| `includes/` | Header, Footer, Admin Header |

## Features

- Modern SaaS UI with animations
- User auth with rate limiting & bcrypt
- User dashboard with messaging & notifications
- Full admin panel with analytics charts
- Blog & Portfolio CMS
- Media library with drag & drop
- SIEM-style activity logs
- 1-5 star rating system
- Responsive mobile-first design
