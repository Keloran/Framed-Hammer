<?php
/**
 * Gallery_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Gallery_Install {
	private $oNails;
	private $oDB;

	/**
	 * Gallery_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("gallery");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function upgrade() {
		if ($this->oNails->checkVersion("gallery", "1.4") == false) {
			//1.1
			$cSQL = "ALTER TABLE `gallery` ADD COLUMN `bPrivate` BOOL NOT NULL";
			$this->oNails->updateVersion("gallery", "1.1", $cSQL, "Added Private setting");

			//1.2
			$cSQL = "ALTER TABLE `gallery` ADD COLUMN `cFileSmall` TEXT, ADD COLUMN `cFileMedium` TEXT";
			$this->oNails->updateVersion("gallery", "1.2", $cSQL);

			//1.3
			$cSQL = "ALTER TABLE `gallery_user` ADD COLUMN `bPrivate` BOOL NOT NULL";
			$this->oNails->updateVersion("gallery", "1.3", $cSQL, "Added Private Flag");

			//1.4
			$cSQL = "ALTER TABLE `gallery_exif` ADD COLUMN `cExposureMode` TEXT NULL DEFAULT NULL";
			$this->oNails->updateVersion("gallery", "1.4", $cSQL, "Added Exposure");
		}
	}

	private function install() {
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `gallery` (
				`iGalleryID` INT NOT NULL AUTO_INCREMENT,
				`iUserID` INT NOT NULL,
				`cFile` TEXT NOT NULL,
				`cLongFile` TEXT NOT NULL,
				`cLabel` VARCHAR(255) NOT NULL,
				`iUserGalleryID` INT NOT NULL,
				`tsDate` INT NOT NULL,
				PRIMARY KEY (`iGalleryID`)
			) ENGINE = MYISAM
		");
		$this->oNails->addIndexs("gallery", "iUserGalleryID");
		$this->oNails->addIndexs("gallery", "iUserID");
		$this->oNails->addIndexs("gallery", "tsDate");

		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `gallery_user` (
				`iGalleryID` INT NOT NULL AUTO_INCREMENT,
				`cLabel` VARCHAR(150) NOT NULL,
				`iUserID` INT NOT NULL,
				PRIMARY KEY (`iGalleryID`)
			) ENGINE = MyISAM
		");
		$this->oNails->addIndexs("gallery_user", "iUserID");

		$this->oNails->addTable("
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
				PRIMARY KEY (`iExifID`)
			) ENGINE = MyISAM
		");
		$this->oNails->addIndexs("gallery_exif", "iUserID");

		$this->oNails->addVersion("gallery", "1.0");
		$this->oNails->addVersion("gallery_exif", "1.0");
		$this->oNails->addVersion("gallery_user", "1.0");

		$this->oNails->sendLocation("install");
	}
}