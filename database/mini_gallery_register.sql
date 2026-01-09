/*
 Navicat MySQL Data Transfer

 Source Server         : KOHALIK
 Source Server Type    : MySQL
 Source Server Version : 90400
 Source Host           : localhost:3306
 Source Schema         : qcubed-4

 Target Server Type    : MySQL
 Target Server Version : 90400
 File Encoding         : 65001

 Date: 09/01/2026 16:22:21
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for mini_gallery_register
-- ----------------------------
DROP TABLE IF EXISTS `mini_gallery_register`;
CREATE TABLE `mini_gallery_register` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int unsigned DEFAULT NULL,
  `folder_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of mini_gallery_register
-- ----------------------------
BEGIN;
INSERT INTO `mini_gallery_register` VALUES (15, 1207, '/mini-gallery', '2026-01-07 20:00:44');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
