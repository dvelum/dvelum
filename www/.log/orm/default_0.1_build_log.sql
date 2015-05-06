
--
--2015-05-06 09:46:17
--
 ALTER TABLE sb_content CHANGE `text` `text_one` longtext  NULL  COMMENT 'Text' 
--
--2015-05-06 09:47:49
--
 ALTER TABLE sb_content CHANGE `text_one` `text` longtext  NULL  COMMENT 'Text' 
--
--2015-05-06 09:48:10
--
 CREATE TABLE  `sb_to` (`id` bigint (20) UNSIGNED  AUTO_INCREMENT NOT NULL  COMMENT 'Primary key'  ,  
 
 PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
--
--2015-05-06 09:48:27
--
ALTER TABLE `dvelum`.`sb_to` 
ADD `new_field` varchar (255)  NOT NULL  COMMENT 'new_field' ;
--
--2015-05-06 09:49:17
--
 ALTER TABLE sb_to CHANGE `new_field` `new_fieldw` varchar (255)  NOT NULL  COMMENT 'new_fieldw' 
--
--2015-05-06 09:50:30
--
 ALTER TABLE sb_to CHANGE `new_fieldw` `new_fieldwf` varchar (255)  NOT NULL  COMMENT 'new_fieldwf' 
--
--2015-05-06 09:54:20
--
DROP TABLE `sb_to`
--
--2015-05-06 09:57:04
--
 CREATE TABLE  `sb_testo` (`id` bigint (20) UNSIGNED  AUTO_INCREMENT NOT NULL  COMMENT 'Primary key'  ,  
 
 PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
--
--2015-05-06 09:57:24
--
ALTER TABLE `dvelum`.`sb_testo` 
ADD `field_one` varchar (255)  NOT NULL  COMMENT 'field_one' ;
--
--2015-05-06 09:57:35
--
 ALTER TABLE sb_testo CHANGE `field_one` `field_one2` varchar (255)  NOT NULL  COMMENT 'field_one2' 