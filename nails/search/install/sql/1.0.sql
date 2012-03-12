CREATE TABLE IF NOT EXISTS `search` (
	`iSearchID` INT NOT NULL AUTO_INCREMENT,
	`cSearch` VARCHAR(200),
	`cSource` TEXT,
	`dDated` DATETIME,
	PRIMARY KEY (`iSearchID`),
	INDEX (`dDated`)
);