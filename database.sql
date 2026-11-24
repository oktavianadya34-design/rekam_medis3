-- Database Aplikasi Rekam Medis Sederhana (data fiktif)
CREATE DATABASE IF NOT EXISTS db_rekam_medis CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_rekam_medis;

DROP TABLE IF EXISTS rekam_medis;
DROP TABLE IF EXISTS pasien;
DROP TABLE IF EXISTS dokter;
DROP TABLE IF EXISTS obat;

CREATE TABLE pasien (
  id_pasien     INT(11)      NOT NULL AUTO_INCREMENT,
  no_rm         VARCHAR(20)  NOT NULL,
  nama_pasien   VARCHAR(100) NOT NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  tanggal_lahir DATE         NOT NULL,
  alamat        TEXT         NOT NULL,
  PRIMARY KEY (id_pasien),
  UNIQUE KEY uk_no_rm (no_rm)
) ENGINE=InnoDB;

CREATE TABLE dokter (
  id_dokter    INT(11)      NOT NULL AUTO_INCREMENT,
  nama_dokter  VARCHAR(100) NOT NULL,
  spesialisasi VARCHAR(50)  NOT NULL,
  PRIMARY KEY (id_dokter)
) ENGINE=InnoDB;

CREATE TABLE obat (
  id_obat   INT(11)      NOT NULL AUTO_INCREMENT,
  nama_obat VARCHAR(100) NOT NULL,
  satuan    VARCHAR(30)  NOT NULL,
  stok      INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (id_obat)
) ENGINE=InnoDB;

CREATE TABLE rekam_medis (
  id_rekam        INT(11) NOT NULL AUTO_INCREMENT,
  id_pasien       INT(11) NOT NULL,
  id_dokter       INT(11) NOT NULL,
  id_obat         INT(11) NOT NULL,
  tanggal_periksa DATE    NOT NULL,
  keluhan         TEXT    NOT NULL,
  diagnosa        TEXT    NOT NULL,
  tindakan        TEXT    NOT NULL,
  resep           VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_rekam),
  KEY idx_pasien (id_pasien),
  KEY idx_dokter (id_dokter),
  KEY idx_obat (id_obat),
  CONSTRAINT fk_rekam_pasien FOREIGN KEY (id_pasien) REFERENCES pasien (id_pasien) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_rekam_dokter FOREIGN KEY (id_dokter) REFERENCES dokter (id_dokter) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_rekam_obat   FOREIGN KEY (id_obat)   REFERENCES obat (id_obat)     ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Data uji (fiktif)
INSERT INTO pasien (no_rm, nama_pasien, jenis_kelamin, tanggal_lahir, alamat) VALUES
('RM-0001', 'Budi Santoso',  'L', '2005-03-14', 'Jl. Contoh No. 1, Tuban'),
('RM-0002', 'Siti Aminah',   'P', '1998-07-22', 'Jl. Melati No. 5, Tuban'),
('RM-0003', 'Agus Prasetyo', 'L', '1985-11-02', 'Jl. Mawar No. 9, Tuban');

INSERT INTO dokter (nama_dokter, spesialisasi) VALUES
('dr. Rina Wulandari', 'Umum'),
('dr. Hendra Gunawan, Sp.PD', 'Penyakit Dalam');

INSERT INTO obat (nama_obat, satuan, stok) VALUES
('Paracetamol 500 mg', 'Tablet', 120),
('Amoxicillin 500 mg', 'Kapsul', 80),
('OBH Sirup', 'Sirup', 35);

INSERT INTO rekam_medis (id_pasien, id_dokter, id_obat, tanggal_periksa, keluhan, diagnosa, tindakan, resep) VALUES
(1, 1, 1, '2026-11-02', 'Demam dan sakit kepala sejak 2 hari', 'Demam (febris)', 'Pemeriksaan suhu dan tekanan darah', '3 x 1 tablet sesudah makan'),
(2, 2, 2, '2026-11-03', 'Nyeri tenggorokan dan sulit menelan', 'Faringitis akut', 'Pemeriksaan tenggorokan', '3 x 1 kapsul sesudah makan'),
(3, 1, 3, '2026-11-04', 'Batuk berdahak 5 hari', 'Bronkitis ringan', 'Pemeriksaan paru dan edukasi istirahat', '3 x 1 sendok makan');
