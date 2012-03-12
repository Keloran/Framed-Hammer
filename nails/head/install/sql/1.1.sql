CREATE TABLE IF NOT EXISTS `head_titles` (
	`iTitleID` INT NOT NULL AUTO_INCREMENT,
	`cPage` VARCHAR(50),
	`cAction` VARCHAR(50),
	`cChoice` VARCHAR(50),
	`iItem` INT,
	`cTitle` TEXT,
	PRIMARY KEY(`iTitleID`)
);