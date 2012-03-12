CREATE TABLE IF NOT EXISTS `notifications_groups` (
	`iNotifyID` INT NOT NULL AUTO_INCREMENT,
	`iGroupID` INT(11) NOT NULL,
	`tsDated` TIMESTAMP NOT NULL ,
	`cMessage` VARCHAR(150) NULL ,
	`cPermission` VARCHAR(100) NULL,
	PRIMARY KEY (`iNotifyID`) ,
	INDEX `groups_time` (`iGroupID` ASC, `tsDated` DESC),
	INDEX `permissions` (`cPermission` ASC)
) ENGINE = MEMORY;