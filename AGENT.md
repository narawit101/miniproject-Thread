# AI Agent Developer Instructions (`agent.md`)

Welcome, fellow AI agents! This document serves as a guide for development and refactoring inside this project. Adhering to these rules ensures correct path resolution, robustness, and clean directory structure.

---

## 📁 Workspace Folder Layout

This project utilizes a **Central Router** architecture. All browser traffic is directed to `index.php?page=<page_name>` at the root.

Directories are structured as follows:
1. **Root (`index.php`):** The single entry point script.
2. **Configuration (`config/`):** Database connections and JSON databases.
3. **Common UI (`layouts/`):** Shared templates like headers, footers, sliders, and controllers.
4. **Static Assets (`assets/`):** Client-side scripts (`assets/js/`) and styles.
5. **Views (`src/`):** Grouped subdirectories containing PHP views and endpoints:
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
- **JSON Handler:** When reading/writing to `config/posts.json`, ensure the file is initialized as an empty array `[]` if missing.
