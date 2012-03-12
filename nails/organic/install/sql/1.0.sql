CREATE TABLE IF NOT EXISTS `organic` (
	`iOrganic` INT NOT NULL AUTO_INCREMENT,
	`cHost` VARCHAR(100),
	`cOrganic` VARCHAR(150),
	`dDated` DATETIME,
	PRIMARY KEY (`iOrganic`),
	INDEX(`dDated`, `cOrganic`)
);