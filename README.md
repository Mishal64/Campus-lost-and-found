# Campus Lost & Found System

## Features
- User registration and login
- Post lost items
- Post found items
- Upload item images
- Claim found items
- Approve/reject claims by item owner
- Item status tracking
- Admin monitoring and item deletion

## Tech Stack
- PHP
- MySQL
- HTML/CSS
- JavaScript
- XAMPP

## Project Structure
- `admin/` admin pages
- `assets/` CSS and JS
- `config/` database connection and schema
- `student/` student feature pages
- `tests/` smoke checks for key flows
- `uploads/` uploaded images

## Setup
1. Start Apache and MySQL in XAMPP.
2. Create/import DB using `config/database-schema.sql`.
3. Verify `config/database.php` credentials.
4. Open `http://localhost/campus_lost_found`.
