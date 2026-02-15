-- Mood-Based Recipe Recommender Database Schema
-- MySQL 5.7+ / MariaDB 10.2+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Database: mood_food_recommender
CREATE DATABASE IF NOT EXISTS `mood_food_recommender` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mood_food_recommender`;

-- Table: users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `premium_expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_premium` (`is_premium`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: moods
CREATE TABLE `moods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text,
  `icon` varchar(100) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#FFB6C1',
  `food_intent` text COMMENT 'Description of food characteristics for this mood',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cuisines
CREATE TABLE `cuisines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `flag_emoji` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: recipes
CREATE TABLE `recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `cuisine_id` int(11) NOT NULL,
  `description` text,
  `image_url` varchar(500) DEFAULT NULL,
  `is_veg` tinyint(1) DEFAULT 0,
  `is_high_protein` tinyint(1) DEFAULT 0,
  `calories` int(11) DEFAULT NULL,
  `proteins_g` decimal(5,2) DEFAULT NULL,
  `carbs_g` decimal(5,2) DEFAULT NULL,
  `fats_g` decimal(5,2) DEFAULT NULL,
  `prep_time` int(11) DEFAULT NULL COMMENT 'Minutes',
  `cook_time` int(11) DEFAULT NULL COMMENT 'Minutes',
  `servings` int(11) DEFAULT 1,
  `ingredients_json` text COMMENT 'JSON array of {qty, unit, item}',
  `steps_json` text COMMENT 'JSON array of ordered steps',
  `mood_tags_json` text COMMENT 'JSON array of mood slugs',
  `views_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_cuisine` (`cuisine_id`),
  KEY `idx_veg` (`is_veg`),
  KEY `idx_high_protein` (`is_high_protein`),
  KEY `idx_active` (`is_active`),
  KEY `idx_views` (`views_count`),
  FULLTEXT KEY `ft_title_desc` (`title`, `description`),
  CONSTRAINT `fk_recipe_cuisine` FOREIGN KEY (`cuisine_id`) REFERENCES `cuisines` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user_saved_recipes
CREATE TABLE `user_saved_recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_recipe` (`user_id`, `recipe_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_recipe` (`recipe_id`),
  CONSTRAINT `fk_saved_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saved_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sessions (for PHP session management - optional, can use PHP native sessions)
CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `data` text,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

