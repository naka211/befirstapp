CREATE TABLE IF NOT EXISTS `#__campaign` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`name` VARCHAR(255)  NOT NULL ,
`created_time` DATETIME NOT NULL ,
`end_date` DATE NOT NULL ,
`end_hour` VARCHAR(255)  NOT NULL ,
`end_minute` VARCHAR(255)  NOT NULL ,
`gender` VARCHAR(255)  NOT NULL ,
`from_age` TINYINT(4)  NOT NULL ,
`to_age` TINYINT(4)  NOT NULL ,
`from_zipcode` VARCHAR(255)  NOT NULL ,
`to_zipcode` VARCHAR(255)  NOT NULL ,
`image` VARCHAR(255)  NOT NULL ,
`video` VARCHAR(255)  NOT NULL ,
`minimum_seconds` TINYINT(4)  NOT NULL ,
`reward` VARCHAR(500)  NOT NULL ,
`number_of_winners` TINYINT(4)  NOT NULL ,
`instruction` TEXT NOT NULL ,
`active` TINYINT(1)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

