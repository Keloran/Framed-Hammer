<?php
/**
 * Head_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Head_Install{
	private $oNails;
	private $oDB;

	/**
	 * Head_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("head");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Head_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("head", "1.3") == false) {

			//1.1
			$cSQL	= "CREATE TABLE IF NOT EXISTS `head_titles` (`iTitleID` INT NOT NULL AUTO_INCREMENT, `cPage` VARCHAR(50), `cAction` VARCHAR(50), `cChoice` VARCHAR(50), `iItem` INT, `cTitle` TEXT, PRIMARY KEY(`iTitleID`))";
			$this->oNails->updateVersion("head", "1.1", $cSQL, "Added the title table");

			//1.2
			$this->oNails->updateVersion("head", "1.2", false, "Tester of XML");

			//1.3
			$this->oNails->updateVersion("head", "1.3", false, "Update to version 1.5 of jQuery, and version 1.8.9 of UI");
		}
	}

	/**
	 * Head_Install::install()
	 *
	 * @return
	 */
	private function install() {
		// Create the keywords table
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `keywords` (
				`iKeywordID` INT NOT NULL AUTO_INCREMENT,
				`cPage` VARCHAR(50) NOT NULL,
				`iItem` INT NOT NULL DEFAULT 0,
				`cKeywords` TEXT NOT NULL,
				PRIMARY KEY(`iKeywordID`))
			ENGINE = MyISAM");

		//1.0
		$this->oNails->addVersion("head", "1.0");

		$this->oNails->sendLocation("install");
	}
}