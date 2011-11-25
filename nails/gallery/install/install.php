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

	/**
	 * Gallery_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("gallery", "1.5") == false) {
			//1.1
			$this->rapier();

			//1.2
			$this->aruba();

			//1.3
			$this->plato();

			//1.4
			$this->denali();

			//1.5
			$this->hawk();
		}
	}

	/**
	 * Gallery_Install::install()
	 *
	 * @return
	 */
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

	/**
	 * Gallery_Install::rapier()
	 *
	 * @return null
	 */
	public function rapier() {
		$cSQL = "ALTER TABLE `gallery` ADD COLUMN `bPrivate` BOOL NOT NULL";
		$this->oNails->updateVersion("gallery", "1.1", $cSQL, "Added Private setting");
	}

	/**
	 * Gallery_Install::aruba()
	 *
	 * @return
	 */
	public function aruba() {
		$cSQL = "ALTER TABLE `gallery` ADD COLUMN `cFileSmall` TEXT, ADD COLUMN `cFileMedium` TEXT";
		$this->oNails->updateVersion("gallery", "1.2", $cSQL);
	}

	/**
	 * Gallery_Install::plato()
	 *
	 * @return
	 */
	public function plato() {
		$cSQL = "ALTER TABLE `gallery_user` ADD COLUMN `bPrivate` BOOL NOT NULL";
		$this->oNails->updateVersion("gallery", "1.3", $cSQL, "Added Private Flag");
	}

	/**
	 * Gallery_Install::denali()
	 *
	 * @return
	 */
	public function denali() {
		$cSQL = "ALTER TABLE `gallery_exif` ADD COLUMN `cExposureMode` TEXT NULL DEFAULT NULL";
		$this->oNails->updateVersion("gallery", "1.4", $cSQL, "Added Exposure");
	}

	/**
	 * Gallery_Install::hawk()
	 *
	 * @return
	 */
	public function hawk() {
		$cSQL	= "CREATE TABLE IF NOT EXISTS `image_comment` (
			`iCommentID` INT NOT NULL AUTO_INCREMENT,
			`iImageID` INT NOT NULL,
			`iUserID` INT NOT NULL,
			`cComment` TEXT,
			PRIMARY KEY (`iCommentID`)
		)";
		$this->oNails->updateVersion("gallery", "1.5", $cSQL, "Added Comment Support");
	}
}