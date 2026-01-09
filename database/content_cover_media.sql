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

 Date: 09/01/2026 16:22:51
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for content_cover_media
-- ----------------------------
DROP TABLE IF EXISTS `content_cover_media`;
CREATE TABLE `content_cover_media` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int unsigned DEFAULT NULL,
  `menu_content_id` int unsigned DEFAULT NULL,
  `media_type_id` int unsigned DEFAULT NULL,
  `picture_id` int unsigned DEFAULT NULL,
  `video_embed` text COLLATE utf8mb4_unicode_ci,
  `folder_id` int unsigned DEFAULT NULL,
  `folder_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preview_file_id` int unsigned DEFAULT NULL,
  `preview_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int unsigned DEFAULT '2',
  `post_date` datetime DEFAULT NULL,
  `post_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_type_id_idx` (`media_type_id`) USING BTREE,
  KEY `status_id_idx` (`status`) USING BTREE,
  CONSTRAINT `media_type_id_ibfk` FOREIGN KEY (`media_type_id`) REFERENCES `media_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `status_id_ibfk` FOREIGN KEY (`status`) REFERENCES `status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
