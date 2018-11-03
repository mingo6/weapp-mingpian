/*
 Navicat Premium Data Transfer

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 50722
 Source Host           : localhost:3306
 Source Schema         : mingpian1

 Target Server Type    : MySQL
 Target Server Version : 50722
 File Encoding         : 65001

 Date: 03/11/2018 20:34:18
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for card
-- ----------------------------
DROP TABLE IF EXISTS `card`;
CREATE TABLE `card`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `gender` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别',
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '头像',
  `pinyin` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '拼音',
  `mobile` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `valid` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '是否验证过手机号',
  `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '公司',
  `position` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '职位',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '邮箱',
  `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '座机',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '地址',
  `weixin` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '微信',
  `latitude` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '纬度',
  `longitude` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '经度',
  `intro` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '简介',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
  `type` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '名片类型',
  `viewes` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '查看数',
  `likes` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '点赞数',
  `collectes` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '收藏数',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态',
  `qrcode_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '二维码',
  `simple_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '简版',
  `card_url` varchar(255) CHARACTER SET utf32 COLLATE utf32_general_ci NULL DEFAULT '' COMMENT '名片',
  `full_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '完整版',
  `template_id` int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '模版ID',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_card_user_id`(`user_id`) USING BTREE,
  CONSTRAINT `fk_card_user_id` FOREIGN KEY (`user_id`) REFERENCES `wechat_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for card_apply_log
-- ----------------------------
DROP TABLE IF EXISTS `card_apply_log`;
CREATE TABLE `card_apply_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `card_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '名片ID',
  `requester` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '请求者',
  `apply_type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '申请类型',
  `request_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '请求时间',
  `reply_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '回复时间',
  `reply_state` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '回复状态',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_apply_requester`(`requester`) USING BTREE,
  INDEX `fk_apply_card_id`(`card_id`) USING BTREE,
  CONSTRAINT `fk_apply_card_id` FOREIGN KEY (`card_id`) REFERENCES `card` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_apply_requester` FOREIGN KEY (`requester`) REFERENCES `wechat_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for card_part
-- ----------------------------
DROP TABLE IF EXISTS `card_part`;
CREATE TABLE `card_part`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拥有者',
  `handler` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作者',
  `card_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '名片ID',
  `viewed` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '查看数',
  `liked` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `collected` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '收藏数',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_part_card_id`(`card_id`) USING BTREE,
  INDEX `fk_part_handler`(`handler`) USING BTREE,
  INDEX `fk_part_owner`(`owner`) USING BTREE,
  CONSTRAINT `fk_part_card_id` FOREIGN KEY (`card_id`) REFERENCES `card` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_part_handler` FOREIGN KEY (`handler`) REFERENCES `wechat_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_part_owner` FOREIGN KEY (`owner`) REFERENCES `wechat_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for card_view_log
-- ----------------------------
DROP TABLE IF EXISTS `card_view_log`;
CREATE TABLE `card_view_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `card_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `viewer` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `view_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_view_card_id`(`card_id`) USING BTREE,
  INDEX `fk_view_viewer`(`viewer`) USING BTREE,
  CONSTRAINT `fk_view_card_id` FOREIGN KEY (`card_id`) REFERENCES `card` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_view_viewer` FOREIGN KEY (`viewer`) REFERENCES `wechat_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wechat_user
-- ----------------------------
DROP TABLE IF EXISTS `wechat_user`;
CREATE TABLE `wechat_user`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `appid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `openid` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `session_key` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `unionid` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'unionid',
  `nickName` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户的昵称',
  `gender` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `city` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `country` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在国家',
  `province` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所在省份',
  `language` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户的语言，简体中文为zh_CN',
  `avatarUrl` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `openid`(`openid`) USING BTREE,
  INDEX `unionid`(`unionid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
