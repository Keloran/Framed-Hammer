<?php
/**
 * Gallery
 *
 * @package Gallery
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Gallery implements Nails_Interface {
	private $iUserID	= false;
	private $oNails		= false;

	private static $oGallery;

	public $oDB			= false;
	public $oImage		= false;
	public $oUser		= false;

	/**
	 * Gallery::__construct()
	 *
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;

		if ($this->oNails->checkInstalled("gallery") == false) {
			$this->install();
		}

		//do the checks on the gallery tables
		if ($this->oNails->checkVersion("gallery", "1.2") == false) {
			$this->upgrade("gallery", "1.2");
		}

		if ($this->oNails->checkVersion("gallery_user", "1.0") == false) {
		}

		if ($this->oNails->checkVersion("gallery_exif", "1.1") == false) {
			$this->upgrade("gallery_exif", "1.1");
		}

		$this->oUser	= $this->oNails->getUser();
		$this->iUserID	= $this->oUser->getUserID();

		$this->oDB		= $this->oNails->getDatabase();
		$this->oImage	= $this->oNails->getNail("image");
	}

	/**
	 * Gallery::getInstance()
	 *
	 * @return
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oGallery)) {
			self::$oGallery = new Gallery($oNails);
		}

		return self::$oGallery;
	}

	/**
	 * Gallery::upgrade()
	 *
	 * @param string $cTable
	 * @param float $cVersion
	 * @return null
	 */
	private function upgrade($cTable, $cVersion) {
		if ($cTable == "gallery") { //Gallery
			switch ($cVersion) {
				case "1.0":
					$this->oNails->addVersion("gallery", "1.0");
					break;
				case "1.1":
					$cSQL = "ALTER TABLE `gallery` ADD COLUMN `bPrivate` BOOL NOT NULL";
					$this->oNails->updateVersion("gallery", "1.1", $cSQL, "Added Private setting");
					break;
				case "1.2":
					$cSQL = "ALTER TABLE `gallery` ADD COLUMN `cFileSmall` TEXT, ADD COLUMN `cFileMedium` TEXT";
					$this->oNails->updateVersion("gallery", "1.2", $cSQL);
					break;

			}
		} else if ($cTable == "gallery_user") { //Gallery User
			switch ($cVersion) {
				case "1.0":
					$this->oNails->addVersion("gallery_user", "1.0");
					break;
				case "1.1":
					$cSQL = "ALTER TABLE `gallery_user` ADD COLUMN `bPrivate` BOOL NOT NULL";
					$this->oNails->updateVersion("gallery_user", "1.1", $cSQL, "Added Private Flag");
					break;
			}
		} else if ($cTable == "gallery_exif") {
			switch ($cVersion) {
				case "1.0":
					$this->oNails->addVersion("gallery_exif", "1.0");
					break;

				case "1.1":
					$cSQL = "ALTER TABLE `gallery_exif` ADD COLUMN `cExposureMode` TEXT NULL DEFAULT NULL";
					$this->oNails->updateVersion("gallery_exif", "1.1", $cSQL, "Added Exposure");
					break;
			}
		}
	}

	/**
	 * Gallery::install()
	 *
	 * @return null
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

		$this->oNails->sendLocation("refer");
	}

	/**
	 * Gallery::getGallery()
	 *
	 * @param string $cName
	 * @return array
	 */
	public function getGallery($cName = false) {
		$aReturn	= false;
		$i			= 0;
		$iUserID	= false;

		if (!$cName) {
			$iUserID = $this->iUserID;
		} else {
			$this->oDB->read("SELECT iUserID FROM users WHERE cUsername = ? LIMIT 1", $cName);
			if ($this->oDB->nextRecord()) {
				$iUserID = $this->oDB->f('iUserID');
			}
		}

		//Since this user doesnt exist
		if (!$iUserID) {
			$this->oNails->sendLocation("/gallery/");
		}

		$this->oDB->read("SELECT iGalleryID, cLabel, cFile, cFileSmall, iUserGalleryID, tsDate FROM gallery WHERE iUserID = ? ORDER BY iGalleryID DESC", $iUserID);
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['label']		= $this->oDB->f('cLabel');
			$aReturn[$i]['id']		= $this->oDB->f('iGalleryID');
			$aReturn[$i]['filesmall']	= $this->oDB->f('cFileSmall');
			$aReturn[$i]['dated']		= date("d/m/Y", $this->oDB->f('tsDate'));
			$aReturn[$i]['galleryid']	= $this->oDB->f('iUserGalleryID');
			$aReturn[$i]['file']		= $this->oDB->f('cFile');
			$aReturn[$i]['userid']		= $iUserID;

			$i++;
		}

		//get gallery names, and username
		$iCount = count($aReturn);
		for ($i = 0; $i < $iCount; $i++) {
			if ($cName) {
				$aReturn[$i]['username']	= $cName;
			} else {
				$aReturn[$i]['username']	= $this->getUserDetails($aReturn[$i]['userid']);
			}

			if (isset($aReturn[$i]['galleryid'])) {
				//gallery
				if ($aReturn[$i]['galleryid'] == 0) {
					$aReturn[$i]['gallery']	= "Base";
				} else {
					$aReturn[$i]['gallery']	= $this->getGalleryName($aReturn[$i]['galleryid']);
				}
			} else {
				$aReturn[$i]['gallery']	= "Base";
			}
		}

		return $aReturn;
	}

	/**
	 * Gallery::viewGallery()
	 *
	 * @param string $cName
	 * @param int $iUserID
	 * @return int
	 */
	public function viewGallery($cName, $iUserID) {
		$aReturn	= false;
		$i			= 0;
		$iGallery	= 0;

		if ($cName == "base") {
			$iGallery = 0;
		} else {
			$aSelect = array($cName, $iUserID);
			$this->oDB->read("SELECT iGalleryID FROM gallery_user WHERE cLabel = ? AND iUserID = ? AND bPrivate = 0 LIMIT 1", $aSelect);
			if ($this->oDB->nextRecord()) {
				$iGallery = $this->oDB->f('iGalleryID');
			}
		}


		//Get the name of the person whos gallery it is
		$cUsername = $this->userDetails($iUserID);

		$aSelect = array($iGallery, $iUserID);
		$this->oDB->read("SELECT iGalleryID, cLabel, cFile, cFileSmall, iUserGalleryID, iUserID, tsDate FROM gallery WHERE iUserGalleryID = ? AND iUserID = ? ORDER BY iGalleryID DESC", $aSelect);
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['label']	= $this->oDB->f('cLabel');
			$aReturn[$i]['id']		= $this->oDB->f('iGalleryID');
			$aReturn[$i]['file']	= $this->oDB->f('cFile');
			$aReturn[$i]['dated']	= date("d/m/Y", $this->oDB->f('tsDate'));
			$aReturn[$i]['user']	= $cUsername;
			$aReturn[$i]['small']	= $this->oDB->f('cFileSmall');

			$i++;
		}

		return $aReturn;
	}

	/**
	 * Gallery::userDetails()
	 *
	 * @param int $iUserID
	 * @return mixed
	 */
	private function userDetails($iUserID) {
		$cReturn	= false;

		if (($iUserID) && ($iUserID != "0")) {
			$this->oDB->read("SELECT cUsername FROM users WHERE iUserID = ? LIMIT 1", $iUserID);
			if ($this->oDB->nextRecord()) {
				$cReturn = $this->oDB->f('cUsername');
			}
		}

		return $cReturn;
	}

	/**
	 * Gallery::getGalleryName()
	 *
	 * @param int $iGalleryID
	 * @return mixed
	 */
	private function getGalleryName($iGalleryID) {
		$cReturn	 = false;

		$this->oDB->read("SELECT cLabel FROM gallery_user WHERE iGalleryID = ? LIMIT 1", $iGalleryID);
		if ($this->oDB->nextRecord()) {
			$cReturn = $this->oDB->f('cLabel');
		}

		return $cReturn;
	}

	/**
	 * Gallery::getUserGallerys()
	 *
	 * @return array
	 */
	public function getUserGallerys() {
		$aReturn	= false;
		$i			= 0;

		//Base gallery that everyone has
		$aReturn[$i]['label']	= "Base";
		$aReturn[$i]['id']		= 0;
		$i++;

		$this->oDB->read("SELECT cLabel, iGalleryID FROM gallery_user WHERE iUserID = ? AND bPrivate = 0 ORDER BY cLabel DESC", $this->iUserID);
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['label']	= $this->oDB->f("cLabel");
			$aReturn[$i]['id']		= $this->oDB->f("iGalleryID");
			$i++;
		}

		return $aReturn;
	}

	/**
	 * Gallery::getLatest()
	 *
	 * @param bool $bNamesOnly
	 * @return array
	 */
	public function getLatest($bNamesOnly = false) {
		$aReturn	= array();
		$i			= 0;

		$this->oDB->read("SELECT iGalleryID, cLabel, cFile, cFileSmall, iUserGalleryID, iUserID, tsDate FROM gallery WHERE bPrivate = 0 ORDER BY iGalleryID DESC LIMIT 20");
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['label']		= $this->oDB->f('cLabel');
			$aReturn[$i]['id']			= $this->oDB->f('iGalleryID');
			$aReturn[$i]['file']		= $this->oDB->f('cFile');
			$aReturn[$i]['dated']		= date("d/m/Y", $this->oDB->f('tsDate'));
			$aReturn[$i]['userid']		= $this->oDB->f('iUserID');
			$aReturn[$i]['galleryid']	= $this->oDB->f('iUserGalleryID');
			$aReturn[$i]['filesmall']	= $this->oDB->f('cFileSmall');

			$i++;
		}

		//get the gallery name
		for ($i = 0; $i < count($aReturn); $i++) {
			if ($bNamesOnly) {
				$aReturn[$i]['gallery'] = $this->userDetails($aReturn[$i]['userid']);
			} else {
				if ($aReturn[$i]['galleryid'] == 0) {
					$aReturn[$i]['gallery'] = "base";
				} else {
					$aReturn[$i]['gallery'] = $this->getGalleryName($aReturn[$i]['galleryid']);
				}
			}

			//get the userid
			if ($aReturn[$i]['userid']) {
				$iUserID = $aReturn[$i]['userid'];
			} else {
				$iUserID = false;
			}

			$aReturn[$i]['username']		= $this->userDetails($iUserID);
		}

		return $aReturn;
	}

	/**
	 * Gallery::addBasicImage()
	 *
	 * @param string $cLabel
	 * @param array $aFile
	 * @return
	 */
	public function addBasicImage($cLabel, $aFile) {
		$cOriginal	= UPLOAD . $this->iUserID . time() . $aFile['name'];
		$cFinal		= $this->iUserID . time() . $aFile['name'];

		if (move_uploaded_file($aFile['tmpName'], $cOriginal)) {
			//Create the thumnail image
			$cImageData		= $this->oImage->resizer($cOriginal, 100, 150);
			$cImageSmall	= $this->createImage($cImageData, "small");

			//Create the image for the staging area
			$cImageData		= $this->oImage->resizer($cOriginal, 500, 700);
			$cImageMedium	= $this->createImage($cImageData, "medium");

			$aInsert = array($this->iUserID, $cLabel, $cFinal, $cOriginal, $cImageSmall, $cImageMedium);
			$this->oDB->write("INSERT INTO gallery (iUserID, cLabel, cFile, cLongFile, tsDate, cFileSmall, cFileMedium) VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?)", $aInsert);

			return $this->oDB->insertID();
		} else {
			throw new Spanner("Error occoured file couldn't be uploaded, " . $aFile['error'], 300);
		}
	}

	/**
	 * Gallery::addImage()
	 *
	 * @param object $oForm
	 * @return int
	 */
	public function addImage(Form $oForm) {
		$cLabel		= $oForm->getValue("label");
		$aFile		= $oForm->getValue("image", true);
		$iGalleryID	= $oForm->getValue("gallery");

		$cOriginal		= UPLOAD . $this->iUserID . time() . $aFile['name'];
		$cFinal			= $this->iUserID . time() .  $aFile['name'];

		try {
			if (move_uploaded_file($aFile['tmpName'], $cOriginal)) {
				switch ($aFile['type']) {
					case "jpg":
					case "jpeg":
					case "tiff":
						$bExif = true;
						break;

					default:
						$bExif = false;
						break;
				}

				//this is to convert it straight away if its a tiff
				if (strstr($cFinal, "tif")) {
					$cImageData = $this->oImage->resizer($cOriginal, 1000, 1000);
					$cFinal  	= $this->createImage($cImageData, "large");
				}

				//this is for the exif data
				if ($bExif) {
					//exif has to happen after its been moved
					$aExif 			= exif_read_data($cOriginal, "EXIF");
					if ($aExif) {
						$aComputed		= isset($aExif['COMPUTED'])				? $aExif['COMPUTED'] 			: "";
						$cCCD			= isset($aComputed['CCDWidth'])			? $aComputed['CCDWidth'] 		: "";
						$cApatureNumber	= isset($aComputed['ApertureFNumber'])	? $aComputed['ApertureFNumber']	: "";
						$cMake			= isset($aExif['Make'])					? $aExif['Make']				: "";
						$cModel			= isset($aExif['Model'])				? $aExif['Model']				: "";
						$cSoftware		= isset($aExif['Software'])				? $aExif['Software']			: "";
						$cExposure		= isset($aExif['ExposureTime'])			? $aExif['ExposureTime']		: "";
						$cShutterSpeed	= isset($aExif['ShutterSpeedValue'])	? $aExif['ShutterSpeedValue']	: "";
						$cApatureValue	= isset($aExif['ApertureValue'])		? $aExif['ApertureValue']		: "";
						$cFocalLength	= isset($aExif['FocalLength'])			? $aExif['FocalLength']			: "";
						$cExposureMode	= isset($aExif['ExposureMode'])			? $aExif['ExposureMode']		: "";
						$cWhiteBalance	= isset($aExif['WhiteBalance'])			? $aExif['WhiteBalance']		: "";

						$aInsert		= array($this->iUserID, $cCCD, $cApatureNumber, $cMake, $cModel, $cSoftware, $cExposure, 	$cShutterSpeed, $cApatureValue, $cFocalLength, $cExposure, $cWhiteBalance, $cExposureMode);
						$this->oDB->write("INSERT INTO gallery_exif (iUserID, cCCD, cApatureNumber, cMake, cModel, cSoftware, cExposure, cShutterSpeed, cApatureValue, cFocalLength, cWhiteBalance, cExposureMode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $aInsert);

						$iExifID	= $this->oDB->insertID();
					} else {
						$iExifID 	= "";
					}
				} else {
					$iExifID = "";
				}

				//Create the thumnail image
				$cImageData		= $this->oImage->resizer($cOriginal, 100, 150);
				$cImageSmall	= $this->createImage($cImageData, "small");

				//Create the image for the staging area
				$cImageData		= $this->oImage->resizer($cOriginal, 500, 700);
				$cImageMedium	= $this->createImage($cImageData, "medium");

				$aInsert = array($this->iUserID, $cLabel, $cFinal, $cOriginal, $iGalleryID, $cImageSmall, $cImageMedium, $iExifID);
				$this->oDB->write("INSERT INTO gallery (iUserID, cLabel, cFile, cLongFile, iUserGalleryID, tsDate, cFileSmall, cFileMedium, iExifID) VALUES (?, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?, ?)", $aInsert);

				return $this->oDB->insertID();
			} else {
				throw new Spanner("Error occoured file couldn't be uploaded, " . $aFile['error'] . " -- " . $aFile['name'], 300);
			}
		} catch (Spanner $e) {
			throw new Spanner($e->getMessage(), 300);
		}
	}

	/**
	 * Gallery::getImage()
	 *
	 * @param int $iImageID
	 * @return array
	 */
	public function getImage($iImageID = false) {
		$aReturn	= false;

		$iImageID 	= $iImageID ? $iImageID : $this->oNails->iItem;

		$this->oDB->read("SELECT iGalleryID, cLabel, tsDate, iUserID, iUserGalleryID, cFile, cFileMedium, iExifID FROM gallery WHERE iGalleryID = ? AND bPrivate = 0 LIMIT 1", $iImageID);
		if ($this->oDB->nextRecord()) {
			$aReturn['label']		= $this->oDB->f('cLabel');
			$aReturn['dated']		= date("d/m/Y", $this->oDB->f('tsDate'));
			$aReturn['file']		= $this->oDB->f('cFileMedium');
			$aReturn['userid']		= $this->oDB->f('iUserID');
			$aReturn['galleryid']	= $this->oDB->f('iGalleryID');
			$aReturn['full']		= $this->oDB->f('cFile');
			$aReturn['usergallery']	= $this->oDB->f('iUserGalleryID');
			$aReturn['exif']		= $this->getExif($this->oDB->f('iExifID'));
		}

		$aReturn['user']	= $this->userDetails($aReturn['userid']);

		if (isset($aReturn['usergallery']) && ($aReturn['usergallery'] == 0)) {
			$aReturn['gallery'] = "Base";
		} else {
			if (isset($aReturn['usergallery'])) {
				$aReturn['gallery']		= $this->getGalleryName($aReturn['usergallery']);
			} else {
				$aReturn['gallery'] = "Base";
			}
		}

		return $aReturn;
	}

	/**
	 * Gallery::getExif()
	 *
	 * @param int $iExifID
	 * @return string
	 */
	private function getExif($iExifID) {
		$cReturn	= "";

		if (($iExifID ) && ($iExifID != "0")) {
			$this->oDB->read("SELECT cMake, cModel, cExposure, cSoftware, cFocalLength, cShutterSpeed, cCCD, cWhiteBalance FROM gallery_exif WHERE iExifID = ? LIMIT 1", $iExifID);
			if ($this->oDB->nextRecord()) {
				//Camera info, but only if it exists
				if ($this->oDB->f('cMake')) {
					$cReturn	.= "<h3>Camera Info</h3>";
					$cReturn	.= "Camera Make: "			. $this->oDB->f('cMake');
				}
				if ($this->oDB->f('cModel')) { 	$cReturn	.= "<br />Camera Model: "	. $this->oDB->f('cModel');			}
				if ($this->oDB->f('cCCD')) {	$cReturn	.= "<br />Camera has " 		. $this->oDB->f('cCCD') . " CCD's";	}

				//Shot info but only if it exists, this usually will
				if ($this->oDB->f('cExposure')) {
					$cReturn	.= "<h3>Shot Info</h3>";
					$cReturn	.= "Exposure Time: " . $this->oDB->f('cExposure');
				}
				if ($this->oDB->f('cFocalLength')) { 	$cReturn	.= "<br />Focal Length: "	. $this->oDB->f('cFocalLength'); 	}
				if ($this->oDB->f('cShutterSpeed')) {	$cReturn	.= "<br />Shutter Speed: "	. $this->oDB->f('cShutterSpeed');	}
				if ($this->oDB->f('cWhiteBalance')) {	$cReturn	.= "<br />White Balance: "	. $this->oDB->f('cWhiteBalance');	}
				if ($this->oDB->f('cExposureMode')) {	$cReturn	.= "<br />Exposure Mode: "	. $this->oDB->f('cExposureMode'); 	}

				//Software info, this will exist, at least i cant see a time when it wont
				if ($this->oDB->f('cSoftware')) {
					$cReturn	.= "<h3>Software Used</h3>";
					$cReturn	.= $this->oDB->f('cSoftware');
				}
			}
		}

		return $cReturn;
	}

	/**
	 * Gallery::createImage()
	 *
	 * @param string $cData
	 * @param string $cExtra
	 * @return mixed
	 */
	private function createImage($cData, $cExtra, $cFormat = "jpg") {
		if ($cData[55]) {
			$cPath		= substr(UPLOAD, 0, (strlen(UPLOAD) -1));
			$fTempFile	= tempnam($cPath, $cFormat);
			$fHandle	= fopen($fTempFile, "w");
			$fWrite		= fwrite($fHandle, $cData);

			$cFinalName		= UPLOAD . $this->iUserID . time() . $cExtra . "." . $cFormat;
			$cReturnName	= $this->iUserID . time() . $cExtra . "." . $cFormat;
			$fRename		= rename($fTempFile, $cFinalName);

			return $cReturnName;
		}

		return false;
	}

	/**
	 * Gallery::getOwner()
	 *
	 * @param int $iImageID
	 * @return int
	 */
	public function getOwner($iImageID) {
		$iReturn	= false;

		$this->oDB->read("SELECT iUserID FROM gallery WHERE iGalleryID = ? LIMIT 1", $iImageID);
		if ($this->oDB->nextRecord()) {
			$iReturn = $this->oDB->f('iUserID');
		}

		return $iReturn;
	}

	/**
	 * Gallery::deleteImage()
	 *
	 * @param int $iImageID
	 * @param int $iUserID
	 * @return bool
	 */
	public function deleteImage($iImageID, $iUserID = false) {
		if ($iUserID) {
			$bCanDelete = $this->oUser->canDoThis("deleteImage");
			if ($bCanDelete) {
				$user	= $iUserID;
			}
		}

		$iUser = isset($user) ? $user : $this->iUserID;

		$aDelete = array($iImageID, $iUser);
		$this->oDB->write("DELETE FROM gallery WHERE iGalleryID = ? AND iUserID = ? LIMIT 1", $aDelete);
	}

	/**
	 * Gallery::createGallery()
	 *
	 * @param object $oForm
	 * @return null
	 */
	public function createGallery(Form $oForm) {
		$cGallery	= $oForm->getValue("gallery");

		$aInsert	= array($cGallery, $this->iUserID);
		$this->oDB->write("INSERT INTO gallery_user (cLabel, iUserID) VALUES (?, ?)", $aInsert);
	}
}