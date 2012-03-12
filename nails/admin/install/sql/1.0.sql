CREATE TABLE IF NOT EXISTS `users_banned` (
	`iBannedID` INT NOT NULL AUTO_INCREMENT,
	`iBannedIP` INT NOT NULL,
	`iUserID`	INT NOT NULL,
	PRIMARY KEY (`iBannedID`),
	INDEX (`iBannedID`, `iUserID`)
) ENGINE = InnoDB;