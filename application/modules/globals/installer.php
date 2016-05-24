<?php

/** 
 * Sections Table
 * id
 * title
 * text
 * description
 * url
 */
CREATE TABLE  `ironarm.co`.`sections` (
`id` TINYINT( 2 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`title` VARCHAR( 100 ) NOT NULL ,
`text` TEXT NULL ,
`description` TEXT NULL ,
`url` VARCHAR( 100 ) NOT NULL
) ENGINE = MYISAM ;
?>