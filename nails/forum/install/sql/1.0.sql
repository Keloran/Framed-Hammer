CREATE TABLE IF NOT EXISTS `forums` (
	`iForumID` INT NOT NULL AUTO_INCREMENT,
	`iParentID` INT NOT NULL DEFAULT 0,
	`cTitle` VARCHAR(150) NOT NULL,
	`cDescription` VARCHAR(250) NOT NULL,
	`iGroupID` INT NOT NULL DEFAULT 1,
	PRIMARY KEY (`iForumID`),
	INDEX `forums` (`iParentID`, `iGroupID)
) ENGINE = MYISAM;

CREATE TABLE IF NOT EXISTS `forums_topics` (
	`iTopicID` INT NOT NULL AUTO_INCREMENT,
	`cTitle` VARCHAR(100) NOT NULL,
	`iForumID` INT NOT NULL,
	`iPosterID` INT NOT NULL,
	`tsDate` INT NOT NULL,
	`cContent` TEXT,
	`bSticky` BOOL NOT NULL DEFAULT 0,
	`iViews` INT NOT NULL DEFAULT 0,
	`bLocked` BOOL NOT NULL DEFAULT 0,
	PRIMARY KEY (`iTopicID`),
	INDEX `forums_topics` (`iForumID`, `iPosterID`, `bSticky`, `iViews`, `bLocked`)
) ENGINE = MYISAM;

CREATE TABLE IF NOT EXISTS `forums_replys` (
	`iReplyID` INT NOT NULL AUTO_INCREMENT,
	`iTopicID` INT NOT NULL,
	`iForumID` INT NOT NULL,
	`cContent` TEXT,
	`tsDate` INT NOT NULL,
	`iPosterID` INT NOT NULL,
	PRIMARY KEY (`iReplyID`),
	INDEX `forums_replys` (`iTopicID`, `iPosterID`, `iForumID`)
) ENGINE = MYISAM;

ALTER TABLE `users` ADD `cSignature` VARCHAR(200) NOT NULL, ADD `iPosts` INT NOT NULL DEFAULT 0;
ALTER TABLE `users` ADD INDEX(`iPosts`);

ALTER TABLE `users_groups` ADD COLUMN `bForumsReply` BOOL NOT NULL;
ALTER TABLE `users_groups` ADD COLUMN `bForumsTopic` BOOL NOT NULL;
ALTER TABLE `users_groups` ADD COLUMN `bForumsSticky` BOOL NOT NULL;
ALTER TABLE `users_groups` ADD COLUMN `bForumsDelete` BOOL NOT NULL;
ALTER TABLE `users_groups` ADD COLUMN `bForumsLock` BOOL NOT NULL;

UPDATE `users_groups` SET bForumsReply = 1, bForumsTopic = 1, bForumsSticky = 1, bForumsDelete = 1, bForumsLock = 1 WHERE iGroupID = 5;
UPDATE `users_groups` SET bForumsReply = 1, bForumsTopic = 1 WHERE iGroupID = 3;