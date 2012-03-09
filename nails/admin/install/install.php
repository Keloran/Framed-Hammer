<?php
/**
 * Admin_Install
 *
 * @package
 * @author hootonm
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Admin_Install {
	private $oDB;
	private $oNails;

	/**
	 * Admin_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("admin");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function install() {
		//Create the banned table
		printRead("users_banned");
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users_banned` (
				`iBannedID` INT NOT NULL AUTO_INCREMENT,
				`iBannedIP` INT NOT NULL,
				`iUserID`	INT NOT NULL,
				PRIMARY KEY (`iBannedID`),
				INDEX (`iBannedID`, `iUserID`)
			) ENGINE = InnoDB");

		$this->oNails->getUsers();

		$this->oNails->addGroups("admin");
		$this->oNails->addAbility("admin", "Admin");

		$this->oNails->addVersion("admin", "1.0");
		$this->oNails->sendLocation("install");
	}

	private function upgrade() {
		if ($this->oNails->checkVersion("admin", "1.1") == false) {
			//1.1
			$cSQL	= "CREATE TABLE IF NOT EXISTS admin_contest (`iContestID` INT NOT NULL AUTO_INCREMENT, `dDated` DATETIME, `iUserID` INT NOT NULL, `cContest` VARCHAR(150), PRIMARY KEY(`iContestID`), INDEX(`iUserID`))";
			$this->oNails->updateVersion("admin", "1.1", $cSQL);
		}
	}
}