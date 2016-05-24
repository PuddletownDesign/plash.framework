<?php

/** 
 * Comments Table
 * id
 * name
 * email
 * text
 * section
 * post
 * created
 * admin
 */
CREATE TABLE  `ironarm.co`.`comments` (
`id` INT( 6 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 80 ) NOT NULL ,
`email` VARCHAR( 200 ) NOT NULL ,
`text` LONGTEXT NOT NULL ,
`section` TINYINT NOT NULL ,
`post` TINYINT( 5 ) UNSIGNED NOT NULL ,
`created` DATETIME NOT NULL ,
`admin` TINYINT( 1 ) UNSIGNED NULL DEFAULT  '0',
INDEX (  `post` )
) ENGINE = MYISAM ;
?>