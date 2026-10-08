# School Management System

Laravel (API, MVC) + React (MVC-style) + MySQL.

## Stack

| Layer | Tech | Structure |
|-------|------|-----------|
| Backend | Laravel 11, Sanctum | `Models`, `Http/Controllers`, `Http/Requests`, `Http/Resources`, `database/migrations` |
| Frontend | React 19, Vite, React Router | `src/models`, `src/controllers`, `src/views` |
| Database | MySQL (XAMPP) | Database name: `school_management` |

## Prerequisites

- PHP **8.2+** (Laravel 11; upgrade XAMPP PHP if you still have 7.x), Composer
- MySQL (XAMPP)
- Node.js 20+ and npm

## Backend setup

1. Create the database in phpMyAdmin or MySQL:

   ```sql
   CREATE DATABASE school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Configure `backend/.env` (MySQL credentials are pre-set for typical XAMPP: user `root`, empty password).

3. From `backend/`:

   ```bash
   composer install
   php artisan migrate --seed
   php artisan serve
   ```

   API base URL: `http://localhost:8000/api/v1`

**Demo login:** `admin@school.local` / `password`

## Frontend setup

From `frontend/`:

```bash
npm install
npm run dev
```

App URL: `http://localhost:5173`

Copy `frontend/.env.example` to `frontend/.env` if you change the API URL.

## API overview

- `POST /auth/login`, `POST /auth/register`
- `GET /dashboard` (stats)
- CRUD: `/teachers`, `/students`, `/classes`, `/subjects` (Bearer token required)
- Attendance: `GET /attendance/sheet`, `POST /attendance/bulk`, `GET /attendance/summary`, `GET /attendance`
- Exams & grades: CRUD `/exams`, `GET /exams/{id}/sheet`, `POST /exams/{id}/grades/bulk`, `GET /exams/{id}/summary`
- Timetable: `GET /timetable/grid`, `POST /timetable/bulk`, `GET /timetable`
- Parents: CRUD `/parents` (link students, optional portal login)
- Fees: CRUD `/fee-types`, `/student-fees`, payments, bulk class assign
- Announcements: CRUD `/announcements`, feed `/announcements/feed`
- Parent portal: `/parent/overview`, `/parent/fees`, `/parent/announcements` (role: parent)

**Demo logins** (password: `password`):

| Role | Email |
|------|--------|
| Admin | `admin@school.local` |
| Teacher | `teacher@school.local` |
| Staff | `staff@school.local` |
| Parent | `parent@school.local` |

**Roles:** Admin (full access), Teacher (attendance + exams/grades for their classes), Staff (fees + parents + notices), Parent (portal).
Permissions are defined in `backend/config/permissions.php`.

## Production

See [DEPLOYMENT.md](./DEPLOYMENT.md) for HTTPS, queues, frontend build, storage link, and backups.

## API tests (demo / regression)

From `backend/` (uses in-memory SQLite; seeds demo data):

```bash
php artisan test --filter=ApiSchoolManagementTest
```
