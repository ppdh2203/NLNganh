CREATE DATABASE IF NOT EXISTS scholarship
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE scholarship;

-- Tài khoản dùng chung cho Student, Provider và Admin
CREATE TABLE users (
    user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Student', 'Provider', 'Admin') NOT NULL,
    status ENUM('Pending', 'Active', 'Locked') NOT NULL DEFAULT 'Active',
    avatar VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Thông tin riêng của tài khoản Student
CREATE TABLE students (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    student_code VARCHAR(50) NULL,
    student_school VARCHAR(150) NOT NULL,
    student_major VARCHAR(150) NOT NULL,

    CONSTRAINT fk_students_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);