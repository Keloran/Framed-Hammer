<?php
/**
 * News_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class News_Install{
	private $oNails;
	private $oDB;

	/**
	 * News_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("news");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * News_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("news", "1.1") == false) {
			$this->oNails->updateVersion("news", "1.1", false, "Updated to make HTML5 easier");
		}
	}

	/**
	 * News_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("news", "1.0");

		$this->oNails->sendLocation("install");
	}
}
