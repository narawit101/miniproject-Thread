# DPI Thread Forum - Web Discussion Board System

A multi-page web discussion board forum application built with PHP and MySQL (PDO). This platform allows users to register, log in, create discussion threads, write comments, like posts, and interact with the community. It also features a sidebar announcement widget for administrators managed via a local JSON database.

---

## 🌟 Key Features

### 👤 General User Features
- **User Authentication:** Safe registration and login validation with session management.
- **Category Slider:** Interactive Swiper slider allowing users to filter threads by category.
- **Discussion Threads:** Ability to create, view, edit, and delete text posts with optional image attachments.
- **Likes System:** Real-time thread like/unlike system utilizing AJAX (Fetch API).
- **Comments Section:** Leave replies on threads with support for text content and image uploads.
- **Profile Customization:** Modify profile settings including first name, last name, avatar, and password.

### 🔑 Administrator Features
- **User Management:** Access list of registered users to edit their profile information, toggle roles (User/Admin), or remove accounts.
- **Category Management:** Add new discussion categories, delete inactive categories, and update category icons.
- **System Announcements:** Create and manage special administrator announcements that display in the sidebar widget (stored and fetched from MySQL database).

---

## 📁 Directory Structure

The project utilizes a **Central Router** architecture. All traffic passes through `index.php`.

```text
miniproject-Thread/
├── docker/                    # Docker service configuration
│   ├── nginx/
│   │   └── nginx.conf         # Nginx virtual host config (FastCGI → PHP-FPM)
│   └── php/
│       ├── Dockerfile         # PHP 8.2-FPM with required extensions
│       └── php.ini            # PHP runtime settings (upload size, timezone, errors)
├── config/                    # Application configuration
│   ├── server.php             # MySQL database connection via PDO (reads from env vars)
│   └── swal_helper.php        # SweetAlert helper functions
├── layouts/                   # Shared UI layouts and components
│   ├── dataheader.php         # Global session verification and user profile loader
│   ├── top_layouts.php        # Navigation bar and header structure
│   ├── category_slide.php     # Category swiper slider layout
│   ├── con4.php               # Sidebar announcement widget component
│   └── bottom_layouts.php     # Footer layout and closing HTML tags
├── assets/                    # Static frontend assets
│   └── js/
│       └── script.js          # Client-side JavaScript for Swiper and navbar dropdown
├── src/                       # Structured PHP page scripts (View Modules)
│   ├── auth/                  # login.php, logout.php, register.php
│   ├── admin/                 # admin.php, manage_users.php, add_category.php, etc.
│   ├── user/                  # profile.php, edit_profile.php, edit_password.php
│   └── posts/                 # homepage.php, post.php, comments and likes actions
├── docker-compose.yml         # Docker Compose: Nginx + PHP-FPM + MySQL + phpMyAdmin
├── .env                       # Environment variables (DB credentials — not committed)
├── .env.example               # Template for .env file
├── db.sql                     # Database initialization script (auto-imported by Docker)
├── index.php                  # Central Router (Single entry point)
├── README.md                  # Project overview and setup guide
├── AGENT.md                   # AI developer guidelines and coding instructions
└── CONTEXT.md                 # Business domain context, roles, and schema diagrams
```

---

## 🐳 Installation & Setup (Docker)

> **Prerequisites:** Install [Docker Desktop](https://www.docker.com/products/docker-desktop/) before proceeding.

### 1. Clone the Repository
```bash
git clone <repository-url>
cd miniproject-Thread
```

### 2. Configure Environment Variables
Copy the example env file and fill in your credentials:
```bash
copy .env.example .env
```

Edit `.env`:
```env
DB_NAME=dpi_db
DB_USER=dpi_user
DB_PASS=your_password_here
DB_ROOT_PASS=your_root_password_here
```

### 3. Start All Services
```bash
# First time (build images + start containers)
docker compose up -d --build

# Subsequent runs
docker compose up -d
```

Docker will automatically:
- 🐘 Build the PHP 8.2-FPM container with all required extensions
- 🌐 Start Nginx on port `8080`
- 🗄️ Start MySQL and **auto-import `db.sql`** to initialize the database
- 🖥️ Start phpMyAdmin on port `8081`

### 4. Access the Application

| Service | URL |
|---|---|
| 🌐 Web Application | http://localhost:8080 |
| 🖥️ phpMyAdmin | http://localhost:8081 |

### 5. Default Admin Account
Login with the pre-seeded administrator account:
- **Email:** `admin@gmail.com`
- **Password:** `123456`

### 6. Stop Services
```bash
docker compose down

# Stop and remove database volume (full reset)
docker compose down -v
```

---

## ⚙️ Docker Services Overview

| Container | Image | Port | Role |
|---|---|---|---|
| `dpi_nginx` | `nginx:alpine` | `8080:80` | Web server, serves static files, proxy PHP |
| `dpi_php` | Custom PHP 8.2-FPM | internal | Executes PHP scripts |
| `dpi_mysql` | `mysql:8.0` | `3307:3306` | Relational database |
| `dpi_phpmyadmin` | `phpmyadmin:latest` | `8081:80` | Database GUI management |

---

## 🔑 Database Connection

The `config/server.php` connection reads credentials from environment variables set in `.env`:

```php
$host   = getenv('DB_HOST') ?: 'mysql';
$dbname = getenv('DB_NAME') ?: 'dpi_db';
$user   = getenv('DB_USER') ?: 'dpi_user';
$pass   = getenv('DB_PASS') ?: '';
```

> **Note:** The host name is `mysql` (the Docker service name), not `localhost`.

---

## 📖 Developer Documentation Guide

If you are developing or refactoring this project, please follow this documentation workflow:

> [!IMPORTANT]
> ### 🚨 Workflow & Documentation Rules
> 1. **[AGENT.md](file:///c:/D/thread/miniproject-Thread/AGENT.md) (Root Guide & Coordinator):** Read this first to understand directory layouts, coding rules, environment configurations, and security standards. It guides you on which files to read or update next.
> 2. **[CONTEXT.md](file:///c:/D/thread/miniproject-Thread/CONTEXT.md) (System Context):** Read to understand relational database schemas and logic flows. **You MUST update this file whenever you make important changes** to database structures or core backend logic.
> 3. **[DESIGN.md](file:///c:/D/thread/miniproject-Thread/DESIGN.md) (Design System):** **You MUST read this whenever building new pages, UI components, or editing CSS styles** to ensure compliance with the Forest Green theme.

