<?php
/**
 * Form_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Form_Install {
	private $oNails;
	private $oDB;

	/**
	 * Form_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("form");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Form_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("form", "1.0");
		$this->oNails->sendLocation("install");
	}

	/**
	 * Form_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if (isset($this->oNails) && $this->oNails) {
			//Check the version and do any updats
			if ($this->oNails->checkVersion("form", "1.2") == false) {
				//1.1
				$this->oNails->updateVersion("form", "1.1", false, "De-Coupled");

				//1.2
				$this->oNails->updateVersion("form", "1.2", false, "Moved so that it can be replaced easier");
			}
		}
	}
}
