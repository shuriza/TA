# ⚡ Quick Reference - LMS Cerdas

Cheatsheet command Laravel yang sering dipakai untuk project ini.

---

## 🚀 Getting Started

```bash
# Clone & setup
git clone <repo-url> lms-cerdas
cd lms-cerdas
composer install
npm install
cp .env.example .env
php artisan key:generate

# Database
createdb lms_cerdas  # PostgreSQL
php artisan migrate
php artisan db:seed

# Build & Run
npm run dev          # Development watcher
php artisan serve    # Web server
php artisan queue:work       # Queue worker
php artisan schedule:work    # Scheduler
```

---

## 📦 Artisan Commands

### Migration
```bash
# Create migration
php artisan make:migration create_table_name
php artisan make:migration add_column_to_table --table=table_name

# Run migrations
php artisan migrate
php artisan migrate:fresh       # Drop all & re-migrate
php artisan migrate:fresh --seed  # + run seeders
php artisan migrate:rollback    # Rollback last batch
php artisan migrate:status      # Show migration status
```

### Model
```bash
# Create model
php artisan make:model ModelName
php artisan make:model ModelName -m    # + migration
php artisan make:model ModelName -f    # + factory
php artisan make:model ModelName -s    # + seeder
php artisan make:model ModelName -c    # + controller
php artisan make:model ModelName -a    # all (mfsc)
php artisan make:model ModelName -mfs  # migration + factory + seeder
```

### Controller
```bash
# Create controller
php artisan make:controller NameController
php artisan make:controller NameController --resource  # with CRUD methods
php artisan make:controller NameController --api       # API resource
php artisan make:controller Api/NameController --api   # in subfolder
php artisan make:controller NameController --invokable # single action
```

### Seeder
```bash
# Create seeder
php artisan make:seeder UserSeeder

# Run seeders
php artisan db:seed
php artisan db:seed --class=UserSeeder
php artisan migrate:fresh --seed
```

### Request Validation
```bash
php artisan make:request StoreAssignmentRequest
php artisan make:request UpdateAssignmentRequest
```

### Job & Queue
```bash
# Create job
php artisan make:job ProcessSyncJob

# Run queue worker
php artisan queue:work
php artisan queue:work --queue=high,default,low
php artisan queue:work --tries=3
php artisan queue:listen  # Auto-reload on code change

# Queue management
php artisan queue:failed       # List failed jobs
php artisan queue:retry <id>   # Retry failed job
php artisan queue:retry all    # Retry all failed
php artisan queue:flush        # Clear failed jobs
```

### Command
```bash
# Create command
php artisan make:command GenerateDailyPlan

# Run command
php artisan plan:generate
php artisan lms:sync moodle

# List all commands
php artisan list
```

### Notification
```bash
php artisan make:notification TaskDueSoon
php artisan make:notification AssignmentCreated
```

### Policy
```bash
php artisan make:policy AssignmentPolicy
php artisan make:policy AssignmentPolicy --model=Assignment
```

### Event & Listener
```bash
php artisan make:event AssignmentCreated
php artisan make:listener SendReminderNotification --event=AssignmentCreated
```

---

## 🔍 Laravel Scout (Meilisearch)

```bash
# Import all records
php artisan scout:import "App\Models\Material"
php artisan scout:import "App\Models\Assignment"

# Flush index
php artisan scout:flush "App\Models\Material"

# Delete index
php artisan scout:delete-index materials
```

---

## 🛡️ Laravel Sanctum

```bash
# Publish Sanctum config
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

---

## 👥 Spatie Permission

```bash
# Publish config & migration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Create permission
php artisan permission:create-permission "edit assignments"

# Create role
php artisan permission:create-role dosen

# Assign permission to role
php artisan permission:assign edit assignments dosen

# Cache reset
php artisan permission:cache-reset
```

---

## 🧹 Cache & Optimization

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize
php artisan optimize
php artisan optimize:clear
```

---

## 🗄️ Database

```bash
# Database info
php artisan db:show
php artisan db:table users

# Database monitor
php artisan db:monitor

# Run specific seeder
php artisan db:seed --class=CourseSeeder

# Wipe database (drop all tables)
php artisan db:wipe
```

---

## 🧪 Testing

```bash
# Create test
php artisan make:test AssignmentTest
php artisan make:test Services/PrioritizerTest --unit

# Run tests
php artisan test
php artisan test --filter=AssignmentTest
php artisan test --parallel
php artisan test --coverage
php artisan test --coverage-html coverage  # HTML report
```

---

## 📋 Routes

```bash
# List all routes
php artisan route:list

# Filter by name
php artisan route:list --name=api

# Filter by method
php artisan route:list --method=GET

# Filter by path
php artisan route:list --path=assignments
```

---

## 🔧 Tinker (REPL)

```bash
php artisan tinker

# Examples in tinker:
>>> $user = User::first()
>>> $user->name
>>> $assignments = Assignment::where('status', 'published')->get()
>>> Assignment::factory()->count(10)->create()
>>> Cache::get('key')
>>> \Log::info('Test log')
```

---

## 📦 Composer

```bash
# Install packages
composer require package/name
composer require package/name --dev

# Update
composer update
composer update package/name

# Dump autoload
composer dump-autoload
composer dump-autoload -o  # Optimized

# Show installed packages
composer show
composer show -i  # Installed only
```

---

## 📦 NPM

```bash
# Install
npm install
npm install package-name
npm install package-name --save-dev

# Build
npm run dev          # Development
npm run build        # Production
npm run watch        # Watch mode

# Update
npm update
```

---

## 🐳 Laravel Sail (Optional Docker)

```bash
# Install Sail
php artisan sail:install

# Start containers
./vendor/bin/sail up
./vendor/bin/sail up -d  # Detached mode

# Stop containers
./vendor/bin/sail down

# Run artisan commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker

# Run composer
./vendor/bin/sail composer install

# Run npm
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

---

## 🔐 Environment

```bash
# Generate app key
php artisan key:generate

# Copy env
cp .env.example .env

# Show env
php artisan env
```

---

## 📊 Maintenance Mode

```bash
# Enable maintenance
php artisan down
php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"  # With bypass

# Disable maintenance
php artisan up
```

---

## 🔄 Storage

```bash
# Create symbolic link
php artisan storage:link

# Clear compiled
php artisan clear-compiled
```

---

## 📝 Make Commands (Quick List)

```bash
php artisan make:cast
php artisan make:channel
php artisan make:command
php artisan make:component
php artisan make:controller
php artisan make:event
php artisan make:exception
php artisan make:factory
php artisan make:job
php artisan make:listener
php artisan make:mail
php artisan make:middleware
php artisan make:migration
php artisan make:model
php artisan make:notification
php artisan make:observer
php artisan make:policy
php artisan make:provider
php artisan make:request
php artisan make:resource
php artisan make:rule
php artisan make:seeder
php artisan make:test
```

---

## 🎯 Custom Commands (Project Specific)

```bash
# Generate daily plan
php artisan plan:generate
php artisan plan:generate --today

# Sync from LMS
php artisan lms:sync spada
php artisan lms:sync classroom

# Send reminders
php artisan reminder:send
php artisan reminder:send --force

# Cleanup old data
php artisan activities:cleanup --days=90

# Demo reset
php artisan demo:reset  # Fresh migrate + seed demo data
```

---

## 🔍 Debugging

```bash
# Install Telescope (dev only)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Log viewer
tail -f storage/logs/laravel.log

# Query log
DB::listen(function($query) {
    \Log::info($query->sql);
});
```

---

## 🚢 Deployment

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
npm run build

# Permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Queue worker (Supervisor)
sudo supervisorctl start lms-queue-worker:*
sudo supervisorctl status
sudo supervisorctl restart all
```

---

## 📊 Monitoring

```bash
# Queue monitoring
php artisan queue:monitor redis:default,redis:high --max=100

# Schedule monitoring
php artisan schedule:list
php artisan schedule:test

# Horizon (advanced queue)
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

---

## 🔄 Git Workflow

```bash
# Daily workflow
git status
git add .
git commit -m "feat: add assignment CRUD API"
git push origin main

# Branch
git checkout -b feature/telegram-integration
git push origin feature/telegram-integration

# Pull latest
git pull origin main
git fetch --all
```

---

## 🎨 Code Style

```bash
# Laravel Pint (code formatter)
./vendor/bin/pint
./vendor/bin/pint --test  # Check only
./vendor/bin/pint --dirty # Only changed files

# PHPStan (static analysis)
composer require phpstan/phpstan --dev
./vendor/bin/phpstan analyse
```

---

## 📱 MySQL Commands

```bash
# Connect to database
mysql -u root -p

# In MySQL:
SHOW DATABASES;                    # List databases
USE lms_cerdas;                    # Select database
SHOW TABLES;                       # List tables
DESCRIBE users;                    # Describe table structure
SELECT * FROM users LIMIT 10;      # Query data
EXIT;                              # Quit

# Create database
mysql -u root -p -e "CREATE DATABASE lms_cerdas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Drop database
mysql -u root -p -e "DROP DATABASE lms_cerdas;"

# Backup
mysqldump -u root -p lms_cerdas > backup.sql

# Restore
mysql -u root -p lms_cerdas < backup.sql

# Import SQL file
mysql -u root -p lms_cerdas < database.sql
```

---

## 🔴 Redis Commands

```bash
# Connect
redis-cli

# In redis-cli:
KEYS *                     # List all keys
GET key_name              # Get value
DEL key_name              # Delete key
FLUSHDB                   # Clear current database
FLUSHALL                  # Clear all databases
INFO                      # Server info
MONITOR                   # Monitor commands
```

---

**Happy Coding! 🚀**

_Bookmark this for quick reference!_
