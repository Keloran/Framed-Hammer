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

	/**
	 * Session_Robots_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("session_robots", "1.3") == false) {
			//1.1
			$this->oNails->updateVersion("session_robots", "1.1");

			//1.2
			$this->oNails->updateVersion("session_robots", "1.2");

			//1.3
			$this->oNails->updateVersion("session_robots", "1.3", false, "Add Visitors Sessions");
		}
	}

	/**
	 * Session_Robots_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("session_robots", "1.0");
	}
}