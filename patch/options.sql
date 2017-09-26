
CREATE TABLE IF NOT EXISTS `options` (
  `table_id` varchar(100) NOT NULL,
  `field` varchar(100) NOT NULL,
  `record` varchar(100) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `icon` varchar(200) NOT NULL,
  `tooltip` longtext NOT NULL,
  KEY `table_id` (`table_id`,`field`,`record`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
