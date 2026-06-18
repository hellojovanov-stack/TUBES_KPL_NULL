CREATE TABLE IF NOT EXISTS `kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `kategori` (`nama_kategori`, `deskripsi`) VALUES
('Demam', 'Obat-obatan untuk mengatasi demam dan panas'),
('Antibiotik', 'Obat antibiotik untuk infeksi bakteri'),
('Vitamin', 'Suplemen vitamin dan mineral'),
('Analgesik', 'Obat pereda nyeri'),
('Sirup', 'Obat dalam bentuk sirup'),
('Tablet', 'Obat dalam bentuk tablet');

CREATE TABLE IF NOT EXISTS `supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `supplier` (`nama_supplier`, `alamat`, `telepon`) VALUES
('Kimia Farma', 'Jl. Kebon Jeruk No. 12, Jakarta', '021-5551234'),
('Indofarma', 'Jl. Industri No. 45, Cikarang', '021-8905678'),
('Kalbe Farma', 'Jl. Letjen Suprapto Kav. 4, Jakarta', '021-42873000');

CREATE TABLE IF NOT EXISTS `riwayat_transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total_bayar` int(11) NOT NULL DEFAULT 0,
  `jumlah_item` int(11) NOT NULL DEFAULT 0,
  `kasir` varchar(100) DEFAULT 'admin',
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `obat`
  ADD COLUMN `id_kategori` int(11) DEFAULT NULL AFTER `kategori`,
  ADD COLUMN `id_supplier` int(11) DEFAULT NULL AFTER `id_kategori`;

ALTER TABLE `obat`
  ADD CONSTRAINT `fk_obat_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_obat_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `transaksi`
  ADD COLUMN `id_riwayat` int(11) DEFAULT NULL AFTER `id`;

ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_riwayat` FOREIGN KEY (`id_riwayat`) REFERENCES `riwayat_transaksi`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
