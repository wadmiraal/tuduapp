SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `todos` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `last_updated` datetime DEFAULT NULL,
  `notify_participants` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `tasks` (
  `todo_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `num` int(11) NOT NULL,
  `task` text COLLATE utf8_unicode_ci NOT NULL,
  `done` tinyint(1) NOT NULL,
  `meta_due` datetime DEFAULT NULL,
  `meta_assigned_to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `num_2` (`num`,`todo_id`),
  UNIQUE KEY `num_3` (`num`,`todo_id`),
  KEY `num` (`num`,`todo_id`),
  KEY `todo_id` (`todo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`todo_id`) REFERENCES `todos` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;


CREATE TABLE IF NOT EXISTS `participants` (
  `todo_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_message_id` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `email_2` (`email`,`todo_id`),
  UNIQUE KEY `email_3` (`email`,`todo_id`),
  KEY `email` (`email`,`todo_id`),
  KEY `todo_id` (`todo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`todo_id`) REFERENCES `todos` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
