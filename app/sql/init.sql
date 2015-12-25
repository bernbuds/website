CREATE TABLE `bud` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `zip` varchar(11) DEFAULT NULL,
  `can_drive` tinyint(1) DEFAULT NULL,
  `num_pickup` tinyint(2) DEFAULT NULL,
  `is_email_verified` tinyint(1) unsigned DEFAULT '0',
  `is_phone_verified` tinyint(1) DEFAULT '0',
  `email_code` varchar(4) DEFAULT NULL,
  `phone_code` varchar(4) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;