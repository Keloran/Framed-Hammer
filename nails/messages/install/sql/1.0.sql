CREATE TABLE IF NOT EXISTS `users_messages` (
	`iMessageID` INT NOT NULL AUTO_INCREMENT,
	`iRecieverID` INT NOT NULL,
	`iSenderID` INT NOT NULL,
	`cTitle` VARCHAR(150) NOT NULL,
	`cMessage` TEXT NOT NULL,
	`tsDate` INT NOT NULL,
	`bRead` BOOL DEFAULT 0,
	PRIMARY KEY (`iMessageID`),
	INDEX (`iRecieverID`),
	INDEX (`iSenderID`)
) ENGINE = MYISAM;