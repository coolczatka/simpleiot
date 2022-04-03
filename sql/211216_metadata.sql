CREATE TABLE `metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` text NOT NULL,
  `value` text NOT NULL,
  `type` text NOT NULL,
  `encrypted` tinyint(1) NOT NULL DEFAULT 0,
  `label` text NOT NULL DEFAULT ' ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_unique` (`key`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=latin1

CREATE TABLE `kurdeeha_stajenka`.`reminds` ( `id` INT NOT NULL AUTO_INCREMENT , `datetime` DATETIME NOT NULL , `cyclical` INT NOT NULL DEFAULT '0' , `content` TEXT NOT NULL DEFAULT ' ' , PRIMARY KEY (`id`)) ENGINE = InnoDB; 