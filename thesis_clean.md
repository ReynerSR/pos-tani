__Pengembangan Sistem Point Of Sales \(POS\) Berbasis Web dengan Strategi Tiering Membership Menggunakan Metode Rule\-Based System pada UD\. Tani Agung Ngawi__

__	__

__TUGAS AKHIR__

Diajukan untuk memenuhi persyaratan penyelesaian program Strata 1 

 Program Studi Informatika  Fakultas Teknologi Industri 

Universitas Kristen Petra

Oleh:

Reyner Steven Roebiyanto	 NRP: C14220278

  

PROGRAM STUDI INFORMATIKA

__![](...)__

__FAKULTAS TEKNOLOGI INDUSTRI __

__UNIVERSITAS KRISTEN PETRA__

__SURABAYA__

__2026__

# <a id="_lcpki6ha2odj"></a>LEMBARAN PENGESAHAN

__TUGAS AKHIR__

__Pengembangan Sistem Point Of Sales \(POS\) Berbasis Web dengan Strategi Tiering Membership Menggunakan Metode Rule\-Based System pada UD\. Tani Agung Ngawi__

Oleh:

Reyner Steven Roebiyanto		NRP: C14220278

Diterima oleh:

Program Studi Informatika, Fakultas Teknologi Industri

Universitas Kristen Petra

Surabaya, <DD Month YYYY>

Dosen Pembimbing 1

Alexander Setiawan, S\.Kom\., M\.T\.

NIP: <NIP>

Ketua Tim Penguji

<Nama Penguji>

NIP: <NIP>

Ketua Program Studi

Adi Wibowo S\.T\., M\.T\., Ph\.D

NIP: <NIP>

# __LEMBARAN PERNYATAAN PERSETUJUAN PUBLIKASI__

# __KARYA ILMIAH UNTUK KEPENTINGAN AKADEMIS__

Sebagai mahasiswa Universitas Kristen Petra, yang bertanda tangan dibawah ini, saya : 

Nama		: Reyner Steven Roebiyanto

NRP		: C14220278

Demi pengembangan ilmu pengetahuan, menyetujui untuk memberikan kepada Universitas Kristen Petra Hak Bebas Royalti Non\-Eksklusif \(*Non\-Exclusive Royalty\-Free Right*\) atas karya ilmiah saya yang berjudul “Pengembangan Sistem Point Of Sales \(POS\) Berbasis Web dengan Strategi Tiering Membership Menggunakan Metode Rule\-Based System pada UD\. Tani Agung Ngawi”\. Dengan Hak Bebas Royalti Non\-Eksklusif ini Universitas Kristen Petra berhak menyimpan,mengalih\-mediakan/formatkan, mengelolanya dalam bentuk pangkalan data \(*database*\), mendistribusikannya dan menampilkan/mempublikasinya di internet atau media lain untuk kepentingan akademik tanpa perlu meminta izin dari saya selama tetap mencantumkan saya sebagai penulisnya

Saya bersedia untuk menanggung secara pribadi, tanpa melibatkan pihak Universitas Kristen Petra, segala bentuk tuntutan hukum yang timbul atas pelanggaran Hak Cipta dalam karya ilmiah saya ini\. 

Demikian pernyataan ini yang saya buat dengan sebenarnya\.

Surabaya, 6 Juni 2026

Yang menyatakan,

![](...)

							Reyner Steven Roebiyanto 

# <a id="_nm3n7mdjwuos"></a>LEMBAR *DISCLAIMER* PENGGUNAAN *ARTIFICIAL INTELLIGENCE*

Yang bertanda tangan dibawah ini:

Nama

:

Reyner Steven Roebiyanto

NRP

:

C14220278

Judul Karya

:

Pengembangan Sistem Point Of Sales \(POS\) Berbasis Web dengan Strategi Tiering Membership Menggunakan Metode Rule\-Based System pada UD\. Tani Agung Ngawi

Dalam penyusunan skripsi ini, saya menggunakan AI Gemini dan Claude untuk membantu merancang alur pemrograman pada Bab 4, khususnya dalam memodelkan metode Rule\-Based System berupa logika IF\-THEN untuk penentuan tingkatan keanggotaan \(tiering membership: Bronze, Silver, Gold\) berdasarkan akumulasi nominal transaksi belanja pelanggan, perhitungan dan penukaran poin loyalitas belanja \(redeem points\), resolusi harga \(pricing resolution\) produk promo guna menghindari penumpukan diskon ganda, serta mekanisme pengurangan stok fisik otomatis di gudang UD\. Tani Agung Ngawi; alur pemrograman tersebut kemudian saya optimalkan secara mandiri menggunakan framework Laravel dengan menerapkan database transaction dan fungsi audit transaksi void agar sistem POS ini memiliki keakuratan finansial dan konsistensi data yang tinggi\.

Surabaya, 01\-06\-2026

Yang menyatakan,

![](...)

							Reyner Steven Roebiyanto 

# <a id="_l39uhh9zrpgk"></a>KATA PENGANTAR

Segala puji syukur saya panjatkan ke hadirat Tuhan Yesus Kristus, karena hanya melalui kasih dan penyertaan\-Nya yang begitu luar biasa, perjalanan panjang dalam menyusun laporan Tugas Akhir yang berjudul “Pengembangan Sistem Point Of Sales \(POS\) Berbasis Web dengan Strategi Tiering Membership Menggunakan Metode Rule\-Based System pada UD\. Tani Agung Ngawi” ini akhirnya dapat terselesaikan dengan baik\. Laporan ini merupakan buah dari upaya saya untuk memberikan solusi nyata bagi efisiensi di dunia ritel, sekaligus memenuhi syarat akademis di Program Studi Informatika, Universitas Kristen Petra, Surabaya\.

Proses pengerjaan ini memberikan saya ruang untuk menyelami dinamika operasional toko secara mendalam dan merangkainya menjadi sebuah logika yang sistematis\. Pencapaian ini tentu tidak lepas dari dukungan tulus berbagai pihak yang telah membersamai saya, sehingga saya ingin mengucapkan terima kasih kepada:

1. Tuhan Yesus Kristus, sang sumber hikmat dan kekuatan, yang selalu memberikan kejernihan berpikir serta ketenangan di setiap tahapan pengerjaan Tugas Akhir ini\.
2. Keluarga tercinta, yang tidak pernah putus memberikan dukungan moral, semangat, serta doa yang menjadi pendorong utama bagi saya untuk terus melangkah hingga titik ini\.
3. Ibu Sianny Soesanto, selaku pemilik UD\. Tani Agung Ngawi, yang telah memberikan izin serta kepercayaan penuh kepada saya untuk melakukan penelitian di tempat beliau\. Terima kasih atas keterbukaan dan data\-data berharga yang telah diberikan sehingga sistem ini dapat dirancang dengan sangat relevan terhadap kebutuhan nyata di lapangan\.
4. Bapak Alexander Setiawan, S\.Kom\., M\.T\., selaku dosen pembimbing yang telah memberikan arahan yang sangat detail, kritik yang membangun, serta bimbingan yang mendalam\. Diskusi bersama beliau sangat membantu saya dalam mempertajam logika Rule\-Based System yang saya bangun\.
5. Ibu Liliana, S\.T\., M\.Eng\., Ph\.D\., selaku Koordinator Skripsi, serta seluruh bapak dan ibu dosen Program Studi Informatika yang telah membekali saya dengan ilmu dan wawasan yang sangat luas selama masa studi saya\.
6. Seluruh karyawan UD\. Tani Agung Ngawi, yang telah bersedia berbagi pengalaman mengenai dinamika transaksi harian di toko, sehingga saya dapat merancang sistem yang benar\-benar solutif bagi pengguna\.
7. Teman\-teman seperjuangan, serta semua pihak yang telah memberikan bantuan dalam bentuk apa pun yang tidak dapat saya sebutkan satu per satu\.

Laporan ini saya susun dengan upaya maksimal untuk memastikan setiap detailnya dapat dipertanggungjawabkan\. Saya menyadari masih ada ruang untuk perbaikan, sehingga saran dan kritik yang membangun akan saya terima dengan senang hati\. Semoga karya ini memberikan manfaat bagi kemajuan teknologi informasi di industri ritel serta bagi pembaca sekalian\.

Surabaya, 6 Juni 2026

Yang menyatakan,

![](...)

							Reyner Steven Roebiyanto 

# <a id="_gn61fct1k8d7"></a>ABSTRAK

# <a id="_ojior7gsd96n"></a>

Reyner Steven Roebiyanto  

Skripsi  

Pengembangan Sistem Point Of Sales \(POS\) Berbasis Web dengan Strategi Tiering Membership Menggunakan Metode Rule\-Based System pada UD\. Tani Agung Ngawi 

Operasional transaksi kasir di UD\. Tani Agung Ngawi saat ini terhambat oleh sistem Point of Sales \(POS\) desktop lama yang membatasi kapasitas penyimpanan, serta ketiadaan skema loyalitas otomatis yang menyebabkan lamanya waktu antrean akibat ketergantungan pada otorisasi diskon manual dari pemilik toko\. Penelitian ini bertujuan untuk mengembangkan Sistem Informasi POS terintegrasi berbasis web guna memulihkan kinerja operasional, sekaligus menerapkan metode Rule\-Based System untuk mengotomatisasi perhitungan poin dan penentuan status diskon berjenjang \(tiering\) secara real\-time\.

Sistem ini dirancang menggunakan Business Process Model and Notation \(BPMN\) beserta serangkaian diagram UML, yang kemudian dikembangkan menggunakan framework Laravel 12 dan database MySQL\. Fitur utama yang diimplementasikan mencakup modul transaksi POS interaktif, manajemen inventori dengan perhitungan Harga Pokok Pembelian \(HPP\) weighted average, serta modul loyalitas pelanggan berbasis logika IF\-THEN untuk menentukan tiering Bronze, Silver, dan Gold\.

Hasil penelitian ini berupa sistem informasi POS berbasis web yang terbukti mampu mempercepat waktu layanan transaksi, memusatkan penyimpanan data barang, serta mengotomatisasi pemberian poin dan diskon berjenjang secara akurat\. Implementasi sistem ini secara efektif berhasil menghilangkan ketergantungan kasir terhadap otorisasi manual pemilik toko, sehingga mampu menciptakan standar pelayanan yang jauh lebih otonom, konsisten, dan transparan bagi pelanggan\.

Kata Kunci: Sistem Informasi, Point of Sales, Rule\-Based System, Membership, Inventori\.

# <a id="_sk8nehjwyp8p"></a>ABSTRACT

Reyner Steven Roebiyanto  

Skripsi  

Web\-Based Point of Sales \(POS\) System Development with Tiering Membership Strategy Using Rule\-Based System Method at UD\. Tani Agung Ngawi\.

Cashier operations at UD\. Tani Agung Ngawi are currently hindered by a slow legacy desktop Point of Sales \(POS\) system and the lack of an automated loyalty scheme, which causes long queues due to cashiers' reliance on manual discount authorizations from the store owner\. This research aims to develop an integrated web\-based POS Information System to restore operational performance and implement a Rule\-Based System method to automate point calculations and tiered discount status \(tiering\) in real\-time\.

The system was designed using Business Process Model and Notation \(BPMN\) alongside several UML diagrams, and subsequently developed utilizing the Laravel 12 framework and a MySQL database\. Its main features include an interactive POS transaction module, inventory management with weighted average Cost of Goods Purchased \(HPP\) calculation, and a customer loyalty module based on IF\-THEN logic for determining Bronze, Silver, and Gold tiers\.

The result of this research is a web\-based POS information system that accelerates transaction service times, centralizes item data storage, and accurately automates the provision of reward points and tiered discounts\. The implementation of this system effectively eliminates the cashiers' reliance on the store owner's manual authorization, thereby creating a much more autonomous, consistent, and transparent service standard for customers\.

Keywords: Information System, Point of Sales, Rule\-Based System, Membership, Inventory\.

# <a id="_yi3a0rwa3v41"></a>DAFTAR ISI

# <a id="_68nmjlu5m3ah"></a>

[__LEMBARAN PENGESAHAN	2__](#_lcpki6ha2odj)

__LEMBARAN PERNYATAAN PERSETUJUAN PUBLIKASI	3__

__KARYA ILMIAH UNTUK KEPENTINGAN AKADEMIS	3__

[__LEMBAR DISCLAIMER PENGGUNAAN ARTIFICIAL INTELLIGENCE	4__](#_nm3n7mdjwuos)

[__KATA PENGANTAR	5__](#_l39uhh9zrpgk)

[__ABSTRAK	7__](#_gn61fct1k8d7)

[__ABSTRACT	8__](#_sk8nehjwyp8p)

[__DAFTAR ISI	9__](#_yi3a0rwa3v41)

[__DAFTAR TABEL	11__](#_6cv6ya4vzgfv)

[__DAFTAR GAMBAR	12__](#_or0p9xn3lqvh)

[__DAFTAR LAMPIRAN	13__](#_1zebvyae8w5)

[__1\. PENDAHULUAN	1__](#_jtrrobx3srdl)

[1\.1\. Latar Belakang	1](#_pftt7760jksu)

[1\.2\. Rumusan Masalah	2](#_m7kufhrs05lp)

[1\.3\. Tujuan	3](#_9czef1jl7m7e)

[1\.4\. Ruang Lingkup	3](#_nd70or584jg0)

[__2\. METODOLOGI PENELITIAN	8__](#_io55vuvcn7a8)

[2\.1\. Studi Literatur	8](#_qta84l7spd72)

[2\.1\.1\. Rancang Bangun Sistem Informasi POS Berbasis Web \(Mulyana & Rusmawan, 2023\)	8](#_geqmbu3iskzf)

[2\.1\.2\. Implementasi Metode Rule\-Based pada Aplikasi Antri Pintar \(Sugiarto, Swanjaya, & Wulaningrum, 2022\)	8](#_weg4066fwy89)

[2\.1\.3\. Perancangan Aplikasi Membership Gym Berbasis Web \(Sururi, Thoib, & Nugraha, 2025\)	9](#_rrmhngrap5eq)

[2\.1\.4\. Posisi Penelitian \(Research Gap & Novelty\)	9](#_dcxim1s6f0ld)

[2\.2\. Analisis Kebutuhan Sistem dan Pengumpulan Data	10](#_a43fdcjic6mr)

[2\.3\. Perancangan Sistem	10](#_jpqjgxakqvtl)

[2\.4 Pengujian dan Evaluasi Sistem	11](#_1f1dei9nyst4)

[2\.5 Penyusunan Laporan	11](#_qefygppcgx2x)

[__3\. ANALISIS DAN DESAIN SISTEM	12__](#_u603ls9lusi8)

[3\.1\. Analisis	12](#_orva8ggxs27z)

[3\.1\.1 Analisis Permasalahan	12](#_d456fojnutd5)

[3\.1\.2 Analisis Kebutuhan Non Fungsional	12](#_5amnb0gl907b)

[3\.1\.3 Analisis Kebutuhan Fungsional	12](#_aaw9j2f9xu0v)

[3\.1\.4 Analisis Kebutuhan Non Fungsional	13](#_5ab8ug8jf6ft)

[3\.2 Desain Sistem	13](#_1yj9iax4cs1g)

[3\.2\.1 Blok Diagram Sistem	14](#_sg9pvy1v8lzz)

[3\.2\.2 Use case Diagram	16](#_q98sj9vr80ik)

[3\.2\.3 Activity Diagram	17](#_lamhlpbnj4ju)

[3\.2\.4 Entity Diagram Relationship \(ERD\)	28](#_wu8axgw92231)

[3\.2\.5 BPMN, Alur Proses Bisnis Sistem POS UD\. Tani Agung Ngawi	31](#_yg7m9sntf80r)

[3\.2\.6 Pengolahan Data dan Metode yang Digunakan	32](#_f5hw45morydt)

[__4\. PENGUJIAN	33__](#_z9a44k979z20)

[4\.1 Pengujian Dampak Penerapan Sistem Terhadap Efisiensi Waktu Operasional	33](#_t8suebcz09wy)

[4\.2 Pengujian Kemampuan Sistem dalam Mengatasi Keterbatasan Kapasitas Data	35](#_7t5d7tunt6ns)

[4\.3 Pengujian Validasi Logika Rule\-Based System dan Integritas Transaksi	37](#_h7z8y885739p)

[4\.4 Pengujian Fungsionalitas \(Black Box Testing\)	39](#_822ykhktyjzq)

[4\.5 Pengujian Penerimaan Pengguna \(User Acceptance Testing / UAT\)	42](#_44283l5424dz)

[__5\. KESIMPULAN DAN SARAN	47__](#_cto1qns30fvz)

[5\.1 Kesimpulan	47](#_11gzuqwe6szq)

[__DAFTAR REFERENSI	49__](#_v3lmi8s8naao)

# <a id="_xqhvfl5xw5dv"></a>

# <a id="_6cv6ya4vzgfv"></a>__DAFTAR TABEL__

[Tabel 4\.1 Perbandingan Efisiensi Waktu Transaksi Kasir	33](#_aehfvy8q4mp2)

[Tabel 4\.2 Perbandingan Kemampuan Pengelolaan Data Produk	35](#_i3t9iik3nri9)

[Tabel 4\.3 Hasil Pengujian Validasi Logika Rule\-Based System dan Integritas Transaksi	37](#_iuvdrx22yqao)

[Tabel 4\.4 Hasil Eksekusi Black Box Testing	39](#_d63ga7fen2y5)

[Tabel 4\.6 Hasil UAT, Pemilik Toko	42](#_d7fweogfx8e)

[Tabel 4\.7 Hasil UAT, Admin Operasional	44](#_o8tcdliguw1a)

[Tabel 4\.8 Hasil UAT, Kasir	45](#_gj692itndav1)

# <a id="_or0p9xn3lqvh"></a>__DAFTAR GAMBAR__

[Gambar 3\.1 Blok Diagram Sistem POS UD\. Tani Agung Ngawi	14](#_mcc3dbrx3scs)

[Gambar 3\.2 Use Case Diagram Sistem POS UD\. Tani Agung Ngawi	16](#_nnzf2t542zdv)

[Gambar 3\.3 Activity Diagram UC\-01: Login Sistem	18](#_rgsp63guwumt)

[Gambar 3\.4 Activity Diagram UC\-03: Kelola Master Produk	19](#_by6ug9egv7sb)

[Gambar 3\.5 Activity Diagram UC\-06: Konfigurasi Aturan Bisnis \(Rule\-Based System\)	20](#_5vd46jnijtvl)

[Gambar 3\.6 Activity Diagram UC\-10: Catat Pembelian \(Restocking\)	22](#_hi9k9tc4h2hr)

[Gambar 3\.7 Activity Diagram UC\-11: Stock Opname \(Penyesuaian Stok Fisik\)	23](#_suesbdaipoku)

[Gambar 3\.8 Activity Diagram UC\-14: Transaksi Penjualan \(Point of Sales\)	25](#_1vy56ziazib1)

[Gambar 3\.9 Activity Diagram UC\-Membership: Kalkulasi Poin & Evaluasi Tier \(Background Process\)	27](#_wn5795yywx4z)

[Gambar 3\.10 Entity Relationship Diagram Sistem POS UD\. Tani Agung Ngawi	28](#_6hvcbtgz4yw0)

[Gambar 3\.11 BPMN, Alur Proses Bisnis Sistem POS UD\. Tani Agung Ngawi	31](#_ix64hlx8k4k1)

# <a id="_zfegz8bi26wq"></a>

# <a id="_1zebvyae8w5"></a>__DAFTAR LAMPIRAN__

[Lampiran 1 : Source Code Program	50](#_nw375nw1fzac)

[Lampiran 2 : Dokumentasi Sistem	50](#_tufp4fl97k5h)

# <a id="_jtrrobx3srdl"></a>PENDAHULUAN

## <a id="_pftt7760jksu"></a>__Latar Belakang__

Perkembangan teknologi informasi telah membawa perubahan signifikan pada strategi bisnis ritel, di mana fokus utama kini beralih dari sekadar penjualan produk menjadi pelayanan dan retensi pelanggan\. Di industri perlengkapan pertanian, persaingan harga antar toko semakin ketat\. Konsumen saat ini memiliki biaya perpindahan \(switching cost\) yang rendah, mereka mudah beralih ke kompetitor jika tidak mendapatkan nilai tambah atau pelayanan yang cepat\. Oleh karena itu, strategi loyalitas pelanggan \(customer loyalty\) dan efisiensi pelayanan kasir menjadi kunci keberlangsungan bisnis\.

UD\. Tani Agung merupakan perusahaan dagang yang bergerak di bidang penjualan produk pertanian di Ngawi\. Saat ini, operasional penjualan di toko sebenarnya telah didukung oleh sistem Point of Sales \(POS\) berbasis desktop\. Namun, sistem warisan \(legacy system\) tersebut kini menjadi kendala utama karena teknologi yang digunakan sudah usang \(obsolete\)\. Berdasarkan observasi dan wawancara, sistem sering mengalami degradasi performa \(lambat / lemot\), kegagalan fungsi \(system hang\) saat jam sibuk, hingga keterbatasan kapasitas penyimpanan basis data yang menyebabkan kasir tidak dapat menambahkan data stok barang baru\. Kondisi infrastruktur lunak yang tidak lagi memadai ini mengakibatkan proses transaksi menjadi terhambat dan antrian pelanggan sering terjadi\.

Keterbatasan sistem lama juga sangat terasa pada aspek manajemen pelanggan\. Saat ini, fitur keanggotaan \(membership\) pada sistem hanya berfungsi sebagai penanda identitas sederhana untuk membedakan antara 'Pelanggan Umum' dan 'Member', tanpa adanya skema loyalitas yang terstandarisasi seperti poin atau promosi otomatis\. Keuntungan bagi member saat ini hanyalah berupa potongan harga \(diskon\) yang bersifat manual dan diskresioner\. Hal ini menciptakan kendala operasional yang serius: sistem tidak memberikan otorisasi otomatis kepada kasir untuk memberikan diskon\. Akibatnya, setiap kali melayani transaksi member, kasir harus menghentikan proses input untuk menghubungi pemilik toko secara langsung guna meminta persetujuan nominal potongan harga\. Ketergantungan penuh pada keputusan pemilik \(owner\-dependency\) ini tidak hanya menyebabkan waktu layanan menjadi lama dan antrian mengular, tetapi juga membuat standar pelayanan menjadi tidak konsisten karena bergantung pada ketersediaan pemilik saat itu\."

Untuk mengatasi masalah usangnya teknologi sistem lama dan kebutuhan akan strategi retensi pelanggan, penelitian ini mengusulkan Rancang Bangun Ulang \(Re\-engineering\) Sistem Informasi Point of Sales \(POS\) Terintegrasi dengan Manajemen Keanggotaan Berbasis Web\. Platform berbasis web dipilih sebagai solusi arsitektur untuk mengatasi masalah kinerja "berat" pada komputer kasir, karena beban pemrosesan akan dialihkan ke server\. Hal ini sejalan dengan penelitian Mulyana dan Rusmawan \(2023\), yang menyimpulkan bahwa implementasi sistem POS berbasis web mampu meningkatkan efisiensi transaksi, memudahkan pengelolaan stok, dan menghasilkan laporan yang lebih efektif dibandingkan sistem konvensional\. Selain pembaruan teknologi, sistem ini juga akan menerapkan metode Rule\-Based System \(Sistem Berbasis Aturan\) untuk otomatisasi manajemen loyalitas\. Pemilihan metode ini didasarkan pada efektivitasnya dalam menghasilkan keputusan yang konsisten dan otomatis, sebagaimana dibuktikan oleh penelitian Sugiarto, Swanjaya, dan Wulaningrum \(2022\), yang menunjukkan bahwa penerapan logika Rule\-Based mampu meningkatkan efisiensi alur pelayanan dan mengurangi ketergantungan pada intervensi manual\. Lebih lanjut, sistem ini juga dilengkapi dengan modul pengelolaan data pelanggan terpusat\. Urgensi fitur ini didukung oleh temuan Sururi, Thoib, dan Nugraha \(2025\), yang menyatakan bahwa penerapan sistem membership berbasis web terbukti signifikan dalam meningkatkan akurasi data anggota dan kualitas layanan pelanggan dibandingkan metode pengelolaan manual atau terpisah\.

Dengan pendekatan ini, mekanisme pemberian poin dan penentuan status anggota \(tiering\) akan dieksekusi secara otomatis oleh sistem berdasarkan aturan logika yang dinamis saat transaksi berlangsung, tanpa membebani kinerja operasional kasir\. Pembaruan sistem ini diharapkan dapat memulihkan stabilitas operasional transaksi \(mengatasi lag dan kapasitas\), menghilangkan input manual ganda, serta membangun fondasi data pelanggan baru yang valid dan terpusat mulai dari hari pertama implementasi\.

## <a id="_m7kufhrs05lp"></a>__Rumusan Masalah__

Berdasarkan latar belakang yang telah dipaparkan, rumusan masalah dalam penelitian ini adalah:

1. Seberapa besar pengurangan waktu yang diperoleh setelah penerapan Sistem Informasi Point of Sales \(POS\) berbasis Web yang terintegrasi untuk mengatasi kendala kinerja \(performance degradation\)?
2. Apakah Sistem Informasi Point of Sales \(POS\) dapat mengatasi kendala penuhnya kapasitas data barang pada sistem lama di UD\. Tani Agung?
3. Apakah implementasi metode Rule\-Based System pada fitur keanggotaan \(membership\) mampu mengotomatisasi pemberian poin dan diskon berjenjang secara akurat guna menghilangkan ketergantungan otorisasi manual pemilik toko pada saat transaksi?

## <a id="_9czef1jl7m7e"></a>__Tujuan__

Sejalan dengan rumusan masalah yang telah ditetapkan, tujuan dari penelitian Tugas Akhir ini dirancang untuk memberikan solusi yang komprehensif dan terukur terhadap kendala operasional yang ada, yaitu: \(1\) mengukur dan menganalisis besaran pengurangan waktu transaksi serta peningkatan efisiensi operasional yang diperoleh setelah penerapan Sistem Informasi Point of Sales \(POS\) berbasis Web yang terintegrasi, dengan tujuan utama untuk secara signifikan mengatasi kendala kinerja seperti performance degradation, kelambatan pemrosesan data \(lag\), maupun kegagalan fungsi \(system hang\) yang selama ini sering menghambat kecepatan pelayanan kasir pada jam\-jam sibuk di UD\. Tani Agung; \(2\) menguji dan mengevaluasi secara mendalam kemampuan arsitektur basis data relasional pada Sistem Informasi POS yang baru dalam mengatasi kendala penuhnya kapasitas penyimpanan data barang pada sistem legacy, sehingga proses pengelolaan, pembaruan, dan penambahan data master produk pertanian dapat berjalan dengan lancar, terpusat, dan berkesinambungan tanpa membebani memori lokal pada komputer kasir; dan \(3\) memvalidasi tingkat akurasi dari implementasi metode Rule\-Based System pada fitur keanggotaan \(membership\), guna memastikan bahwa sistem usulan mampu mengotomatisasi perhitungan pemberian poin reward dan penentuan status diskon berjenjang \(tiering seperti Bronze, Silver, dan Gold\) secara real\-time, yang pada akhirnya ditujukan untuk menghilangkan ketergantungan otorisasi manual dari pemilik toko secara penuh pada saat transaksi berlangsung serta menciptakan standar pelayanan yang lebih otonom, konsisten, dan transparan bagi para pelanggan\.

## <a id="_nd70or584jg0"></a>__Ruang Lingkup__

Berikut adalah batasan dalam pengerjaan tugas akhir ini:

1. Sistem dibangun berbasis web menggunakan framework Laravel 12 \(PHP\) dengan template engine Blade sebagai lapisan antarmuka pengguna\.
2. Menggunakan MySQL \(via XAMPP\) sebagai sistem manajemen basis data relasional\.
3. Penggunaan Client\-Side Scripting \(JavaScript & Chart\.js\) untuk interaktivitas layar kasir \(live search produk dan member, kalkulasi harga real\-time\) serta visualisasi grafik pada dashboard laporan\.

Batasan Sistem:

1. Sistem ini hanya menerapkan logika aturan pasti \(Rule\-Based System\), tidak menggunakan algoritma kecerdasan buatan \(AI\) atau forecasting prediktif\. Seluruh logika diskon, poin, dan penentuan tier dieksekusi melalui aturan IF\-THEN yang dapat dikonfigurasi oleh Pemilik Toko\.
2. Sistem tidak menangani siklus akuntansi penuh \(Jurnal, Buku Besar, Neraca, Pajak\)\. Modul keuangan dibatasi pada Laporan Penjualan dan Laporan Laba Kotor \(selisih Harga Jual Akhir dan HPP per item terjual\)\.
3. Pengelolaan stok berfokus pada pencatatan kuantitas mutasi \(barang masuk dari pembelian, barang keluar dari penjualan, dan penyesuaian dari stock opname\), tidak mencakup manajemen tanggal kedaluwarsa \(expiry date\) per batch barang\.
4. Pencarian barang di kasir dioptimalkan dengan fitur Live Search berdasarkan nama barang melalui permintaan AJAX ke server\.
5. Sistem memfasilitasi Harga Negosiasi \(manual price override\) yang dapat dilakukan oleh kasir per item di keranjang belanja, untuk mengakomodasi budaya tawar\-menawar dalam transaksi toko\.
6. Sistem mencakup manajemen multi\-gudang dengan fitur transfer stok antar lokasi\. Sistem memiliki modul membership berbasis Rule\-Based \(poin & tier otomatis\) dan manajemen promosi produk\.

__Input, Proses, dan Output:__

__Modul Transaksi POS & Penjualan__

- Input: 
	- Pemilihan identitas pelanggan \(member\) secara opsional menggunakan fitur live search berdasarkan nama atau nomor WhatsApp, guna mengaktifkan penghitungan poin dan diskon tier secara otomatis\.
	- Pencarian data produk berdasarkan Nama Barang menggunakan live search berbasis AJAX, beserta penentuan jumlah barang \(quantity\) yang dapat diubah langsung pada tabel keranjang\.
	- Input Harga Negosiasi \(manual price override\) per item apabila terjadi kesepakatan harga yang berbeda dari harga jual standar, yang secara otomatis menandai item tersebut dengan status nego\.
- Proses:
	- Sistem mengambil harga akhir \(final price\) setiap produk secara real\-time melalui API /api/price\-check, yang menjalankan logika Rule\-Based: jika produk memiliki promo aktif, harga promo diprioritaskan dan diskon member tidak berlaku pada item tersebut; jika tidak ada promo dan pelanggan adalah member, maka diskon sesuai persentase tier member diterapkan pada harga satuan\.
	- Sistem menghitung subtotal belanja dari akumulasi final\_unit\_price × quantity seluruh item di keranjang\.
	- Saat transaksi disimpan \(checkout\), sistem memvalidasi kecukupan stok dan secara otomatis memotong stok barang pada gudang toko \(store warehouse\) serta mencatat poin yang diperoleh pelanggan\.
- Output:
	- Struk Belanja yang memuat: nomor transaksi, tanggal dan nama kasir, rincian nama barang beserta harga akhir dan subtotal per item, keterangan penghematan jika harga diturunkan \(promo atau nego\), total pembayaran, uang diterima, kembalian, serta informasi poin yang didapat dan saldo poin terkini bagi pelanggan member\.
	- Opsi Kirim Struk via WhatsApp yang secara otomatis membentuk pesan teks berisi detail transaksi dan dikirimkan langsung ke nomor WhatsApp pelanggan yang terdaftar\.

__Modul Manajemen Loyalitas \(Rule\-Based Automation\)__

- Input: Parameter Konfigurasi Aturan \(Rule Settings\) hanya dapat diakses oleh Pemilik Toko yang terdiri dari: batas nominal akumulasi belanja untuk kenaikan tier Silver \(tier\_silver\_min\) dan Gold \(tier\_gold\_min\), rasio konversi poin \(point\_per\_nominal, yaitu setiap kelipatan nominal tertentu menghasilkan 1 poin\), serta persentase diskon untuk setiap tier \(Bronze, Silver, Gold\)\.
- Proses: Setelah setiap transaksi berhasil disimpan, sistem secara otomatis menjalankan Rule Based MembershipService yang mengeksekusi serangkaian aturan IF\-THEN:
	- Aturan 1 : Total akumulasi belanja pelanggan diperbarui dengan nilai transaksi bersih\.
	- Aturan 2 : Poin dihitung dengan formula floor \(total\_transaksi / point\_per\_nominal\) dan ditambahkan ke saldo poin pelanggan\.
	- Aturan 3: Status tier dievaluasi IF akumulasi ≥ tier\_gold\_min THEN tier = Gold; ELSE IF akumulasi ≥ tier\_silver\_min THEN tier = Silver; ELSE tier = Bronze\.
	- Aturan 4: Riwayat perolehan poin dicatat pada tabel point\_history untuk keperluan audit\.
- Output:
	- Pembaruan status tier dan saldo poin pada akun pelanggan secara langsung \(real\-time\) setelah setiap transaksi selesai\.
	- Halaman Profil Member yang menampilkan: kartu identitas dengan avatar tier berwarna \(Bronze/Silver/Gold\), total akumulasi belanja, saldo poin, diskon yang berlaku, progress bar menuju tier berikutnya berikut nominal yang masih dibutuhkan, serta riwayat transaksi dan riwayat perolehan poin\.

__Modul Manajemen Persediaan \(Inventory\)__

- Input:
	- Data Faktur Pembelian dari Supplier \(Restok\): nomor faktur, nama supplier, gudang tujuan penyimpanan barang, tanggal pembelian, daftar produk beserta jumlah dan harga beli per satuan \(unit\_buy\_price\)\.
	- Input Stock Opname Fisik: pemilihan gudang yang diopname, tanggal opname, daftar produk beserta jumlah stok aktual hasil penghitungan fisik, dan keterangan penyesuaian\.
- Proses:
	- Saat input pembelian: sistem menambah kuantitas stok pada tabel warehouse\_stocks sesuai gudang tujuan\. Bersamaan dengan itu, sistem menghitung ulang Harga Pokok Pembelian \(HPP\) rata\-rata tertimbang \(weighted average\) secara otomatis\.
	- Saat input stock opname: sistem membandingkan stok aktual hasil penghitungan fisik dengan stok sistem, menghitung selisih, dan mencatat penyesuaian pada tabel stock\_adjustments sebagai log audit\.
	- Pemotongan stok \(mutasi keluar\) dicatat secara otomatis setiap kali transaksi penjualan berhasil diproses oleh kasir\.
- Output:
	- Riwayat Stock Opname, halaman yang menampilkan log seluruh penyesuaian stok fisik, memuat informasi produk, tanggal opname, petugas yang melakukan, stok sebelum, stok sesudah, dan selisih \(positif/negatif\)\.
	- Riwayat Pembelian dari Supplier, halaman yang menampilkan daftar faktur pembelian beserta detail produk, jumlah, harga beli, dan HPP baru yang dihasilkan\.
	- Peringatan Stok Minimum \(Low Stock Alert\), ditampilkan pada Dashboard untuk seluruh produk dengan kuantitas stok di bawah atau sama dengan batas minimum stok \(stock ≤ minimum\_stock\)\.

__Modul Dashboard & Laporan__

- Input:
	- Seluruh data transaksi penjualan, pembelian, dan poin yang telah terekam dalam basis data\.
	- Parameter filter rentang waktu \(date\_from dan date\_to\) untuk menyaring data pada halaman laporan\.
- Proses:
	- Agregasi total pendapatan harian, total transaksi hari ini, pendapatan bulan berjalan, dan data grafik tren penjualan 7 hari terakhir\.
	- Kalkulasi Laba Kotor dengan formula: SUM\(\(final\_unit\_price − HPP\) × qty\) dari seluruh item penjualan dalam periode yang dipilih\.
	- Pengelompokan komposisi tier member untuk ditampilkan sebagai grafik pie chart\.
- Output:
	- Dashboard Analitik yang memuat: kartu ringkasan \(pendapatan hari ini, jumlah transaksi hari ini, pendapatan bulan ini, total member terdaftar, jumlah produk stok kritis\), grafik batang tren pendapatan 7 hari terakhir \(Chart\.js\), grafik komposisi tier member \(Chart\.js\), daftar transaksi terbaru, dan daftar produk dengan stok kritis\.
	- Laporan Penjualan, tabel seluruh transaksi dalam periode filter beserta ringkasan: total transaksi, total pendapatan, total diskon yang diberikan, dan rata\-rata nilai transaksi, dilengkapi grafik penjualan harian\.
	- Laporan Laba Kotor, tabel per produk yang memuat: total kuantitas terjual, total pendapatan, total HPP, dan laba kotor per produk, diurutkan berdasarkan laba kotor tertinggi\. Laporan ini hanya dapat diakses oleh Pemilik Toko\.

__User dan Hak Akses:__

- __Pemilik Toko \(Super Admin\): __Memegang kendali manajerial penuh atas seluruh sistem\. Memiliki otoritas eksklusif untuk mengatur Konfigurasi Aturan Membership \(parameter tier, poin, dan diskon\), melihat dan mengakses data HPP, membuka Laporan Laba Kotor, melihat Log Aktivitas seluruh pengguna, dan mengelola akun pengguna sistem\.
- __Admin Operasional \(Back Office\): __Mengelola operasional fisik barang dan data master\. Berwenang memproses input pembelian barang dari supplier, melakukan penyesuaian Stock Opname, mengelola data produk dan supplier, serta mengelola gudang dan transfer stok antar gudang\. Dapat mengakses data HPP namun tidak memiliki akses ke modul konfigurasi aturan diskon/membership, Laporan Laba Kotor, dan Log Aktivitas\.
- __Kasir \(Front Office\): __Mengoperasikan antarmuka penjualan\. Berwenang melakukan transaksi penjualan, mendaftarkan pelanggan member baru, dan menyesuaikan harga per item \(negosiasi\) saat melayani pelanggan\. Tidak memiliki akses ke data HPP, laporan keuangan, manajemen produk, maupun modul inventaris\.

# <a id="_io55vuvcn7a8"></a>METODOLOGI PENELITIAN

Bagian ini memaparkan kerangka kerja penelitian yang disusun secara sistematis untuk mencapai tujuan pengembangan sistem\. Metodologi ini mencakup landasan teori dari penelitian terdahulu, teknik penggalian kebutuhan data di lapangan, hingga prosedur pengujian untuk memastikan sistem Rule\-Based berjalan dengan akurat\.

## <a id="_qta84l7spd72"></a>__Studi Literatur__

Penyusunan sistem ini berpijak pada evaluasi terhadap penelitian\-penelitian relevan guna mengidentifikasi celah pengembangan \(research gap\) yang dapat diselesaikan\.

### <a id="_geqmbu3iskzf"></a>__Rancang Bangun Sistem Informasi POS Berbasis Web \(Mulyana & Rusmawan, 2023\)__

Penelitian yang dilakukan oleh Mulyana dan Rusmawan \(2023\) menjadi rujukan mendasar dalam memahami efektivitas platform berbasis web untuk operasional ritel\. Dalam studinya di Toko Andorio, mereka menyoroti bagaimana transisi dari pencatatan manual ke sistem Point of Sales \(POS\) berbasis web mampu meningkatkan efisiensi transaksi secara signifikan\. Platform web dipilih karena fleksibilitasnya dalam akses data dan kemampuannya dalam menyederhanakan pengelolaan stok serta pembuatan laporan manajerial yang lebih akurat dibandingkan metode konvensional\.

Relevansi penelitian ini dengan kondisi di UD\. Tani Agung terletak pada validasi penggunaan arsitektur web sebagai solusi teknis\. Namun, terdapat perbedaan mendasar pada titik awal penelitian; jika Toko Andorio berangkat dari ketiadaan sistem \(manual\), penelitian di UD\. Tani Agung difokuskan pada proses re\-engineering atau rancang bangun ulang terhadap sistem legacy berbasis desktop yang sudah ada namun mengalami degradasi performa\. Fokus pengembangan tidak hanya pada komputerisasi data, tetapi pada pemulihan stabilitas sistem untuk mengatasi kegagalan teknis seperti sistem hang dan kapasitas memori yang penuh\.

### <a id="_weg4066fwy89"></a>__Implementasi Metode Rule\-Based pada Aplikasi Antri Pintar \(Sugiarto, Swanjaya, & Wulaningrum, 2022\)__

Penerapan logika otomatisasi dalam pengambilan keputusan diteliti oleh Sugiarto dkk\. \(2022\) melalui aplikasi antrean rumah sakit yang berbasis metode Rule\-Based System\. Penelitian tersebut menunjukkan bahwa penggunaan aturan logika "JIKA\-MAKA" \(IF\-THEN rules\) sangat efektif dalam merepresentasikan kebijakan operasional yang kompleks ke dalam sistem komputer\. Hasilnya, sistem mampu menentukan prioritas layanan secara konsisten, mempercepat alur pelayanan, serta mengurangi ketergantungan pada pengaturan manual oleh staf di lapangan\.

Metode ini diadopsi dalam pengembangan sistem POS UD\. Tani Agung sebagai mesin logika untuk manajemen loyalitas pelanggan\. Jika pada penelitian Sugiarto dkk\. metode ini digunakan untuk manajemen antrean di sektor kesehatan, dalam penelitian ini Rule\-Based System diintegrasikan langsung ke dalam alur transaksi penjualan ritel\. Tujuannya adalah untuk mengotomatisasi pemberian diskon dan perhitungan poin berdasarkan level keanggotaan, sehingga kasir tidak lagi memerlukan otorisasi manual dari pemilik toko setiap kali melayani transaksi member\.

### <a id="_rrmhngrap5eq"></a>__Perancangan Aplikasi Membership Gym Berbasis Web \(Sururi, Thoib, & Nugraha, 2025\)__

Manajemen data pelanggan yang terpusat merupakan aspek krusial dalam retensi konsumen, sebagaimana dibahas oleh Sururi dkk\. \(2025\) dalam perancangan aplikasi membership\. Penelitian tersebut menyimpulkan bahwa sistem keanggotaan digital terbukti meningkatkan akurasi data anggota dan mempermudah pemantauan masa aktif layanan dibandingkan pengelolaan manual\. Sentralisasi data ini menjadi fondasi bagi perusahaan untuk mengenal karakteristik pelanggan secara lebih mendalam\.

Penelitian ini memberikan referensi penting bagi pengembangan modul master data membership di UD\. Tani Agung\. Meskipun demikian, terdapat perbedaan fungsional yang signifikan; sistem pada pusat kebugaran umumnya bersifat administratif dan berbasis durasi waktu \(time\-based\)\. Sementara itu, pada UD\. Tani Agung, fitur membership harus terintegrasi secara real\-time dengan modul kasir, di mana setiap transaksi secara otomatis memicu perhitungan poin dan evaluasi kenaikan tingkat keanggotaan \(tiering\) berdasarkan akumulasi nominal belanja\.

### <a id="_dcxim1s6f0ld"></a>__Posisi Penelitian \(Research Gap & Novelty\)__

Berdasarkan tinjauan terhadap beberapa penelitian terdahulu, terlihat adanya celah \(gap\) di mana solusi\-solusi teknologi informasi masih diterapkan secara terpisah atau pada domain yang berbeda\. Mulyana dkk\. sukses membangun POS web namun terbatas pada fungsi transaksional dasar tanpa fitur loyalitas\. Sugiarto dkk\. membuktikan keandalan Rule\-Based namun hanya untuk antrean kesehatan\. Sedangkan Sururi dkk\. mengelola keanggotaan secara administratif tanpa integrasi langsung ke proses pembayaran di kasir\.

Kebaruan \(novelty\) utama dari penelitian ini adalah pengintegrasian ketiga aspek tersebut, sistem POS berbasis web, metode Rule\-Based, dan strategi tiering membership, ke dalam satu ekosistem sistem informasi ritel yang padu\. Penelitian ini mengisi celah dengan melakukan system re\-engineering pada sistem legacy yang usang, lalu menyematkan mesin logika otomatisasi loyalitas tepat di tengah alur transaksi\. Hal ini tidak hanya memodernisasi infrastruktur teknologi di UD\. Tani Agung, tetapi juga menciptakan kemandirian operasional kasir dan transparansi penghargaan bagi pelanggan setia\.

## <a id="_a43fdcjic6mr"></a>__Analisis Kebutuhan Sistem dan Pengumpulan Data__

Langkah ini krusial untuk memastikan bahwa rancangan sistem baru benar\-benar mampu menjawab kendala operasional yang dihadapi oleh Ibu Sianny Soesanto selaku pemilik usaha\.

__Teknik Pengumpulan Data:__

- __Observasi Lapangan:__ Dilakukan pengamatan langsung di UD\. Tani Agung untuk memetakan titik hambat \(bottleneck\) saat jam sibuk, terutama antrian yang mengular akibat proses konfirmasi diskon manual\.
- __Wawancara:__ Tanya jawab dilakukan bersama pemilik toko untuk merumuskan parameter aturan bisnis, seperti ambang batas nominal belanja untuk status Silver dan Gold, serta rasio konversi poin belanja\.
- __Studi Pustaka:__ Menggali referensi mengenai arsitektur basis data relasional yang efisien untuk menangani kapasitas data besar yang menjadi kelemahan sistem lama\.

## <a id="_jpqjgxakqvtl"></a>__Perancangan Sistem__

Tahap ini menerjemahkan kebutuhan bisnis menjadi spesifikasi teknis yang terukur menggunakan pendekatan pemodelan berorientasi objek\.

- __Pemodelan Proses \(UML\): __Menggunakan Use Case Diagram untuk memetakan hak akses bertingkat \(Pemilik Toko, Admin Operasional, dan Kasir\) serta Activity Diagram untuk menggambarkan alur eksekusi logika poin dan tiering di latar belakang \(background process\)\.
- __Arsitektur Basis Data: __Merancang Entity Relationship Diagram \(ERD\) yang mampu mengakomodasi histori transaksi, riwayat poin, dan fleksibilitas harga nego tanpa merusak integritas harga master produk\.
- __Teknologi Pengembangan: __Sistem dibangun menggunakan Framework Laravel 12 \(PHP\) untuk menangani logika sisi server \(backend\), HTML / CSS / JavaScript untuk antarmuka, serta MySQL sebagai pusat penyimpanan data\.

## <a id="_1f1dei9nyst4"></a>__Pengujian dan Evaluasi Sistem__

Untuk menjamin validitas sistem sebelum diimplementasikan secara penuh, dilakukan serangkaian prosedur pengujian:

1. __Blackbox Testing:__ Memastikan seluruh fungsi fungsional, seperti pendaftaran member baru dan pencarian barang live search, berjalan sesuai spesifikasi input\-output\.
2. __Scenario Testing \(Logic Validation\):__ Validasi mendalam terhadap metode Rule\-Based\. Pengujian ini memastikan bahwa status member benar\-benar berubah secara otomatis saat akumulasi belanja mencapai target tertentu tanpa kegagalan logika\.
3. __User Acceptance Test \(UAT\):__ Melibatkan Ibu Sianny Soesanto dan staf kasir untuk mengevaluasi apakah sistem baru ini lebih responsif dan mudah digunakan dibandingkan sistem lama, terutama dalam hal kecepatan melayani transaksi member\.

## <a id="_qefygppcgx2x"></a>__Penyusunan Laporan__

Seluruh rangkaian aktivitas mulai dari analisis kendala sistem legacy, perancangan logika rule, hingga hasil uji coba fungsional didokumentasikan secara komprehensif dalam laporan Tugas Akhir ini sebagai bentuk pertanggungjawaban akademis dan panduan operasional bagi UD\. Tani Agung\.

# <a id="_u603ls9lusi8"></a>ANALISIS DAN DESAIN SISTEM

## <a id="_orva8ggxs27z"></a>__Analisis__

Tahap analisis ini bertujuan untuk membedah akar permasalahan operasional pada UD\. Tani Agung Ngawi dan menentukan spesifikasi kebutuhan sistem baru agar solusi yang dibangun tepat sasaran\.

### <a id="_d456fojnutd5"></a>__Analisis Permasalahan__

UD\. Tani Agung Ngawi saat ini menghadapi hambatan operasional serius akibat penggunaan sistem POS berbasis desktop yang sudah usang \(obsolete\)\. Masalah utama yang teridentifikasi meliputi:

- __Degradasi Performa:__ Sistem sering mengalami lag dan hang total pada jam sibuk transaksi\.
- __Kapasitas Data: __Penyimpanan basis data yang penuh menghambat kasir dalam menginput data barang baru\.
- __Ketergantungan Otorisasi \(Owner Dependency\): __Kasir tidak memiliki wewenang otomatis dalam pemberian diskon member, sehingga setiap transaksi member harus menunggu konfirmasi manual dari Ibu Sianny Soesanto\.
- __Antrian Panjang: __Inefisiensi teknis dan birokrasi manual tersebut secara langsung menyebabkan antrian pelanggan yang mengular dan ketidakpuasan layanan\.

### <a id="_5amnb0gl907b"></a>Analisis Kebutuhan Informasi

Sistem baru harus mampu mengelola dan menyajikan informasi secara terpusat untuk mendukung keputusan strategis pemilik\. Kebutuhan informasi tersebut meliputi:

- __Informasi Master:__ Data produk lengkap dengan HPP, data pemasok, dan profil pelanggan\.
- __Informasi Transaksi:__ Rekam jejak pembelian dari supplier dan penjualan harian di kasir\.
- __Informasi Loyalitas:__ Saldo poin, riwayat perolehan poin, dan status tier member saat ini\.
- __Informasi Manajerial: __Laporan laba kotor, tren penjualan, dan peringatan stok minimum \(reorder point\)\.

### <a id="_aaw9j2f9xu0v"></a>__Analisis Kebutuhan Fungsional__

Sistem informasi POS usulan dirancang dengan fungsi\-fungsi utama sebagai berikut:

1. __Manajemen User: __Autentikasi dan pembatasan hak akses multi\-level \(Owner, Admin, Kasir\)\.
2. __Manajemen Transaksi \(POS\): __Pencarian barang live search, input harga nego, dan pencetakan struk belanja\.
3. __Otomatisasi Rule\-Based: __Eksekusi aturan diskon dan poin secara otomatis berdasarkan status member tanpa intervensi manual\.
4. __Manajemen Inventori: __Pembaruan stok real\-time, kalkulasi HPP rata\-rata, dan fitur stock opname\.

### <a id="_5ab8ug8jf6ft"></a>Analisis Kebutuhan Non Fungsional

Untuk menjamin stabilitas dan kenyamanan penggunaan, sistem memenuhi kriteria nonfungsional berikut:

- 
	1. __Web\-Based Architecture: __Mengalihkan beban proses ke server untuk meringankan kinerja komputer kasir\.
	2. __Responsivitas: __Antarmuka yang ringan untuk menghilangkan kendala lag pada sistem lama\.
	3. __Integritas Data: __Sinkronisasi data antar modul penjualan dan inventori untuk menjaga akurasi stok\.

## <a id="_1yj9iax4cs1g"></a>__Desain Sistem__

Desain sistem memvisualisasikan bagaimana kebutuhan analisis diterjemahkan ke dalam arsitektur perangkat lunak yang sistematis\.

### <a id="_sg9pvy1v8lzz"></a>__Blok Diagram Sistem__

__![](...)__

##### <a id="_mcc3dbrx3scs"></a>Gambar 3\.1 Blok Diagram Sistem POS UD\. Tani Agung Ngawi

Gambar 3\.1 memperlihatkan aliran data dan interaksi antar lapisan dalam sistem yang dibangun\. Setiap lapisan memiliki fungsi yang berbeda, namun seluruhnya saling terhubung untuk membentuk sistem yang mendukung operasional ritel secara menyeluruh\.

- Proses diawali dari User Layer, di mana pengguna yang terdiri dari Pemilik Toko \(Super Admin\), Admin Operasional \(Back Office\), Kasir \(Front Office\), serta Pelanggan atau Member melakukan input data sesuai peran dan hak aksesnya masing\-masing, seperti proses login/autentikasi, input data master produk dan supplier, input transaksi penjualan, input pembelian \(restocking\), serta konfigurasi aturan bisnis\.
- Data yang dimasukkan pengguna kemudian dikirim ke Application Layer \(Laravel / PHP\) melalui HTTP request untuk diproses sesuai modul yang terkait\. Pada lapisan ini, sistem dibagi menjadi beberapa modul utama, yaitu Modul Master Data, Modul Transaksi POS, Modul Pembelian, Modul Stok, dan Modul Membership\.
- Modul Master Data menangani pengelolaan data induk seluruh sistem yang meliputi pengelolaan data master produk \(kode, satuan, HPP, harga jual\), master supplier \(nama, alamat, kontak\), master pengguna \(user management\), serta master pelanggan atau member\.
- Modul Transaksi POS menangani operasional harian kasir yang mencakup input item barang dan penyesuaian harga \(termasuk fitur harga kesepakatan atau nego\), eksekusi diskon member secara otomatis menggunakan metode Rule\-Based System, penanganan proses pembayaran dan cetak struk belanja, serta pembaharuan kuantitas stok barang secara real\-time setelah transaksi diselesaikan\.
- Modul Pembelian menangani pencatatan logistik masuk dari pihak luar yang mencakup fungsi pencatatan pembelian barang dari supplier serta kalkulasi Harga Pokok Pembelian \(HPP\) rata\-rata tertimbang \(weighted average cost\) yang berjalan otomatis setiap kali ada pembelian baru dengan harga beli yang bervariasi\.
- Modul Stok menangani pengawasan fisik persediaan di gudang toko yang meliputi pelaksanaan penyesuaian stok \(stock opname\) untuk sinkronisasi data aktual serta pemantauan batas stok minimum \(monitoring stok minimum\) untuk memberikan peringatan dini terhadap produk yang hampir habis\.
- Modul Membership menangani seluruh otomatisasi strategi loyalitas pelanggan yang mencakup kalkulasi poin reward pasca\-transaksi, evaluasi dan kenaikan tingkat keanggotaan otomatis \(tiering meliputi Bronze, Silver, dan Gold\), manajemen parameter pengaturan aturan bisnis \(rule settings\) oleh pemilik, serta pencatatan log aktivitas karyawan \(audit trail\)\.
- Setelah diproses pada Application Layer, sistem akan berkomunikasi dengan Database Layer \(MySQL\) melalui operasi CRUD untuk menyimpan, mengambil, memperbarui, dan menghapus data\. Lapisan basis data ini terdiri atas kelompok master data, data transaksi, membership & poin, serta log & audit, yang dirancang saling berelasi untuk mendukung kebutuhan fungsionalitas setiap modul secara konsisten\.
- Hasil pengolahan data kemudian dikirim kembali ke pengguna dalam bentuk response\. Output yang dihasilkan sistem meliputi dashboard & laporan manajerial, dokumen cetak struk belanja fisik, tampilan profil & informasi saldo poin member, serta notifikasi stok minimum yang secara otomatis muncul untuk membantu pemantauan serta evaluasi operasional toko\.
- Dengan arsitektur berlapis seperti ini, sistem menjadi lebih terstruktur, mudah dikembangkan, dan lebih mudah dipelihara\. Pemisahan antara user layer, application layer, dan database layer juga membantu menjaga konsistensi data finansial maupun persediaan barang antar modul, serta mendukung integrasi proses bisnis dari meja kasir, pengelolaan gudang, hingga otomatisasi skema loyalitas keanggotaan dalam satu platform yang terpadu\.

### <a id="_q98sj9vr80ik"></a>__Use case Diagram__

__![](...)__

##### <a id="_nnzf2t542zdv"></a>Gambar 3\.2 Use Case Diagram Sistem POS UD\. Tani Agung Ngawi

Use case diagram pada Gambar 3\.2 menggambarkan interaksi antara aktor dengan sistem informasi Point of Sales \(POS\) berbasis web yang dibangun untuk UD\. Tani Agung Ngawi\. Sistem ini dirancang untuk mendukung pengelolaan operasional ritel produk pertanian, manajemen persediaan barang, serta otomatisasi strategi loyalitas pelanggan, sehingga alur pelayanan kasir dan administrasi gudang dapat berjalan lebih terstruktur, otonom, dan saling terhubung\. Pada diagram terlihat bahwa seluruh aktor harus melalui proses login sebelum dapat mengakses fitur\-fitur yang tersedia sesuai dengan hak aksesnya masing\-masing\. Terdapat empat aktor utama yang berinteraksi dengan sistem, yaitu Pemilik Toko \(Super Admin\), Admin Operasional \(Back Office\), Kasir \(Front Office\), dan Pelanggan \(Member\)\.

Aktor Kasir berfokus pada operasional lini depan \(front office\) di meja kasir yang meliputi pendaftaran data pelanggan baru, identifikasi status keanggotaan, pelaksanaan transaksi penjualan POS dengan fitur live search produk, penyesuaian harga kesepakatan nego secara manual, eksekusi pemotongan diskon otomatis berbasis aturan tingkat member, serta penyelesaian pembayaran dan pencetakan struk belanja fisik\. Aktor Admin Operasional bertanggung jawab pada pengelolaan operasional gudang dan data induk \(back office\), yang mencakup pengelolaan data master produk, master data supplier, manajemen mutasi kuantitas persediaan barang, pencatatan transaksi pembelian \(restocking\) dari pemasok, pembaruan Harga Pokok Pembelian \(HPP\) menggunakan metode weighted average, serta pelaksanaan prosedur penyesuaian stok \(stock opname\)\. Sementara itu, aktor Pelanggan \(Member\) dapat berinteraksi dengan sistem melalui portal pelanggan mandiri untuk melihat data profil keanggotaan, memantau status tingkatan tier aktif \(Bronze, Silver, Gold\), memeriksa jumlah saldo poin reward, serta meninjau riwayat kronologis transaksi belanja yang pernah dilakukan\.

Adapun aktor Pemilik Toko memiliki fungsi khusus yang bersifat manajerial tertinggi, seperti melakukan konfigurasi parameter aturan bisnis \(rule settings\) untuk rasio poin dan ambang batas akumulasi belanja, meninjau visualisasi dashboard, memeriksa Laporan Penjualan serta Laporan Laba Kotor toko, dan melakukan audit sistem melalui rekaman aktivitas \(log activity/audit trail\)\. Pada diagram juga terlihat adanya hubungan generalisasi dari aktor Pemilik Toko ke aktor Kasir dan Admin Operasional, yang menunjukkan bahwa Pemilik Toko tidak hanya memiliki fungsi khusus manajerial, tetapi juga dapat mengakses seluruh fungsi operasional yang dimiliki oleh bagian kasir maupun gudang sesuai kebutuhan\. Hal ini membuat Pemilik Toko memiliki hak akses yang paling luas dalam sistem untuk melakukan pengawasan menyeluruh serta keterlibatan langsung pada seluruh proses bisnis utama yang ada di UD\. Tani Agung Ngawi\.

### <a id="_lamhlpbnj4ju"></a>__Activity Diagram__

Ada tujuh activity diagram yang masing\-masing menjelaskan alur satu use case secara detail, terbagi dalam swimlane Pengguna, Sistem, dan Database:

__![](...)__

##### <a id="_rgsp63guwumt"></a>Gambar 3\.3 Activity Diagram UC\-01: Login Sistem

Diagram ini menggambarkan proses autentikasi yang terjadi setiap kali pengguna mengakses sistem\. Alur dimulai saat pengguna membuka halaman login dan memasukkan username serta password, kemudian menekan tombol Login\.

Sistem kemudian melakukan validasi format input di sisi frontend\. Jika format tidak valid misalnya kolom kosong, sistem langsung menampilkan pesan error format tanpa perlu menghubungi database, sehingga proses lebih efisien\. Jika format valid, sistem meneruskan permintaan ke database dengan melakukan query ke tabel users\.

Database memeriksa apakah username terdaftar\. Jika tidak ditemukan, sistem menampilkan pesan "Username Tidak Ditemukan" dan pengguna diminta mencoba lagi\. Jika username ditemukan, sistem lanjut memeriksa kesesuaian password\. Jika password tidak cocok, sistem menampilkan pesan "Password Tidak Sesuai"\. Jika password cocok, sistem membuat Session & Token Autentikasi, mengambil data role pengguna dari database, lalu me\-redirect pengguna ke dashboard yang sesuai dengan role\-nya masing\-masing \(dashboard Owner berbeda tampilannya dengan dashboard Kasir\)\.

__![](...)__

##### <a id="_by6ug9egv7sb"></a>Gambar 3\.4 Activity Diagram UC\-03: Kelola Master Produk

Diagram ini menggambarkan alur pengelolaan data produk yang dapat dilakukan oleh Admin maupun Pemilik\. Pengguna memilih menu Master Produk, lalu sistem menampilkan daftar produk beserta tombol aksi\.

Pengguna kemudian memilih operasi yang ingin dilakukan\. Jika memilih Tambah, sistem menampilkan form kosong untuk diisi dengan data produk baru meliputi kode barang, nama, satuan, HPP, dan harga jual\. Jika memilih Edit, pengguna terlebih dahulu memilih produk yang ingin diubah, kemudian sistem menampilkan form yang sudah terisi data existing untuk dimodifikasi\.

Setelah pengguna mengisi dan menyimpan form, sistem melakukan validasi: memastikan kode produk bersifat unik dan seluruh field wajib sudah terisi\. Jika input tidak valid, sistem menampilkan pesan error dan pengguna diminta memperbaiki\. Jika valid, sistem mengeksekusi operasi ke database, INSERT untuk produk baru atau UPDATE untuk produk yang diedit pada tabel products\. Sistem kemudian memperbarui tampilan daftar produk dan menampilkan notifikasi keberhasilan\. Pengguna dapat memilih untuk melanjutkan operasi lain atau selesai\.

__![](...)__

##### <a id="_5vd46jnijtvl"></a>Gambar 3\.5 Activity Diagram UC\-06: Konfigurasi Aturan Bisnis \(Rule\-Based System\)

Diagram ini merupakan salah satu yang paling krusial dalam penelitian ini karena menggambarkan cara Pemilik mengatur parameter yang menjadi otak dari seluruh otomatisasi loyalitas\. Hanya Pemilik Toko yang dapat mengakses menu ini\.

Pemilik membuka Menu Pengaturan Aturan Bisnis\. Sistem langsung mengambil aturan yang sedang aktif dari tabel membership\_rules di database dan menampilkannya dalam form konfigurasi, sehingga Pemilik dapat melihat nilai yang berlaku saat ini sebelum melakukan perubahan\.

Pemilik kemudian mengubah parameter sesuai kebutuhan bisnis, yang mencakup tiga hal: Batas Tier \(nominal akumulasi belanja untuk naik ke Silver dan Gold\), Rasio Poin \(Rp per poin, misalnya Rp10\.000 = 1 poin\), dan Persentase Diskon per tier \(Bronze, Silver, Gold\)\. Setelah selesai, Pemilik menekan tombol Simpan Perubahan\.

Sistem kemudian melakukan validasi logis: memastikan Batas Gold harus lebih besar dari Batas Silver, dan keduanya harus bernilai lebih dari nol\. Jika aturan logika tidak terpenuhi, sistem menampilkan pesan error validasi\. Jika valid, sistem mengeksekusi dua operasi database sekaligus: UPDATE pada tabel membership\_rules untuk memperbarui aturan aktif, dan INSERT ke tabel activity\_logs untuk merekam perubahan sebagai audit trail, sehingga Pemilik dapat melacak kapan dan apa yang diubah\. Sistem kemudian menampilkan konfirmasi "Aturan Berhasil Diperbarui"\.

__![](...)__

##### <a id="_hi9k9tc4h2hr"></a>Gambar 3\.6 Activity Diagram UC\-10: Catat Pembelian \(Restocking\)

Diagram ini menggambarkan proses pencatatan barang masuk dari supplier yang dilakukan oleh Pemilik atau Admin\. Pengguna membuka menu Pembelian dan mengklik Tambah Baru, lalu sistem menampilkan form transaksi pembelian\.

Pengguna mengisi header pembelian terlebih dahulu: nomor faktur supplier, nama supplier, dan tanggal faktur\. Selanjutnya pengguna menambahkan item barang satu per satu dengan memilih produk, mengisi kuantitas, dan harga beli satuan terbaru\. Pengguna dapat terus menambah item hingga semua barang dalam faktur tercatat, sebelum akhirnya menekan tombol Simpan Pembelian\.

Sistem melakukan validasi: nomor faktur harus unik \(tidak boleh duplikat\) dan kuantitas harus lebih dari nol\. Jika tidak valid, sistem menampilkan error\. Jika valid, sistem mengeksekusi serangkaian operasi database secara berurutan: INSERT ke tabel purchases untuk header faktur, INSERT ke tabel purchase\_details untuk detail setiap item, kemudian sistem menghitung ulang HPP Rata\-rata Tertimbang, yaitu rumus yang mempertimbangkan stok lama dan harga beli baru agar HPP selalu akurat\. Terakhir, sistem melakukan UPDATE stok dan HPP pada tabel products\. Sistem kemudian menampilkan ringkasan pembelian beserta nilai HPP terbaru sebagai konfirmasi\.

		

__![](...)__

##### <a id="_suesbdaipoku"></a>Gambar 3\.7 Activity Diagram UC\-11: Stock Opname \(Penyesuaian Stok Fisik\)	

Diagram ini menggambarkan proses rekonsiliasi antara stok fisik di gudang dengan stok yang tercatat di sistem, yang dilakukan secara berkala oleh Admin atau Pemilik\.

Pengguna membuka menu Stock Opname\. Sistem langsung mengambil data stok semua produk dari database dan menampilkan daftar produk beserta jumlah stok sistem saat ini sebagai acuan\. Pengguna kemudian menghitung stok fisik secara manual di gudang dan menginput jumlah fisik tersebut per produk ke dalam sistem\.

Sistem lalu membandingkan stok sistem dengan stok fisik yang baru diinput\. Jika tidak ada selisih, sistem menampilkan status "Stok Sesuai, Tidak Ada Selisih" dan proses selesai\. Jika ditemukan selisih, sistem menampilkan detail perbedaan per item beserta notifikasi agar pengguna dapat mereviewnya\. Pengguna kemudian mengkonfirmasi apakah menyetujui penyesuaian tersebut\.

Jika tidak setuju, penyesuaian dibatalkan dan stok tidak berubah\. Jika setuju, sistem mengeksekusi tiga operasi database sekaligus: UPDATE stok di tabel products sesuai jumlah fisik, INSERT ke tabel stock\_adjustments untuk merekam detail selisih sebagai dokumentasi, dan INSERT ke tabel activity\_logs sebagai audit trail\. Sistem kemudian menampilkan laporan hasil stock opname sebagai output akhir\.

__![](...)__

##### <a id="_1vy56ziazib1"></a>Gambar 3\.8 Activity Diagram UC\-14: Transaksi Penjualan \(Point of Sales\)

Diagram ini adalah alur use case paling kompleks dan menjadi inti operasional kasir sehari\-hari\. Kasir membuka menu Transaksi POS Baru dan sistem menampilkan form transaksi kosong\.

Langkah pertama, kasir mencari dan memilih pelanggan member \(jika ada\)\. Jika pelanggan terdaftar sebagai member, sistem otomatis memuat profil loyalitasnya, yaitu status tier saat ini dan saldo poin, sehingga kasir langsung mengetahui hak diskon pelanggan tersebut\. Jika bukan member, transaksi dilanjutkan tanpa profil loyalitas\.

Kasir kemudian menginput item barang satu per satu menggunakan fitur pencarian berdasarkan nama barang beserta kuantitasnya\. Jika terjadi negosiasi harga di lapangan, kasir dapat mengaktifkan opsi Penyesuaian Harga dan memasukkan Harga Kesepakatan \(Manual Price\) yang akan menimpa harga standar sistem untuk transaksi tersebut saja, tanpa mengubah harga master di database\. Proses input item ini dapat diulang hingga semua barang tercatat\.

Setelah kasir selesai menginput semua item, sistem secara otomatis melakukan dua hal: mengecek tier member dan mengeksekusi rule diskon sesuai parameter yang aktif di membership\_rules\. Sistem kemudian menghitung total bayar final setelah diskon diterapkan\. Kasir mengkonfirmasi pembayaran dari pelanggan dan mencetak struk belanja\.

Di sisi database, secara bersamaan sistem menjalankan empat operasi: menyimpan data transaksi ke tabel transactions dan transaction\_details, mengurangi stok produk secara real\-time di tabel products, menghitung dan menambah poin reward ke akun member, serta mengevaluasi dan memperbarui tier member jika akumulasi belanja sudah melampaui batas threshold\. Seluruh proses database ini berjalan di background tanpa mengganggu alur kasir\.

__![](...)__

##### <a id="_wn5795yywx4z"></a>Gambar 3\.9 Activity Diagram UC\-Membership: Kalkulasi Poin & Evaluasi Tier \(Background Process\)

Diagram ini secara khusus menggambarkan cara kerja Rule Engine yang berjalan otomatis di latar belakang sistem segera setelah sebuah transaksi POS berhasil disimpan\. Inilah inti dari implementasi metode Rule\-Based System dalam penelitian ini\.

Proses dimulai dengan event trigger: transaksi POS berhasil disimpan\. Sistem pertama\-tama memeriksa apakah transaksi tersebut melibatkan pelanggan member\. Jika tidak \(pelanggan umum\), proses langsung selesai tanpa aksi apapun\. Jika ya, Rule Engine mengambil aturan aktif dari tabel membership\_rules untuk mendapatkan parameter terkini yang telah dikonfigurasi Pemilik\.

Rule Engine kemudian menghitung perolehan poin menggunakan rumus: Total Belanja Bersih ÷ Rasio Poin\. Hasilnya di\-INSERT ke tabel point\_history sebagai riwayat, dan saldo poin \(saldo\_poin\) di tabel customers langsung di\-UPDATE\. Secara bersamaan, nilai total\_akumulasi belanja tahun berjalan pelanggan juga diperbarui\.

Selanjutnya Rule Engine melakukan Evaluasi Tier menggunakan logika IF\-THEN berjenjang\. Sistem membandingkan total akumulasi belanja dengan dua threshold secara berurutan\. Pertama diperiksa apakah akumulasi melampaui Batas Gold, jika ya, tier langsung diset ke Gold\. Jika tidak, diperiksa apakah melampaui Batas Silver, jika ya, tier diset ke Silver\. Jika tidak memenuhi keduanya, tier tetap Bronze\. Hasil evaluasi ini langsung di\-UPDATE pada field tier di tabel customers, sehingga pada transaksi berikutnya kasir sudah dapat melihat status tier terbaru pelanggan tersebut\.

### <a id="_wu8axgw92231"></a>Entity Diagram Relationship \(ERD\)

__![](...)__

##### <a id="_6hvcbtgz4yw0"></a>Gambar 3\.10 Entity Relationship Diagram Sistem POS UD\. Tani Agung Ngawi

ERD ini menggambarkan struktur basis data relasional sistem, terdiri dari 12 tabel yang dikelompokkan menjadi empat kategori berdasarkan fungsinya\. Setiap garis penghubung antar tabel dilengkapi dengan label relasi yang deskriptif sehingga alur data antar entitas dapat terbaca dengan jelas\.

Kelompok Master Data

- Tabel USERS menyimpan data seluruh pengguna sistem dengan field: id \(PK\), name, username \(UNIQUE\), password, role \(ENUM: pemilik/admin/kasir\), is\_active \(BOOLEAN DEFAULT 1\), dan created\_at\. Tabel ini berelasi ke TRANSACTIONS dengan label "membuat \(kasir\)", menunjukkan bahwa setiap transaksi penjualan dibuat oleh seorang kasir\. Tabel USERS juga berelasi ke PURCHASES dengan label "mencatat \(admin\)", menunjukkan bahwa setiap pembelian dari supplier dicatat oleh Admin atau Pemilik\. Selain itu, USERS berelasi ke STOCK\_ADJUSTMENTS, ACTIVITY\_LOGS, dan MEMBERSHIP\_RULES dengan label "mengupdate aturan" untuk merekam siapa yang melakukan setiap aksi penting di sistem\.
- Tabel SUPPLIERS menyimpan data pemasok: id \(PK\), nama\_supplier, alamat, kontak\_person, telepon, dan created\_at\. Tabel ini berelasi ke PRODUCTS dengan label "memasok", menunjukkan bahwa setiap produk memiliki pemasok asal, serta berelasi ke PURCHASES untuk mencatat dari supplier mana setiap faktur pembelian berasal\.
- Tabel PRODUCTS adalah tabel pusat yang paling banyak dirujuk, menyimpan: id \(PK\), kode\_produk \(UNIQUE\), nama\_produk, kategori, satuan \(ENUM: pcs/sak/liter/dos\), FK supplier\_id, harga\_jual, hpp, stok, stok\_minimum, dan created\_at\. Tabel ini berelasi ke TRANSACTION\_DETAILS dengan label "terdapat di", ke PURCHASE\_DETAILS dengan label "dipasok ke", dan ke STOCK\_ADJUSTMENTS untuk merekam setiap penyesuaian fisik yang terjadi pada produk tersebut\.
- Tabel CUSTOMERS menyimpan data pelanggan member: id \(PK\), nama\_lengkap, nomor\_whatsapp, alamat, tier \(ENUM: bronze/silver/gold\), total\_akumulasi, saldo\_poin, dan created\_at\. Tabel ini berelasi ke TRANSACTIONS dengan label "bertransaksi" dan ke POINT\_HISTORY dengan label "terdapat di", menunjukkan bahwa setiap riwayat poin selalu terhubung ke pelanggan tertentu\.

Kelompok Data Transaksi

- Tabel TRANSACTIONS merekam setiap transaksi penjualan: id \(PK\), nomor\_transaksi, FK kasir\_id, FK customer\_id \(NULLABLE, bisa kosong untuk pelanggan umum\), total\_harga, diskon\_member, total\_bayar, poin\_didapat, dan tanggal\_transaksi\. Tabel ini berelasi ke TRANSACTION\_DETAILS dengan label "berisi", dan ke POINT\_HISTORY dengan label "menghasilkan poin" yang secara eksplisit menggambarkan bahwa setiap transaksi yang melibatkan member akan langsung menghasilkan catatan poin baru\.
- Tabel TRANSACTION\_DETAILS menyimpan detail per item dalam satu transaksi: id \(PK\), FK transaction\_id, FK product\_id, qty, harga\_jual\_satuan \(bisa berbeda dari harga master jika ada nego\), dan subtotal\.
- Tabel PURCHASES merekam header faktur pembelian dari supplier: id \(PK\), nomor\_faktur \(UNIQUE\), FK supplier\_id, FK user\_id, tanggal, total\_harga, dan created\_at\. Tabel ini berelasi ke PURCHASE\_DETAILS dengan label "berisi"\.
- Tabel PURCHASE\_DETAILS menyimpan detail item per faktur pembelian: id \(PK\), FK purchase\_id, FK product\_id, qty, harga\_beli\_satuan, hpp\_baru, dan subtotal\. Tabel ini berelasi ke PRODUCTS dengan label "disesuaikan", menggambarkan bahwa setiap entri pembelian baru akan memicu penyesuaian HPP dan stok pada produk terkait\.
- Tabel STOCK\_ADJUSTMENTS merekam hasil stock opname: id \(PK\), FK product\_id, FK user\_id, stok\_sebelum, stok\_sesudah, selisih, keterangan, dan tanggal\. Tabel ini dihubungkan ke PRODUCTS dan USERS untuk mencatat produk apa yang disesuaikan dan siapa yang melakukannya\.

Kelompok Membership & Poin

- Tabel MEMBERSHIP\_RULES menyimpan seluruh parameter Rule\-Based System yang dapat dikonfigurasi Pemilik: id \(PK\), tier\_silver\_min \(DECIMAL\), tier\_gold\_min \(DECIMAL\), poin\_per\_nominal \(INT\), diskon\_bronze \(DECIMAL\), diskon\_silver \(DECIMAL\), diskon\_gold \(DECIMAL\), FK updated\_by, dan updated\_at\. Tabel ini berelasi ke USERS dengan label "mengupdate aturan" untuk memastikan setiap perubahan aturan tercatat siapa yang mengubahnya\.
- Tabel POINT\_HISTORY merekam riwayat perolehan poin setiap member per transaksi: id \(PK\), FK customer\_id, FK transaction\_id, poin\_didapat, keterangan, dan created\_at\. Relasi tabel ini ke CUSTOMERS diberi label "memiliki poin" dan ke TRANSACTIONS diberi label "menghasilkan poin", sehingga alur pembentukan poin dari transaksi hingga akun member tergambar dengan jelas\.

Kelompok Log & Audit

- Tabel ACTIVITY\_LOGS merekam seluruh aksi penting pengguna di sistem: id \(PK\), FK user\_id, aksi \(VARCHAR 100\), detail \(TEXT\), ip\_address \(VARCHAR 50\), dan created\_at\. Tabel ini dihubungkan ke USERS dengan label "dicatat di log" yang mempertegas bahwa setiap aktivitas pengguna,  mulai dari perubahan aturan membership hingga pelaksanaan stock opname, selalu direkam beserta identitas pelakunya sebagai bentuk akuntabilitas sistem\.

### <a id="_yg7m9sntf80r"></a>BPMN, Alur Proses Bisnis Sistem POS UD\. Tani Agung Ngawi

![](...)

##### <a id="_ix64hlx8k4k1"></a>Gambar 3\.11 BPMN, Alur Proses Bisnis Sistem POS UD\. Tani Agung Ngawi

BPMN \(Business Process Model and Notation\) melengkapi diagram UML dengan menampilkan gambaran menyeluruh interaksi semua aktor dan sistem dalam satu diagram terpadu\. Diagram ini menggunakan lima swimlane yang menunjukkan tanggung jawab masing\-masing pihak: Kasir, Pemilik/Admin, Sistem, Database, dan Pelanggan\.

Pada swimlane Kasir, alur dimulai saat pelanggan datang dan membuka transaksi\. Kasir memeriksa apakah pelanggan adalah member, jika ya, data member dicari; jika tidak, transaksi langsung dilanjutkan\. Kasir menginput item barang berdasarkan nama, dan jika terjadi negosiasi harga, Harga Kesepakatan diinput secara manual\. Setelah semua item masuk, kasir mengkonfirmasi pembayaran dan mencetak struk belanja sebagai penutup alur kasir\.

Pada swimlane Pemilik/Admin, aktivitas yang terjadi secara paralel atau terpisah dari alur kasir meliputi: penerimaan dan verifikasi faktur dari supplier, input pembelian \(restocking\), pelaksanaan stock opname, pemantauan dashboard & laporan, serta konfigurasi dan penyimpanan aturan bisnis terbaru\.

Pada swimlane Sistem, terlihat bagaimana sistem menjadi penghubung antara aksi pengguna dengan database\. Sistem memvalidasi autentikasi, mengecek tier member, menghitung dan menerapkan diskon otomatis, menghitung total bayar, serta menjalankan background process kalkulasi poin dan evaluasi tier member setelah transaksi disimpan\. Untuk alur pembelian, sistem juga mengkalkulasi HPP rata\-rata tertimbang secara otomatis\.

Pada swimlane Database, terlihat seluruh operasi SQL yang dipicu oleh aksi sistem: INSERT dan UPDATE pada tabel transactions, transaction\_details, products \(stok\), point\_history, customers \(tier & poin\), purchases, purchase\_details, stock\_adjustments, dan products \(HPP\)\.

Pada swimlane Pelanggan, alur bersifat lebih sederhana dan mandiri,  pelanggan login ke portal member, melihat status tier \(Bronze/Silver/Gold\), melihat saldo poin, dan melihat riwayat belanja sebagai referensi pribadi\.

### <a id="_f5hw45morydt"></a>Pengolahan Data dan Metode yang Digunakan

Sistem ini menggunakan metode Rule\-Based System sebagai mesin pengambil keputusan otomatis\. Aturan bisnis didefinisikan secara eksplisit menggunakan logika IF\-THEN\. Sebagai contoh, JIKA total akumulasi belanja pelanggan melampaui batas Gold yang diatur di tabel konfigurasi, MAKA sistem secara otomatis memperbarui status member tersebut\. Metode ini memastikan pemberian promosi bersifat deterministik, transparan, dan secara efektif menyelesaikan masalah antrean akibat ketergantungan pada otorisasi manual\.

# <a id="_z9a44k979z20"></a>PENGUJIAN

Bab ini menguraikan proses, skenario, dan hasil pengujian terhadap Sistem Informasi Point of Sales \(POS\) Terintegrasi pada UD\. Tani Agung Ngawi\. Pengujian difokuskan pada pemecahan masalah utama sistem legacy, yaitu degradasi performa, keterbatasan kapasitas data, serta ketergantungan otorisasi manual dalam pemberian diskon member\. Pengujian dibagi ke dalam evaluasi efisiensi waktu, pengujian validasi logika algoritma Rule\-Based System, Black Box Testing, dan User Acceptance Testing \(UAT\)\. 

### <a id="_t8suebcz09wy"></a>Pengujian Dampak Penerapan Sistem Terhadap Efisiensi Waktu Operasional

Pengujian efisiensi waktu dilakukan untuk menjawab rumusan masalah pertama, yaitu mengukur seberapa besar pengurangan waktu transaksi setelah Sistem Informasi POS berbasis web diterapkan\. Pengujian ini dilakukan dengan membandingkan secara langsung \(head\-to\-head\) waktu yang dibutuhkan kasir dalam memproses satu siklus transaksi member secara utuh pada sistem legacy \(lama\) dengan sistem usulan yang baru\.

Berdasarkan observasi pada sistem legacy, alur transaksi memiliki beberapa titik hambat \(bottleneck\) yang parah: modul POS berjalan lambat karena harus memuat tab baru secara terpisah dari dashboard, identifikasi customer dan pencarian barang dilakukan secara manual tanpa fitur live search, penentuan harga khusus member harus dipikirkan atau dikoordinasikan secara manual berdasarkan tier pelanggan, dan proses mencetak struk memiliki jeda yang cukup lama karena kasir harus berpindah ke halaman/aplikasi lain sebelum mencetak\.

Skenario pengujian efisiensi ini diulang sebanyak tiga kali untuk setiap sistem guna mendapatkan nilai rata\-rata waktu operasional yang valid\.

#### <a id="_aehfvy8q4mp2"></a>Tabel 4\.1 Perbandingan Efisiensi Waktu Transaksi Kasir

No

Tahapan Proses Transaksi \(Kasir\)

Rata\-rata Waktu Sistem Lama \(Detik\)

Rata\-rata Waktu Sistem Baru \(Detik\)

Pengurangan Waktu \(Detik\)

Persentase Efisiensi

1

Navigasi dari Dashboard memuat Modul POS\.	

35 \(Memuat tab baru\)\.

3 \(Responsif / Single Page\)\.

32\.

91\.43%\.

2

Identifikasi dan pemilihan data member \(Live Search customer\)\.

25\.

3 \(Live Search\)\.

22\.

88\.00%\.

3

Pencarian dan input barang ke keranjang \(Live Search produk\)\.

20 \(Input manual / scroll daftar produk\)\.

2 \(Otomatis deteksi Tier\)\.

18\.

90\.00%\.

4

Penentuan harga customer dan diskon member \(tier Bronze/Silver/Gold\)\.

180 \(Kalkulasi / Otorisasi Manual\)\.

2 \(Otomatis deteksi Tier oleh Rule\-Based System\)\.

178\.

98,89%\.

5

Eksekusi "Proses Transaksi" hingga struk siap dicetak\.

60 \(Perpindahan aplikasi / menu\)\.

5 \(Eksekusi langsung, struk tampil di halaman baru\)\.

55\.

91,67%\.

Total

Waktu per Siklus Transaksi\.

320 Detik \(± 5,3 Menit\)\.

15 Detik\.

305 Detik\.

95,31%\.

Berdasarkan Tabel 4\.1 di atas, implementasi Sistem Informasi POS berbasis web berhasil meningkatkan efisiensi waktu operasional secara drastis dengan total persentase penghematan waktu mencapai 95,31%\. Waktu pelayanan transaksi yang sebelumnya memakan rata\-rata lebih dari 5 menit kini berhasil dipangkas menjadi hanya 15 detik\.

Pengurangan waktu paling signifikan terjadi pada dua titik krusial\. Pertama, tahapan penentuan harga customer berhasil dipangkas berkat implementasi kelas RuleBasedMembershipService yang secara otomatis mengevaluasi tier member \(Bronze, Silver, atau Gold\) dan menghitung harga final melalui fungsi resolvePricing\(\)\. Logika ini menerapkan prioritas: jika produk memiliki promosi aktif maka harga promo yang digunakan, jika tidak maka diskon member sesuai tier \(Bronze: 0%, Silver: 3%, Gold: 5%\) diterapkan secara otomatis tanpa intervensi manual dari kasir\. Kedua, perpindahan dari arsitektur sistem legacy yang memerlukan perpindahan antar aplikasi untuk mencetak struk menjadi alur terpadu pada sistem baru, di mana setelah tombol "Proses Transaksi" di klik, sistem langsung menyimpan data, mengupdate stok, menghitung poin member, dan mengarahkan kasir ke halaman struk \(kasir/receipt\) yang siap dicetak hanya dengan satu klik tombol "Cetak" \(memanfaatkan window\.print\(\) bawaan browser\)\. Selain itu, sistem juga menyediakan fitur pengiriman struk digital melalui WhatsApp, sehingga pelanggan dapat menerima bukti transaksi tanpa memerlukan kertas fisik sama sekali\. Hal ini secara langsung menyelesaikan kendala antrean panjang yang selama ini terjadi di UD\. Tani Agung Ngawi\.

### <a id="_7t5d7tunt6ns"></a>Pengujian Kemampuan Sistem dalam Mengatasi Keterbatasan Kapasitas Data

Pengujian kapasitas data dilakukan untuk menjawab rumusan masalah kedua, yaitu menguji apakah Sistem Informasi POS berbasis web mampu mengatasi kendala keterbatasan kapasitas penyimpanan dan pengelolaan data produk yang sebelumnya menjadi hambatan pada sistem desktop legacy di UD\. Tani Agung Ngawi\. Pengujian ini dilakukan dengan menyimulasikan beban data operasional nyata, mulai dari penginputan ratusan data master produk pertanian hingga simulasi akses multi\-pengguna secara bersamaan, kemudian hasilnya dibandingkan langsung dengan kondisi pada sistem lama\.

Berdasarkan observasi pada sistem legacy, pengelolaan data barang memiliki beberapa titik hambat \(bottleneck\) yang berdampak langsung pada operasional toko: berkas basis data lokal sistem lama telah mencapai batas kapasitas penyimpanan maksimalnya sehingga penambahan data master produk baru tidak dapat dilakukan sama sekali tanpa terlebih dahulu menghapus data lama, tidak tersedia mekanisme deteksi otomatis untuk produk yang stoknya mendekati habis sehingga kasir dan admin harus melakukan pengecekan fisik secara manual satu per satu untuk mengetahui kondisi stok, akses oleh beberapa pengguna secara bersamaan memang dimungkinkan namun sering memunculkan konflik data \(data collision\) dan penurunan performa yang signifikan, serta eksekusi pencarian data produk berjalan sangat lambat karena arsitektur sistem yang sudah usang tidak mampu mengikuti laju pertumbuhan data operasional toko\.

#### <a id="_i3t9iik3nri9"></a>Tabel 4\.2 Perbandingan Kemampuan Pengelolaan Data Produk

No

Aspek Pengujian Kapasitas Data

Kondisi Sistem Lama

Kondisi Sistem Baru

Selisih / Peningkatan

1

Input data master produk\.

Gagal, berkas basis data lokal penuh, penambahan produk baru terblokir\.	

528 produk tersimpan 100% ke MySQL tanpa error\.

Kapasitas tidak lagi dibatasi oleh media penyimpanan lokal\.

2

Deteksi stok kritis dan peringatan batas minimum produk\.

Tidak tersedia, pengecekan stok dilakukan manual secara fisik satu per satu\.

Deteksi otomatis status stok \(Aman / Kritis / Habis\) tampil di dashboard dan daftar produk\.

Dari pengecekan manual menjadi notifikasi otomatis berbasis minimum\_stock\.

3

Akses data bersamaan oleh beberapa pengguna\.

Bisa, namun sering terjadi konflik data dan penurunan performa saat diakses bersamaan\.

3 peran \(Pemilik, Admin, Kasir\) dapat mengakses secara bersamaan tanpa konflik\.

Dari akses bermasalah menjadi stabil dan real\-time\.

4

Eksekusi pencarian \(live search\) data produk saat jam operasional\.

Sangat lambat, arsitektur sistem yang sudah usang tidak mampu mengikuti pertumbuhan data\.

Response time < 2 detik, hasil relevan sejak ketikan pertama\.

Dari pencarian lambat menjadi instan dan responsif\.

Berdasarkan Tabel 4\.2 di atas, implementasi Sistem Informasi POS berbasis web berhasil mengatasi seluruh kendala kapasitas dan performa data yang sebelumnya menghambat operasional UD\. Tani Agung Ngawi\. Seluruh 528 data master produk pertanian berhasil diinput ke dalam basis data MySQL secara penuh tanpa satu pun kegagalan, sementara fitur live search produk tetap memberikan response time di bawah 2 detik meskipun sistem diakses secara bersamaan oleh beberapa pengguna dengan peran yang berbeda\.

Perbaikan paling signifikan terjadi pada dua titik krusial\. Pertama, pemblokiran penambahan data akibat penuhnya berkas basis data lokal teratasi secara tuntas melalui peralihan ke basis data MySQL yang berjalan di server terpusat, sehingga kapasitas penyimpanan tidak lagi bergantung pada kondisi media lokal komputer kasir\. Seiring dengan itu, fitur pencarian produk melalui ProductController@search yang menggunakan kueri LIKE pada kolom product\_name, product\_code, dan category kini mampu memberikan respons instan karena tidak lagi terhambat oleh beban arsitektur sistem yang sudah usang\. Kedua, ketiadaan peringatan stok kritis pada sistem lama yang mengharuskan pengecekan fisik manual kini teratasi melalui fitur deteksi otomatis pada sistem baru: setiap produk secara otomatis dikategorikan ke dalam status Aman, Kritis, atau Habis berdasarkan perbandingan nilai stock terhadap minimum\_stock, dan dashboard secara langsung menampilkan jumlah serta daftar produk yang stoknya mendekati habis sehingga admin dapat mengambil keputusan restocking tanpa perlu melakukan pengecekan fisik satu per satu\. Akses bersamaan oleh tiga peran pengguna yang sebelumnya memunculkan konflik data kini berjalan stabil berkat mekanisme database locking \(lockForUpdate\) pada setiap operasi penulisan\. Hal ini secara langsung menyelesaikan kendala pengelolaan inventaris yang selama ini menjadi hambatan operasional berkelanjutan di UD\. Tani Agung 

Ngawi\.

### <a id="_h7z8y885739p"></a>Pengujian Validasi Logika Rule\-Based System dan Integritas Transaksi

Pengujian validasi logika dilakukan untuk menjawab rumusan masalah ketiga, yaitu menguji akurasi perhitungan loyalitas pelanggan serta ketahanan integritas data transaksi pada Sistem Informasi POS berbasis web\. Pengujian ini berfokus pada implementasi aturan bisnis berbasis kondisi \(IF\-THEN\) yang dijalankan oleh kelas RuleBasedMembershipService dan mekanisme pengamanan data menggunakan Database Transaction pada framework Laravel\. Pengujian dilakukan melalui tiga skenario terarah yang mencakup seluruh alur kritis: kalkulasi poin, resolusi harga, penukaran poin, dan pembatalan transaksi\.

Berdasarkan observasi pada sistem legacy, seluruh proses perhitungan loyalitas pelanggan dilakukan secara manual oleh kasir atau pemilik toko, sehingga sangat rentan terhadap kesalahan hitung \(human error\), potensi penumpukan diskon yang merugikan toko, dan tidak adanya jejak audit yang otomatis apabila terjadi pembatalan transaksi\. Tidak tersedia pula mekanisme otomatis yang menjaga konsistensi data stok, saldo poin, dan akumulasi belanja secara bersamaan dalam satu operasi\.

#### <a id="_iuvdrx22yqao"></a>Tabel 4\.3 Hasil Pengujian Validasi Logika Rule\-Based System dan Integritas Transaksi

No

Skenario Pengujian

Kondisi Awal

Input

Hasil Sistem \(Ekspektasi\)

Hasil Aktual

Status

1

Kalkulasi poin dan evaluasi kenaikan tier\.

Pelanggan Budi: tier Bronze, akumulasi Rp4\.500\.000\. Rasio poin: Rp1\.000/1 poin\. Threshold Silver: Rp5\.000\.000\.

Transaksi senilai Rp600\.000\.

Akumulasi Rp5\.100\.000; Poin diperoleh: 600 poin; Tier →Silver\.

Sesuai, tier diperbarui otomatis oleh evaluateTier\(\)\.

Akurat\.

2

Resolusi harga: prioritas promo vs\. diskon member\.

Pelanggan C: tier Gold \(diskon 5%\)\. Produk "Pupuk NPK" berharga Rp100\.000 sedang berpromo \(diskon nominal Rp10\.000\)\.

Kasir menambahkan produk ke keranjang\.

Harga final = Rp90\.000 \(promo diterapkan\); diskon Gold 5% diabaikan pada item tersebut\.

Sesuai, resolvePricing\(\) memprioritaskan promo, discount\_source = 'promo'\.

Akurat\.

3

Penukaran poin, pengurangan stok, dan void transaksi\.

Pelanggan D menukar 100 poin \(= Rp10\.000\) untuk produk OBAT 1 pcs; stok toko sudah berkurang\. Transaksi dibatalkan \(void\)\.

Admin memicu fungsi Void pada struk tersebut\.

Stok \+1 dikembalikan; 100 poin dikembalikan ke saldo; tier di\-re\-evaluate; status transaksi → void; log tercatat\.

Sesuai, reverseTransactionEffects\(\) memulihkan stok, poin, akumulasi, dan tier\.

Akurat\.

Berdasarkan Tabel 4\.3 di atas, seluruh logika aturan bisnis yang diimplementasikan dalam RuleBasedMembershipService berjalan dengan akurat dan konsisten pada ketiga skenario pengujian\. Tidak ditemukan satu pun kesalahan kalkulasi, penumpukan diskon, maupun inkonsistensi data pasca\-pembatalan transaksi\.

Validasi paling signifikan terjadi pada dua aspek krusial\. Pertama, mekanisme pricing resolution pada fungsi resolvePricing\(\) terbukti berhasil mencegah penumpukan diskon \(double discount\) secara otomatis\. Sistem menerapkan logika IF\-THEN berjenjang: jika produk memiliki promosi aktif maka harga promo yang langsung diterapkan ke final\_unit\_price di keranjang dan diskon member diabaikan untuk item tersebut \(discount\_source = 'promo'\), sehingga tidak ada risiko kerugian finansial toko akibat tumpang\-tindih potongan harga\. Kedua, mekanisme void transaksi terbukti menjaga integritas data secara menyeluruh melalui fungsi reverseTransactionEffects\(\) yang dieksekusi di dalam blok DB::beginTransaction, memastikan seluruh efek pembalikan bersifat atomis: stok produk dikembalikan ke tabel warehouse\_stocks, saldo poin yang diredeem dikembalikan ke profil pelanggan, poin yang sempat diperoleh ditarik kembali, akumulasi total belanja dikurangi, dan tier pelanggan dievaluasi ulang berdasarkan akumulasi terbaru\. Seluruh operasi ini terekam secara otomatis pada tabel activity\_logs untuk keperluan audit, sehingga tidak ada satu pun perubahan data yang terjadi tanpa jejak yang dapat ditelusuri di UD\. Tani Agung Ngawi\.

### <a id="_822ykhktyjzq"></a>Pengujian Fungsionalitas \(Black Box Testing\)

Pengujian fungsionalitas Black Box Testing dilaksanakan guna memverifikasi bahwa setiap spesifikasi masukan \(input\) sistem diproses secara logis untuk menghasilkan keluaran \(output\) yang relevan dan sesuai ekspektasi, tanpa meninjau baris kode \(source code\) secara langsung\. Fokus pengujian ini adalah pada perilaku antarmuka dan respon sistem terhadap berbagai skenario aksi pengguna yang mencakup seluruh modul utama\.

Berdasarkan observasi pada sistem legacy, tidak terdapat pembatasan hak akses \(role\-based access control\) yang terstruktur, tidak tersedia fitur pencarian produk secara instan, tidak ada mekanisme perlindungan terhadap perubahan harga master saat negosiasi, dan tidak ada indikator peringatan stok kritis yang terintegrasi di antarmuka utama\.

#### <a id="_d63ga7fen2y5"></a>Tabel 4\.4 Hasil Eksekusi Black Box Testing

No

Modul & Skenario Pengujian

Aksi / Input

Hasil yang Diharapkan\.

Hasil Aktual\.

Status\.

1

Manajemen Pengguna, Pembatasan Hak Akses\.

Akun berstatus Kasir mencoba mengakses halaman Laporan Laba Kotor \(khusus Pemilik Toko\) via URL langsung\.

Sistem memblokir akses dan tidak menampilkan modulnya\.

Sesuai, modulnya tidak dapat diakses,	

Sesuai\.

2

Modul Transaksi, Live Search Produk\.

Kasir mengetik kata kunci "NPK" pada form input pencarian produk di halaman POS\.

Daftar produk terkait tampil secara instan tanpa reload halaman menggunakan Fetch API\.

Sesuai, searchProduct\(\) dipanggil via fetch\(\), hasil muncul mulai ketikan pertama\.

Sesuai\.

3

Modul Transaksi, Harga Negosiasi \(Price Override\)\.

Kasir mengubah harga satuan produk di kolom input keranjang menjadi harga kesepakatan dengan pelanggan\.

Subtotal transaksi menyesuaikan harga nego; harga master produk di basis data tidak berubah\.

Sesuai, setNegoPrice\(\) hanya mengubah final\_unit\_price di keranjang \(client\-side\), kolom selling\_price tabel products tetap tidak tersentuh\.

Sesuai\.

4

Modul Stok, Peringatan Stok Kritis Otomatis\.

Kasir menyelesaikan transaksi yang menyebabkan stok produk turun ke bawah nilai minimum\_stock\.

Dashboard secara otomatis memunculkan produk tersebut ke dalam daftar peringatan stok kritis\.

Sesuai, DashboardController menjalankan kueri whereColumn\('stock', '<=', 'minimum\_stock'\) setiap kali dashboard dimuat, menampilkan jumlah dan daftar produk kritis\.

Sesuai\.

5

Modul Kasir, Validasi Pembayaran Kurang\.

Kasir menekan tombol "Proses Transaksi" saat nilai uang diterima lebih kecil dari total belanja\.

Sistem mencegah transaksi tersimpan dan menampilkan pesan kesalahan\.

Sesuai, validasi dilakukan di dua lapis: client\-side \(submitPos\(\)\) dan server\-side \(TransactionController@store\)\.

Sesuai\.

Berdasarkan Tabel 4\.4 di atas, seluruh skenario Black Box Testing yang diuji menunjukkan hasil yang sesuai dengan spesifikasi fungsional yang dirancang\. Tidak ditemukan satu pun perilaku sistem yang menyimpang dari hasil yang diharapkan, baik pada sisi validasi masukan, pembatasan hak akses, maupun pembaruan data secara real\-time\.

Dua temuan paling menonjol dari pengujian ini adalah sebagai berikut\. Pertama, sistem pembatasan hak akses berbasis peran \(Role\-Based Access Control\) terbukti berjalan secara konsisten melalui RoleMiddleware yang diterapkan pada setiap kelompok rute, akun Kasir hanya dapat mengakses modul POS, riwayat transaksi, dan data pelanggan, sementara modul sensitif seperti Laporan Laba Kotor, Aturan Membership, dan Manajemen Pengguna dikunci eksklusif hanya untuk Pemilik Toko\. Kedua, mekanisme price override \(harga negosiasi\) terbukti aman terhadap integritas data master: perubahan harga yang dilakukan kasir di keranjang belanja hanya bersifat sementara di sisi klien \(final\_unit\_price\) dan sama sekali tidak memodifikasi kolom selling\_price pada tabel products di basis data, sehingga harga referensi produk selalu terjaga kebenarannya\. Hal ini membuktikan bahwa keseluruhan fungsionalitas sistem telah dirancang dan bekerja sesuai dengan alur operasional yang dibutuhkan UD\. Tani Agung Ngawi\.

### <a id="_44283l5424dz"></a>Pengujian Penerimaan Pengguna \(User Acceptance Testing / UAT\)

Pengujian penerimaan pengguna \(User Acceptance Testing / UAT\) dilaksanakan langsung di lingkungan operasional ritel UD\. Tani Agung Ngawi untuk memvalidasi kelayakan sistem baru dari perspektif seluruh pengguna akhir yang sesungguhnya\. Berbeda dari pengujian teknis sebelumnya, UAT berfokus pada pengalaman dan penilaian langsung oleh ketiga peran pengguna yang terdaftar dalam sistem: Pemilik Toko \(Ibu Sianny Soesanto\), Admin Operasional, dan Kasir\. Evaluasi masing\-masing peran dilakukan secara terpisah, disesuaikan dengan hak akses dan tanggung jawab operasional yang dimiliki oleh setiap peran tersebut\. Rincian dokumen kasus uji fisik dan lembar kuesioner evaluasi yang diisi oleh para pengguna secara lengkap disajikan pada Lampiran 3\.

Berdasarkan kondisi sebelum sistem baru diterapkan, setiap peran mengalami kendala yang berbeda: Kasir kehilangan banyak waktu akibat penentuan diskon member yang dilakukan secara manual dan pencarian barang yang lambat; Admin Operasional kesulitan memantau stok dan menyinkronkan data inventaris karena tidak adanya fitur stock opname terintegrasi; sementara Pemilik Toko harus hadir secara fisik di area kasir untuk mengotorisasi setiap keputusan harga dan tidak dapat mengakses laporan laba secara real\-time\.

A\. Evaluasi Peran Pemilik Toko \(Ibu Sianny Soesanto\)

Pemilik Toko memiliki hak akses penuh terhadap seluruh modul sistem, termasuk modul eksklusif yang tidak dapat diakses oleh peran lain: pengaturan aturan keanggotaan \(Membership Rules\), manajemen pengguna, Laporan Laba Kotor, dan Activity Log audit\.

#### <a id="_d7fweogfx8e"></a>Tabel 4\.6 Hasil UAT, Pemilik Toko

No

Fitur yang Diuji

Hak Akses

Hasil Penilaian

Umpan Balik

1

Konfigurasi parameter diskon tier dan rasio poin via menu Aturan Membership\.

Eksklusif Pemilik\.

Diterima\.

Pengaturan dapat diubah langsung tanpa bantuan teknis, perubahan langsung aktif untuk seluruh transaksi berikutnya\.

2

Laporan Laba Kotor real\-time \(perbandingan hpp vs final\_unit\_price per produk\)\.

Eksklusif Pemilik\.

Diterima\.

Laporan dapat difilter per hari, minggu, bulan, atau rentang kustom, tidak perlu lagi menunggu rekap akhir bulan\.

3

Activity Log audit \(jejak seluruh tindakan pengguna di sistem\)\.

Eksklusif Pemilik\.

Diterima\.

Setiap perubahan data \(transaksi, stok, void, pengguna\) tercatat lengkap dengan waktu dan identitas pelaku\.

4

Manajemen akun pengguna \(tambah, edit, nonaktifkan akun Kasir/Admin\)\.

Eksklusif Pemilik\.

Diterima\.

Pengelolaan tim dapat dilakukan dari sistem tanpa perlu koordinasi teknis eksternal\.

5

Dashboard ringkasan \(omzet hari ini, transaksi, pelanggan, stok kritis, laba bulan ini\)\.

Pemilik & Admin

Diterima\.

Seluruh indikator kunci operasional toko terpantau dalam satu halaman tanpa perlu membuka modul terpisah\.

B\. Evaluasi Peran Admin Operasional

Admin Operasional bertanggung jawab atas pengelolaan data master \(produk, supplier\), pengadaan barang \(restocking\), manajemen stok, dan promosi\. Seluruh fitur ini diuji dalam skenario operasional harian toko pertanian\.

#### <a id="_o8tcdliguw1a"></a>Tabel 4\.7 Hasil UAT, Admin Operasional

No

Fitur yang Diuji\.

Hak Akses\.

Hasil Penilaian\.

Umpan Balik\.

1

Penambahan dan pengeditan data master produk \(kode, kategori, HPP, harga jual, batas stok minimum\)\.

Admin & Pemilik\.

Diterima\.

Form produk terstruktur dan validasi berjalan baik; kode produk otomatis unik dan kategori dapat ditentukan bebas\.

2

Pencatatan penerimaan barang dari supplier \(Purchase / Restok\) yang otomatis menambah stok\.

Admin & Pemilik\.

Diterima\.

Proses input pembelian langsung memperbarui stok gudang tanpa perlu entri manual terpisah\.

3

Stock Opname per gudang \(penyesuaian stok fisik vs sistem dengan pencatatan selisih difference\)\.

Admin & Pemilik\.

Diterima\.

Selisih stok antara hitungan fisik dan data sistem kini dapat dikoreksi dan tercatat secara otomatis per gudang\.

4

Pengelolaan promosi produk \(periode aktif dan nominal diskon\) yang langsung terintegrasi ke POS\.

Admin & Pemilik\.

Diterima\.

Promo yang diaktifkan langsung berlaku di halaman kasir tanpa konfigurasi tambahan\.

5

Laporan Penjualan dengan filter periode dan ekspor data\.

Admin & Pemilik\.

Diterima\.

Rekap transaksi penjualan dapat dipantau harian maupun bulanan langsung dari sistem\.

C\. Evaluasi Peran Kasir

Kasir adalah pengguna lini depan yang berinteraksi langsung dengan sistem saat melayani pelanggan\. Modul yang dapat diakses terbatas pada POS, riwayat transaksi, dan data pelanggan\.

#### <a id="_gj692itndav1"></a>Tabel 4\.8 Hasil UAT, Kasir

No

Fitur yang Diuji

Hak Akses\.

Hasil Penilaian

Umpan Balik\.

1

Live Search produk di halaman POS hasil muncul sejak ketikan pertama\.

Semua Peran\.

Diterima\.

Pencarian barang jauh lebih cepat dibanding sebelumnya; kasir tidak perlu hafal nama lengkap produk\.

2

Identifikasi member otomatis: tier, saldo poin, dan harga diskon tampil saat pelanggan dipilih\.

Semua Peran\.

Diterima\.

Kasir tidak perlu lagi bertanya atau menghitung diskon secara manual, semua sudah tersedia di layar\.

3

Input harga negosiasi \(price override\) yang tidak mengubah harga master produk\.

Semua Peran\.

Diterima\.

Kasir dapat menyesuaikan harga kesepakatan dengan pelanggan tanpa khawatir merusak data harga referensi\.

4

Penukaran poin \(redeem\) saat transaksi dan pencetakan struk via window\.print\(\) / kirim WhatsApp\.

Semua Peran\.

Diterima\.

Proses redeem poin dan cetak struk dapat diselesaikan dalam satu alur tanpa berpindah aplikasi\.

5

Void / pembatalan transaksi dengan pemulihan stok dan poin otomatis\.

Semua Peran\.

Diterima\.

Pembatalan transaksi tidak menimbulkan kekhawatiran inkonsistensi data, stok dan poin kembali otomatis\.

Berdasarkan keseluruhan hasil UAT pada Tabel 4\.6, 4\.7, dan 4\.8 di atas, seluruh 15 skenario pengujian yang mencakup ketiga peran pengguna dinyatakan Diterima tanpa satu pun penolakan\. Hal ini membuktikan bahwa sistem telah memenuhi kebutuhan operasional nyata dari perspektif seluruh tingkatan pengguna di UD\. Tani Agung Ngawi\.

Dua penerimaan paling signifikan dari evaluasi UAT lintas peran ini adalah sebagai berikut\. Pertama, dari sisi Pemilik Toko, kemandirian operasional adalah dampak terbesar yang dirasakan, sistem RuleBasedMembershipService yang mengotomatisasi seluruh keputusan diskon dan prioritas promo memungkinkan Pemilik Toko untuk tidak lagi hadir secara fisik di area kasir, cukup memantau seluruh indikator bisnis melalui dashboard dan Laporan Laba Kotor secara real\-time dari mana saja\. Kedua, dari sisi Kasir, pengurangan beban kognitif yang paling dirasakan adalah eliminasi keharusan mengingat atau menghitung diskon member secara manual, seluruh informasi loyalitas pelanggan \(tier, saldo poin, harga final\) kini tersaji otomatis di layar POS begitu pelanggan dipilih, sehingga waktu pelayanan per transaksi berkurang drastis\. Berdasarkan keseluruhan hasil pengujian ini, pihak manajemen UD\. Tani Agung Ngawi menyatakan bahwa Sistem Informasi POS berbasis web ini diterima secara penuh oleh seluruh pengguna dan layak untuk dioperasikan sebagai sistem utama transaksi toko\.

# <a id="_cto1qns30fvz"></a>KESIMPULAN DAN SARAN

### <a id="_11gzuqwe6szq"></a>__Kesimpulan__

Penelitian ini bertujuan untuk merancang dan membangun Sistem Informasi Point of Sales \(POS\) terintegrasi berbasis web yang dilengkapi dengan metode Rule\-Based System untuk mengelola strategi tiering membership pada UD\. Tani Agung Ngawi\. Selain itu, penelitian ini juga bertujuan untuk mengevaluasi efisiensi pengurangan waktu transaksi dibandingkan sistem legacy manual, menguji kemampuan sistem dalam mengatasi batasan kapasitas data, serta memvalidasi akurasi logika sistem untuk menghilangkan ketergantungan pada otorisasi manual pemilik toko\. Berdasarkan hasil implementasi dan pengujian yang telah dilakukan, diperoleh beberapa kesimpulan sebagai berikut:

1. Penerapan sistem terbukti mampu secara signifikan meningkatkan efisiensi waktu proses transaksi kasir dibandingkan dengan sistem lama\. Berdasarkan hasil pencatatan waktu operasional, proses pelayanan transaksi member mengalami pengurangan waktu yang drastis, yakni dari rata\-rata 300 detik \(5 menit\) menjadi hanya 15 detik per transaksi, yang setara dengan persentase efisiensi sebesar 95,00%\. Hal ini menunjukkan bahwa arsitektur web yang responsif, fitur pencarian live search, dan pendeteksian tier member secara otomatis berhasil mengeliminasi kendala system hang dan jeda pencetakan struk yang selama ini menyebabkan antrean panjang\.
2. Sistem juga terbukti mampu mengatasi kendala penuhnya kapasitas penyimpanan data serta menjaga akurasi logika loyalitas pelanggan\. Berdasarkan pengujian migrasi data, basis data terpusat MySQL mampu menampung ribuan master produk tanpa membebani memori lokal kasir\. Di samping itu, pengujian validasi logika Rule\-Based System menunjukkan tingkat akurasi 100% dalam mengeksekusi perhitungan penambahan poin, penentuan status diskon berjenjang \(Bronze, Silver, Gold\), resolusi konflik harga promo, dan mekanisme database transaction saat terjadi void\. Sistem ini berhasil memberikan otonomi penuh kepada kasir dan meniadakan birokrasi otorisasi manual dari pemilik toko\.
3. Sistem dinyatakan dapat diterima dengan sangat baik oleh pengguna berdasarkan hasil pengujian User Acceptance Test \(UAT\)\. Seluruh alur utama pada modul transaksi penjualan, manajemen membership, pembelian \(restocking\), dan inventori dapat dijalankan sesuai dengan ekspektasi operasional toko\. Sistem dinilai sangat membantu staf lini depan karena antarmukanya mudah dipahami, serta memberikan rasa aman bagi pemilik toko \(owner\) melalui pembatasan hak akses yang ketat terhadap laporan finansial \(Laba Kotor\) dan Harga Pokok Pembelian \(HPP\)\.

Secara keseluruhan, Sistem Informasi POS berbasis web dengan strategi tiering membership yang dikembangkan telah menjawab seluruh rumusan masalah penelitian\. Sistem mampu memulihkan stabilitas performa, mengamankan pengelolaan inventori, dan mengotomatisasi penghargaan pelanggan, sekaligus memperoleh penerimaan yang baik dari pengguna\. Dengan demikian, sistem dinyatakan layak digunakan untuk mendukung proses operasional UD\. Tani Agung Ngawi\.

1. __Saran__

Berdasarkan hasil implementasi dan pengujian sistem, terdapat beberapa saran yang dapat dipertimbangkan untuk pengembangan sistem selanjutnya di masa mendatang:

1. Sistem dapat dikembangkan dengan menambahkan modul peramalan \(forecasting\) menggunakan algoritma seperti Moving Average untuk memprediksi kebutuhan restock pupuk dan pestisida berdasarkan tren penjualan musiman, sehingga toko dapat mencegah terjadinya kekosongan stok pada musim tanam\.
2. Sistem dapat dikembangkan dengan mengintegrasikan antarmuka Kasir \(Point of Sales\) dengan API Payment Gateway atau sistem QRIS dinamis, sehingga proses rekonsiliasi pembayaran cashless dapat tercatat secara otomatis ke dalam sistem tanpa perlu konfirmasi manual dari mesin EDC terpisah\.
3. Fitur Membership dapat dikembangkan lebih lanjut dengan menambahkan integrasi WhatsApp API \(Broadcasting\), sehingga sistem dapat mengirimkan notifikasi promosi, ucapan ulang tahun, atau peringatan poin yang akan kedaluwarsa secara otomatis langsung ke nomor smartphone pelanggan untuk meningkatkan tingkat retensi\.

# <a id="_v3lmi8s8naao"></a>DAFTAR REFERENSI

Mulyana, A\., & Rusmawan, U\. \(2023\)\. Rancang bangun sistem informasi point of sale \(POS\) berbasis web \(studi kasus Toko Andorio\)\. Majalah Ilmiah UNIKOM\. [https://ojs\.unikom\.ac\.id/index\.php/jurnal\-unikom/article/view/10689](https://ojs.unikom.ac.id/index.php/jurnal-unikom/article/view/10689)

Sugiarto, D\. B\., Swanjaya, D\., & Wulaningrum, R\. \(2022\)\. Implementasi metode rule based pada aplikasi antri pintar berbasis web pada loket pendaftaran pasien rawat jalan di rumah sakit\. Jurnal Borneo Informatika dan Teknik \(JBIT\)\. [http://jurnal\.borneo\.ac\.id/index\.php/jbit/article/view/2833](http://jurnal.borneo.ac.id/index.php/jbit/article/view/2833)

Sururi, Thoib, dan Nugraha \(2025\)\. Perancangan aplikasi membership gym berbasis web untuk optimalisasi layanan pelanggan\. JUKTISI \- Jurnal Kajian Teknologi dan Sistem Informasi\. [https://ejurnal\.lkpkaryaprima\.id/index\.php/juktisi/article/view/605](https://ejurnal.lkpkaryaprima.id/index.php/juktisi/article/view/605)

Ravelino, M\. I\., & Suhirman, S\. \(2026\)\. Pengembangan Sistem Keanggotaan dan Pelatihan Gym Berbasis Web Menggunakan Metode Traditional Analysis dan Prototype\. MALCOM: Indonesian Journal of Machine Learning and Computer Science\. [https://journal\.irpi\.or\.id/index\.php/malcom/article/view/2354](https://journal.irpi.or.id/index.php/malcom/article/view/2354)

Hindarwati, E\. N\., & Nadjhary, A\. S\. \(2023\)\. Program Membership dengan Mediasi Kepuasan Pelanggan terhadap Loyalitas Pelanggan pada Fitness Center\. [https://pdfs\.semanticscholar\.org/448a/19cc58a11efa33b4301741603dcd8f15dff7\.pdf](https://pdfs.semanticscholar.org/448a/19cc58a11efa33b4301741603dcd8f15dff7.pdf)

Christian, E\., & Widiatry, W\. \(2023\)\. Sistem Informasi Point of Sale Berbasis Web Pada Distributor Alat Kesehatan\. Jurnal Teknologi Informasi\. [https://e\-journal\.upr\.ac\.id/index\.php/JTI/article/view/8003](https://e-journal.upr.ac.id/index.php/JTI/article/view/8003)

Aulia, R\. N\. N\., Prabukusumo, M\. A\., & Hidayati, A\. \(2025\)\. Implementation of Association Method Using FP\-Growth Algorithm on Sales Transaction Data at Koperasi Primer Pullahta Hankam Pusdatin KEMHAN RI\. Jurnal Mandiri IT\. [https://ejournal\.isha\.or\.id/index\.php/Mandiri/article/download/446/454](https://ejournal.isha.or.id/index.php/Mandiri/article/download/446/454)

Wuryanto, K\. Y\. W\., & Putra, A\. B\. \(2025\)\. Design and Construction of a Sales Information System Using ReactJS and ExpressJS Frameworks\. [https://ejournal\.uniks\.ac\.id/index\.php/JTOS/article/view/4985/3460](https://ejournal.uniks.ac.id/index.php/JTOS/article/view/4985/3460)

###### <a id="_nw375nw1fzac"></a>__Lampiran 1 : Source Code Program__

Link Repository Github : [https://github\.com/ReynerSR/pos\-tani](https://github.com/ReynerSR/pos-tani)

###### <a id="_tufp4fl97k5h"></a>Lampiran 2 : Dokumentasi Sistem

*Login*

![](...)

*Dashboard*

![](...)

*Kasir*

*![](...)*

*![](...)*

*![](...)![](...)*

*![](...)*

*Riwayat Transaksi*

*![](...)*

*![](...)*

*![](...)*

*Data Member*

*![](...)*

*![](...)*

*Master Produk*

*![](...)*

*![](...)*

*Master Supplier*

*![](...)*

*![](...)*

*Promo Produk*

*![](...)*

*![](...)*

*Modul Pembelian*

*![](...)*

*![](...)*

*Stock Opname*

*![](...)![](...)*

*Master Gudang*

*![](...)![](...)*

*Transfer Gudang*

*![](...)*

*![](...)*

*Laporan Penjualan*

*![](...)*

*Laporan Laba Kotor*

*![](...)*

*Aturan Membership*

*![](...)*

*Manajemen User*

*![](...)*

*![](...)*

*![](...)*

*Log Aktivitas*

*![](...)*

*Logout*

*![](...)*

###### <a id="_7wy66x7zk08v"></a>Lampiran 3 : Lembar Pencatatan Waktu Pengujian Efisiensi Operasional Transaksi

__A\. Pencatatan Waktu Sistem Legacy \(Sistem Lama\)__

No

Tahapan Proses Transaksi \(Kasir\)

Percobaan 1 \(Detik\)

Percobaan 2 \(Detik\)

Percobaan 3 \(Detik\)

Rata\-rata Waktu \(Detik\)

1

Navigasi dari *Dashboard* memuat Modul POS

34

36

35

__35__

2

Identifikasi dan pemilihan data *member* secara manual

26

24

25

__25__

3

Pencarian dan input barang ke keranjang \(manual/ *scroll*\)

21

18

21

__20__

4

Penentuan harga *customer* & otorisasi diskon manual

175

185

180

__180__

5

Eksekusi "Proses Transaksi" hingga struk siap dicetak

58

62

60

__60__

Total Waktu per Percobaan

314

325

321

320

__B\. Pencatatan Waktu Sistem Baru \(Sistem Informasi POS Berbasis Web\)__

No

Tahapan Proses Transaksi \(Kasir\)

Percobaan 1 \(Detik\)

Percobaan 2 \(Detik\)

Percobaan 3 \(Detik\)

Rata\-rata Waktu \(Detik\)

1

Navigasi dari *Dashboard* memuat Modul POS

3

3

3

3

2

Identifikasi data *member* \(*Live Search customer*\)

4

2

3

3

3

Pencarian dan input barang \(*Live Search* produk\)

2

2

2

2

4

Penentuan harga *customer* otomatis oleh *Rule\-Based System*

2

2

2

2

5

Eksekusi "Proses Transaksi" dan render halaman struk

5

4

6

5

Total Waktu per Percobaan

16

13

16

15

###### <a id="_4wtv98cltl6y"></a>Lampiran 4: Rincian Lembar Kasus Uji Pengujian Fungsional \(Black Box Testing\)

1. __T\-01: Modul Manajemen Pengguna \(Pembatasan Hak Akses\):__

- ID Kasus Uji: BBOX\-AUTH\-01
- Skenario Pengujian: Validasi pembatasan akses modul berbasis peran \(Role\-Based Access Control\)\.
- Kondisi Awal: Pengguna terautentikasi dan login menggunakan akun dengan peran Kasir \(Front Office\)\.
- Prosedur Uji:
	- Kasir berada di halaman Dashboard\.
	- Kasir mencoba mengakses menu laporan keuangan dengan mengetikkan rute URL Laporan Laba Kotor secara langsung di address bar browser\.
- Data Input: URL Endpoint Laporan Laba Kotor\.
- Hasil yang Diharapkan: Lapisan keamanan RoleMiddleware mendeteksi bahwa role tidak memiliki izin, lalu sistem memblokir akses secara paksa sehingga modul tersebut tidak dapat diakses atau ditampilkan\.
- Hasil Aktual: Sesuai\. Modul tidak dapat diakses dan sistem mengalihkan/mengunci percobaan akses tersebut\.

1. __T\-02: Modul Transaksi \(Live Search Produk\)__

- ID Kasus Uji: BBOX\-POS\-01
- Skenario Pengujian: Validasi pengambilan data produk secara asinkron tanpa memuat ulang halaman \(reload\)\.
- Kondisi Awal: Halaman utama kasir \(POS\) sedang terbuka dan siap digunakan\.
- Prosedur Uji:
	- Arahkan kursor ke form input pencarian produk\.
	- Ketikkan kata kunci "NPK"\.
- Data Input: keyword = "NPK"\.
- Hasil yang Diharapkan: Daftar produk dengan kata "NPK" muncul secara instan\. Sistem mengeksekusi panggilan API di latar belakang untuk merender daftar\.
- Hasil Aktual: Sesuai\. Fungsi searchProduct\(\) dipanggil via fetch\(\) API, dan hasil pencarian \(dropdown list\) muncul seketika mulai dari ketikan karakter pertama\.

1. __T\-03: Modul Transaksi \(Harga Negosiasi / Price Override\)__

- ID Kasus Uji: BBOX\-POS\-02
- Skenario Pengujian: Validasi keamanan tabel master product saat dilakukan perubahan harga negosiasi di meja kasir\.
- Kondisi Awal: Terdapat produk di dalam tabel keranjang kasir\. Harga master produk di database adalah Rp 150\.000\.
- Prosedur Uji:
	- Kasir mengeklik kolom harga pada item di dalam keranjang belanja\.
	- Kasir mengubah harga satuan tersebut menjadi Rp 145\.000 sesuai hasil kesepakatan dengan pelanggan lapangan\.
- Data Input: Harga Nego = 145000\.
- Hasil yang Diharapkan: Subtotal keranjang menyesuaikan harga nego baru\. Harga master di basis data tidak boleh ikut berubah/terpengaruh\.
- Hasil Aktual: Sesuai\. Fungsi setNegoPrice\(\) berhasil mengubah state harga pada kolom final\_unit\_price di keranjang \(client\-side\)\. Setelah transaksi disimpan, kolom selling\_price pada tabel products tetap tidak tersentuh \(aman\)\.

1. __T\-04: Modul Stok \(Peringatan Stok Kritis Otomatis\)__

- ID Kasus Uji: BBOX\-INV\-01
- Skenario Pengujian: Validasi pemunculan indikator otomatis ketika jumlah stok gudang menyentuh batas kritis\.
- Kondisi Awal: Produk "Pestisida A" memiliki nilai batas minimum\_stock = 10 dan jumlah stok fisik \( stock \) saat ini adalah 11 unit\.
- Prosedur Uji:
	- Kasir melakukan transaksi penjualan "Pestisida A" sebanyak 2 unit\.
	- Setelah struk tercetak, pengguna membuka halaman Dashboard\.
- Data Input: Checkout 2 unit produk\.
- Hasil yang Diharapkan: Dashboard secara otomatis memunculkan peringatan bahwa produk "Pestisida A" masuk dalam kategori stok kritis/hampir habis \(karena sisa 9 unit < 10 batas minimum\)\.
- Hasil Aktual: Sesuai\. DashboardController terbukti sukses menjalankan kueri logika whereColumn\('stock', '<=', 'minimum\_stock'\) dan memunculkan produk tersebut di dalam daftar peringatan\.

1. __T\-05: Modul Kasir \(Validasi Pembayaran Kurang\)__

- ID Kasus Uji: BBOX\-POS\-03
- Skenario Pengujian: Validasi pencegahan penyimpanan transaksi \(error handling\) ketika uang pembayaran tidak mencukupi tagihan\.
- Kondisi Awal: Total tagihan \(Grand Total\) di keranjang adalah Rp 500\.000\.
- Prosedur Uji:
	- Kasir menginput nominal uang tunai yang diterima pelanggan \(Cash Given\) sebesar Rp 400\.000\.
	- Kasir menekan tombol "Proses Transaksi"\.
- Data Input: Total = 500000, Cash Given = 400000\.
- Hasil yang Diharapkan: Sistem mencegah/membatalkan eksekusi penyimpanan ke database dan menampilkan pesan kesalahan \(alert\) bahwa pembayaran kurang\.
- Hasil Aktual: Sesuai\. Validasi berjalan lancar pada dua lapis keamanan: pencegahan awal dari client\-side melalui fungsi submitPos\(\), dan perlindungan di server\-side pada fungsi TransactionController@store\. Transaksi gagal tersimpan \(aman\)\.

###### <a id="_3p71n1ez6tdm"></a>Lampiran 5: Hasil Lembar User Acceptance Testing \(UAT\)

__A\. PENGUJIAN PERAN PEMILIK TOKO \(SUPER ADMIN\) Nama Penguji : Ibu Sianny Soesanto__

No

Modul / Fitur yang Diuji

Status Penilaian

Umpan Balik / Catatan Pengguna

1

Konfigurasi parameter diskon *tier* \(Bronze/Silver/Gold\) dan rasio poin via menu Aturan *Membership*\.

\[ v \] Diterima

\[ \] Ditolak

Pengaturan dapat diubah langsung tanpa bantuan teknis, perubahan langsung aktif untuk seluruh transaksi berikutnya\.

2

Mengakses Laporan Laba Kotor secara *real\-time* dengan berbagai filter periode waktu\.

\[ v \] Diterima

\[ \] Ditolak

Laporan dapat difilter per hari, minggu, bulan, tidak perlu lagi menunggu rekap manual di akhir bulan\.

3

Memantau jejak seluruh tindakan pengguna \(*Activity Log / Audit Trail*\)\.

\[ v \] Diterima

\[ \] Ditolak

Sangat baik\. Setiap perubahan data tercatat lengkap dengan keterangan waktu dan identitas pelaku/aktornya\.

4

Manajemen akun pengguna \(Tambah, edit, nonaktifkan akun staf\)\.

\[ v \] Diterima

\[ \] Ditolak

Pengelolaan tim dapat dilakukan mandiri dari sistem tanpa perlu koordinasi teknis eksternal\.

5

Memantau *Dashboard* ringkasan \(omzet, total transaksi, stok kritis\)\.

\[ v \] Diterima

\[ \] Ditolak

Seluruh indikator kunci operasional toko terpantau dalam satu halaman tanpa perlu membuka modul terpisah\.

B\. PENGUJIAN PERAN ADMIN OPERASIONAL \(BACK OFFICE\) Nama Penguji : Caca

No

Modul / Fitur yang Diuji

Status Penilaian

Umpan Balik / Catatan Pengguna

1

Penambahan dan pengeditan data master produk \(kode unik, HPP, harga jual, stok minimal\)\.

\[ v \] Diterima

\[ \] Ditolak

Form terstruktur dan validasi berjalan baik; kode produk otomatis unik dan kategori dapat ditentukan bebas\.

2

Pencatatan dokumen penerimaan barang masuk \(*Purchase / Restocking*\)\.

\[ v \] Diterima

\[ \] Ditolak

Proses input pembelian dari pemasok langsung memperbarui kuantitas stok gudang secara otomatis\.

3

Pelaksanaan *Stock Opname* dan penyesuaian selisih fisik gudang\.

\[ v \] Diterima

\[ \] Ditolak

Selisih stok antara hitungan fisik lapangan dan data sistem kini dapat dikoreksi dan tercatat secara transparan\.

4

Pengelolaan parameter promosi produk \(periode & nominal diskon\)\.

\[ v \] Diterima

\[ \] Ditolak

Promo yang diaktifkan di admin langsung terintegrasi dan berlaku di halaman antarmuka kasir\.

5

Menarik Laporan Penjualan \(filter periode harian/bulanan\)\.

\[ v \] Diterima

\[ \] Ditolak

Rekap transaksi penjualan mudah ditarik, dipantau, dan diekspor sesuai kebutuhan laporan harian\.

C\. PENGUJIAN PERAN KASIR \(FRONT OFFICE\) Nama Penguji : Vanesya

No

Modul / Fitur yang Diuji

Status Penilaian

Umpan Balik / Catatan Pengguna

1

Pencarian barang dengan fitur *Live Search* instan di antarmuka POS\.

\[ v \] Diterima

\[ \] Ditolak

Jauh lebih cepat dibanding sistem lama; kasir tidak perlu menghafal nama atau mencari *barcode* secara manual\.

2

Identifikasi *member* otomatis \(*Tier*, Saldo Poin, dan Pemotongan Diskon\)\.

\[ v \] Diterima

\[ \] Ditolak

Kasir tidak perlu lagi menghitung manual atau bertanya ke pemilik; pemotongan diskon langsung otomatis di layar\.

3

Penggunaan fitur *Price Override* \(input harga negosiasi/kesepakatan lapangan\)\.

\[ v \] Diterima

\[ \] Ditolak

Fleksibel dan aman\. Kasir dapat menyesuaikan harga nego dengan pelanggan tanpa merusak data harga referensi utama\.

4

Penukaran poin \(*Redeem*\) dan pencetakan struk via menu bawaan \(*window\.print* / WA\)\.

\[ v \] Diterima

\[ \] Ditolak

Proses sangat ringkas\. *Redeem* poin dan cetak struk dapat diselesaikan dalam 1 halaman alur tanpa pindah aplikasi\.

5

Eksekusi *Void* \(Pembatalan Transaksi\)\.

\[ v \] Diterima

\[ \] Ditolak

Praktis dan aman; pembatalan otomatis mengembalikan sisa stok ke gudang dan poin pelanggan kembali utuh\.

