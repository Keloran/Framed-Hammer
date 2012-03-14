CREATE TABLE IF NOT EXISTS `users_sessions_visitors` (
	`iSessionID` INT NOT NULL AUTO_INCREMENT,
	`cUserAgent` TEXT,
	`tsDate` INT NOT NULL,
	PRIMARY KEY (`iSessionID`)
) ENGINE = MyISAM;