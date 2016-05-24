<?php

/** 
 * Blog DB Installer
 * Creates a generic Blog DB Table in the defined Database
 */


/*/

CREATE TABLE  `ironarm.co`.`blog` (
`id` TINYINT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`title` VARCHAR( 200 ) NOT NULL ,
`text` LONGTEXT NULL ,
`description` TEXT NULL,
`url` VARCHAR( 200 ) NOT NULL ,
`created` DATETIME NOT NULL ,
`updated` DATETIME NOT NULL ,
`author` TINYINT( 3 ) NULL ,
`tags` VARCHAR( 200 ) NULL ,
`category` TINYINT( 3 ) UNSIGNED NULL ,
`commentcount` TINYINT( 4 ) UNSIGNED NULL DEFAULT  '0' ,
UNIQUE (
`url`
)
) ENGINE = MYISAM ;

INSERT INTO sections (`title`, `url`)
VALUES (`Blog`, `blog`)
//*/

?>