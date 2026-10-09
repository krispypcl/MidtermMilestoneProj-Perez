-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2026 at 08:01 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lutong_bahay`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(6, 'Almusal (Breakfast)'),
(4, 'Kakanin (Rice Cake)'),
(3, 'Merienda (Snack)'),
(5, 'Panghimagas (Dessert)'),
(2, 'Sabaw (Soup)'),
(1, 'Ulam (Main Dish)');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `body` varchar(500) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `recipe_id`, `user_id`, `body`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Just like my lola used to make. Salamat!', '2026-10-09 13:46:08', NULL),
(4, 2, 4, 'testing', '2026-10-09 13:50:50', '2026-10-09 13:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`user_id`, `recipe_id`, `created_at`) VALUES
(2, 1, '2026-10-09 13:46:08'),
(4, 2, '2026-10-09 14:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipe_id`, `name`) VALUES
(1, 1, '1 kg chicken, cut into pieces'),
(2, 1, '1/2 cup soy sauce'),
(3, 1, '1/3 cup vinegar'),
(4, 1, '1 head garlic, crushed'),
(5, 1, '3 bay leaves'),
(6, 1, '1 tsp whole peppercorns'),
(7, 2, '1 kg pork ribs'),
(8, 2, '1 pack sinigang mix'),
(9, 2, '1 radish, sliced'),
(10, 2, '1 bunch kangkong'),
(11, 2, '2 tomatoes'),
(12, 3, '6 saba bananas'),
(13, 3, '1 cup ripe langka strips'),
(14, 3, '12 lumpia wrappers'),
(15, 3, '1 cup brown sugar'),
(23, 5, '1 tbsp Water'),
(24, 5, '3 cloves of Garlic');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `steps` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `user_id`, `category_id`, `title`, `description`, `steps`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Chicken Adobo', 'The classic soy-and-vinegar braise every Filipino home knows by heart.', '1. Marinate the chicken in soy sauce and garlic for 30 minutes.\n2. Add vinegar, bay leaves and peppercorns. Do not stir until it boils.\n3. Simmer for 30 minutes until tender and the sauce thickens.', '2026-10-07 13:46:08', NULL),
(2, 2, 2, 'Sinigang na Baboy', 'Sour tamarind pork soup, perfect for rainy days.', '1. Boil the pork until tender.\n2. Add onion and tomato, then the tamarind mix.\n3. Add radish, sitaw and kangkong. Season with patis.', '2026-10-08 13:46:08', NULL),
(3, 1, 3, 'Turon', 'Crispy banana spring rolls with caramelized sugar.', '1. Roll a slice of saba and langka in a wrapper with brown sugar.\n2. Fry until golden.\n3. Sprinkle more sugar while hot so it caramelizes.', '2026-10-09 13:46:08', NULL),
(5, 4, 1, 'Test Recipe', 'A test post...', 'Testing this app', '2026-10-09 13:57:39', '2026-10-09 13:57:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'aling_nena', '$2y$10$gq.JVHwdyV4E1zBp5YeB/OTYY3wi3T.Ibht59TGXlxxQQXjDIxLFC', '2026-10-09 13:46:08'),
(2, 'mang_tonyo', '$2y$10$gq.JVHwdyV4E1zBp5YeB/OTYY3wi3T.Ibht59TGXlxxQQXjDIxLFC', '2026-10-09 13:46:08'),
(4, 'testuser', '$2y$10$Z/A1xesIF1Y3mDMNIZ5H2.Gn.CuRhpiezdbBqT6gHZuaPdRazIBiS', '2026-10-09 13:49:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
