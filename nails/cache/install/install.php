<?php
/**
 * Cache_Install
 *
 * @package Cache
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Cache_Install {
	private $oDB;
	private $oNails;

	/**
	 * Cache_Install::__construct()
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
	 * Cache_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("cache", "1.0");
		$this->oNails->sendLocation("install");
	}

	/**
	 * Cache_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {

	}
}