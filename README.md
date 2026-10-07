# 🚀 Project Comparison Portal

Aplikasi web modern untuk **mengelola, memantau, dan membandingkan proyek** secara cepat dan terstruktur.

Project Comparison Portal dirancang untuk membantu pengguna mengelola informasi proyek, memantau statistik, serta menyediakan API yang dapat digunakan untuk integrasi dengan aplikasi eksternal.

---

## ✨ Features

### 📁 Project Management

* Membuat, melihat, mengubah, dan menghapus proyek.
* Mengelola informasi dan status proyek.
* Membandingkan data antar proyek dengan lebih mudah.

### 👥 User Management

* Manajemen pengguna.
* Sistem autentikasi dan otorisasi.
* Pengelolaan akses berdasarkan hak pengguna.

### 📊 Dashboard

* Ringkasan statistik proyek.
* Informasi status dan perkembangan proyek.
* Tampilan data yang terstruktur dan mudah dipahami.

### 🔌 REST API

* API untuk integrasi dengan aplikasi eksternal.
* Endpoint terstruktur untuk mengakses data proyek.
* Mendukung pengembangan aplikasi frontend maupun layanan pihak ketiga.

### 🧪 Automated Testing

* Test suite menggunakan Pest PHP.
* Memastikan fitur utama tetap berjalan setelah perubahan kode.
* Terintegrasi dengan CI untuk validasi otomatis.

---

## 🛠️ Tech Stack

| Technology         | Usage                  |
| ------------------ | ---------------------- |
| **PHP 8.2+**       | Backend                |
| **Laravel 12**     | Web Framework          |
| **Vue 3**          | Frontend               |
| **Vue Router**     | Client-side Routing    |
| **Tailwind CSS**   | UI & Styling           |
| **Pest PHP**       | Testing                |
| **Vite**           | Frontend Build Tool    |
| **MySQL / SQLite** | Database               |
| **GitHub Actions** | Continuous Integration |

---

## 🏗️ Architecture

Project ini menggunakan pendekatan modern dengan pemisahan antara backend, frontend, database, dan automated testing.

```text
┌─────────────────────────────┐
│          Vue 3              │
│       Vue Router            │
│       Tailwind CSS          │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│        Laravel 12           │
│                             │
│  Controllers                │
│  Authentication             │
│  Authorization              │
│  Business Logic             │
│  REST API                   │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│          Database           │
│       MySQL / SQLite        │
└─────────────────────────────┘

              +
              
┌─────────────────────────────┐
│       Pest PHP Tests        │
│       GitHub Actions        │
└─────────────────────────────┘
```

---

## 📋 Requirements

Pastikan environment berikut sudah tersedia sebelum menjalankan project:

* PHP **8.2 atau lebih baru**
* Composer
* Node.js & npm
* Database seperti MySQL atau SQLite
* Git

Untuk memastikan versi yang digunakan:

```bash
php -v
composer -V
node -v
npm -v
```

---

## 🚀 Installation

### 1. Clone Repository

```bash
git clone https://github.com/USERNAME/projectcmp.git
cd projectcmp
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Configure Environment

Copy file environment:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Kemudian konfigurasi database pada file `.env`.

Contoh menggunakan MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=projectcmp
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run Database Migration

```bash
php artisan migrate
```

Jika project menyediakan database seeder:

```bash
php artisan db:seed
```

atau:

```bash
php artisan migrate --seed
```

### 6. Build Frontend Assets

Untuk production build:

```bash
npm run build
```

Untuk development:

```bash
npm run dev
```

### 7. Start Laravel Development Server

```bash
php artisan serve
```

Aplikasi dapat diakses melalui:

```text
http://127.0.0.1:8000
```

---

## 🧪 Testing

Project menggunakan **Pest PHP** untuk automated testing.

Jalankan seluruh test suite:

```bash
composer test
```

atau:

```bash
vendor/bin/pest
```

Menjalankan test dengan detail:

```bash
vendor/bin/pest -v
```

Menjalankan test tertentu:

```bash
vendor/bin/pest tests/Feature
```

### ✅ Quality Gate

Sebelum melakukan merge ke `main`, pastikan:

```text
✓ Tests passing
✓ Code formatted
✓ No critical errors
✓ Frontend build successful
```

---

## 🔌 API

Project menyediakan REST API yang dapat digunakan oleh aplikasi eksternal atau frontend client.

Contoh struktur endpoint:

```text
GET    /api/projects
POST   /api/projects
GET    /api/projects/{id}
PUT    /api/projects/{id}
DELETE /api/projects/{id}
```

> Detail endpoint, authentication, request body, dan response dapat disesuaikan dengan implementasi API pada project.

Untuk testing API, tools seperti **Postman** atau **Insomnia** dapat digunakan.

---

## 🔄 CI/CD

Project menggunakan **GitHub Actions** untuk melakukan validasi otomatis setiap kali terjadi perubahan kode.

Pipeline CI mencakup:

```text
Push / Pull Request
        │
        ▼
Install Dependencies
        │
        ▼
Run Tests
        │
        ▼
Build Frontend
        │
        ▼
   CI Validation
        │
        ▼
 Ready to Merge
```

Contoh workflow:

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          coverage: none

      - name: Install Composer Dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Run Tests
        run: composer test

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: npm

      - name: Install Node Dependencies
        run: npm ci

      - name: Build Frontend
        run: npm run build
```

Status CI dapat dilihat melalui tab **Actions** pada repository GitHub.

---

## 📂 Project Structure

Struktur utama project Laravel:

```text
projectcmp/
├── app/
│   ├── Http/
│   ├── Models/
│   └── ...
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── js/
│   ├── css/
│   └── views/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── public/
├── storage/
├── .env.example
├── composer.json
├── package.json
└── vite.config.js
```

---

## 🔐 Environment & Security

File `.env` berisi konfigurasi yang bersifat lokal dan **tidak boleh di-commit ke repository**.

Pastikan:

```text
.env          → ❌ Jangan commit
.env.example  → ✅ Commit
```

Jangan menyimpan informasi sensitif seperti:

* Database password
* API keys
* Access tokens
* Application secrets
* Production credentials

di dalam source code atau repository publik.

---

## 🌱 Development Workflow

Recommended workflow untuk pengembangan:

```bash
# Create feature branch
git checkout -b feature/project-management

# Make changes
git add .

# Commit changes
git commit -m "feat: add project management"

# Push branch
git push origin feature/project-management
```

Kemudian buat **Pull Request** menuju branch `main`.

---

## 📝 Commit Convention

Project menggunakan conventional commit style untuk menjaga history Git tetap konsisten.

Contoh:

```text
feat: add project comparison
fix: resolve project validation issue
refactor: simplify project service
test: add project feature tests
docs: update API documentation
chore: update dependencies
```

---

## 🗺️ Roadmap

Pengembangan berikutnya dapat mencakup:

* [ ] Advanced project comparison
* [ ] Project filtering & sorting
* [ ] Advanced dashboard analytics
* [ ] Role & permission management
* [ ] API documentation
* [ ] API authentication
* [ ] Export project reports
* [ ] Improved automated testing
* [ ] Production deployment

---

## 🤝 Contributing

Contributions, suggestions, and improvements are welcome.

1. Fork repository ini.
2. Buat feature branch.
3. Implementasikan perubahan.
4. Jalankan test suite.
5. Pastikan CI berhasil.
6. Buat Pull Request.

Pastikan perubahan yang dibuat tidak menyebabkan existing tests gagal.

---

## 📄 License

Project ini menggunakan **MIT License**.

Lihat file [`LICENSE`](LICENSE) untuk informasi lengkap mengenai lisensi.

---

## 👨‍💻 Author

**Rhesa Ivander**

Web Developer yang berfokus pada pengembangan aplikasi web menggunakan Laravel, PHP, Vue.js, dan teknologi web modern.

---

<p align="center">
  Built with ❤️ using Laravel and Vue.js
</p>
