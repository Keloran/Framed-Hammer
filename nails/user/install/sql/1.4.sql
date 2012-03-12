CREATE TABLE IF NOT EXISTS `users_notify` (
	`iNotifyID` INT NOT NULL AUTO_INCREMENT,
	`iUserID` INT NOT NULL,
	`cMessage` TEXT,
	PRIMARY KEY(`iNotifyID`),
	INDEX(`iUserID`)
);