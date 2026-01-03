CREATE DATABASE IF NOT EXISTS task5_social;
USE task5_social;

CREATE TABLE IF NOT EXISTS facebook_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120),
  email VARCHAR(150),
  password_hash VARCHAR(255),
  dob DATE,
  gender VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS instagram_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60),
  email VARCHAR(150),
  password_hash VARCHAR(255),
  phone VARCHAR(30),
  dob DATE
);

CREATE TABLE IF NOT EXISTS twitter_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  display_name VARCHAR(120),
  handle VARCHAR(60),
  email VARCHAR(150),
  password_hash VARCHAR(255),
  dob DATE
);
