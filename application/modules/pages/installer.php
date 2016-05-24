CREATE TABLE  `ironarm.co`.`pages` (
`id` TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`title` VARCHAR( 90 ) NOT NULL ,
`text` LONGTEXT NULL ,
`description` TEXT NULL ,
`url` VARCHAR( 90 ) NOT NULL ,
`created` DATETIME NOT NULL ,
`updated` DATETIME NULL ,
UNIQUE (
`url`
)