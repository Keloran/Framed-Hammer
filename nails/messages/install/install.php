<?php
/**
 * Messages_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Messages_Install {
	private $oNails;
	private $oDB;

	/**
	 * Messages_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("messages");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Messages_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($oNails->checkVersion("messages", "1.2") == false) {
			//1.1
			$cSQL = "
				CREATE TABLE IF NOT EXISTS `notifications_groups` (
  					`iNotifyID` INT NOT NULL AUTO_INCREMENT,
  					`iGroupID` INT(11) NOT NULL,
  					`tsDated` TIMESTAMP NOT NULL ,
  					`cMessage` VARCHAR(150) NULL ,
  					`cPermission` VARCHAR(100) NULL,
  					PRIMARY KEY (`iNotifyID`) ,
  					INDEX `groups_time` (`iGroupID` ASC, `tsDated` DESC),
  					INDEX `permissions` (`cPermission` ASC)
  				) ENGINE = MEMORY";
			$oNails->updateVersion("messages", "1.1", $cSQL, "Added Groups Notifications");

			//1.2
			$cSQL = "
				CREATE TABLE IF NOT EXISTS `notifications_users` (
  					`iNotifyID` INT(11) NOT NULL AUTO_INCREMENT ,
  					`iUserID` INT(11) NOT NULL ,
  					`cMessage` VARCHAR(150) NULL DEFAULT NULL ,
  					`tsDated` TIMESTAMP NOT NULL ,
  					PRIMARY KEY (`iNotifyID`) ,
  					INDEX `iUserID` (`iUserID` ASC, `tsDated` ASC) ,
  					INDEX `fk_users_notify_users1` (`iUserID` ASC)
    			) ENGINE = MEMORY";
			$oNails->updateVersion("messages", "1.2", $cSQL, "Added Users Notifications");
		}
	}

	/**
	 * Messages_Install::install()
	 *
	 * @return
	 */
	private function install() {
		//Add the forums
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users_messages` (
				`iMessageID` INT NOT NULL AUTO_INCREMENT,
				`iRecieverID` INT NOT NULL,
				`iSenderID` INT NOT NULL,
				`cTitle` VARCHAR(150) NOT NULL,
				`cMessage` TEXT NOT NULL,
				`tsDate` INT NOT NULL,
				`bRead` BOOL DEFAULT 0,
				PRIMARY KEY (`iMessageID`)
			) ENGINE = MYISAM");
		$this->oNails->addIndexs("user_messages", array("iRecieverID", "iSenderID"));

		$this->oNails->addVersion("messages", "1.0");

		$this->oNails->sendLocation("install");
	}
}