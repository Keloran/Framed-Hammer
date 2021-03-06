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

		$bInstalled	= $oNails->checkInstalled("admin");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function install() {
		$oUsers	= new User_Install($this->oNails);

		$this->oNails->addVersion("admin", "1.0");
		$this->oNails->sendLocation("install");
	}

	private function upgrade() {
		if ($this->oNails->checkVersion("admin", "1.1") == false) {
			//make sure this is always done
			$oUsers	= new User_Install($this->oNails);

			$this->oNails->updateVersion("admin", "1.1");
		}
	}
}