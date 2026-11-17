# MizahVerse

MizahVerse is a social humor-sharing web platform where users can create content, follow each other, interact through comments, and exchange private messages.

##  Features

- User registration and login system (authentication & session management)
- Follow / unfollow users
- Personalized content feed based on followed users
- Create, edit, and delete posts (CRUD operations)
- Comment system (add, edit, delete)
- Post voting system
- Private messaging between users
- User profile pages
- Search functionality
- Relational MySQL database structure

##  Technologies Used

- Backend: PHP
- Database: MySQL
- Frontend: HTML, CSS
- Tools: VS Code, Git

##  Learning Outcomes

- Implemented authentication and session management
- Designed relational database schema (users, follows, messages, posts, comments)
- Built user-based content filtering system
- Developed full-stack workflow for a social platform

##  How to Run

1. Clone the repository
2. Set up local server (XAMPP/WAMP)
3. Configure the database connection in `MizahVerse/db.php` for your local environment.
4. Restore the complete base schema before importing `MizahVerse/mesajlar_tablosu.sql` and `MizahVerse/takip_tablosu.sql`.
5. Serve `MizahVerse/` through localhost.

**Current setup limitation:** this repository contains only the messages and follows table scripts. The base schema (including users and posts) is missing; those two scripts alone are not a complete installation. Restore the original schema before attempting a fresh setup. Authentication, authorization and input handling need a separate review before a public deployment.
