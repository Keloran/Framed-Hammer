CREATE TABLE IF NOT EXISTS `users_groups` (
	`iGroupID` INT NOT NULL AUTO_INCREMENT,
	`cGroup` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`iGroupID`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `users` (
	`iUserID` INT NOT NULL AUTO_INCREMENT,
	`tsDate` INT NOT NULL,
	`cUsername` VARCHAR(150) NOT NULL,
	`cPassword` VARCHAR(65) NOT NULL,
	`cEmail` VARCHAR(250) NOT NULL,
	`cUserImage` VARCHAR(50) NOT NULL,
	`iGroupID` INT NOT NULL,
	`cRegisterHash` VARCHAR(65) NOT NULL,
	`cLoginHash` VARCHAR(65) NOT NULL,
	`cLastIP` VARCHAR(15) NOT NULL,
	PRIMARY KEY (`iUserID`),
	INDEX (`iGroupID`),
	INDEX (`tsDate`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `users_settings` (
	`iSettingID` INT NOT NULL AUTO_INCREMENT,
	`iUserID` INT NOT NULL,
	`cSettingName` VARCHAR(50) NOT NULL,
	`cSettingValue` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`iSettingID`),
	INDEX (`iUserID`),
	FOREIGN KEY (`iUserID`)
		REFERENCES `users` (`iUserID`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE = InnoDB;

INSERT INTO users_groups (cGroup) VALUES ('Non-Registered'), ('Non-Confirmed'), ('Registered'), ('Banned'), ('Admin');

ALTER TABLE users_groups ADD `bInstall` BOOL NOT NULL;
ALTER TABLE users_groups ADD INDEX (`bInstall`);

UPDATE users_groups SET bInstall = 1 WHERE iGroupID = 5;
