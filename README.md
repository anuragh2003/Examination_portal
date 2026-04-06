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

7. **Storage setup**
   ```bash
   # Create symlink for file uploads
   php artisan storage:link
   ```

8. **Start the server**
   ```bash
   php artisan serve
   ```
   Access at: http://127.0.0.1:8000

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

4. **Rebuild assets (if needed)**
   ```bash
   npm run build
   ```

5. **Restart server**
   ```bash
   php artisan serve
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

- **Migration fails**: Ensure database exists and credentials are correct
- **Composer install fails**: Check PHP version (8.2+) and required extensions
- **Assets not loading**: Run `npm run dev` or `npm run build`
- **Permission errors**: Check storage folder permissions

### Required PHP Extensions
- pdo_mysql
- mbstring
- tokenizer
- xml
- ctype
- json
- bcmath

## 🔐 Default Credentials

- **Admin Login**: `admin@email.com` / Check your database
- **Student Access**: Via exam invitation links

## 📝 Development Notes

- Core exam logic: `app/Services/QuestionSelector.php`
- Proctoring: Screenshot-based (no video recording)
- Scoring: Auto-scoring for MCQ, manual approval for descriptive answers
- File uploads: Stored in `storage/app/public/screenshots/`

## 🤝 Contributing

1. Create feature branch from `main`
2. Make changes with proper testing
3. Submit pull request with description
