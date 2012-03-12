CREATE TABLE IF NOT EXISTS admin_contest (
	`iContestID` INT NOT NULL AUTO_INCREMENT,
	`dDated` DATETIME,
	`iUserID` INT NOT NULL,
	`cContest` VARCHAR(150),
	PRIMARY KEY(`iContestID`),
	INDEX(`iUserID`)
);

ALTER TABLE `users_groups` ADD COLUMN `bAdmin` BOOL NOT NULL;
UPDATE `users_groups` SET bAdmin = 1 WHERE iGroupID = 5;

ALTER TABLE `users_groups` ADD COLUMN `bAddAbilitys` BOOL NOT NULL;
UPDATE `users_groups` SET bAddAbilitys = 1 WHERE iGroupID = 5;