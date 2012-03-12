CREATE TABLE IF NOT EXISTS `gallery` (
	`iGalleryID` INT NOT NULL AUTO_INCREMENT,
	`iUserID` INT NOT NULL,
	`cFile` TEXT NOT NULL,
	`cLongFile` TEXT NOT NULL,
	`cLabel` VARCHAR(255) NOT NULL,
	`iUserGalleryID` INT NOT NULL,
	`tsDate` INT NOT NULL,
	PRIMARY KEY (`iGalleryID`)
) ENGINE = MYISAM;

CREATE TABLE IF NOT EXISTS `gallery_user` (
	`iGalleryID` INT NOT NULL AUTO_INCREMENT,
	`cLabel` VARCHAR(150) NOT NULL,
	`iUserID` INT NOT NULL,
	PRIMARY KEY (`iGalleryID`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `gallery_exif` (
	`iExifID` INT NOT NULL AUTO_INCREMENT,
	`iUserID` INT NOT NULL,
	`cModel` TEXT,
	`cMake` TEXT,
	`cSoftware` TEXT,
	`cCCD` TEXT,
	`cApatureNumber` TEXT,
	`cExposure` TEXT,
	`cShutterSpeed` TEXT,
	`cApatureValue` TEXT,
	`cFocalLength` TEXT,
	`cWhiteBalance` TEXT,
	PRIMARY KEY (`iExifID`),
	INDEX (`iUserID`)
) ENGINE = MyISAM;

