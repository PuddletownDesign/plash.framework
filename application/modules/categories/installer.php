<?php

/*/
Table Schema

id
title
text
section
description
url


//*/
CREATE TABLE  `ironarm.co`.`categories` (
`id` TINYINT( 4 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`title` VARCHAR( 70 ) NOT NULL ,
`text` TEXT NOT NULL ,
`section` TINYINT( 4 ) UNSIGNED NOT NULL ,
`description` TEXT NOT NULL ,
`url` VARCHAR( 70 ) NOT NULL
) ENGINE = MYISAM ;

?>