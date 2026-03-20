# Examination Portal (Laravel)

A lightweight Laravel-based online examination portal with student management, exam creation, proctoring video capture, and auto-scoring.

## 🔎 Project Summary

- Built with Laravel (PHP) and Bootstrap/Vite frontend.
- Student-facing exam flow: login, take exam, upload proctor video, submit answers.
- Admin-facing flow: create exams, define questions/options, view results and students.
- Autoscore support: multiple-choice questions, answer tracking via student_answers.

## 📁 Key files and major functionality

- pp/Http/Controllers/ : HTTP controllers that handle web/API requests.
  - ExamController.php (exam lifecycle endpoints)
  - StudentController.php (student onboarding, profile)
  - QuestionController.php (CRUD for questions/options)
- pp/Models/ : Eloquent models for core domain objects.
  - Exam.php, Question.php, QuestionOption.php, Student.php, Student_Answer.php, ProctorVideo.php
- pp/Services/QuestionSelector.php : exam question selection logic.
- 
Routes/web.php : app routes for UI pages and flows.
- 
Routes/api.php : API route definitions if used by JS/front-end.
- 
Resources/views/ : Blade templates for UI pages (exam dashboard, question pages, results).
- database/migrations/ : DB schema on tables such as exams, sessions, students, questions, question_options, student_answers, proctor_videos.
- public/storage : uploaded proctor video/media storage target.

## ⚙️ Setup and launch (developer workflow)

1. Clone the repository

   `ash
   git clone https://github.com/anuragh2003/Examination_portal.git Examination_portal
   cd Examination_portal
   `

2. Copy environment file and configure DB

   - On Linux/macOS: cp .env.example .env
   - On Windows PowerShell: Copy-Item .env.example .env

   Update .env values:
   - DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

3. Install PHP dependencies

   `ash
   composer install
   php artisan key:generate
   `

4. Run database migrations (and optional seeding)

   `ash
   php artisan migrate
   # optional: php artisan db:seed
   `

5. (Optional) Install JS dependencies for frontend assets

   `ash
   npm install
   npm run dev
   # or npm run build for production
   `

6. Create storage symlink (for file uploads)

   `ash
   php artisan storage:link
   `

7. Run local server

   `ash
   php artisan serve
   `

   Access at http://127.0.0.1:8000.

## 🧪 Testing

- Run PHPUnit tests:

  `ash
  ./vendor/bin/phpunit
  `

## 🛠 Troubleshooting

- php artisan migrate fails: ensure DB exists and credentials match.
- composer install fails: check PHP 8+ version, extensions (pdo_mysql, mbstring, tokenizer, xml).
- missing .env: confirm .env.example is present.

## 📌 Notes for teammates

- Start by reviewing pp/Services/QuestionSelector.php and pp/Http/Controllers/ExamController.php for core exam behavior.
- For schema changes, update migrations and model relationships under pp/Models.
- Seed dummy data to verify flows quickly.
