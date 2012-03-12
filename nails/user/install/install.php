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
			$this->oNails->updateVersion("users", 1.1, false, "Added the special Privs table");
			printRead("1.1 End");

			//1.2
			printRead("1.2 Start");
			$this->oNails->updateVersion("users", 1.2, false, "De-Coupled");
			printRead("1.2 End");

			//1.3
			printRead("1.3 Start");
			$this->oNails->updateVersion("users", 1.3, false, "Added Temp flag for passwords");
			printRead("1.3 End");

			//1.4
			printRead("1.4 Start");
			$this->oNails->updateVersion("users", 1.4, falsw, "Added Notifications");
			printRead("1.4 End");

			//1.5
			printRead("1.5 Start");
			$this->oNails->updateVersion("users", 1.5, false, "Updated MD5 fields to 65 chars");
			printRead("1.5 End");

			//1.6
			printRead("1.6 Start");
			$this->oNails->updateVersion("users", 1.6, false, "Added Deleted and Banned flags");
			printRead("1.6 End");
		}
	}

	/**
	 * User_Install::install()
	 *
	 * @return
	 */
	private function install() {
		//Install the groups
		printRead("Add Version");
		$this->oNails->addVersion("users", "1.0");

		printRead("Add Groups");
	}
}