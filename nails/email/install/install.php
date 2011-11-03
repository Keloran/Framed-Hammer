<?php
/**
 * Email_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Email_Install {
	private $oNails;
	private $oDB;

	/**
	 * Email_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("cache");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Email_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		//get the version
		if ($this->oNails->checkVersion("webmail", "1.2") == false) {
			//1.1
			$this->oNails->updateVersion("webmail", "1.1", false, "Fixed parser so it displays full urls, and added pagination");

			//1.2
			$this->oNails->updateVersion("webmail", "1.2", false, "Added Spam Flag");
		}
	}

	/**
	 * Email_Install::install()
	 *
	 * @return
	 */
	private function install() {
		//1.0
		$this->oNails->addVersion("webmail", "1.0");
		$this->oNails->sendLocation("install");
	}
}