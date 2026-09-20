-- =====================================================================
-- StayNest - Student Accommodation & PG Booking Platform Database
-- Database: student_accommodation
-- Normalized MySQL Schema & Realistic Sample Data
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `student_accommodation` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `student_accommodation`;

-- ---------------------------------------------------------------------
-- Table: users
-- Stores registered students with bcrypt hashed passwords
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: properties
-- Stores PG & student accommodation listings
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `properties` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Co-living') NOT NULL,
    `rating` DECIMAL(2,1) NOT NULL DEFAULT 4.0,
    `description` TEXT NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: amenities
-- Master table of amenities available in accommodations
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `amenities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-check-circle'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: property_amenities
-- Junction table mapping properties to their available amenities
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `property_amenities` (
    `property_id` INT NOT NULL,
    `amenity_id` INT NOT NULL,
    PRIMARY KEY (`property_id`, `amenity_id`),
    CONSTRAINT `fk_pa_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pa_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: property_images
-- Supports multiple gallery images per property listing
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `property_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `property_id` INT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    CONSTRAINT `fk_pi_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: interested_users
-- Tracks student shortlist / interest with unique constraint per user & property
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `interested_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `property_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_user_property` (`user_id`, `property_id`),
    CONSTRAINT `fk_iu_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_iu_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SAMPLE DATA INSERTION
-- =====================================================================

-- 1. Insert Amenities
INSERT INTO `amenities` (`id`, `name`, `icon`) VALUES
(1, 'WiFi', 'bi-wifi'),
(2, 'AC', 'bi-snow'),
(3, 'Food', 'bi-egg-fried'),
(4, 'Laundry', 'bi-droplet-half'),
(5, 'Parking', 'bi-p-circle'),
(6, 'CCTV', 'bi-shield-check'),
(7, 'Power Backup', 'bi-lightning-charge'),
(8, 'Study Room', 'bi-book'),
(9, 'Gym', 'bi-heart-pulse'),
(10, 'Housekeeping', 'bi-stars')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. Insert Users (Password: Password123! - bcrypt hash)
-- Hash generated via password_hash('Password123!', PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `created_at`) VALUES
(1, 'Rahul Sharma', 'rahul@student.edu', '$2y$10$w09yJc6pS611sFsqyvRjC.N8Wq6Q018T59qN.02G0M9Z6U1j0U0yO', '+91 9876543210', '2026-01-15 10:00:00'),
(2, 'Priya Kulkarni', 'priya@student.edu', '$2y$10$w09yJc6pS611sFsqyvRjC.N8Wq6Q018T59qN.02G0M9Z6U1j0U0yO', '+91 9845012345', '2026-02-01 11:30:00'),
(3, 'Vikram Patil', 'vikram@student.edu', '$2y$10$w09yJc6pS611sFsqyvRjC.N8Wq6Q018T59qN.02G0M9Z6U1j0U0yO', '+91 9741234567', '2026-02-10 14:15:00')
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`);

-- 3. Insert 15 Realistic Properties Across Required Cities
INSERT INTO `properties` (`id`, `name`, `city`, `address`, `price`, `gender`, `rating`, `description`, `image`, `created_at`) VALUES
(1, 'Student Nest PG', 'Bengaluru', '5th Block, Koramangala, Near Jyoti Nivas College', 7500.00, 'Male', 4.6, 'Premium student residence with high-speed 500Mbps WiFi, 3 hygienic meals daily, biometric entry, and quiet study lounges. Walking distance from major colleges and tech parks.', 'assets/images/property-1.jpg', '2026-01-10 09:00:00'),
(2, 'Campus Comfort Stay', 'Dharwad', 'Near Karnatak University Campus, Kelageri Road', 4500.00, 'Co-living', 4.4, 'Spacious and peaceful student accommodation designed specifically for university students. Features serene study gardens, home-cooked North/South Indian meals, and 24/7 security.', 'assets/images/property-2.jpg', '2026-01-12 10:30:00'),
(3, 'Scholar''s Residency', 'Hubballi', 'Vidyanagar, Opposite KLE Technological University', 5000.00, 'Male', 4.3, 'Strategically located directly opposite university gates. Well-ventilated rooms with study tables, hot water, power backup, laundry service, and delicious veg/non-veg mess.', 'assets/images/property-3.jpg', '2026-01-14 11:00:00'),
(4, 'Royal Palace Luxury Stay', 'Mysuru', 'Saraswathipuram, Near St. Philomena''s College', 6500.00, 'Female', 4.7, 'Safe and secure women''s PG with round-the-clock female security warden, CCTV surveillance, finger-print access, fully furnished rooms, attached washrooms, and healthy home-style dining.', 'assets/images/property-4.jpg', '2026-01-18 12:00:00'),
(5, 'Cyber Stay Co-living', 'Hyderabad', 'Phase 2, HITEC City, Near IIIT Hyderabad', 9500.00, 'Co-living', 4.8, 'Modern high-tech co-living space tailored for IT scholars and university interns. Offers AC rooms, ergonomic workstations, gaming lounge, gym, high-speed fiber internet, and buffet meals.', 'assets/images/property-5.jpg', '2026-01-20 13:00:00'),
(6, 'Elite Student Living', 'Pune', 'Viman Nagar, Close to Symbiosis International University', 8500.00, 'Female', 4.6, 'Upscale girls PG offering luxury shared and private rooms. Includes air-conditioned rooms, daily housekeeping, chef-prepared meals, rooftop relaxation deck, and secure digital entry.', 'assets/images/property-6.jpg', '2026-01-22 14:00:00'),
(7, 'Green View PG', 'Bengaluru', 'Electronic City Phase 1, Near PES University Campus', 6000.00, 'Male', 4.2, 'Affordable, clean accommodation surrounded by lush greenery. Includes WiFi, purified drinking water, daily cleaning, nutritious home-cooked meals, and two-wheeler parking.', 'assets/images/property-7.jpg', '2026-01-25 15:00:00'),
(8, 'Serene Girls Hostel & PG', 'Dharwad', 'Kalyan Nagar, Near SDM College of Medical Sciences', 4200.00, 'Female', 4.5, 'Warm and welcoming residence for female scholars and medical students. Safe neighborhood, in-house warden, study desk in every room, laundry machines, and wholesome local cuisine.', 'assets/images/property-8.jpg', '2026-01-28 16:00:00'),
(9, 'Tech Hub Student Living', 'Hubballi', 'BVB Campus Road, Vidyanagar', 5500.00, 'Co-living', 4.4, 'Vibrant student community housing engineers and design students. Features high-speed fiber internet, open terrace study zone, modern kitchen facilities, and bi-weekly events.', 'assets/images/property-9.jpg', '2026-02-02 09:30:00'),
(10, 'Heritage PG for Men', 'Mysuru', 'Gokulam 3rd Stage, Near CFTRI & Yoga Hub', 5200.00, 'Male', 4.3, 'Peaceful accommodation in Mysore''s premier residential locality. Spacious double and triple sharing rooms, clean bathrooms, laundry facilities, and authentic traditional food.', 'assets/images/property-10.jpg', '2026-02-05 10:45:00'),
(11, 'Skyline Deluxe Residency', 'Hyderabad', 'Telecom Nagar, Gachibowli, Near University of Hyderabad', 10000.00, 'Co-living', 4.9, 'Premium co-living property with hotel-style amenities. Includes air conditioning, smart TVs, rooftop cafeteria, professional housekeeping, laundry care, and 24/7 fitness center.', 'assets/images/property-11.jpg', '2026-02-08 11:15:00'),
(12, 'Oxford Haven PG', 'Pune', 'Kothrud, Near MIT World Peace University', 7000.00, 'Male', 4.5, 'Top-rated boy''s accommodation located minutes from top colleges. Fully furnished with box beds, individual wardrobes, high-speed WiFi, RO water, and vegetarian & non-vegetarian mess.', 'assets/images/property-12.jpg', '2026-02-12 12:30:00'),
(13, 'Blossom Luxury Residence', 'Bengaluru', '100 Feet Road, Indiranagar, Near Metro Station', 10500.00, 'Female', 4.8, 'Boutique women''s residence in prime Indiranagar. Air-conditioned suites, aesthetic interior design, biometric security, microwave & fridge access, daily housekeeping, and 3-course meals.', 'assets/images/property-13.jpg', '2026-02-15 13:45:00'),
(14, 'Vidyarthi Nilaya', 'Dharwad', 'Saptapur Last Bus Stop, Near JSS College', 4000.00, 'Male', 4.1, 'Budget-friendly, highly practical hostel for competitive exam aspirants and college students. Quiet surroundings, dedicated library hall, hot water solar heaters, and hygienic meal plans.', 'assets/images/property-14.jpg', '2026-02-18 14:20:00'),
(15, 'Phoenix Premium Co-living', 'Pune', 'Phase 1, Hinjewadi Rajiv Gandhi Infotech Park', 9000.00, 'Co-living', 4.7, 'State-of-the-art student & tech intern co-living complex. Community kitchen, high-speed mesh WiFi, recreation room with pool table, weekly social mixers, and daily sanitization.', 'assets/images/property-15.jpg', '2026-02-22 15:10:00')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 4. Insert Property Amenities Mappings
INSERT INTO `property_amenities` (`property_id`, `amenity_id`) VALUES
-- Property 1 (Student Nest PG - Bengaluru)
(1, 1), (1, 3), (1, 4), (1, 6), (1, 7), (1, 8), (1, 10),
-- Property 2 (Campus Comfort Stay - Dharwad)
(2, 1), (2, 3), (2, 5), (2, 6), (2, 7), (2, 8),
-- Property 3 (Scholar's Residency - Hubballi)
(3, 1), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7),
-- Property 4 (Royal Palace Luxury Stay - Mysuru)
(4, 1), (4, 2), (4, 3), (4, 4), (4, 6), (4, 7), (4, 10),
-- Property 5 (Cyber Stay Co-living - Hyderabad)
(5, 1), (5, 2), (5, 3), (5, 4), (5, 6), (5, 7), (5, 8), (5, 9), (5, 10),
-- Property 6 (Elite Student Living - Pune)
(6, 1), (6, 2), (6, 3), (6, 4), (6, 6), (6, 7), (6, 8), (6, 10),
-- Property 7 (Green View PG - Bengaluru)
(7, 1), (7, 3), (7, 5), (7, 6), (7, 7), (7, 10),
-- Property 8 (Serene Girls Hostel - Dharwad)
(8, 1), (8, 3), (8, 4), (8, 6), (8, 7), (8, 8), (8, 10),
-- Property 9 (Tech Hub Student Living - Hubballi)
(9, 1), (9, 3), (9, 4), (9, 5), (9, 6), (9, 7), (9, 8),
-- Property 10 (Heritage PG for Men - Mysuru)
(10, 1), (10, 3), (10, 5), (10, 6), (10, 7), (10, 10),
-- Property 11 (Skyline Deluxe Residency - Hyderabad)
(11, 1), (11, 2), (11, 3), (11, 4), (11, 5), (11, 6), (11, 7), (11, 8), (11, 9), (11, 10),
-- Property 12 (Oxford Haven PG - Pune)
(12, 1), (12, 3), (12, 4), (12, 6), (12, 7), (12, 8), (12, 10),
-- Property 13 (Blossom Luxury Residence - Bengaluru)
(13, 1), (13, 2), (13, 3), (13, 4), (13, 6), (13, 7), (13, 8), (13, 10),
-- Property 14 (Vidyarthi Nilaya - Dharwad)
(14, 1), (14, 3), (14, 5), (14, 6), (14, 7), (14, 8),
-- Property 15 (Phoenix Premium Co-living - Pune)
(15, 1), (15, 2), (15, 3), (15, 4), (15, 6), (15, 7), (15, 8), (15, 9), (15, 10)
ON DUPLICATE KEY UPDATE `property_id`=VALUES(`property_id`);

-- 5. Insert Multiple Gallery Images per Property
INSERT INTO `property_images` (`property_id`, `image_url`) VALUES
(1, 'assets/images/property-1.jpg'),
(1, 'assets/images/gallery/p1-room.jpg'),
(1, 'assets/images/gallery/p1-mess.jpg'),
(1, 'assets/images/gallery/p1-study.jpg'),
(2, 'assets/images/property-2.jpg'),
(2, 'assets/images/gallery/p2-room.jpg'),
(2, 'assets/images/gallery/p2-lounge.jpg'),
(3, 'assets/images/property-3.jpg'),
(3, 'assets/images/gallery/p3-room.jpg'),
(3, 'assets/images/gallery/p3-dining.jpg'),
(4, 'assets/images/property-4.jpg'),
(4, 'assets/images/gallery/p4-room.jpg'),
(4, 'assets/images/gallery/p4-lobby.jpg'),
(5, 'assets/images/property-5.jpg'),
(5, 'assets/images/gallery/p5-room.jpg'),
(5, 'assets/images/gallery/p5-gym.jpg'),
(5, 'assets/images/gallery/p5-lounge.jpg'),
(6, 'assets/images/property-6.jpg'),
(6, 'assets/images/gallery/p6-room.jpg'),
(6, 'assets/images/gallery/p6-deck.jpg'),
(7, 'assets/images/property-7.jpg'),
(7, 'assets/images/gallery/p7-room.jpg'),
(8, 'assets/images/property-8.jpg'),
(8, 'assets/images/gallery/p8-room.jpg'),
(9, 'assets/images/property-9.jpg'),
(9, 'assets/images/gallery/p9-room.jpg'),
(10, 'assets/images/property-10.jpg'),
(10, 'assets/images/gallery/p10-room.jpg'),
(11, 'assets/images/property-11.jpg'),
(11, 'assets/images/gallery/p11-room.jpg'),
(11, 'assets/images/gallery/p11-gym.jpg'),
(12, 'assets/images/property-12.jpg'),
(12, 'assets/images/gallery/p12-room.jpg'),
(13, 'assets/images/property-13.jpg'),
(13, 'assets/images/gallery/p13-room.jpg'),
(14, 'assets/images/property-14.jpg'),
(14, 'assets/images/gallery/p14-room.jpg'),
(15, 'assets/images/property-15.jpg'),
(15, 'assets/images/gallery/p15-room.jpg'),
(15, 'assets/images/gallery/p15-games.jpg');

-- 6. Insert Sample Shortlisted / Interested Records
INSERT INTO `interested_users` (`user_id`, `property_id`, `created_at`) VALUES
(1, 1, '2026-02-25 10:00:00'),
(1, 5, '2026-02-26 14:30:00'),
(1, 11, '2026-02-27 18:45:00'),
(2, 4, '2026-02-28 09:15:00'),
(2, 6, '2026-03-01 16:20:00')
ON DUPLICATE KEY UPDATE `created_at`=VALUES(`created_at`);
