CREATE DATABASE IF NOT EXISTS db_reservasi_ruangan;
USE db_reservasi_ruangan;

DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS buildings;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL UNIQUE,
    label VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    address VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    department_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    nim_nidn VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    floor VARCHAR(20) NOT NULL,
    capacity INT NOT NULL,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_rooms_building FOREIGN KEY (building_id) REFERENCES buildings(id) ON DELETE RESTRICT
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    purpose TEXT NOT NULL,
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    participants INT NOT NULL,
    document VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','verified','approved','rejected','cancelled') DEFAULT 'pending',
    admin_note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservation_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reservation_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

INSERT INTO roles (id, name, label) VALUES
(1, 'admin', 'Admin'),
(2, 'dosen', 'Dosen'),
(3, 'mahasiswa', 'Mahasiswa');

INSERT INTO departments (id, name) VALUES
(1, 'Umum'),
(2, 'Teknik Informatika'),
(3, 'Sistem Informasi'),
(4, 'Manajemen'),
(5, 'Teknik Sipil');

INSERT INTO buildings (id, name, address) VALUES
(1, 'Gedung A', 'Kampus Utama Blok A'),
(2, 'Gedung B', 'Kampus Utama Blok B'),
(3, 'Gedung C', 'Kampus Utama Blok C'),
(4, 'Gedung D', 'Kampus Utama Blok D');

INSERT INTO users (name, email, password, role_id, department_id, phone, nim_nidn) VALUES
('Administrator', 'admin@example.com', '$2y$12$H1OkoVAw4jxpgwPZD3GLAuCZOUnZQdbw1yc/QGaME/ylUD5Rj0KVG', 1, 1, '081234567890', 'ADM001'),
('Dosen Demo', 'dosen@example.com', '$2y$12$CkzsTKJUPlGi5XUrEnuHneeX9IYbxRLZUUOBcvg21b8.5WWcY31v.', 2, 2, '081111111111', 'NIDN001'),
('Mahasiswa Demo', 'mahasiswa@example.com', '$2y$12$4gXzZ7l900dxdHKjRM8UtuajsJp7imG/vLWz2Ly52yiui2OScqN3e', 3, 3, '082222222222', '22123456');

INSERT INTO rooms (name, building_id, floor, capacity, status, description) VALUES
('Ruang Seminar 1', 1, 'Lantai 2', 50, 'aktif', 'Ruangan untuk seminar dan presentasi.'),
('Lab Komputer 2', 2, 'Lantai 1', 35, 'aktif', 'Laboratorium komputer untuk praktikum.'),
('Ruang Rapat Pimpinan', 3, 'Lantai 3', 20, 'aktif', 'Ruangan rapat internal.'),
('Aula Kecil', 4, 'Lantai Dasar', 80, 'aktif', 'Aula untuk kegiatan kampus skala sedang.');

INSERT INTO reservations (user_id, room_id, title, purpose, reservation_date, start_time, end_time, participants, status, admin_note) VALUES
(2, 1, 'Seminar Metodologi Penelitian', 'Kegiatan seminar dosen bersama mahasiswa.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '09:30:00', 40, 'approved', 'Disetujui admin.'),
(3, 2, 'Diskusi Kelompok', 'Diskusi tugas akhir mahasiswa.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '13:30:00', 10, 'pending', 'Menunggu verifikasi admin.');
