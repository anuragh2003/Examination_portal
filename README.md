# Examination Portal (Laravel)

A comprehensive Laravel-based online examination portal with student management, exam creation, screenshot-based proctoring, and automated scoring.

## 🔎 Project Overview

- **Framework**: Laravel 12.x with PHP 8.2+
- **Frontend**: Bootstrap + Vite for modern UI
- **Database**: MySQL with Eloquent ORM
- **Features**:
  - Student exam flow with screenshot proctoring
  - Admin dashboard for exam management
  - Auto-scoring for multiple choice questions
  - Descriptive answer approval system
  - Real-time exam monitoring

## 🚀 Quick Start

### Prerequisites
- **PHP 8.2+** (with extensions: pdo_mysql, mbstring, tokenizer, xml, ctype, json, bcmath)
- **Composer** for PHP dependencies
- **MySQL** database
- **Nginx** web server (for production-like setup)
- **XAMPP** (for PHP and MySQL on Windows)

### For New Users (First Time Setup)

1. **Clone the repository**
   ```bash
   git clone https://github.com/anuragh2003/Examination_portal.git Examination_portal
   cd Examination_portal
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   # Copy environment file
   cp .env.example .env  # Linux/Mac
   # OR
   copy .env.example .env  # Windows

   # Generate application key
   php artisan key:generate
   ```

4. **Database configuration**
   - Open `.env` file and update database settings:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=examination_portal
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Database setup**
   ```bash
   # Create database (if not exists)
   # Run migrations
   php artisan migrate

   # Optional: Seed with sample data
   php artisan db:seed
   ```

6. **Storage setup**
   ```bash
   # Create symlink for file uploads
   php artisan storage:link
   ```

7. **Web Server Setup (Nginx + PHP)**
   - **Install Nginx**: Download from [nginx.org](https://nginx.org/download/nginx-1.24.0.zip) and extract to `C:\nginx\nginx-1.24.0`
   - **Configure Nginx**: Copy the provided `nginx.conf` from the project or use the one in `C:\nginx\nginx-1.24.0\conf\nginx.conf`
     - Update `root` path to your project: `"C:/Users/YOUR_USERNAME/Examination_portal/public"`
     - Ensure SSL certificates are generated (self-signed for local dev)
   - **Start Services**:
     ```bash
     # Start PHP FastCGI (in one terminal)
     C:\xampp\php\php-cgi.exe -b 127.0.0.1:9000

     # Start Nginx (in another terminal)
     C:\nginx\nginx-1.24.0\nginx.exe -p C:\nginx\nginx-1.24.0 -c conf\nginx.conf
     ```
   - **Access URLs**:
     - HTTP: `http://172.31.254.116:8080` (redirects to HTTPS)
     - HTTPS: `https://172.31.254.116:8443` (accept self-signed certificate warning)

### For Existing Users (Updating Code)

If you already have the project and want to update to the latest version:

1. **Pull latest changes**
   ```bash
   git pull origin main
   ```

2. **Update dependencies**
   ```bash
   composer install
   npm install  # if package.json changed
   ```

3. **Database updates**
   ```bash
   # Run new migrations (safe to run multiple times)
   php artisan migrate

   # Clear caches
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

5. **Restart services**
   ```bash
   # Stop existing services
   taskkill /F /IM nginx.exe
   taskkill /F /IM php-cgi.exe

   # Restart PHP and Nginx as above
   ```

## ⚙️ Post-Configuration Steps

After configuring Nginx and starting the services, run these commands to ensure the project works properly:

### Initial Setup Commands (Run Once)
```bash
# 1. Install PHP dependencies
composer install

# 2. Copy and configure environment
copy .env.example .env
php artisan key:generate

# 3. Set up database (update .env first)
php artisan migrate
php artisan db:seed  # Optional: loads sample data

# 4. Create storage symlink
php artisan storage:link

# 5. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Regular Maintenance Commands
```bash
# After code updates
php artisan migrate  # Run new migrations
php artisan config:cache  # Cache config for performance

# Clear caches if issues occur
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Rebuild frontend assets
npm run build

# Check application health
php artisan tinker  # Interactive shell for testing
```

### Service Management
```bash
# Start services
C:\xampp\php\php-cgi.exe -b 127.0.0.1:9000  # PHP (keep running)
C:\nginx\nginx-1.24.0\nginx.exe -p C:\nginx\nginx-1.24.0 -c conf\nginx.conf  # Nginx

# Stop services
taskkill /F /IM nginx.exe
taskkill /F /IM php-cgi.exe

# Reload Nginx config (after changes)
C:\nginx\nginx-1.24.0\nginx.exe -s reload -p C:\nginx\nginx-1.24.0 -c conf\nginx.conf
```

### Testing the Setup
```bash
# Test HTTP redirect
curl -I http://172.31.254.116:8080

# Test HTTPS (ignore cert warning)
curl -k -I https://172.31.254.116:8443

# Run application tests
./vendor/bin/phpunit
```

## 📁 Project Structure

```
app/
├── Http/Controllers/     # Request handlers
│   ├── ExamController.php    # Exam management
│   ├── StudentController.php # Student operations
│   └── UserController.php    # Authentication
├── Models/              # Database models
│   ├── Exam.php
│   ├── Question.php
│   ├── Student.php
│   └── ProctorScreenshot.php
└── Services/            # Business logic
    └── QuestionSelector.php

resources/views/         # Blade templates
database/migrations/     # Database schema
routes/                  # Route definitions
```

## 🧪 Testing

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Feature/ExampleTest.php
```

## 🛠 Troubleshooting

### Common Issues

- **Migration fails**: Ensure database exists and credentials are correct in `.env`
- **Composer install fails**: Check PHP version (8.2+) and required extensions
- **Assets not loading**: Run `npm run dev` or `npm run build`
- **Permission errors**: Check storage folder permissions (`chmod -R 755 storage/`)
- **Nginx fails to start**: Check config syntax with `nginx.exe -t -c conf\nginx.conf`
- **PHP FastCGI errors**: Ensure `php-cgi.exe` is running on port 9000
- **SSL certificate warnings**: Accept self-signed cert or get real certificates for production
- **Port conflicts**: Ensure ports 8080/8443/9000 are not used by other services

### Nginx-Specific Issues
- **Config syntax error**: Run `C:\nginx\nginx-1.24.0\nginx.exe -t -c conf\nginx.conf`
- **Permission denied**: Run terminals as Administrator
- **SSL cert not found**: Generate certificates in `C:\nginx\nginx-1.24.0\conf\ssl\`
- **PHP not responding**: Check if `php-cgi.exe` is running (`Get-Process php-cgi`)

### Required PHP Extensions
- pdo_mysql
- mbstring
- tokenizer
- xml
- ctype
- json
- bcmath
- fileinfo
- openssl

## 🔐 Default Credentials

- **Admin Login**: `admin@email.com` / Check your database or seeder
- **Student Access**: Via exam invitation links
- **Access URLs**:
  - Local HTTP: `http://172.31.254.116:8080` (redirects to HTTPS)
  - Local HTTPS: `https://172.31.254.116:8443` (accept certificate warning)

## 📝 Development Notes

- Core exam logic: `app/Services/QuestionSelector.php`
- Proctoring: Screenshot-based (no video recording)
- Scoring: Auto-scoring for MCQ, manual approval for descriptive answers
- File uploads: Stored in `storage/app/public/screenshots/`

## 🤝 Contributing

1. Create feature branch from `main`
2. Make changes with proper testing
3. Submit pull request with description
