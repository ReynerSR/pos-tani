# Alur Program Aplikasi POS-Tani

Dokumen ini menjelaskan secara detail dan teknis bagaimana aplikasi POS-Tani bekerja dari awal hingga akhir. Aplikasi ini merupakan Sistem Point of Sale (POS) dan Manajemen Inventaris yang dibangun menggunakan arsitektur MVC pada Framework Laravel.

## 1. Arsitektur Dasar Aplikasi
Aplikasi ini dibangun menggunakan kerangka kerja (Framework) **Laravel** dengan mengadopsi pola desain **MVC (Model-View-Controller)** secara komprehensif. Berikut adalah rincian teknis dari komponen arsitekturnya:

1. **Routing & Middleware Layer**:
   - **File Terpusat**: Seluruh _Endpoint_ web didefinisikan secara terstruktur di dalam `routes/web.php` dan `routes/auth.php`.
   - **Sistem Pengamanan Lapis (Middleware)**: Sebelum _Request_ HTTP mencapai *Controller*, sistem akan melewati gerbang *Middleware*. Pertama, `auth` memastikan adanya sesi login yang valid. Kedua, kustom middleware `role` memvalidasi tabel hak akses (`pemilik`, `admin`, `kasir`) untuk mencegah _Unauthorized Access_ (Pembobolan Hak Akses) ke URL yang sensitif.

2. **Controller Layer (Logika Bisnis & Pengendali)**:
   - **Pemisahan Modul**: Logika bisnis utama dipisah rapi ke dalam masing-masing _Controller_ spesifik berdasarkan entitas (seperti `ProductController`, `TransactionController`, `StockController`).
   - **Service Pattern**: Aplikasi ini juga mengadopsi _Service Pattern_. Algoritma yang rumit dan sering dipakai berulang (seperti perhitungan poin member dan evaluasi diskon/promo) diisolasi ke dalam _Class_ tersendiri, yaitu `RuleBasedMembershipService`. Hal ini menjaga agar _Controller_ kasir tidak menjadi terlalu gemuk (*Fat Controller*).

3. **Model & Database Layer (Eloquent ORM)**:
   - **Struktur Skema Inti (Core Schema)**: Aplikasi bertumpu pada beberapa tabel sentral, di antaranya `products` (master barang), `warehouses` dan `warehouse_stocks` (pemisah lokasi fisik penyimpanan), `transactions` & `transaction_details` (keranjang kasir), serta `stock_adjustments` (audit riwayat stok).
   - **Relasi Mendalam (Relationships)**: Aplikasi mendefinisikan relasi tingkat tinggi layaknya *One-to-Many* (`hasMany`, `belongsTo`) pada model. Sebagai contoh, model `Transaction` berelasi otomatis ke `TransactionDetail`, `Customer`, dan `User` (Kasir).
   - **Optimasi Kinerja (Eager Loading)**: Untuk menghindari masalah performa _N+1 Query_ yang membuat _server_ lambat, arsitektur ini secara konsisten menyuntikkan instruksi `with(['relasi1', 'relasi2'])` pada setiap kueri pemanggilan laporan berskala besar.
   - **Concurrency Control (Pessimistic Locking)**: Pada proses finansial dan stok yang amat krusial (seperti proses Checkout Kasir atau Approval Restok), aplikasi menanamkan fungsi `lockForUpdate()` dan `DB::beginTransaction()`. Arsitektur ini memastikan tidak akan terjadi bentrokan stok (Data *Race Condition*) apabila ada dua kasir menekan tombol "Bayar" di detik dan milidetik yang sama secara bersamaan.

4. **View Layer (Antarmuka Pengguna / UI)**:
   - **Blade Template Engine**: Sistem memanfaatkan fitur kompilasi _Blade_ milik Laravel untuk menghasilkan dokumen HTML dinamis di sisi *Server*.
   - **AJAX & Asynchronous API**: Pada halaman yang membutuhkan interaksi cepat secara _Real-Time_ (seperti modul Kasir POS), arsitektur View menggabungkan bahasa pemrograman _JavaScript_ untuk menembak _Endpoint API Internal_ (misalnya `POST /api/price-check`). Server merespons dalam format `JSON`, lalu JavaScript merender tampilannya tanpa perlu melakukan proses _Reload_ (Muat Ulang Halaman).

## 2. Autentikasi dan Sistem Hak Akses (Role-Based Access Control)
Sistem ini membedakan penggunanya ke dalam 3 Role (Peran) mutlak. Pembatasan (*Restriction*) ini dijaga sangat ketat di dua sisi: Pada level _Route/Middleware_ (`middleware(['role:nama_role'])`) untuk memblokir halaman, dan di level _Controller_ (_Hardcoded Policy_) untuk memanipulasi kemampuan kueri pengguna.

Berikut rincian spesifik apa saja yang bisa dan tidak bisa dilakukan tiap peran berdasarkan _source code_ sistem:

### 1. Pemilik (Owner)
Berperan sebagai *Super Admin* yang memiliki wewenang eksekutif 100% tanpa batas ke seluruh sudut sistem.
- **Hak Eksklusif (HANYA Pemilik yang bisa melakukan ini)**:
  - **Manajemen Karyawan**: Menambah, mengedit, dan menonaktifkan akun Admin/Kasir (`UserController`).
  - **Urusan Keuangan (HPP & Laba)**: Hanya pemilik yang bisa melihat Laporan Laba Kotor dan Margin Profit (`ReportController@profit`).
  - **Validasi Harga Modal**: Menetapkan dan mengubah Harga Jual serta Harga Pokok Penjualan (HPP) pada master produk (`ProductController@edit`).
  - **Otorisasi (Approval)**: Menyetujui Restok/Pembelian barang (`PurchaseController@approve`) dan Stock Opname (`StockController@approve`) agar stok resmi bertambah.
  - **Koreksi Data (Void & Edit)**: Menghapus (VOID) dan Merevisi nota kasir yang salah input (`TransactionController@destroy` & `update`).
  - **Pengawasan (Audit Trail)**: Mengakses seluruh log jejak aktivitas karyawan (`ActivityLog`).
  - **Manajemen Kebijakan**: Mengubah aturan Membership (Nilai per-poin, Tier).

### 2. Admin Operasional
Berperan menjalankan roda operasi logistik _Back-Office_. Admin **BISA** membuka fitur Kasir jika dibutuhkan.
- **Hak yang Diberikan**:
  - Mengelola Katalog Produk (Menambah Produk Baru), Supplier, dan Gudang.
  - Menginput/Membuat Draft Pembelian Barang (Restok).
  - Melakukan Transfer Stok antar Gudang.
  - Melakukan pendataan penyesuaian Stok (Stock Opname).
  - Membuat dan mengatur Event Promo/Diskon bulanan.
- **Hak yang DIBATASI (Restriksi Admin)**:
  - **Harga Terkunci**: Saat admin menambah produk baru, sistem secara paksa (*hardcode*) mengunci input Harga Jual dan HPP menjadi `0`. Admin tidak diizinkan menentukan harga barang sama sekali (hanya Pemilik yang bisa mengisinya di menu Edit).
  - **Tidak Ada Hak Otorisasi**: Admin hanya bisa _menyodorkan_ nota Pembelian dan Stock Opname berstatus `Pending`. Ia tidak punya tombol `Approve` untuk menyahkannya menjadi stok riil.
  - **Dilarang Menghapus Data**: Admin diblokir dari fitur hapus/edit produk maupun membatalkan nota kasir.
  - Tidak bisa melihat laporan keuntungan finansial dan log aktivitas.

### 3. Kasir
Berperan eksklusif hanya pada modul Front-End (Garda Depan) pelayanan pelanggan.
- **Hak yang Diberikan**:
  - Mengakses halaman transaksi POS (Point of Sale) dan mencetak struk.
  - Mencari katalog produk dan mengecek harga ter-update secara _read-only_.
  - Mendaftarkan dan mencari riwayat pelanggan (Member).
  - Mengedit _password_ dan profil pribadinya sendiri.
- **Hak yang DIBATASI (Restriksi Kasir)**:
  - **Data Terisolasi (Scope Guard)**: Saat kasir membuka halaman _Dashboard_ atau _History_ Transaksi, sistem mengunci kueri database (menginjeksi `where('cashier_id', auth_id)`). Artinya, **Kasir A tidak akan pernah bisa melihat jumlah uang omzet dan riwayat nota milik Kasir B**.
  - **Terblokir Total dari Inventaris**: Tidak bisa membuka Gudang, Restok Pembelian, Transfer Stok, Opname, Promo, maupun Master Data Supplier.
  - Tidak bisa mengedit barang apa pun, apalagi menghapus transaksi yang sudah tercetak (harus panggil Pemilik).

### Alur Teknis Login (Authentication Flow)
Secara spesifik, ketika pengguna mengakses halaman login dan masuk ke sistem, berikut adalah alur data dan eksekusinya secara berurutan:

1. **Akses Halaman Utama (GET `/`) dan Intersepsi Middleware**:
   - Pengguna mengakses root URL (`/`).
   - `routes/web.php` mengarahkan pengguna ke halaman `dashboard`.
   - Karena `dashboard` dilindungi oleh **middleware `auth`** (wajib login), sistem menolak akses tersebut dan secara otomatis mengarahkan (redirect) pengguna ke rute `login`.

2. **Akses Halaman Login (GET `/login`)**:
   - Permintaan ditangani oleh `routes/auth.php` yang meneruskannya ke `AuthenticatedSessionController@create`.
   - Fungsi `create()` pada Controller merender dan mengembalikan antarmuka visual (HTML/CSS) dari file view `resources/views/auth/login.blade.php`.

3. **Submit Form Login (POST `/login`)**:
   - Pengguna memasukkan data `username` dan `password` pada form di halaman login, kemudian mengirimkannya (submit).
   - `routes/auth.php` menangkap request POST ini dan meneruskannya ke `AuthenticatedSessionController@store`.

4. **Validasi dan Percobaan Autentikasi (`Auth::attempt`)**:
   - Sistem memvalidasi inputan (memastikan `username` dan `password` diisi).
   - `Auth::attempt($credentials, false)` dijalankan oleh Laravel untuk melakukan pencocokan data `username` dan _hashed password_ di tabel `users`.
   - Jika gagal (username/password salah), pengguna dikembalikan (`redirect back`) ke halaman login beserta pesan error (Validation Errors).

5. **Pengecekan Status Aktif (`is_active`)**:
   - Jika kredensial benar, sistem menarik data user (`Auth::user()`).
   - Sistem mengecek kolom `is_active` pada model User. Jika `false` (akun dinonaktifkan oleh pemilik), maka sesi langsung dihancurkan (`Auth::logout()`) dan pengguna dilempar kembali dengan pesan error "Akun Anda telah dinonaktifkan. Hubungi pemilik toko."

6. **Manajemen Sesi dan Keamanan (Session & Security)**:
   - **Regenerasi Session**: `request()->session()->regenerate()` dipanggil untuk mencegah serangan *Session Fixation*.
   - **Catatan Waktu**: Menyimpan `last_activity_at` ke dalam sesi pengguna saat ini.
   - **Anti-Multi Login**: Sistem menyimpan ID Sesi terbaru di Cache (`Cache::forever('active_session_user_'. $user->id, session()->getId())`) untuk mengunci sesi sehingga satu akun hanya dapat aktif di satu perangkat/browser pada saat yang bersamaan.
   - **Update Status Online**: Meng-update kolom `last_seen_at` pada tabel `users` menjadi waktu sekarang (`now()`) agar pengguna berstatus "Online".

7. **Pencatatan Aktivitas (Audit Trail)**:
   - Memanggil `ActivityLog::record('LOGIN', "Login berhasil — Role: {$user->role}")` untuk merekam rekam jejak bahwa pengguna tersebut telah berhasil login ke dalam sistem.

8. **Pengalihan (Redirection)**:
   - Sistem mengarahkan pengguna (`redirect()->intended(...)`) ke halaman tujuan sebelumnya jika ada, atau mengarahkannya secara default ke rute `dashboard` (`/dashboard`).

### Alur Otorisasi HTTP Request (Middleware)
Setelah berhasil login, setiap HTTP request ke rute sistem akan divalidasi oleh:
- **Middleware `auth`**: Memastikan request memiliki session valid.
- **Middleware `role`**: Mencocokkan kolom `role` (pemilik/admin/kasir) pada tabel `users` dengan parameter hak akses route yang dituju (misal `middleware(['role:pemilik,admin'])`). Jika tidak cocok, akses ditolak (403 Forbidden).

## 3. Alur Dashboard (Halaman Utama)
Setelah proses login berhasil, pengguna akan dialihkan melalui perintah `redirect()->intended(route('dashboard'))` menuju ke rute `/dashboard`. Halaman ini merupakan pusat kendali yang menampilkan ringkasan data operasional secara _real-time_. Alur teknis pengambilan datanya adalah sebagai berikut:

### 3.1. Pintu Masuk Rute (`routes/web.php`)
Sistem pertama kali akan mencari definisi rute di dalam file `routes/web.php`. Rute `/dashboard` dibungkus oleh **middleware `auth`** untuk memastikan pengguna memiliki sesi login yang valid. Jika valid, permintaan diteruskan ke `DashboardController` pada fungsi `index()`.

### 3.2. Otak Pemroses Data (`app/Http/Controllers/DashboardController.php`)
File ini melakukan pemrosesan tingkat berat (_Heavy Lifting_) saat halaman dimuat. Fungsi `index()` berinteraksi dengan berbagai Model (seperti `Transaction`, `Customer`, `Product`) untuk menarik data:

1. **Pendeteksian Peran (Role Scoping)**: 
   Sistem mengecek peran pengguna (`auth()->user()->role`). **Proteksi Akses**: Jika pengguna adalah **Kasir**, sistem akan memanipulasi kueri database dengan menambahkan `where('cashier_id', $user->id)` sehingga Kasir hanya melihat omzet dan riwayat dari transaksinya sendiri. Jika **Pemilik/Admin**, mereka dapat melihat data keseluruhan.
2. **Kalkulasi Kartu Statistik (Stats Cards)**:
   - **Penjualan & Transaksi Hari Ini**: Melakukan kalkulasi `sum('total_price')` dan `count()` pada tabel `transactions` yang lunas.
   - **Total Member**: Menghitung jumlah keseluruhan pelanggan dari tabel `customers`.
   - **Monitor Stok**: Melakukan kueri ke tabel `products` untuk mencari jumlah stok habis (`emptyStockCount`) atau stok menipis di bawah batas minimum (`lowStockCount`).
3. **Agregasi Visualisasi Grafik (Charts)**:
   - **Tren Pendapatan Harian**: Melakukan perulangan mundur 7 hari terakhir untuk membuat struktur data Array (`chartLabels`, `chartValues`) bagi grafik batang.
   - **Komposisi Tier Pelanggan**: Menggunakan _raw query_ (`selectRaw('tier, COUNT(*) as total')`) yang dikelompokkan berdasarkan field `tier`.
4. **Kalkulasi Analitik Eksklusif (Khusus Pemilik & Admin)**:
   - **Laba Kotor / Profit (Khusus Pemilik)**: Operasi berat yang menggabungkan (`JOIN`) tabel `transaction_details`, `transactions`, dan `products`. Laba kotor dihitung menggunakan rumus SQL `SUM((Harga Jual Akhir - Harga Pokok Penjualan/HPP) * Kuantitas)` yang dieksekusi langsung pada layer Database.
   - **Produk Terlaris (Trending)**: Mengelompokkan riwayat terjual per produk dan mencari akumulasi `qty` tertinggi di bulan berjalan.
   - **Performa Kasir Harian**: Melakukan join antara transaksi lunas hari ini dengan tabel pengguna (`users`) untuk merekap omzet dan jumlah transaksi masing-masing kasir.
5. **Mengirim Data ke Tampilan**: Pada tahap akhir, semua hasil perhitungan dibungkus menggunakan `compact(...)` dan disuntikkan ke template Blade `dashboard.index`.

### 3.3. Merender Tampilan Antarmuka (`resources/views/dashboard/index.blade.php`)
View ini bertugas menerima puluhan variabel data dari Controller dan merendernya menjadi antarmuka HTML/CSS visual.
- **Pengkondisian Blade (`@if`)**: Digunakan untuk menyembunyikan elemen visual. Misalnya, Kasir hanya melihat kartu pendapatan sederhana, sedangkan Pemilik melihat kartu lengkap beserta analitik laba kotor.
- **Tabel Transaksi (`@forelse`)**: Melooping baris data riwayat transaksi terbaru dan merendernya menjadi struktur tabel HTML.
- **Grafik Interaktif (Chart.js)**: Blok `<script>` JavaScript di paling bawah view akan menerima parameter Array dari PHP (di-_parse_ menggunakan sintaks `@json(...)`) dan menggambar elemen kanvas visual untuk _Revenue Chart_, _Tier Chart_, dan _Trending Chart_.

## 4. Alur Transaksi Kasir

### 4.1. Point of Sale (Kasir & Transaksi)
Ini adalah inti sistem di mana interaksi transaksi bisnis terjadi. Halaman Kasir bertindak layaknya aplikasi mandiri (_Single Page Application_) di mana banyak interaksi terjadi di latar belakang secara *Real-Time* menggunakan **JavaScript (AJAX)**. Berikut adalah aliran teknis yang terjadi secara berurutan:

1. **Pintu Masuk Rute & Mempersiapkan Layar (`GET /kasir`)**: 
   - Rute di `routes/web.php` menangkap permintaan `/kasir` dan memvalidasi _middleware_ `auth` serta `role:pemilik,admin,kasir`.
   - Permintaan diteruskan ke `TransactionController@create`.
   - Controller tidak memuat semua produk, melainkan hanya menyiapkan aturan dasar dengan menarik memori *State* Aturan Membership (`MembershipRule::getCurrent()`).
   - Jika kasir baru saja mendaftarkan member baru, data pelanggan disuntikkan ke tampilan via _Session_ (`pos_new_customer_id`).
   - Merender antarmuka dari file `resources/views/kasir/pos.blade.php`.

2. **Tampilan Antarmuka dan Logika Latar Belakang (AJAX & API)**:
   File `pos.blade.php` memiliki ratusan baris kode JavaScript untuk menangani interaksi pengguna tanpa me-_reload_ halaman.
   - **Pencarian Produk**: Saat kasir mengetik, JavaScript memanggil `GET /products/search` secara dinamis. Di latar belakang, `ProductController@search` menarik data dari database.
   - **Pencarian Member**: Memanggil `GET /customers/search` yang kemudian ditangani oleh `CustomerController@search`.
   - **Sinkronisasi Harga Real-Time (`POST /api/price-check`)**: Setiap item yang diklik masuk ke keranjang akan memicu request API ke `TransactionController@priceCheck`. Fungsi ini memanggil `RuleBasedMembershipService` (Otak algoritma promo dan diskon). Service mengecek apakah ada promo aktif atau diskon tier member, lalu mengembalikan `final_price` dalam format JSON.
   - **Pengecekan Harga Pokok (HPP Guard)**: JavaScript di frontend otomatis membandingkan Harga Final dengan Harga Modal (HPP). Jika rugi, peringatan kuning kemerahan akan muncul meminta otorisasi Admin/Pemilik.
   - **Rekaman Draft Transaksi (`POST /kasir/log-draft`)**: Jika transaksi ditunda (Postpone), frontend mengirim log aktivitas via AJAX agar terekam di sistem (`ActivityLog`).

3. **Proses Eksekusi Pembayaran (`POST /kasir`)**:
   Setelah kasir menekan "Proses Pembayaran", data keranjang di-_submit_ ke `TransactionController@store`. Tahap krusial ini dibungkus dalam **`DB::beginTransaction()`** agar terhindar dari anomali data (jika 1 gagal, _Rollback_ semua).
   
   - **Cek Otorisasi Harga Final**: Sistem memvalidasi ulang `itemsBelowHpp`. Kasir diwajibkan memasukkan password Admin/Pemilik ke sistem secara _prompt_ jika belum diotorisasi.
   - **Kalkulasi Subtotal & Poin**: Menghitung pemotongan subtotal berdasarkan Redeem Poin milik member (`calculateRedeem`).
   - **Simpan Induk Transaksi**: Data dimasukkan ke tabel `transactions` (`payment_status = 'paid'`).
   - **Pemotongan Stok Fisik Sistem**: Mengeksekusi pengurangan stok ganda: pada tabel master `products` dan pada `warehouse_stocks` yang spesifik ditandai sebagai gudang "Toko" (`is_store = true`).
   - **Eksekusi Reward Member**: Memanggil `applyAfterTransaction` untuk menambah akumulasi omzet member, _point_balance_, dan mengevaluasi "Kenaikan Level" (Tier Upgrade) otomatis.
   - **Menutup Transaksi**: Menjalankan *Commit DB* (`DB::commit()`) dan mencatat Log Sistem.

4. **Halaman Sukses & Pembuatan Struk (`GET /kasir/receipt/{transaction}`)**:
   - Sistem me-redirect ke halaman nota pembelian.
   - Secara _Back-end_, Controller menyusun _String Format Message_ yang telah di-_UrlEncode_ dengan sangat terperinci (memuat info toko, daftar item belanja, subtotal diskon, histori pengurangan poin, saldo tier terakhir) untuk disuntikkan ke tombol *Kirim Struk via WhatsApp*.
   - Kasir mencetak nota fisik via *Thermal Printer* dan/atau mengirim struk digital.

### 4.2. Riwayat Transaksi & Manajemen Nota (History & Void)
Halaman ini berfungsi layaknya "Buku Besar" digital yang menampung ribuan jejak operasional kasir untuk direkam, diaudit, dan dikoreksi. Alur sistem didesain agar tetap ringan saat menangani data besar:

1. **Pintu Masuk Rute & Pengambilan Data (`GET /kasir/history`)**:
   - Rute ini diteruskan ke `TransactionController@index`.
   - **Eager Loading**: Controller tidak mengambil data satu per satu, melainkan menggunakan `Transaction::with(['customer', 'cashier'])`. Perintah ini menarik tabel transaksi sekaligus merelasikannya dengan tabel pengguna/member secara serentak untuk mencegah *server* melambat akibat _N+1 Query_.
   - **Filter Kompleks**: Sistem mengecek parameter dari URL (tanggal, status lunas/void, pencarian) dan mempersempit kueri database secara dinamis.
   - **Custom Sub-query Sorting**: Saat _user_ mengurutkan data dari antarmuka (misal urut nama kasir A-Z), sistem tidak menggunakan operasi JOIN konvensional yang memakan memori, melainkan menginjeksi _Eloquent Sub-query_ (`User::select('name')->whereColumn(...)`) ke dalam `orderBy`.
   - **Pembagian Halaman (Pagination)**: Data dieksekusi dengan perintah `paginate()` agar layar hanya merender data per blok (misal 20 baris per halaman) alih-alih menarik puluhan ribu data sekaligus.

2. **Tampilan Antarmuka dan Kecerdasan Frontend (`resources/views/kasir/history.blade.php`)**:
   - **Pengkondisian Hak Akses (Role Guard)**: Pada tabel antarmuka, file view ini secara cerdas menggunakan `@if` untuk menyembunyikan tombol **Edit Nota** dan **Hapus (Void)**. Hanya pengguna dengan peran `pemilik` yang bisa melihat tombol tersebut.
   - **Pencarian Cepat Tanpa Reload (AJAX Live Search)**: JavaScript ditanamkan di bagian bawah halaman ini. Ketika pengguna mengetik nomor nota di kotak pencarian, halaman **tidak akan di-reload**. JavaScript mengirim request secara diam-diam, menarik potongan tabel HTML baru dari server, dan menimpanya ke tabel lama dalam hitungan milidetik. Ini membuat pelacakan nota sangat instan tanpa membebani browser.

3. **Detail Nota (View Receipt Detail)**:
   - Masuk ke rute `show()`. Tabel anak (detail barang, produk, harga beli saat transaksi) ditarik sekaligus secara instan dari database.

3. **Revisi / Edit Transaksi (Khusus Pemilik)**:
   Sistem mengakomodasi human-error kasir dengan mekanisme perbaikan data (`PUT /kasir/history/{id}`) yang sangat berhati-hati:
   - **Proteksi Hard-code**: Di level controller, akses divalidasi keras. Kasir / Admin akan dilempar ke halaman error.
   - **Validasi Integritas Poin (Lock Terakhir)**: Jika nota mengandung ID Member, sistem memblokir revisi jika nota tersebut **bukan transaksi terakhir** dari member itu. Memodifikasi nota lama akan mengacaukan perhitungan kronologis *Point* dan *Tier Upgrade*.
   - **Reversal Effect (Pemulihan Transaksi Lama)**: Ini adalah teknik andalan pada sistem ini. Sebelum menimpa data, controller memanggil fungsi _private_ `reverseTransactionEffects()`:
     - Barang fisik dikembalikan kembali ke gudang toko (`+ qty`).
     - Saldo poin yang ditarik dari member di-refund, sedangkan poin *reward* yang sempat diberikan langsung dipotong.
     - Catatan histori khusus dibuat di tabel `customer_tier_histories` bertanda `Transaction Revision`.
   - Setelah sistem disterilkan secara efek samping, keranjang (items) yang baru dikalkulasi ulang untuk menghasilkan Subtotal, diskon, poin _reward_, dan pemotongan stok aktual layaknya proses transaksi normal (termasuk *HPP Guard*).
   - Proses ini seluruhnya dikunci di dalam `DB::beginTransaction()` untuk atomisitas.

4. **Penghapusan Logikal / VOID (`DELETE /kasir/history/{id}`)**:
   - Fitur hapus nota yang hanya bisa diakses Pemilik.
   - Mekanismenya tidak menjalankan SQL `DELETE` sama sekali. Sistem hanya men-trigger *Reversal Effect* (stok & poin kembali seperti sebelum belanja).
   - Kolom tabel `transactions` dimodifikasi statusnya menjadi `payment_status = 'void'`.
   - Kolom `notes` otomatis ditimpa/ditambah awalan teks `[VOID] Alasan: ...` sebagai bukti audit untuk laporan _Activity Log_.

## 5. Alur Data Master

### 5.1. Data Produk (`ProductController`)
Halaman Data Produk (`GET /products`) adalah antarmuka katalog lengkap. Berikut eksekusi teknisnya:
1. **Menampilkan Katalog (Index & Pencarian)**: 
   - Controller melakukan _Eager Load_ stok spesifik di setiap gudang (`with('warehouseStocks.warehouse')`).
   - Sistem merender daftar _Dropdown Filter_ Kategori dan Satuan secara dinamis menggunakan perintah SQL `SELECT DISTINCT` (kategori dan satuan di aplikasi ini menggunakan teks bebas / _free text_).
2. **Tambah Produk Baru (`POST /products`)**:
   - Dapat dilakukan oleh Pemilik dan Admin.
   - **Validasi Spesifik**: Nama produk divalidasi silang untuk mencegah duplikat ganda menggunakan perbandingan _case-insensitive_ `LOWER()`.
   - **Limitasi Harga (Admin)**: Jika yang menginput adalah Admin, sistem akan secara _hardcode_ merekayasa input Harga Jual (`selling_price`) dan Modal (`hpp`) menjadi `0`. Menentukan dan mengedit nilai moneter adalah hak mutlak Pemilik.
   - **Auto-Generate SKU**: Setelah row terbentuk, sistem meracik kode unik via `generateProductCode()`. Misal kategori "PUPUK", ID produk "15", menghasilkan SKU `PUPU-0015`.
   - **Inisialisasi Stok Toko**: Jika isian "stok awal" lebih dari 0, sistem otomatis menanamkan histori awal (_seed_) ke tabel `warehouse_stocks` yang merujuk pada gudang dengan atribut `is_store = true`. Keseluruhan *query* dibungkus dalam `DB::beginTransaction()`.
3. **Pembaruan Massal (Mass-Update Kategori & Satuan)**:
   - Pemilik bisa mengubah penamaan Kategori/Satuan. Sistem memicu `Product::where('category', $old)->update(['category' => $new])` untuk merombak nama kategori pada seluruh produk terkait secara instan tanpa iterasi loop.
   - Jika kategori dihapus (`destroyCategory`), produk-produk terkait **tidak akan ikut terhapus**. Sistem memindahkan golongannya secara otomatis (_fallback_) ke kategori bernama `LAIN-LAIN`. Satuan di-_fallback_ ke `PCS`.
4. **Edit & Hapus Produk (Khusus Pemilik)**:
   - **Audit Keamanan Keterkaitan (Constraint Guard)**: Saat Pemilik menekan hapus (`DELETE /products/{id}`), Controller memvalidasi keberadaannya di tabel `transaction_details` dan `purchase_details`. Jika produk pernah dijual atau dibeli dari supplier **walau hanya sekali**, aksi _Delete_ **ditolak** demi menjaga relasi _Foreign Key_ pada riwayat keuangan/nota lama.
   - Sebagai ganti dari penghapusan fisik, sistem mempraktikkan _Soft-deactivation_ (merubah `is_active = false`). Produk yang dinonaktifkan akan disembunyikan dari kolom pencarian Kasir, namun tetap dapat dianalisis di laporan lama.
5. **Detail Histori Pergerakan (Show)**:
   - Rute `GET /products/{id}` mengumpulkan semua siklus hidup barang tersebut secara agresif. Semua jejak pembelian restok dari supplier (`purchaseDetails`), penjualan oleh kasir (`transactionDetails`), dan manipulasi stok manual (`stockAdjustments`) dirender dalam satu halaman untuk audit trail pergerakan stok dari hulu ke hilir.

### 5.2. Data Pelanggan (Member) - `CustomerController`
- **Path**: `GET /customers` (Daftar), `POST /customers` (Tambah).
- **Mekanisme**: Setiap pelanggan memiliki atribut `point_balance`, `total_accumulation`, dan `tier`. Saat kasir mendaftarkan member baru, datanya langsung terlempar ke antarmuka POS (`pos_new_customer_id` di _Session_). 
- Rute `GET /customers/{id}` akan menarik _Eager Load_ seluruh riwayat pembelanjaan (`transactions`) dan riwayat perubahan poin (`point_histories`), sehingga Pemilik bisa menganalisis loyalitas pelanggan.

### 5.3. Data Supplier - `SupplierController`
- **Path**: Rute Resource `GET /suppliers` hingga `DELETE /suppliers/{id}`.
- **Mekanisme**: Entitas Supplier menjadi *Foreign Key* wajib pada saat Admin melakukan transaksi _Pembelian / Restock_. Penghapusan supplier akan ditolak jika supplier tersebut sudah pernah terikat di tabel `purchases`.

### 5.4. Data Gudang (Warehouse) - `WarehouseController`
- **Path**: Rute Resource `GET /warehouses`.
- **Mekanisme**: Data lokasi gudang membedakan persediaan fisik. Gudang memiliki properti unik `is_store` (bernilai `true/false`). Gudang dengan `is_store = true` diartikan sebagai *Etalase Kasir*, yang stoknya akan otomatis terpotong saat terjadi transaksi di menu POS.

## 6. Alur Manajemen Inventaris (Stok)
Untuk menjaga keakuratan stok fisik dari kecurangan atau kelalaian, aliran inventaris menggunakan sistem **Validasi Multi-Layer**:

### 6.1. Pembelian & Restock Barang (`PurchaseController`)
- **Pembuatan Draft**: Admin membuka `GET /purchases/create` dan memasukkan daftar barang yang dibeli dari Supplier. Data disimpan ke `purchases` dan `purchase_details` dengan `status = pending`. **Stok sistem belum bertambah**.
- **Mekanisme Approval (Otorisasi Pemilik)**: Pemilik me-review nota fisik, lalu menekan tombol Approve (`POST /purchases/{id}/approve`). Di titik inilah `DB::beginTransaction()` berjalan. Sistem melakukan iterasi ke setiap `purchase_details`, mengeksekusi operasi `+ qty` ke tabel `warehouse_stocks` pada gudang yang dituju dan master tabel `products`. Status berubah menjadi `approved` dan dikunci.

### 6.2. Pemindahan Stok Antar Gudang (`StockTransferController`)
- **Mekanisme**: `POST /stock-transfers`. Kasus penggunaan: Memindahkan stok dari *Gudang Belakang* ke *Gudang Etalase (is_store = true)*.
- Algoritma melakukan *Double Query Update*: (1) Mengurangi stok dari `warehouse_id` asal (`- qty`), (2) Menambah stok ke `warehouse_id` tujuan (`+ qty`). Transaksi dibungkus dalam *DB Transaction* untuk mencegah duplikasi atau kehilangan barang.

### 6.3. Stock Opname / Penyesuaian Manual (`StockController`)
- **Mekanisme (Draft vs Approved)**: `POST /stock`. Terjadi jika ada selisih stok (hilang/rusak/kedaluwarsa).
- Jika yang melakukan Opname adalah **Admin**, data masuk ke tabel `stock_adjustments` dengan status `draft`. Stok fisik di sistem **belum berubah sama sekali**.
- Jika yang melakukan Opname adalah **Pemilik** (atau saat Pemilik menekan tombol `Approve` pada draft Admin via `POST /stock/{date}/{warehouse_id}/approve`), sistem mengubah status menjadi `approved`, lalu menimpa (`override`) kolom `stock` aktual di `warehouse_stocks`.
- Setiap perbedaan angka/selisih (Minus/Surplus) dari sistem vs aktual diwajibkan menyertakan Catatan (Notes) sebagai bukti rekam jejak.

## 7. Alur Marketing & Membership

### 7.1. Diskon & Promo Event - `PromotionController`
- **Path**: Rute Resource `GET /promotions`.
- **Mekanisme**: Admin/Pemilik mengatur jadwal rilis (Start/End Date). Promo ini bersifat pasif hingga ditarik oleh rute `API /price-check` di halaman POS. Jika hari itu berada di dalam rentang Promo, harga _checkout_ otomatis dipotong _Discount Amount_ atau persentase promo tersebut.

### 7.2. Pengaturan Membership - `MembershipRuleController`
- **Path**: `GET /membership` & `PUT /membership`. (Khusus Pemilik).
- **Mekanisme Parameter Global**: Menyimpan parameter sistem di tabel `membership_rules`. Data ini mengunci variabel algoritmik sistem secara global.
- **Kalkulasi Algoritma (`RuleBasedMembershipService`)**:
  Di latar belakang modul Kasir POS, terdapat _Service_ khusus untuk menyelesaikan angka akhir tagihan secara matematis:
  1. **Logika Harga Akhir (`resolvePricing`)**: 
     - JIKA produk memiliki Promo Aktif $\rightarrow$ `Harga = Harga Normal - Nominal Promo`. (Diskon member **hangus** agar tidak ada penumpukan diskon/_double discount_).
     - JIKA BUKAN promo dan Pelanggan adalah Member $\rightarrow$ `Harga = Harga Normal * (1 - Diskon Tier%)`.
  2. **Logika Perolehan Poin**: Poin didapat dengan rumus `Floor(Total Belanja / Poin Per Nominal)`.
  3. **Logika Kenaikan Tier Otomatis (`evaluateTier`)**: Setelah tagihan lunas, sistem merekap akumulasi belanja (`total_accumulation`). Jika akumulasi sepanjang masa pelanggan melampaui `tier_gold_min`, maka ia otomatis "Naik Level" ke tier _Gold_ secara *real-time* bulan itu juga. Perubahan historis ini direkam ke `customer_tier_histories`.

## 8. Alur Laporan & Analitik
Seluruh aktivitas hulu-hilir bermuara pada laporan akhir untuk dievaluasi oleh Pemilik Toko (`ReportController`):

### 8.1. Laporan Penjualan (`GET /reports/sales`) 
- Sistem menarik data `transactions` yang berstatus `paid`.
- Melakukan metode agregasi Eloquent (`groupBy` per tanggal, per bulan, per kasir). 
- Controller melakukan _Passing_ data grafik secara komprehensif ke _View_ untuk menampilkan peta omzet harian.

### 8.2. Laporan Profit / Laba Kotor (`GET /reports/profit`) 
- **Mekanisme Khusus Pemilik**: Merupakan proses terberat karena sistem mengeksekusi operasi `JOIN` antara tabel `transactions`, `transaction_details`, dan `products`.
- SQL melakukan iterasi per-row algoritma `SUM((td.final_unit_price - p.hpp) * td.qty)`. Jika barang terjual mahal, profit membesar. Jika HPP naik, margin menyusut. Data _pure_ SQL ini memberikan representasi profit riil kepada _Owner_.

## 9. Pengaturan Sistem

### 9.1. Pengguna Sistem (Karyawan) - `UserController`
- **Path**: `GET /users`. Hak akses mutlak **Hanya Pemilik**.
- **Mekanisme**: Pemilik mengatur kredensial _login_ (Username/Email & Password yang di-Bcrypt Hash). Role ditentukan di sini (`kasir`, `admin`, `pemilik`). Terdapat _toggle_ `is_active` untuk memutus akses _login_ karyawan yang sudah _resign_ (sehingga _soft-deleted_ dan histori kasir mereka tidak hilang).

### 9.2. Log Aktivitas Sistem / Audit Trail (`GET /reports/activity-logs`) 
- **Mekanisme Keamanan**: Menggunakan _Event Logging_ di mana di setiap tindakan krusial (Tambah Barang, Login, Approve Stok, Void Nota, Penjualan bawah HPP) Model `ActivityLog::record(...)` disisipkan ke dalam baris kode.
- Halaman ini membaca tabel `activity_logs` di mana setiap tindakan mencatat: **Siapa** yang melakukan (Relasi `users`), **Tindakan Apa** (Misal: `DELETE_PRODUCT`), **Kapan** (`created_at`), dan **Keterangan Detail** (Misal: "Menghapus kategori PUPUK ke LAIN-LAIN"). Data ini menjadi alat utama untuk membongkar kecurangan atau kelalaian karyawan.

## 10. Ringkasan Eksekusi Siklus (End-to-End Diagram)
1. **Inisialisasi**: Pemilik mengonfigurasi Karyawan, menyetel *Rules* Membership, dan membuat Gudang Etalase.
2. **Pengisian Daya (Supply)**: Admin me-registrasi Produk Master, mendaftarkan Supplier, membuat draft Restock, dan **Pemilik meng-Approve** agar masuk ke _Warehouse Stock_.
3. **Pusat Interaksi (Kasir)**: Kasir menarik pelanggan dengan pendaftaran Member, menggunakan *Live Search* produk untuk checkout. Sistem mengalkulasi *Reward* dan memotong stok etalase.
4. **Koreksi (Adjustment)**: Jika ada barang hilang, dilakukan Stock Opname. Jika kasir salah tekan, Pemilik melakukan _VOID_ untuk mensterilkan _database_ kembali.
5. **Panen Data (Analytics)**: Melalui *Dashboard* dan *Reports*, Pemilik merumuskan strategi promosi bulan depan berdasar laporan Laba Kotor dan Aktivitas Karyawan.
