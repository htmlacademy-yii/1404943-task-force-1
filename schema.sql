CREATE DATABASE IF NOT EXISTS taskforce DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_general_ci;
USE taskforce;

CREATE TABLE cities
(
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80),
  coordinates POINT
);

CREATE TABLE users
(
  id                INT AUTO_INCREMENT PRIMARY KEY,
  created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
  name              VARCHAR(80)  NOT NULL,
  email             VARCHAR(80)  NOT NULL UNIQUE,
  avatar_url        VARCHAR(255),
  city              INT          NOT NULL,
  password          VARCHAR(255) NOT NULL,
  role              INT          NOT NULL,
  birthday          DATE,
  phone             INT,
  url_telegram      VARCHAR(80),
  description       TEXT,
  specialization_id INT,
  FOREIGN KEY (city) REFERENCES cities (id)
);

CREATE TABLE specializations
(
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80)
);

CREATE TABLE user_specialization
(
  user_id           INT NOT NULL,
  specialization_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users (id),
  FOREIGN KEY (specialization_id) REFERENCES specializations (id)
);

CREATE TABLE category
(
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80),
  icon VARCHAR(20)
);


CREATE TABLE tasks
(
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80) NOT NULL,
  description TEXT        NOT NULL,
  category_id INT         NOT NULL,
  coordinates POINT       NOT NULL,
  price       INT         NOT NULL,
  finish_date DATE        NOT NULL,
  img         VARCHAR(255),
  user_id     INT         NOT NULL,
  status      ENUM ('new', 'in_progress', 'done', 'canceled')
                          NOT NULL DEFAULT 'new',
  created_at  DATETIME             DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users (id),
  FOREIGN KEY (category_id) REFERENCES category (id)
);

CREATE TABLE files
(
  id      INT AUTO_INCREMENT PRIMARY KEY,
  url     VARCHAR(80) NOT NULL,
  task_id INT         NOT NULL,
  FOREIGN KEY (task_id) REFERENCES tasks (id)
);
