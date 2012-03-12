CREATE TABLE IF NOT EXISTS `users_sessions` (
	`iSessionID` INT NOT NULL AUTO_INCREMENT,
	`iUserID` INT NOT NULL,
	`cLastSessionID` VARCHAR(50) NOT NULL,
	`tsDate` INT NOT NULL,
	`cReason` VARCHAR(150) NOT NULL,
	`cIP` VARCHAR(15) NOT NULL,
	PRIMARY KEY (`iSessionID`),
	INDEX `fk_users_sessions` (`iUserID` DESC),
	INDEX (`iUserID`),
	INDEX (`cLastSessionID`)
) ENGINE = MEMORY;