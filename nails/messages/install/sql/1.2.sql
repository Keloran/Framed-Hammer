CREATE TABLE IF NOT EXISTS `notifications_users` (
	`iNotifyID` INT(11) NOT NULL AUTO_INCREMENT,
	`iUserID` INT(11) NOT NULL,
	`cMessage` VARCHAR(150) NULL DEFAULT NULL,
	`tsDated` TIMESTAMP NOT NULL,
	PRIMARY KEY (`iNotifyID`),
	INDEX `iUserID` (`iUserID` ASC, `tsDated` ASC),
	INDEX `fk_users_notify_users1` (`iUserID` ASC)
) ENGINE = MEMORY