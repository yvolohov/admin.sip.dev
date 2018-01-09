CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED AUTO_INCREMENT NOT NULL,
  `login` varchar(200) NOT NULL,
  `password` text NOT NULL,
  `role` enum('USER', 'ADMIN'),
  PRIMARY KEY (`id`),
  UNIQUE KEY (`login`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `categories` (
    `id` int(11) UNSIGNED AUTO_INCREMENT NOT NULL,
    `url_name` varchar(200) NOT NULL,
    `native_name` varchar(200) DEFAULT '' NOT NULL,
    `foreign_name` varchar(200) DEFAULT '' NOT NULL,
    `sort_field` int(11) DEFAULT '0' NOT NULL,
    `parent_id` int(11) UNSIGNED DEFAULT '0' NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`sort_field`),
    KEY (`parent_id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `descriptions` (
    `id` int(11) UNSIGNED AUTO_INCREMENT NOT NULL,
    `description` text NOT NULL,
    `category_id` int(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`category_id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `questions` (
    `id` int(11) UNSIGNED AUTO_INCREMENT NOT NULL,
    `keywords` varchar(255) DEFAULT '' NOT NULL,
    `native_sentence` varchar(255) DEFAULT '' NOT NULL,
    `foreign_sentence` varchar(255) DEFAULT '' NOT NULL,
    `source` varchar(50) DEFAULT '' NOT NULL,
    `templates_cnt` int(11) UNSIGNED DEFAULT '0' NOT NULL,
    `sentences_cnt` int(11) UNSIGNED DEFAULT '0' NOT NULL,
    `category_id` int(11) UNSIGNED NOT NULL,
    `created` datetime DEFAULT '1970-01-01 00:00:00' NOT NULL,
    `updated` datetime DEFAULT '1970-01-01 00:00:00' NOT NULL,
    PRIMARY KEY (`id`),
    KEY(`category_id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `templates` (
    `id` int(11) UNSIGNED NOT NULL,
    `question_id` int(11) UNSIGNED NOT NULL,
    `native_template` varchar(255) DEFAULT '' NOT NULL,
    `foreign_template` varchar(255) DEFAULT '' NOT NULL,
    PRIMARY KEY (`question_id`, `id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `sentences` (
    `id` int(11) UNSIGNED NOT NULL,
    `question_id` int(11) UNSIGNED NOT NULL,
    `native_sentence` varchar(255) DEFAULT '' NOT NULL,
    `foreign_sentence` varchar(255) DEFAULT '' NOT NULL,
    `parts` varchar(255) DEFAULT '' NOT NULL,
    PRIMARY KEY (`question_id`, `id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `tests` (
    `user_id` int(11) UNSIGNED NOT NULL,
    `question_id` int(11) UNSIGNED NOT NULL,
    `is_selected` tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
    `passages_cnt` int(11) UNSIGNED DEFAULT '0' NOT NULL,
    `first_passage` datetime DEFAULT '1970-01-01 00:00:00' NOT NULL,
    `last_passage` datetime DEFAULT '1970-01-01 00:00:00' NOT NULL,
    PRIMARY KEY (`user_id`, `question_id`)
) ENGINE = InnoDB;
