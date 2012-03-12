<?php
/**
 * Twitter_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Twitter_Install {
	private $oNails;
	private $oDB;

	/**
	 * Twitter_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	public function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("twitter");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Twitter_Install::upgrade()
	 *
	 * @return null
	 */
	public function upgrade() {
		if ($this->oNails->checkVersion("twitter", "1.3") == false) {
			//1.1
			$this->ocelot();

			//1.2
			$this->catapiller();

			//1.3
			$this->revolver();
		}
	}

	/**
	 * Twitter_Install::install()
	 *
	 * @return null
	 */
	public function install() {
		$this->oNails->addVersion("twitter", "1.0");
	}

	/**
	 * Twitter_Install::ocelot()
	 *
	 * @return
	 */
	private function ocelot() {
		$this->oNails->updateVersion("twitter", "1.1", false, "Make details, and drop old columns");
	}

	/**
	 * Twitter_Install::catapiller()
	 *
	 * @return
	 */
	private function catapiller() {
		$this->oNails->updateVersion("twitter", "1.2",  false, "Add UserID indexs");
	}

	/**
	 * Twitter_Install::revolver()
	 *
	 * @return
	 */
	private function revolver() {
		$this->oNails->updateVersion("twitter", "1.3", false, "Add user image to the tweet for when retweet");
	}
}