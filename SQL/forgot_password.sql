CREATE TABLE `forgot_password` (
  `user_id` int(11) NOT NULL,
  `alternative_email` varchar(200) COLLATE latin1_general_ci NOT NULL,
  `token` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB