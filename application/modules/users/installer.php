CREATE TABLE  `ironarm.co`.`users` (
`id` TINYINT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`user` VARCHAR( 50 ) NOT NULL ,
`name` VARCHAR( 70 ) NOT NULL ,
`password` VARCHAR( 40 ) NOT NULL ,
`priv` TINYINT( 1 ) NOT NULL DEFAULT  '0',
`session` VARCHAR( 40 )  NULL
) ENGINE = MYISAM ;