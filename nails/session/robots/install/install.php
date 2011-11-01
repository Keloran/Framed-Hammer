<?php
/**
 * Session_Robots_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Session_Robots_Install {
	private $oNails;
	private $oDB;

	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("session_robots");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function upgrade() {
		if ($this->oNails->checkVersion("session_robots", "1.3") == false) {
			//1.1
			$cSQL	= "ALTER TABLE `users_sessions_robots` ADD COLUMN `cFullRobotString` TEXT NOT NULL";
			$this->oNails->updateVersion("session_robots", "1.1", $cSQL);

			//1.2
			$this->oNails->updateVersion("session_robots", "1.2");

			//1.3
			$cSQL	= "
				CREATE TABLE IF NOT EXISTS `users_sessions_visitors` (
					`iSessionID` INT NOT NULL AUTO_INCREMENT,
					`cUserAgent` TEXT,
					`tsDate` INT NOT NULL,
					PRIMARY KEY (`iSessionID`)
				) ENGINE = MyISAM
			";
			$this->oNails->updateVersion("session_robots", "1.3", $cSQL, "Add Visitors Sessions");
		}
	}

	private function install() {
		$this->oNails->addTable("
				CREATE TABLE IF NOT EXISTS `users_sessions_robots` (
					`iSessionID` INT NOT NULL AUTO_INCREMENT,
					`cRobotName` VARCHAR (50),
					`tsDate` INT NOT NULL,
					`cRobotSite` TEXT,
					PRIMARY KEY (`iSessionID`)
				) ENGINE = MYISAM");

		$this->oNails->addVersion("session_robots", "1.0");
	}
}