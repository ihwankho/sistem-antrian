-- phpMyAdmin SQL Dump
-- version 5.2.2-1.fc40
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 13 Okt 2025 pada 04.49
-- Versi server: 10.11.11-MariaDB
-- Versi PHP: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `AntrianPtsp`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_100_users` ()   BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE dept_id INT;

    WHILE i <= 100 DO
        -- Ganti daftar berikut sesuai ID yang tersedia di tabel `departemens`
        SET dept_id = ELT(FLOOR(1 + RAND() * 5), 1, 2, 3, 4, 5);

        INSERT INTO users (nama, nama_pengguna, password, role, id_departemen, created_at, updated_at)
        VALUES (
            CONCAT('User ', i + 1), -- agar tidak bentrok dengan user ke-1
            CONCAT('user', i + 1),
            MD5(CONCAT('password', i + 1)),
            IF(i % 10 = 0, 1, 2),  -- 1=admin, 2=staff
            dept_id,
            NOW(),
            NOW()
        );
        SET i = i + 1;
    END WHILE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_users` ()   BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE dept_id INT;
    
    WHILE i <= 100 DO
        -- Ambil id departemen secara acak dari daftar yang valid
        SET dept_id = ELT(FLOOR(1 + RAND() * 3), 1, 3, 4); -- hanya 1, 3, 4

        INSERT INTO users (nama, nama_pengguna, password, role, id_departemen, created_at, updated_at)
        VALUES (
            CONCAT('User ', i),
            CONCAT('user', i),
            MD5(CONCAT('password', i)),
            IF(i % 10 = 0, 1, 2),
            dept_id,
            NOW(),
            NOW()
        );
        SET i = i + 1;
    END WHILE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `antrians`
--

CREATE TABLE `antrians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `nomor_antrian` int(11) NOT NULL,
  `status_antrian` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=Diambil, 2=Dipanggil, 3=Selesai',
  `waktu_panggil` timestamp NULL DEFAULT NULL,
  `id_pengunjung` bigint(20) UNSIGNED NOT NULL,
  `id_pelayanan` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `antrians`
--

INSERT INTO `antrians` (`id`, `uuid`, `nomor_antrian`, `status_antrian`, `waktu_panggil`, `id_pengunjung`, `id_pelayanan`, `created_at`, `updated_at`) VALUES
(1, '74ddd9d0-078e-46b9-b676-e4bdcc4067fe', 1, 2, '2025-10-09 06:49:44', 1, 1, '2025-10-09 06:49:35', '2025-10-09 06:49:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `departemens`
--

CREATE TABLE `departemens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_departemen` varchar(255) NOT NULL,
  `id_loket` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `departemens`
--

INSERT INTO `departemens` (`id`, `nama_departemen`, `id_loket`, `created_at`, `updated_at`) VALUES
(1, 'Hukum', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(2, 'Perdata', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(3, 'Pidana', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `lokets`
--

CREATE TABLE `lokets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_loket` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `lokets`
--

INSERT INTO `lokets` (`id`, `nama_loket`, `created_at`, `updated_at`) VALUES
(1, 'Hukum', '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(2, 'Perdata', '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(3, 'Pidana', '2025-10-09 06:11:15', '2025-10-09 06:11:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_07_07_023035_create_users_table', 1),
(4, '2025_07_07_065417_add_role_to_users_table', 1),
(5, '2025_07_11_005059_create_departemens_table', 1),
(6, '2025_07_11_065721_create_pelayanans_table', 1),
(7, '2025_07_14_033834_update_table_users_add_id_departemen_and_change_role', 1),
(8, '2025_07_16_031341_create_pengunjung_table', 1),
(9, '2025_07_17_021719_create_sessions_table', 1),
(10, '2025_07_18_015012_create_loket_table', 1),
(11, '2025_07_18_021109_add_id_loket_to_departemens_table', 1),
(12, '2025_07_18_030045_update_table_users_change_fk', 1),
(13, '2025_07_18_030345_drop_fk_pengunjungs_table', 1),
(14, '2025_07_22_020814_create_antrian_table', 1),
(15, '2025_07_24_024427_update_pengunjungs_nik_anuniq_table', 1),
(16, '2025_08_01_063116_add_foto_to_users_table', 1),
(17, '2025_08_05_034323_create_panduans_table', 1),
(18, '2025_08_12_023108_create_personal_access_tokens_table', 1),
(19, '2025_09_22_054444_add_uuid_to_antrians_table', 1),
(20, '2025_09_25_144604_add_waktu_panggil_to_antrians_table', 1),
(21, '2025_10_09_123033_make_nik_nullable_in_pengunjungs_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `panduans`
--

CREATE TABLE `panduans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `isi_panduan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelayanans`
--

CREATE TABLE `pelayanans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_layanan` varchar(255) NOT NULL,
  `id_departemen` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pelayanans`
--

INSERT INTO `pelayanans` (`id`, `nama_layanan`, `id_departemen`, `created_at`, `updated_at`) VALUES
(1, 'Permohonan Waarmaking', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(2, 'Permohonan Surat Keterangan tidak tersangkut perkara pidana', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(3, 'Permohonan Surat Keterangan tidak sedang dicabut hak pilihnya', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(4, 'Permohonan pendaftaran surat kuasa', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(5, 'Permohonan Legalisasi surat', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(6, 'Permohonan Informasi sesuai dengan SK KMA 2-144', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(7, 'Penanganan Pengaduan/SIWAS MARI', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(8, 'Permohonan surat izin melaksanakan penelitian dan riset', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(9, 'Layanan-layanan lain yang berhubungan dengan pelayanan Kepaniteraan Hukum', 1, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(10, 'Pendaftaran perkara Gugatan biasa', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(11, 'Pendaftaran perkara Gugatan Sederhana', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(12, 'Pendaftaran Verzet atas putusan Verstek', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(13, 'Pendaftaran perkara Perlawanan/Bantahan', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(14, 'Pendaftaran perkara Permohonan', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(15, 'Pendaftaran permohonan Banding', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(16, 'Pendaftaran permohonan Kasasi', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(17, 'Pendaftaran permohonan Peninjauan Kembali', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(18, 'Permohonan dan Pengambilan Sisa Panjar biaya perkara', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(19, 'Permohonan dan Pengambilan Turunan/Salinan Putusan', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(20, 'Pendaftaran permohonan Eksekusi', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(21, 'Permohonan pengambilan uang Eksekusi', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(22, 'Pendaftaran permohonan Konsinyasi', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(23, 'Permohonan pengambilan uang Konsinyasi', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(24, 'Permohonan pencabutan gugatan, permohonan Banding, Kasasi, Peninjauan Kembali, Eksekusi, dan Konsinyasi', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(25, 'Permohonan Pendaftaran Keberatan Putusan KPPU', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(26, 'Permohonan Pendaftaran Keberatan Putusan BPSK', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(27, 'Layanan-layanan lain yang berhubungan dengan perkara', 2, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(28, 'Menerima pelimpahan berkas perkara pidana biasa, Perikanan Singkat, Ringan dan Cepat/ lalu lintas dari Penuntut Umum/Penyidik', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(29, 'Menerima pendaftaran permohonan praperadilan', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(30, 'Menerima permohonan perlawanan Banding, Kasasi, dan Peninjauan Kembali dan Grasi', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(31, 'Menerima permohonan pencabutan perlawanan Banding, Kasasi, dan Peninjauan Kembali dan Grasi', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(32, 'Menerima permohonan izin/persetujuan penyitaan dan menyerahkan izin/persetujuan penyitaan yang sudah ditanda tangani oleh Ketua Pengadilan Negeri Banyuwangi', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(33, 'Menerima permohonan izin persetujuan pemusnahan Barang bukti dan/atau pelelangan Barang bukti', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(34, 'Menerima permohonan penetapan perpanjangan penahanan yang sudah ditanda tangani oleh Ketua Pengadilan Negeri Banyuwangi', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(35, 'Menerima permohonan pembantaran dan menyerahkan persetujuan pembantaran yang sudah ditanda tangani oleh Ketua Pengadilan Negeri Banyuwangi', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(36, 'Menerima permohonan izin besuk dan menyerahkan pemberian izin besuk', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(37, 'Menerima permohonan dan menyerahkan izin berobat bagi terdakwa yang telah ditanda tangani oleh Ketua Pengadilan Negeri Banyuwangi', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15'),
(38, 'Layanan-layanan lain yang berhubungan dengan proses dan penyelesaian perkara pidana kekhususan', 3, '2025-10-09 06:11:15', '2025-10-09 06:11:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengunjungs`
--

CREATE TABLE `pengunjungs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_pengunjung` varchar(255) NOT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `no_hp` varchar(15) NOT NULL,
  `jenis_kelamin` enum('laki-laki','perempuan') NOT NULL,
  `alamat` text NOT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_wajah` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pengunjungs`
--

INSERT INTO `pengunjungs` (`id`, `nama_pengunjung`, `nik`, `no_hp`, `jenis_kelamin`, `alamat`, `foto_ktp`, `foto_wajah`, `created_at`, `updated_at`) VALUES
(1, 'falen', NULL, '123456789012', 'laki-laki', 'q', NULL, 'wajah/ewoCPmtlCr6O1owkdmkDz66BIFt544CsCvZ6WGvN.jpg', '2025-10-09 06:49:35', '2025-10-09 06:49:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'API Token', 'fbd52cc0c4f1f0b6bdf376f8200a663bb79cbe9608dac549a1e445f925e3a5db', '[\"*\"]', NULL, NULL, '2025-10-09 06:50:36', '2025-10-09 06:50:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nama_pengguna` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `role` int(11) NOT NULL,
  `id_loket` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `nama_pengguna`, `password`, `foto`, `role`, `id_loket`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'adminpn', '$2y$12$Z2Z172yeeWYiNGbTszb9au7vBYQK2nO.bhzhmG/VSPbJVAiZnPgX2', NULL, 1, NULL, '2025-10-09 06:11:15', '2025-10-09 06:50:36');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `antrians`
--
ALTER TABLE `antrians`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `antrians_uuid_unique` (`uuid`),
  ADD KEY `antrians_id_pengunjung_foreign` (`id_pengunjung`),
  ADD KEY `antrians_id_pelayanan_foreign` (`id_pelayanan`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `departemens`
--
ALTER TABLE `departemens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departemens_nama_departemen_unique` (`nama_departemen`),
  ADD KEY `departemens_id_loket_foreign` (`id_loket`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `lokets`
--
ALTER TABLE `lokets`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `panduans`
--
ALTER TABLE `panduans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pelayanans`
--
ALTER TABLE `pelayanans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelayanans_id_departemen_foreign` (`id_departemen`);

--
-- Indeks untuk tabel `pengunjungs`
--
ALTER TABLE `pengunjungs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengunjungs_no_hp_unique` (`no_hp`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_nama_pengguna_unique` (`nama_pengguna`),
  ADD KEY `users_id_loket_foreign` (`id_loket`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `antrians`
--
ALTER TABLE `antrians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `departemens`
--
ALTER TABLE `departemens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `lokets`
--
ALTER TABLE `lokets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `panduans`
--
ALTER TABLE `panduans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pelayanans`
--
ALTER TABLE `pelayanans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT untuk tabel `pengunjungs`
--
ALTER TABLE `pengunjungs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `antrians`
--
ALTER TABLE `antrians`
  ADD CONSTRAINT `antrians_id_pelayanan_foreign` FOREIGN KEY (`id_pelayanan`) REFERENCES `pelayanans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `antrians_id_pengunjung_foreign` FOREIGN KEY (`id_pengunjung`) REFERENCES `pengunjungs` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `departemens`
--
ALTER TABLE `departemens`
  ADD CONSTRAINT `departemens_id_loket_foreign` FOREIGN KEY (`id_loket`) REFERENCES `lokets` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pelayanans`
--
ALTER TABLE `pelayanans`
  ADD CONSTRAINT `pelayanans_id_departemen_foreign` FOREIGN KEY (`id_departemen`) REFERENCES `departemens` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_id_loket_foreign` FOREIGN KEY (`id_loket`) REFERENCES `lokets` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
