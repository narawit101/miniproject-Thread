# AI Agent Developer Instructions (`AGENT.md`)

Welcome, fellow AI agents! This document serves as the primary coordinator for all development and refactoring inside this project.

> [!IMPORTANT]
> ### 🚨 Critical Workflow Rules
> 1. **Initial Step:** Always read [AGENT.md](file:///c:/D/thread/miniproject-Thread/AGENT.md) first! This file serves as the coordinator and navigator, guiding you on which files to read or update next.
> 2. **Important Logic/DB Changes:** You **MUST** update [CONTEXT.md](file:///c:/D/thread/miniproject-Thread/CONTEXT.md) immediately after making database schema modifications, core architecture changes, or significant logic updates.
> 3. **UI / Styling Tasks:** Always read [DESIGN.md](file:///c:/D/thread/miniproject-Thread/DESIGN.md) when building new pages, modifying UI components, or editing CSS stylesheets to maintain visual consistency.

---

## 🐳 Runtime Environment (Docker)

This project runs inside **Docker** — there is no XAMPP, Laragon, or any local server bundle.

| Container | Role | Key Detail |
|---|---|---|
| `dpi_nginx` | Web server | Port `8080` on host |
| `dpi_php` | PHP 8.2-FPM | Executes all `.php` scripts |
| `dpi_mysql` | MySQL 8.0 | Hostname is `mysql` inside Docker network |
| `dpi_phpmyadmin` | DB GUI | Port `8081` on host |

> ⚠️ **Critical:** The database host is `mysql` (Docker service name), **NOT** `localhost`.

### DB Configuration Rule
- **Never hardcode** database credentials in PHP files.
- Always read from environment variables using `getenv()`:
  ```php
  $host   = getenv('DB_HOST') ?: 'mysql';
  $dbname = getenv('DB_NAME') ?: 'dpi_db';
  $user   = getenv('DB_USER') ?: 'dpi_user';
  $pass   = getenv('DB_PASS') ?: '';
  ```
- Credentials are defined in `.env` at the project root and injected by Docker Compose.

---

## 📁 Workspace Folder Layout

This project utilizes a **Central Router** architecture. All browser traffic is directed to `index.php?page=<page_name>` at the root.

Directories are structured as follows:
1. **Root (`index.php`):** The single entry point script.
2. **Docker (`docker/`):** Container configuration — `docker/nginx/nginx.conf`, `docker/php/Dockerfile`, `docker/php/php.ini`.
3. **Configuration (`config/`):** Database connections and helper functions (e.g., `swal_helper.php`).
4. **Common UI (`layouts/`):** Shared templates like headers, footers, sliders, and controllers.
5. **Static Assets (`assets/`):** Client-side scripts (`assets/js/`) and styles.
6. **Views (`src/`):** Grouped subdirectories containing PHP views and endpoints:
   - `src/auth/` - Authentication logic.
   - `src/admin/` - Administrator dashboards and user management actions.
   - `src/user/` - Profile management scripts.
   - `src/posts/` - Thread posting, comments feed, likes actions.

---

## 🛡️ Security Standards

### 1. SQL Injection Prevention
- **Never** concatenate variables directly into SQL strings.
- **Always** use PDO Prepared Statements and pass variables as parameter arrays:
  ```php
  // CORRECT
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  
  // INCORRECT
  $stmt = $conn->query("SELECT * FROM users WHERE email = '$email'"); 
  ```

### 2. XSS (Cross-Site Scripting) Prevention
- Always wrap user-supplied variables in `htmlspecialchars()` when rendering to HTML:
  ```php
  <?= htmlspecialchars($post['content']) ?>
  ```

### 3. Authentication & Authorization
- Include `layouts/dataheader.php` on pages requiring session validation to verify login status.
- Admin validation must verify the user's role saved in the active session:
  ```php
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
      header('Location: index.php?page=login');
      exit();
  }
  ```

---

## 🔗 Path Resolution Guidelines

Because the entry point is always `index.php` running at the root directory, follow these path patterns:

1. **PHP File Inclusions (Server-side):**
   - Refer to layouts and configuration relative to the root directory, regardless of which file inside `src/` contains the code:
     ```php
     require_once 'config/server.php';
     include_once 'layouts/top_layouts.php';
     ```

2. **Client-side Links & Actions:**
   - Hyperlinks (`<a>` tags), Form Actions, and HTTP Header Redirects must route through the Central Router:
     - Page navigation: `href="index.php?page=homepage"`
     - Form submission: `action="index.php?page=login"`
     - Redirects: `header('Location: index.php?page=profile');`
   - Static assets load relative to root:
     - Scripts: `<script src="assets/js/script.js"></script>`
     - CSS styles: `<link rel="stylesheet" href="styles/layoutsstyle.css">`
     - Image pathing: `<img src="uploads/avatar.png">`

---

## 📝 Coding Standards
- **Preserve Existing Logic:** Keep user logic intact (multi-page layouts, database parameters) unless explicitly requested to rewrite database tables.
- **Announcements Table:** System announcements are stored in the MySQL `announcements` database table. Ensure you query the table using PDO prepared statements.
- **No Hardcoded Credentials:** Always use `getenv()` for DB connection values. Never write raw passwords in PHP files.
- **Docker Files:** When modifying Docker config (`docker-compose.yml`, `docker/nginx/nginx.conf`, `docker/php/Dockerfile`, `docker/php/php.ini`), verify changes do not break the PHP-FPM ↔ Nginx FastCGI bridge.
