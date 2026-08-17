# Course Management REST API

A full-featured RESTful API built with Laravel for managing online courses, lessons, enrollments, and reviews — built as a hands-on learning project covering real-world backend development patterns.

## Features

- **Authentication** — Token-based auth using Laravel Sanctum (register, login, logout)
- **Course Management** — Full CRUD with ownership-based authorization
- **Lessons (Nested Resource)** — Course → Lessons relationship with drag-and-drop style reordering
- **Enrollment System** — Many-to-Many relationship between Users and Courses with pivot data
- **Ratings & Reviews** — Enrollment-gated reviews with computed average rating per course
- **Search, Filter, Sort & Pagination** — Query courses by keyword, price range, publish status, and custom sort order
- **Thumbnail Upload** — Image upload and storage for course thumbnails with old-file cleanup on update
- **Consistent JSON Responses** — Uniform `{ success, message, data }` structure across all endpoints
- **Custom Error Handling** — Clean JSON responses for 404 and other exceptions instead of default HTML error pages

## Tech Stack

- **Framework:** Laravel 11+
- **Database:** MySQL
- **Authentication:** Laravel Sanctum
- **API Testing:** Postman

## Core Concepts Covered

- RESTful resource controllers & route model binding
- One-to-Many and Many-to-Many Eloquent relationships
- Nested API resources & nested routing
- API Resources for clean response transformation
- Database transactions for atomic multi-row updates
- File uploads via `multipart/form-data` and Laravel's method-spoofing (`_method`)
- Query scoping for search/filter/sort
- Basic authorization checks and Laravel Policies

## API Overview

| Resource | Endpoints |
|---|---|
| Auth | `POST /api/register`, `POST /api/login`, `POST /api/logout` |
| Courses | `GET/POST /api/courses`, `GET/PUT/PATCH/DELETE /api/courses/{course}` |
| Lessons | `GET/POST /api/courses/{course}/lessons`, `.../{lesson}`, `PATCH .../lessons/reorder` |
| Enrollment | `POST/DELETE /api/courses/{course}/enroll`, `GET /api/my-courses`, `GET /api/courses/{course}/students` |
| Reviews | `GET/POST/PATCH/DELETE /api/courses/{course}/reviews` |

All endpoints (except register/login) require a Bearer token from Sanctum.

## Setup

```bash
git clone <repo-url>
cd course-api
composer install
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database credentials, then:

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

## Status

🚧 Actively being built as a learning project — new features and refinements added incrementally.
