<?

$sql = 'CREATE DATABASE IF NOT EXISTS oasis_nerdnite';
DB::update($sql);

$sql = 'CREATE TABLE `events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `city` varchar(32) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `ticket_link` varchar(255) DEFAULT NULL,
  `topics` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `city` (`city`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
DB::update($sql);
