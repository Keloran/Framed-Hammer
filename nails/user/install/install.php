<?php
/**
 * User_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class User_Install {
	private $oNails;
	private $oDB;

	/**
	 * User_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("users");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * User_Install::upgrade()
	 *
	 * @return null
	 */
	function upgrade() {
		//update
		if ($this->oNails->checkVersion("users", 1.6) == false) {
			//1.1
			printRead("1.1 Start");
			$cSQL	= "CREATE TABLE IF NOT EXISTS `users_special_privs` (`iSpecialID` INT NOT NULL AUTO_INCREMENT, `cAllowed` VARCHAR(50) NOT NULL, `iUserID` INT NOT NULL, PRIMARY KEY (`iSpecialID`), INDEX (`iUserID`))";
			$this->oNails->updateVersion("users", 1.1, $cSQL, "Added the special Privs table");
			printRead("1.1 End");

			//1.2
			printRead("1.2 Start");
			$this->oNails->updateVersion("users", 1.2, false, "De-Coupled");
			printRead("1.2 End");

			//1.3
			printRead("1.3 Start");
			$cSQL	= "ALTER TABLE `users` ADD COLUMN `bTemp` BOOL NOT NULL";
			$this->oNails->updateVersion("users", 1.3, $cSQL, "Added Temp flag for passwords");
			printRead("1.3 End");

			//1.4
			printRead("1.4 Start");
			$cSQL 	= "CREATE TABLE IF NOT EXISTS `users_notify` (`iNotifyID` INT NOT NULL AUTO_INCREMENT, `iUserID` INT NOT NULL, `cMessage` TEXT, PRIMARY KEY(`iNotifyID`), INDEX(`iUserID`))";
			$this->oNails->updateVersion("users", 1.4, $cSQL, "Added Notifications");
			printRead("1.4 End");

			//1.5
			printRead("1.5 Start");
			$mSQL	= array(
				"ALTER TABLE `users` CHANGE COLUMN `cPassword` `cPassword` VARCHAR(65) NOT NULL",
				"ALTER TABLE `users` CHANGE COLUMN `cRegisterHash` `cRegisterHash` VARCHAR(65) NOT NULL",
				"ALTER TABLE `users` CHANGE COLUMN `cLoginHash` `cLoginHash` VARCHAR(65) NOT NULL"
			);
			$this->oNails->updateVersion("users", 1.5, $mSQL, "Updated MD5 fields to 65 chars");
			printRead("1.5 End");

			//1.6
			printRead("1.6 Start");
			$mSQL = array(
				"ALTER TABLE `users` ADD COLUMN `bDeleted` BOOL DEFAULT 0",
				"ALTER TABLE `users` ADD COLUMN `bBanned` BOOL DEFAULT 0"
			);
			$this->oNails->updateVersion("users", 1.6, $mSQL, "Added Deleted and Banned flags");
			printRead("1.6 End");
		}
	}

	/**
	 * User_Install::install()
	 *
	 * @return
	 */
	private function install() {
		//Create the groups table
		printRead("users_groups");
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users_groups` (
				`iGroupID` INT NOT NULL AUTO_INCREMENT,
				`cGroup` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`iGroupID`)
			) ENGINE = InnoDB");

		//Create the users table and its index`s
		printRead("users");
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users` (
				`iUserID` INT NOT NULL AUTO_INCREMENT,
				`tsDate` INT NOT NULL,
				`cUsername` VARCHAR(150) NOT NULL,
				`cPassword` VARCHAR(65) NOT NULL,
				`cEmail` VARCHAR(250) NOT NULL,
				`cUserImage` VARCHAR(50) NOT NULL,
				`iGroupID` INT NOT NULL,
				`cRegisterHash` VARCHAR(65) NOT NULL,
				`cLoginHash` VARCHAR(65) NOT NULL,
				`cLastIP` VARCHAR(15) NOT NULL,
				PRIMARY KEY (`iUserID`),
				INDEX (`iGroupID`),
				INDEX (`tsDate`)
			) ENGINE = InnoDB");

		printRead("users_settings");
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users_settings` (
				`iSettingID` INT NOT NULL AUTO_INCREMENT,
				`iUserID` INT NOT NULL,
				`cSettingName` VARCHAR(50) NOT NULL,
				`cSettingValue` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`iSettingID`),
				INDEX (`iUserID`),
				FOREIGN KEY (`iUserID`)
					REFERENCES `users` (`iUserID`)
					ON DELETE CASCADE
					ON UPDATE CASCADE
			) ENGINE = InnoDB");

		//Install the groups
		printRead("Add Version");
		$this->oNails->addVersion("users", "1.0");

		printRead("Add Groups");
		$this->addInstallGroups();
	}

	/**
	 * User_Install::addInstallGroups()
	 *
	 * @return null
	 */
	private function addInstallGroups() {
		$bInstalled	= $oNails->checkInstalled("users_groups");
		if (!$bInstalled) {
			$aEscape = array(
				"Non-Registered",
				"Non-Confirmed",
				"Registered",
				"Banned",
				"Admin"
			);
			$this->oDB->write("INSERT INTO `users_groups` (cGroup) VALUES (?), (?), (?), (?), (?)", $aEscape);

			//add the groups
			$this->oNails->addGroups("install");
			$this->oNails->addAbility("Admin", "install");

			$this->oNails->addVersion("users_groups", "1.0");
		}
	}
}