CREATE TABLE IF NOT EXISTS `news` (
	`iNewsID` INT NOT NULL AUTO_INCREMENT,
	`tsDate` INT NOT NULL,
	`cTitle` VARCHAR(150) NOT NULL,
	`cContent` TEXT NOT NULL,
	`iUserID` INT NOT NULL,
	`iCategoryID` INT NOT NULL DEFAULT 0,
	`iImageID` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (`iNewsID`),
	INDEX (`iUserID`),
	INDEX (`iCategoryID`),
	INDEX (`iImageID`)
) ENGINE=MYISAM;

CREATE TABLE IF NOT EXISTS `news_categorys` (
	`iCategoryID` INT NOT NULL AUTO_INCREMENT,
	`cCategory` VARCHAR(50) NOT NULL,
	`cImageName` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`iCategoryID`)
) ENGINE=MYISAM;

CREATE TABLE IF NOT EXISTS `news_images` (
	`iImageID` INT NOT NULL AUTO_INCREMENT,
	`iNewsID` INT NOT NULL,
	`cImage` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`iImageID`),
	INDEX `fk_news_images`(`iNewsID` ASC),
	INDEX (`iNewsID`)
) ENGINE=InnoDB;

ALTER TABLE `users_groups` ADD COLUMN `bNews` BOOL NOT NULL;
ALTER TABLE `users_groups` ADD COLUMN `bNewsComments` BOOL NOT NULL;

UPDATE `users_groups` SET bNews = 1 WHERE iGroupID = 5;
UPDATE `users_groups` SET bNewsComments = 1 WHERE iGroupID IN (3, 5);