/*
 Navicat Premium Dump SQL

 Source Server         : 本机数据库
 Source Server Type    : MySQL
 Source Server Version : 80032 (8.0.32)
 Source Host           : localhost:3306
 Source Schema         : nano_payment

 Target Server Type    : MySQL
 Target Server Version : 80032 (8.0.32)
 File Encoding         : 65001

 Date: 08/12/2024 14:48:47
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for nano_bank
-- ----------------------------
DROP TABLE IF EXISTS `nano_bank`;
CREATE TABLE `nano_bank` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '银行图标',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '银行名称',
  `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '银行代码',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态(0=停用,1=启用)',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of nano_bank
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for nano_merchant
-- ----------------------------
DROP TABLE IF EXISTS `nano_merchant`;
CREATE TABLE `nano_merchant` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchant_no` bigint unsigned NOT NULL COMMENT '商户编号',
  `phone` varchar(11) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '手机号码',
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '登录密码',
  `salting` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码盐',
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '邮箱号码',
  `creation_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `balance` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '账户余额',
  `freeze_balance` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '冻结余额',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '账户状态(0=封禁,1=正常)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant_no` (`merchant_no`) USING BTREE,
  UNIQUE KEY `phone` (`phone`) USING BTREE,
  UNIQUE KEY `email` (`email`),
  KEY `balance` (`balance`),
  KEY `freeze_balance` (`freeze_balance`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of nano_merchant
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for nano_merchant_bank_card
-- ----------------------------
DROP TABLE IF EXISTS `nano_merchant_bank_card`;
CREATE TABLE `nano_merchant_bank_card` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchant_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `bank_id` bigint unsigned NOT NULL COMMENT '银行ID',
  `card_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '银行卡号',
  `card_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '银行卡图片',
  `card_image_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '银行卡图片hash',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '银行卡状态(0=异常,1=正常)',
  `creation_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `card_no` (`card_no`) USING BTREE,
  UNIQUE KEY `card_image_hash` (`card_image_hash`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `bank_id` (`bank_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  CONSTRAINT `nano_merchant_bank_card_ibfk_1` FOREIGN KEY (`merchant_id`) REFERENCES `nano_merchant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nano_merchant_bank_card_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `nano_bank` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of nano_merchant_bank_card
-- ----------------------------
BEGIN;
INSERT INTO `nano_merchant_bank_card` (`id`, `merchant_id`, `bank_id`, `card_no`, `card_image`, `card_image_hash`, `status`, `creation_time`) VALUES (6, 17, 1, 'admin@nanoa.cn', NULL, NULL, 1, '2024-10-17 02:04:17');
COMMIT;

-- ----------------------------
-- Table structure for nano_merchant_identity
-- ----------------------------
DROP TABLE IF EXISTS `nano_merchant_identity`;
CREATE TABLE `nano_merchant_identity` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchant_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '姓名',
  `id_card` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '证件号码',
  `type` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '证件类型(0=中国居民身份证,1=香港永久居民身份证,2=澳门永久性居民身份证,3=国际护照（包括大陆/台湾/国际）)',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '认证状态(0=审核中,1=认证成功,2=认证失败)',
  `creation_time` datetime NOT NULL COMMENT '创建时间',
  `review_time` datetime DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `merchant_id` (`merchant_id`) USING BTREE,
  UNIQUE KEY `id_card` (`id_card`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  CONSTRAINT `nano_merchant_identity_ibfk_1` FOREIGN KEY (`merchant_id`) REFERENCES `nano_merchant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of nano_merchant_identity
-- ----------------------------
BEGIN;
INSERT INTO `nano_merchant_identity` (`id`, `merchant_id`, `name`, `id_card`, `type`, `status`, `creation_time`, `review_time`) VALUES (4, 17, '廖学华', '510522197710095831', 0, 1, '2024-10-17 02:03:33', '2024-10-17 02:03:33');
COMMIT;

-- ----------------------------
-- Table structure for nano_merchant_withdraw
-- ----------------------------
DROP TABLE IF EXISTS `nano_merchant_withdraw`;
CREATE TABLE `nano_merchant_withdraw` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint unsigned DEFAULT NULL COMMENT '商户ID',
  `card_id` bigint unsigned DEFAULT NULL COMMENT '银行卡ID',
  `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '订单号',
  `amount` decimal(14,2) unsigned NOT NULL COMMENT '提现金额',
  `status` tinyint unsigned NOT NULL COMMENT '状态:(0=银行处理中,1=提现已到账,2=提现被驳回)',
  `handle_message` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '处理消息',
  `creation_time` datetime NOT NULL COMMENT '创建时间',
  `handle_time` datetime DEFAULT NULL COMMENT '处理时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `trade_no` (`trade_no`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `card_id` (`card_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `creation_time` (`creation_time`) USING BTREE,
  KEY `handle_time` (`handle_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of nano_merchant_withdraw
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for nano_order
-- ----------------------------
DROP TABLE IF EXISTS `nano_order`;
CREATE TABLE `nano_order` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchant_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `trade_no` char(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '平台订单号',
  `order_amount` decimal(10,2) unsigned NOT NULL COMMENT '订单总金额',
  `browser` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '浏览器类型',
  `device` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '设备类型',
  `type` tinyint unsigned NOT NULL COMMENT '订单类型(0=API收款,1=码牌收款,2=余额充值,3=购买用户组)',
  `status` tinyint unsigned NOT NULL COMMENT '状态(0=未付款,1=已付款,2=正在支付中,3=已取消)',
  `terminal_ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '终端IP',
  `creation_time` datetime NOT NULL COMMENT '创建时间',
  `payment_time` datetime DEFAULT NULL COMMENT '付款时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of nano_order
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for nano_payment_order
-- ----------------------------
DROP TABLE IF EXISTS `nano_payment_order`;
CREATE TABLE `nano_payment_order` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_id` bigint unsigned NOT NULL COMMENT '订单ID',
  `merchant_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `channel_id` bigint unsigned NOT NULL COMMENT '通道ID',
  `order_amount` decimal(10,2) unsigned NOT NULL COMMENT '订单总金额',
  `trade_amount` decimal(10,2) unsigned NOT NULL COMMENT '实付金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of nano_payment_order
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for nano_system_user
-- ----------------------------
DROP TABLE IF EXISTS `nano_system_user`;
CREATE TABLE `nano_system_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱号码',
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '登录密码',
  `salting` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码盐',
  `login_time` datetime DEFAULT NULL COMMENT '登录时间',
  `last_login_time` datetime DEFAULT NULL COMMENT '上次登录时间',
  `login_ip` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '登录IP',
  `last_login_ip` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '上次登录IP',
  `login_ua` varchar(768) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '登录UA',
  `last_login_ua` varchar(768) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '上次登录UA',
  `creation_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '账户状态(0=停用,1=启用)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`) USING BTREE,
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of nano_system_user
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for nano_system_user_permission
-- ----------------------------
DROP TABLE IF EXISTS `nano_system_user_permission`;
CREATE TABLE `nano_system_user_permission` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of nano_system_user_permission
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for nano_system_user_role
-- ----------------------------
DROP TABLE IF EXISTS `nano_system_user_role`;
CREATE TABLE `nano_system_user_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
  `creation_time` datetime NOT NULL COMMENT '创建时间',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态(0=停用,1=启用)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of nano_system_user_role
-- ----------------------------
BEGIN;
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
