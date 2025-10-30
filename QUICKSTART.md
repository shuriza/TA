# 🚀 Quick Start Guide - Smart LMS

Panduan cepat untuk setup dan menjalankan Smart LMS dalam 5 menit!

## Prerequisites Checklist

Pastikan sudah terinstall:
- [ ] PHP 8.3+ (`php -v`)
- [ ] Composer (`composer -V`)
- [ ] MySQL 8.0+ 
- [ ] Node.js 18+ & NPM (`node -v` && `npm -v`)
- [ ] Git (`git --version`)

## Step-by-Step Installation

### 1️⃣ Clone & Setup (2 menit)

```bash
# Clone repository
git clone https://github.com/shuriza/TA.git
cd TA

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2️⃣ Database Configuration (1 menit)

Buka `.env` file dan update:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms_cerdas
DB_USERNAME=root
DB_PASSWORD=your_password
```

Buat database MySQL:
```sql
CREATE DATABASE lms_cerdas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3️⃣ Run Migrations & Seeders (1 menit)

```bash
# Migrate database dan seed sample data
php artisan migrate --seed
```

Ini akan membuat:
- ✅ Semua tabel database
- ✅ User admin, dosen, dan mahasiswa
- ✅ Sample courses dan assignments
- ✅ Sample notifications

### 4️⃣ Build Assets (1 menit)

```bash
# Build frontend assets
npm run build

# Atau untuk development (with hot reload)
npm run dev
```

### 5️⃣ Run Application (30 detik)

```bash
# Start Laravel development server
php artisan serve
```

🎉 **Done!** Akses aplikasi di: http://127.0.0.1:8000

## 🔐 Login Credentials

### Admin
```
Email: admin@ulm.ac.id
Password: password
```

### Dosen (Lecturer)
```
Email: dosen@ulm.ac.id
Password: password
```

### Mahasiswa (Student)
```
Email: mahasiswa@ulm.ac.id
Password: password
```

## 🤖 Enable AI Features (Optional)

Jika ingin mengaktifkan AI Assistant:

1. Dapatkan API Key dari OpenAI: https://platform.openai.com/api-keys

2. Tambahkan ke `.env`:
```env
OPENAI_API_KEY=sk-your-openai-api-key-here
```

3. Clear config cache:
```bash
php artisan config:cache
```

4. Test AI Assistant di: http://127.0.0.1:8000/ai-assistant

## 📱 Enable Automated Reminders (Optional)

Untuk menjalankan automated deadline reminders:

```bash
# Terminal 1: Run Laravel server
php artisan serve

# Terminal 2: Run queue worker
php artisan queue:work

# Terminal 3: Run scheduler (atau setup cron)
php artisan schedule:work
```

**Atau setup cron job (production):**
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## ✅ Verify Installation

### Test Checklist:

1. **Login**
   - [ ] Login sebagai admin
   - [ ] Login sebagai dosen
   - [ ] Login sebagai mahasiswa

2. **Dashboard**
   - [ ] Student dashboard menampilkan urgent assignments
   - [ ] Lecturer dashboard menampilkan pending grading
   - [ ] Admin dashboard menampilkan statistics

3. **Assignments**
   - [ ] Create new assignment (as lecturer)
   - [ ] View assignment list
   - [ ] Submit assignment (as student)

4. **Notifications**
   - [ ] Notification bell menampilkan count
   - [ ] Click bell untuk lihat dropdown
   - [ ] Access /notifications untuk full list

5. **AI Assistant** (if enabled)
   - [ ] Access /ai-assistant
   - [ ] Chat with AI
   - [ ] Get recommendations

## 🐛 Troubleshooting

### Database Connection Error
```bash
# Check MySQL is running
mysql -u root -p

# Verify database exists
SHOW DATABASES;
```

### Permission Errors
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

### NPM Build Errors
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

### AI Features Not Working
- Verify `OPENAI_API_KEY` in `.env`
- Check API key is valid
- System will use fallback mode without API key

### Notification Not Appearing
```bash
# Check notifications table exists
php artisan migrate:status

# Seed sample notifications
php artisan db:seed --class=NotificationSeeder
```

## 📚 Next Steps

1. **Explore Features**
   - Browse assignments
   - Test notification system
   - Try AI assistant chat

2. **Customize**
   - Update user profiles
   - Add more courses
   - Create assignments

3. **Development**
   - Read `README.md` for full documentation
   - Check `CHANGELOG.md` for version history
   - Review code structure in `/app`

4. **Production Deployment**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure SSL
   - Setup queue worker
   - Setup cron jobs

## 🆘 Need Help?

- **Documentation**: See `README.md`
- **Issues**: Open issue on GitHub
- **Questions**: Check troubleshooting section

---

**Happy Learning! 📚✨**
