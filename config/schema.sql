CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `name` varchar(100) default NULL,
  `gender` char(1) default NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `user` (`id`, `email`, `username`, `password`, `name`, `gender`, `updated_at`, `created_at`) VALUES
(1, 'matt@email.com', 'matt', NULL, 'Matt', 'm', '2011-08-16 23:41:55', '2011-08-14 10:09:57'),
(2, 'james@email.com', 'james', NULL, 'James', 'm', '2011-08-14 10:10:24', '2011-08-14 10:10:28'),
(3, 'adam@email.com', 'adam', NULL, 'Adam', 'm', '2011-08-14 10:11:47', '2011-08-14 10:11:51');