<?php
/**
 * News_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class News_Install{
	private $oNails;
	private $oDB;

	/**
	 * News_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("news");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * News_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("news", "1.1") == false) {
			$this->oNails->updateVersion("news", "1.1", false, "Updated to make HTML5 easier");
		}
	}

	/**
	 * News_Install::install()
	 *
	 * @return
	 */
	private function install() {
		//Create the news table and its index`s
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `news` (
				`iNewsID` INT NOT NULL AUTO_INCREMENT,
				`tsDate` INT NOT NULL,
				`cTitle` VARCHAR(150) NOT NULL,
				`cContent` TEXT NOT NULL,
				`iUserID` INT NOT NULL,
				`iCategoryID` INT NOT NULL DEFAULT 0,
				`iImageID` INT NOT NULL DEFAULT 0,
				PRIMARY KEY (`iNewsID`))
			ENGINE=MYISAM");
		$aNews = array("iUserID", "iCategoryID", "iImageID");
		$this->oNails->addIndexs("news", $aNews);

		//Create the category table
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `news_categorys` (
				`iCategoryID` INT NOT NULL AUTO_INCREMENT,
				`cCategory` VARCHAR(50) NOT NULL,
				`cImageName` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`iCategoryID`))
			ENGINE=MYISAM");

		//Create the images table
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `news_images` (
				`iImageID` INT NOT NULL AUTO_INCREMENT,
				`iNewsID` INT NOT NULL,
				`cImage` VARCHAR(100) NOT NULL,
				PRIMARY KEY (`iImageID`),
				INDEX `fk_news_images`(`iNewsID` ASC))
			ENGINE=InnoDB");
		$this->oNails->addIndexs("news_images", "iNewsID");

		//check if the groups table is there, if it is add the allowed to comment
		//and news options, and their respective index's
		if ($this->oNails->groupsInstalled()) {
			$aGroups = array("newsComments", "news");
			$this->oNails->addGroups($aGroups);

			$aAdminAbilitys = array("news", "newsComments");
			$this->oNails->addAbility("Admin", $aAdminAbilitys);

			$this->oNails->addAbility("Registered", "newsComments");
		}

		$this->oNails->addVersion("news", "1.0");

		$this->oNails->sendLocation("install");
	}
}
