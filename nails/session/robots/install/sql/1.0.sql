CREATE TABLE IF NOT EXISTS `users_sessions_robots` (
	`iSessionID` INT NOT NULL AUTO_INCREMENT,
	`cRobotName` VARCHAR (50),
	`tsDate` INT NOT NULL,
	`cRobotSite` TEXT,
	PRIMARY KEY (`iSessionID`)
) ENGINE = MYISAM;