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

 Date: 09/01/2026 16:22:33
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for mini_gallery
-- ----------------------------
DROP TABLE IF EXISTS `mini_gallery`;
CREATE TABLE `mini_gallery` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_cover_media_id` int unsigned DEFAULT NULL,
  `folder_id` int unsigned DEFAULT NULL,
  `file_id` int unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` int unsigned DEFAULT '1',
  `post_date` datetime DEFAULT NULL,
  `post_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status_id_idx` (`status`) USING BTREE,
  KEY `content_cover_media_id_idx` (`content_cover_media_id`) USING BTREE,
  CONSTRAINT `mini_gallery_ibfk_1` FOREIGN KEY (`status`) REFERENCES `status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mini_gallery_ibfk_2` FOREIGN KEY (`content_cover_media_id`) REFERENCES `content_cover_media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
