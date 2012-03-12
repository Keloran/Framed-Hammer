CREATE TABLE IF NOT EXISTS `image_comment` (
	`iCommentID` INT NOT NULL AUTO_INCREMENT,
	`iImageID` INT NOT NULL,
	`iUserID` INT NOT NULL,
	`cComment` TEXT,
	PRIMARY KEY (`iCommentID`)
);