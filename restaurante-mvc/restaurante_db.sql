-- ============================================================
-- RestaurantChain — Script SQL para MySQL
-- Base de datos: restaurante_db
-- Generado: 2026-03-05
-- ============================================================

CREATE DATABASE IF NOT EXISTS `restaurante_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `restaurante_db`;

-- ============================================================
-- Tabla: users (requerida por Laravel)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: categories
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `parent_id` BIGINT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`)
    REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: products
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `sku` VARCHAR(255) NOT NULL UNIQUE,
  `price` DECIMAL(10,2) NOT NULL,
  `cost` DECIMAL(10,2) NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `image` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `minimum_stock` INT NOT NULL DEFAULT 5,
  `unit` VARCHAR(50) NOT NULL DEFAULT 'unit',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: branches
-- ============================================================
CREATE TABLE IF NOT EXISTS `branches` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL UNIQUE,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `phone` VARCHAR(20) NULL,
  `email` VARCHAR(255) NULL,
  `manager_name` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `latitude` DECIMAL(10,8) NULL,
  `longitude` DECIMAL(11,8) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla pivot: branch_product
-- ============================================================
CREATE TABLE IF NOT EXISTS `branch_product` (
  `branch_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`branch_id`, `product_id`),
  CONSTRAINT `fk_bp_branch` FOREIGN KEY (`branch_id`)
    REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bp_product` FOREIGN KEY (`product_id`)
    REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: inventory_movements
-- ============================================================
CREATE TABLE IF NOT EXISTS `inventory_movements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `branch_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('in','out','adjust','transfer') NOT NULL DEFAULT 'in',
  `quantity` INT NOT NULL,
  `previous_stock` INT NOT NULL DEFAULT 0,
  `new_stock` INT NOT NULL DEFAULT 0,
  `reason` VARCHAR(255) NULL,
  `reference` VARCHAR(100) NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_im_product` FOREIGN KEY (`product_id`)
    REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_im_branch` FOREIGN KEY (`branch_id`)
    REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_im_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: migrations (requerida por Laravel)
-- ============================================================
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================

-- Categorías
INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Bebidas',          NULL, NULL, 1, 1, NOW(), NOW()),
(2, 'Comidas',          NULL, NULL, 1, 2, NOW(), NOW()),
(3, 'Postres',          NULL, NULL, 1, 3, NOW(), NOW()),
(4, 'Insumos',          NULL, NULL, 1, 4, NOW(), NOW()),
(5, 'Bebidas Calientes',NULL, 1,    1, 1, NOW(), NOW()),
(6, 'Bebidas Frías',    NULL, 1,    1, 2, NOW(), NOW()),
(7, 'Entradas',         NULL, 2,    1, 1, NOW(), NOW()),
(8, 'Platos Principales',NULL,2,    1, 2, NOW(), NOW());

-- Productos
INSERT INTO `products` (`id`, `name`, `sku`, `price`, `cost`, `category_id`, `is_active`, `minimum_stock`, `unit`, `created_at`, `updated_at`) VALUES
(1,  'Café Americano',     'BEB-001', 6.50,  1.20,  1, 1, 20, 'portion', NOW(), NOW()),
(2,  'Cappuccino',          'BEB-002', 8.00,  2.00,  1, 1, 15, 'portion', NOW(), NOW()),
(3,  'Agua Mineral 500ml',  'BEB-003', 3.00,  0.80,  1, 1, 50, 'unit',    NOW(), NOW()),
(4,  'Jugo de Naranja',     'BEB-004', 7.00,  1.50,  1, 1, 10, 'portion', NOW(), NOW()),
(5,  'Hamburguesa Clásica', 'COM-001', 22.00, 8.00,  2, 1, 10, 'portion', NOW(), NOW()),
(6,  'Pizza Margherita',    'COM-002', 35.00, 12.00, 2, 1,  5, 'portion', NOW(), NOW()),
(7,  'Ensalada César',      'COM-003', 18.00, 6.00,  2, 1,  8, 'portion', NOW(), NOW()),
(8,  'Pastel de Chocolate', 'POS-001', 12.00, 4.00,  3, 1, 10, 'portion', NOW(), NOW()),
(9,  'Helado Vainilla',     'POS-002', 8.00,  2.50,  3, 1, 15, 'portion', NOW(), NOW()),
(10, 'Azúcar 1kg',          'INS-001', 3.50,  2.00,  4, 1, 10, 'kg',      NOW(), NOW());

-- Sucursales
INSERT INTO `branches` (`id`, `name`, `code`, `city`, `address`, `manager_name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sucursal Centro',     'SUC-001', 'Lima',  'Av. Larco 1234',  'Juan Pérez',   1, NOW(), NOW()),
(2, 'Sucursal Miraflores', 'SUC-002', 'Lima',  'Calle Lima 456',  'María García', 1, NOW(), NOW()),
(3, 'Sucursal San Isidro', 'SUC-003', 'Lima',  'Jr. Puno 789',    'Carlos López', 1, NOW(), NOW()),
(4, 'Sucursal Cusco',      'SUC-004', 'Cusco', 'Plaza de Armas',  'Ana Torres',   1, NOW(), NOW());

-- Stock por sucursal (branch_product)
INSERT INTO `branch_product` (`branch_id`, `product_id`, `stock`, `is_available`) VALUES
-- Sucursal Centro
(1,1,25,1),(1,2,18,1),(1,3,60,1),(1,4,12,1),(1,5,15,1),(1,6,8,1),(1,7,10,1),(1,8,20,1),(1,9,30,1),(1,10,5,1),
-- Sucursal Miraflores
(2,1,30,1),(2,2,22,1),(2,3,45,1),(2,4,8,1),(2,5,12,1),(2,6,6,1),(2,7,9,1),(2,8,15,1),(2,9,25,1),(2,10,12,1),
-- Sucursal San Isidro
(3,1,20,1),(3,2,15,1),(3,3,80,1),(3,4,15,1),(3,5,20,1),(3,6,10,1),(3,7,12,1),(3,8,18,1),(3,9,22,1),(3,10,8,1),
-- Sucursal Cusco
(4,1,10,1),(4,2,8,1),(4,3,40,1),(4,4,5,1),(4,5,7,1),(4,6,4,1),(4,7,6,1),(4,8,12,1),(4,9,18,1),(4,10,3,1);

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
