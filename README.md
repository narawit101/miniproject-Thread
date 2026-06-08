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
- **System Announcements:** Create and manage special administrator announcements that display in the sidebar widget (stored and fetched from a local JSON database).

---

## 📁 Reorganized Directory Structure

The project has been refactored into a **Central Router** architecture to keep the repository root clean:

```text
miniproject-Thread/
├── config/                # System configuration and database files
│   ├── server.php         # MySQL database connection settings via PDO
│   └── posts.json         # JSON database for admin announcements
├── layouts/               # Shared UI layouts and components
│   ├── dataheader.php     # Global session verification and user profile loader
│   ├── top_layouts.php    # Navigation bar and header structure
│   ├── category_slide.php # Category swiper slider layout
│   ├── con4.php           # Sidebar announcement widget component
│   └── bottom_layouts.php # Footer layout and closing HTML tags
├── assets/                # Static frontend assets
│   └── js/
│       └── script.js      # Client-side JavaScript for Swiper and navbar dropdown
├── src/                   # Structured PHP page scripts (View Modules)
│   ├── auth/              # login.php, logout.php, register.php
│   ├── admin/             # admin.php, manage_users.php, add_category.php, etc.
│   ├── user/              # profile.php, edit_profile.php, edit_password.php
│   └── posts/             # homepage.php, post.php, comments and likes actions
├── index.php              # Central Router (Single entry point)
├── README.md              # Project overview and installation guide (English)
├── agent.md               # AI developer guidelines and coding instructions (English)
└── context.md             # Business domain context, roles, and schema diagrams (English)
```

---

## 🛠️ Installation & Setup

### 📋 Prerequisites
1. Local web server bundle like **XAMPP**, **Laragon**, or **MAMP** running PHP 7.4+ and MySQL.
2. A database administration tool (e.g. **phpMyAdmin** or **HeidiSQL**).

### 🚀 Setup Steps
1. **Copy Files:**
   Clone or copy the `miniproject-Thread` project directory into the document root of your local web server (e.g., `C:/xampp/htdocs/` for XAMPP).

2. **Setup Database:**
   - Go to `http://localhost/phpmyadmin/`.
   - Create a new database named **`dpi_db`** (Collation: `utf8mb4_general_ci`).
   - Import the database schema tables (`users`, `categories`, `posts`, `comments`, `likes`).

3. **Check Connection Configuration:**
   - Verify server host, database name, username, and password credentials inside:
     [config/server.php](file:///c:/D/thread/miniproject-Thread/config/server.php)
     ```php
     $host = 'localhost';
     $dbname = 'dpi_db';
     $user = 'root';
     $pass = ''; // Default XAMPP password is empty
     ```

4. **Launch Application:**
   - Ensure Apache and MySQL are running on your server.
   - Navigate to `http://localhost/miniproject-Thread/index.php` in your browser.
   - Users registering with the email **`admin@gmail.com`** automatically receive administrator privileges.
