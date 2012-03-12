CREATE TABLE IF NOT EXISTS `users_special_privs` (
	`iSpecialID` INT NOT NULL AUTO_INCREMENT,
	`cAllowed` VARCHAR(50) NOT NULL,
	`iUserID` INT NOT NULL,
	PRIMARY KEY (`iSpecialID`),
	INDEX (`iUserID`)
);