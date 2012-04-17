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
class Template_Static_Install {
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

		$bInstalled	= $oNails->checkInstalled("template_static");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Template_Static_Install::upgrade()
	 *
	 * @return null
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("template_static", "1.0") == false) {
			//1.1
			$this->oNails->updateVersion("session", "1.1", false, "Updated to now use ip2long rather than stoping it as a strig, needs to keep as c for old calls");

			//1.2
			$this->oNails->updateVersion("session", "1.2", false, "Added Browser so that I can target browsers");

			//1.3
			$this->oNails->updateVersion("session", "1.3", false, "Updated to autoinstall");

			//1.4
			$this->oNails->updateVersion("session", "1.4", false, "Add Visitors Sessions");
		}
	}

	/**
	 * Session_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("template_static", "1.0");

		$this->oNails->sendLocation("install");
	}
}