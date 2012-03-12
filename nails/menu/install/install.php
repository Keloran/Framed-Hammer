<?php
/**
 * Menu_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Menu_Install {
	private $oNails;
	private $oDB;

	/**
	 * Menu_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("menu");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function upgrade() {

	}

	private function install() {
		$this->oNails->addVersion("menu", "1.0");

		$this->oNails->sendLocation("install");
	}
}