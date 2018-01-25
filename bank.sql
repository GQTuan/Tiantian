/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : x29

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-03-22 14:27:27
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for bank
-- ----------------------------
DROP TABLE IF EXISTS `bank`;
CREATE TABLE `bank` (
  `number` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `state` tinyint(4) DEFAULT '0' COMMENT '0无效1有效'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bank
-- ----------------------------
INSERT INTO `bank` VALUES ('102', '中国工商银行', '1');
INSERT INTO `bank` VALUES ('103', '中国农业银行', '1');
INSERT INTO `bank` VALUES ('104', '中国银行', '1');
INSERT INTO `bank` VALUES ('105', '中国建设银行', '1');
INSERT INTO `bank` VALUES ('201', '国家开发银行', '0');
INSERT INTO `bank` VALUES ('202', '中国进出口银行', '0');
INSERT INTO `bank` VALUES ('203', '中国农业发展银行', '0');
INSERT INTO `bank` VALUES ('301', '交通银行', '1');
INSERT INTO `bank` VALUES ('302', '中信银行', '0');
INSERT INTO `bank` VALUES ('303', '中国光大银行', '1');
INSERT INTO `bank` VALUES ('304', '华夏银行', '0');
INSERT INTO `bank` VALUES ('305', '中国民生银行', '1');
INSERT INTO `bank` VALUES ('306', '广东发展银行', '0');
INSERT INTO `bank` VALUES ('307', '深圳发展银行', '0');
INSERT INTO `bank` VALUES ('308', '招商银行', '1');
INSERT INTO `bank` VALUES ('309', '兴业银行', '0');
INSERT INTO `bank` VALUES ('310', '上海浦东发展银行', '1');
INSERT INTO `bank` VALUES ('313', '城市商业银行', '0');
INSERT INTO `bank` VALUES ('314', '农村商业银行', '0');
INSERT INTO `bank` VALUES ('315', '恒丰银行', '0');
INSERT INTO `bank` VALUES ('316', '浙商银行', '0');
INSERT INTO `bank` VALUES ('317', '农村合作银行', '0');
INSERT INTO `bank` VALUES ('318', '渤海银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('319', '徽商银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('320', '镇银行有限责任公司', '0');
INSERT INTO `bank` VALUES ('401', '城市信用社', '0');
INSERT INTO `bank` VALUES ('402', '农村信用社（含北京农村商业银行）', '0');
INSERT INTO `bank` VALUES ('403', '中国邮政储蓄银行', '1');
INSERT INTO `bank` VALUES ('501', '汇丰银行', '0');
INSERT INTO `bank` VALUES ('502', '东亚银行', '0');
INSERT INTO `bank` VALUES ('503', '南洋商业银行', '0');
INSERT INTO `bank` VALUES ('504', '恒生银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('505', '中国银行（香港）有限公司', '0');
INSERT INTO `bank` VALUES ('506', '集友银行有限公司', '0');
INSERT INTO `bank` VALUES ('507', '创业银行有限公司', '0');
INSERT INTO `bank` VALUES ('509', '星展银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('510', '永亨银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('512', '永隆银行', '0');
INSERT INTO `bank` VALUES ('531', '花旗银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('532', '美国银行有限公司', '0');
INSERT INTO `bank` VALUES ('533', '摩根大通银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('561', '三菱东京日联银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('563', '日本三井住友银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('564', '瑞穗实业银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('565', '日本山口银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('591', '韩国外换银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('593', '友利银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('591', '韩国外换银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('594', '韩国产业银行', '0');
INSERT INTO `bank` VALUES ('595', '新韩银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('596', '韩国中小企业银行有限公司', '0');
INSERT INTO `bank` VALUES ('597', '韩亚银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('621', '华侨银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('622', '大华银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('623', '星展银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('631', '泰国盘谷银行（大众有限公司）', '0');
INSERT INTO `bank` VALUES ('641', '奥地利中央合作银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('651', '比利时联合银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('652', '比利时富通银行有限公司', '0');
INSERT INTO `bank` VALUES ('661', '荷兰银行', '0');
INSERT INTO `bank` VALUES ('662', '荷兰安智银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('671', '渣打银行', '0');
INSERT INTO `bank` VALUES ('672', '英国苏格兰皇家银行公众有限公司', '0');
INSERT INTO `bank` VALUES ('691', '法国兴业银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('694', '法国东方汇理银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('695', '法国外贸银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('711', '德国德累斯登银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('712', '德意志银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('713', '德国商业银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('714', '德国西德银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('715', '德国巴伐利亚州银行', '0');
INSERT INTO `bank` VALUES ('716', '德国北德意志州银行', '0');
INSERT INTO `bank` VALUES ('732', '意大利联合圣保罗银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('741', '瑞士信贷银行股份有限公司', '0');
INSERT INTO `bank` VALUES ('742', '瑞士银行', '0');
INSERT INTO `bank` VALUES ('751', '加拿大丰业银行有限公司', '0');
INSERT INTO `bank` VALUES ('752', '加拿大蒙特利尔银行有限公司', '0');
INSERT INTO `bank` VALUES ('761', '澳大利亚和新西兰银行集团有限公司', '0');
INSERT INTO `bank` VALUES ('771', '摩根士丹利国际银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('775', '联合银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('776', '荷兰合作银行有限公司', '0');
INSERT INTO `bank` VALUES ('781', '厦门国际银行', '0');
INSERT INTO `bank` VALUES ('782', '法国巴黎银行（中国）有限公司', '0');
INSERT INTO `bank` VALUES ('785', '华商银行', '0');
INSERT INTO `bank` VALUES ('787', '华一银行', '0');
INSERT INTO `bank` VALUES ('969', '（澳门地区）银行', '0');
INSERT INTO `bank` VALUES ('989', '（香港地区）银行', '0');
