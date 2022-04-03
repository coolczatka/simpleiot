
CREATE TABLE `reminds` ( 
  `id` INT NOT NULL AUTO_INCREMENT , 
  `datetime` DATETIME NOT NULL , 
  `cyclical` INT NOT NULL DEFAULT '0' , 
  `content` TEXT NOT NULL DEFAULT ' ' , 
  PRIMARY KEY (`id`)) ENGINE = InnoDB; 