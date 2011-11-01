<?php
/**
 * Session_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Session_Install {
	private $oNails;
	private $oDB;

	/**
	 * Session_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("session");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function upgrade() {
		if ($this->oNails->checkVersion("session", "1.2") == false) {
			//1.1
			$cSQL	= "ALTER TABLE `users_sessions` CHANGE COLUMN `cIP` `cIP` INT NULL DEFAULT '0' AFTER `cReason`";
			$this->oNails->updateVersion("session", "1.1", $cSQL, "Updated to now use ip2long rather than stoping it as a strig, needs to keep as c for old calls");


			//1.2
			$cSQL = "ALTER TABLE `users_sessions` ADD COLUMN `cBrowser` varchar(255)";
			$this->oNails->updateVersion("session", "1.2", $cSQL, "Added Browser so that I can target browsers");
		}
	}

	/**
	 * Session_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users_sessions` (
				`iSessionID` INT NOT NULL AUTO_INCREMENT,
				`iUserID` INT NOT NULL,
				`cLastSessionID` VARCHAR(50) NOT NULL,
				`tsDate` INT NOT NULL,
				`cReason` VARCHAR(150) NOT NULL,
				`cIP` VARCHAR(15) NOT NULL,
				PRIMARY KEY (`iSessionID`),
				INDEX `fk_users_sessions` (`iUserID` DESC)
			) ENGINE = MEMORY");
		$this->oNails->addIndexs("users_sessions", array("iUserID", "cLastSessionID"));

		$this->oNails->addVersion("session", "1.0");

		$this->oNails->sendLocation("install");
	}
}