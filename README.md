# 🏨 Hotel Pulang Yu

> 🔑 **Login Admin (default):**
> - Email: `admin@hotel.com`
> - Password: `admin123`
>
> Akun ini dibuat otomatis melalui **seeder** (`database/seeders/UserSeeder.php`) saat menjalankan `php artisan migrate --seed` (atau `php artisan db:seed`), dengan `role_id` yang diarahkan ke role **Admin** pada tabel `roles` (dibuat oleh `RoleSeeder.php`). Jadi begitu seeder dijalankan, akun tersebut sudah langsung bisa dipakai untuk masuk ke panel admin.

Aplikasi manajemen hotel berbasis web yang dibangun dengan **Laravel 12**, mencakup sisi **customer** (pemesanan kamar, riwayat reservasi, profil) dan sisi **admin** (dashboard, manajemen kamar, tipe kamar, fasilitas, reservasi, promosi, laporan).

## ✨ Fitur Utama

### 🙋 Customer
- **Autentikasi** — login & register (termasuk login/register via Google, menggunakan Laravel Socialite).
- **Dashboard** — ringkasan aktivitas dan informasi akun customer.
- **Rooms** — melihat daftar tipe kamar beserta harga, kapasitas, dan fasilitas.
- **Reservasi**
  - Membuat reservasi baru dengan alur konfirmasi bertahap (preview → konfirmasi → sukses).
  - Melihat daftar/riwayat reservasi milik sendiri.
  - Membatalkan reservasi.
  - Validasi kode promo saat booking.
  - Pengecekan ketersediaan kamar (available rooms) secara real-time.
- **Promosi** — melihat daftar promo yang sedang berlaku.
- **Profil** — update data profil, ganti password, upload/hapus foto profil (avatar).
- **Global Search** — pencarian data customer/reservasi.

### 🛠️ Admin
- **Dashboard** — statistik ringkasan (grafik dengan Chart.js).
- **Manajemen Kamar (Room)** — CRUD data kamar.
- **Manajemen Tipe Kamar (Room Type)** — CRUD tipe kamar, relasi many-to-many dengan fasilitas, serta export data.
- **Manajemen Fasilitas** — CRUD fasilitas kamar, termasuk quick-add fasilitas baru langsung dari form lain.
- **Manajemen Reservasi**
  - CRUD reservasi lengkap dengan perhitungan otomatis nights, tax, discount, dan total_amount.
  - Aksi check-in, check-out, dan cancel reservasi.
  - Validasi kode promo dan pengecekan kamar yang tersedia.
  - Deteksi tumpang-tindih (overlap) tanggal booking pada kamar yang sama.
- **Manajemen Promosi** — CRUD promo (persentase, nominal tetap, atau voucher) dengan kuota, minimum booking, maksimum diskon, dan periode aktif; serta export data promo ke PDF.
- **Laporan Reservasi** — laporan reservasi dengan detail per transaksi dan export ke PDF.
- **Profil Admin** — pengaturan akun admin.
- **Global Search** — pencarian cepat di seluruh panel admin.
- **Role-based Access** — middleware `role` membedakan akses admin dan customer.

## 🧰 Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade Templating, Tailwind CSS 4, Vite |
| Database | MySQL (via Eloquent ORM) |
| Autentikasi Sosial | Laravel Socialite (Google) |
| Export PDF | barryvdh/laravel-dompdf |
| Export Excel | maatwebsite/excel |
| Ikon | Remix Icon |
| Notifikasi/Alert | SweetAlert2 |
| Grafik | Chart.js |
| Font | Plus Jakarta Sans |

## 📁 Struktur Proyek (ringkas)

```
app/
├── Http/Controllers/
│   ├── Admin/          # Dashboard, Room, RoomType, Reservation, ReservationReport,
│   │                     Facility, Promotion, Search, Profile
│   ├── Customer/       # Dashboard, Room, Reservation, Promotion, Profile, Search
│   └── Auth/           # AuthController (login, register, Google OAuth)
├── Models/             # User, Role, Guest, Room, RoomType, Facility, Reservation, Promotion
└── Exports/            # Kelas export (Excel/PDF)

database/
├── migrations/         # roles, users, guests, room_types, rooms, promotions,
│                         facilities, facility_room_type (pivot), reservations
└── seeders/

resources/
├── views/
│   ├── admin/          # dashboard, room, room-type, reservations, promotion,
│   │                     facilities, guest, report, profile, partials (navbar/sidebar/footer)
│   ├── customer/       # dashboard, rooms, reservations, profile, partials
│   ├── auth/           # login, register
│   └── layouts/        # admin, customer, guest
├── css/ & js/          # sumber Vite (per-halaman)

public/
├── css/                # admin/*.css, customer/*.css, login.css, style.css
└── js/                 # admin/*.js, customer/*.js, login.js

routes/
└── web.php             # semua route (guest, admin, customer) dengan middleware role
```

## 🗂️ Model & Relasi Data

- **User** `belongsTo` Role, `hasOne` Guest.
- **Guest** `belongsTo` User (data identitas tambahan customer: no. identitas, telepon, alamat).
- **Room** `belongsTo` RoomType, `hasMany` Reservation.
- **RoomType** `hasMany` Room, `belongsToMany` Facility (via tabel pivot `facility_room_type`). Punya accessor turunan: harga termurah, kapasitas terbesar, foto, dan daftar nama fasilitas.
- **Facility** `belongsToMany` RoomType.
- **Reservation** `belongsTo` Room, User, dan Promotion. Kode reservasi (`RSV-YYYY-00001`) digenerate otomatis. Punya method untuk menghitung total biaya (`calculateTotals`) dan mendeteksi bentrok jadwal (`hasOverlap`).
- **Promotion** `hasMany` Reservation. Status promo (Active/Upcoming/Expired/Inactive) dihitung otomatis berdasarkan tanggal. Punya method untuk menghitung nominal diskon dan validasi apakah promo berlaku untuk tipe kamar/subtotal tertentu.

## 🚀 Instalasi & Menjalankan Proyek

### ✅ Kebutuhan
- PHP ^8.2
- Composer
- Node.js & npm
- MySQL

### 📝 Langkah

```bash
# 1. Install dependency PHP
composer install

# 2. Salin file environment & generate app key
cp .env.example .env
php artisan key:generate

# 3. Atur koneksi database di file .env
#    DB_DATABASE=hotel
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Jalankan migrasi (+ seeder jika ada)
php artisan migrate

# 5. Install dependency frontend
npm install

# 6. Jalankan server development (server, queue, vite sekaligus)
composer run dev
```

Aplikasi dapat diakses di https://aspel.cyou/Hotel

> Catatan: perintah `composer run dev` menjalankan `php artisan serve`, `php artisan queue:listen`, dan `npm run dev` secara bersamaan (via `concurrently`).

### 📦 Build untuk Production

```bash
npm run build
```

## 🔐 Autentikasi & Role

- Login/register manual (email & password) serta login/register via Google (Socialite).
- Setelah login, user diarahkan sesuai role (`admin` atau `customer`) melalui middleware `role`.
- Route `/admin/*` hanya dapat diakses role **admin**; route `/customer/*` hanya dapat diakses role **customer**.

## 📌 Status Proyek

Proyek ini merupakan aplikasi akademik/coursework yang masih dalam pengembangan aktif — beberapa area (frontend admin & customer, fitur reservasi, promosi, laporan) telah selesai, sementara penyempurnaan UI/UX dan fitur tambahan masih terus dikerjakan.
