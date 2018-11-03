/*
 Navicat Premium Data Transfer

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 50722
 Source Host           : localhost:3306
 Source Schema         : mingpian

 Target Server Type    : MySQL
 Target Server Version : 50722
 File Encoding         : 65001

 Date: 12/09/2018 01:26:29
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for card
-- ----------------------------
DROP TABLE IF EXISTS `card`;
CREATE TABLE `card`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `pinyin` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mobile` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `valid` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `position` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `weixin` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `lat` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `lng` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `intro` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `viewes` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `likes` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `collectes` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 1,
  `qrcode_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `simple_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `card_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `full_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 11 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of card
-- ----------------------------
INSERT INTO `card` VALUES (2, '喵叔', 'miaoshu', '18661239', 0, '先生', '女士', 'shear@123.com', '0535-6482971', '烟台市芝罘区鼎城2008', 'Jimmie_Liu', '', '', '美好的回忆d', 9, 1, 0, 0, 0, 1535881530, 1, 'http://pdv4zvld2.bkt.clouddn.com/qrcode/2779b5992d0941bb325e61f276f1aaea.png', 'http://pdv4zvld2.bkt.clouddn.com/card/88c86ecbc24f286ffe8ff5c1ba16f4f4.png', 'http://pdv4zvld2.bkt.clouddn.com/card/9f946587a4c5301ab9e0419c38a10ecc.png', 'http://pdv4zvld2.bkt.clouddn.com/card/b40985a051044561c12cd733125ec459.png');
INSERT INTO `card` VALUES (6, '刘', '222', '18661239', 0, '公司', '总经理', '11', '11', '11dsdsfasdfsdafsdfdsafdsfdsfdsfdsf', '11', '', '', '11dsdsfasdfsdafsdfdsafdsfdsfdsfdsf', 9, 1, 0, 0, 0, 1535881565, 1, 'http://pdv4zvld2.bkt.clouddn.com/qrcode/b1c2c86cadfb7c2cbdabd57ced003e7e.png', 'http://pdv4zvld2.bkt.clouddn.com/card/6f4362e44fa8608d729298b78675fc28.png', 'http://pdv4zvld2.bkt.clouddn.com/card/7ba62df5de323064f059ed23bc10936d.png', 'http://pdv4zvld2.bkt.clouddn.com/card/3adaf61a157be9b5525719a863ce506e.png');
INSERT INTO `card` VALUES (7, '张先生', 'zhangxiansheng', '', 0, '公司', '领导', '', '', '', '', '', '', '', 9, 1, 0, 0, 0, 1535884759, 0, 'http://pdv4zvld2.bkt.clouddn.com/qrcode/00228d2e1e2c5e16fbe5b51c5d89a5e9.png', 'http://pdv4zvld2.bkt.clouddn.com/card/948b31eeac5a3072c2f06cd36adb0541.png', 'http://pdv4zvld2.bkt.clouddn.com/card/deb7d85d2940fa4ad06bb1cc0798bd54.png', 'http://pdv4zvld2.bkt.clouddn.com/card/9b35a8c700fdf610d019b3921f2c552f.png');
INSERT INTO `card` VALUES (8, '张先生', 'zhangxiansheng', '18888888888', 0, '单温', '经理', '', '', '', '', '', '', '', 9, 1, 0, 0, 0, 1535884868, 0, 'http://pdv4zvld2.bkt.clouddn.com/qrcode/bef027dac37be3f1985a036f8052980f.png', 'http://pdv4zvld2.bkt.clouddn.com/card/7caebfbf8b4bdff62fdfc015364a3d45.png', 'http://pdv4zvld2.bkt.clouddn.com/card/6d18e600720896f17da574625218c6d2.png', 'http://pdv4zvld2.bkt.clouddn.com/card/307b0c6fe5945174c374b8e7c2262f3a.png');
INSERT INTO `card` VALUES (9, '刘先生', 'liuxiansheng', '18661239', 0, '烟台共享科技有限公司', 'CEO', 'shear@63.com', '', '', '', '', '', '', 9, 1, 0, 0, 0, 1535903198, 1, 'http://pdv4zvld2.bkt.clouddn.com/qrcode/b89a766942d842acbb9151733d8f4d97.png', 'http://pdv4zvld2.bkt.clouddn.com/card/feb552a72935133579e6d5de1ed3d927.png', 'http://pdv4zvld2.bkt.clouddn.com/card/c4fd22447bbe8626cbf47c31ee2191f2.png', 'http://pdv4zvld2.bkt.clouddn.com/card/261c7345d68a4ac83646d2cf76255f59.png');
INSERT INTO `card` VALUES (10, '吴爱丽', 'wuaili', '', 0, '是的撒发', '经理', '', '', '', '', '', '', '', 9, 1, 0, 0, 0, 1535903300, 1, 'http://pdv4zvld2.bkt.clouddn.com/qrcode/2413080c235adcfd476cb56313254c0f.png', 'http://pdv4zvld2.bkt.clouddn.com/card/641b822dc19841249f5d54e35981dad4.png', 'http://pdv4zvld2.bkt.clouddn.com/card/9494239fd9c23ddd17dc78d928e0ac93.png', 'http://pdv4zvld2.bkt.clouddn.com/card/adc9ba96ee604d6391d6b6bedb3bde1c.png');

-- ----------------------------
-- Table structure for card_part
-- ----------------------------
DROP TABLE IF EXISTS `card_part`;
CREATE TABLE `card_part`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `handler` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `card_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `viewed` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `liked` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `collected` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 10 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of card_part
-- ----------------------------
INSERT INTO `card_part` VALUES (1, 9, 9, 2, 1, 1, 1, '', 1534768667);
INSERT INTO `card_part` VALUES (4, 9, 9, 7, 1, 0, 1, '', 1534927009);
INSERT INTO `card_part` VALUES (6, 9, 9, 6, 1, 0, 1, '12', 1535859687);
INSERT INTO `card_part` VALUES (9, 9, 9, 8, 1, 0, 1, '', 1535859687);

-- ----------------------------
-- Table structure for wechat_user
-- ----------------------------
DROP TABLE IF EXISTS `wechat_user`;
CREATE TABLE `wechat_user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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

-- ----------------------------
-- Records of wechat_user
-- ----------------------------
INSERT INTO `wechat_user` VALUES (9, 'wxa18e32dcb8bb3a4e', 'o3_eo5U-PkbV5L2xP7YdCtpH5zmo', 'w1ZQdo4MiJ++VLj8qdrx7Q==', '', '喵叔', 1, '杭州', '中国', '浙江', 'zh_CN', 'https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83eoOorZAicIwnhYicTfRO1L8s3yqoXvfYQibdEP6iaAdffzHz5OsWpb8LOtOT7r6sCL0CZhkmXcVoLnxWw/132', 0);
INSERT INTO `wechat_user` VALUES (10, 'wxa18e32dcb8bb3a4e', 'o3_eo5Sg4exoZcZO74zp3b5qrufc', '4NZLf+u643CLcMilnwiP+g==', '', '', 0, '', '', '', '', '', 0);
INSERT INTO `wechat_user` VALUES (11, 'wxa18e32dcb8bb3a4e', 'o3_eo5Sg4exoZcZO74zp3b5qrufc', '4NZLf+u643CLcMilnwiP+g==', '', '', 0, '', '', '', '', '', 0);

SET FOREIGN_KEY_CHECKS = 1;
