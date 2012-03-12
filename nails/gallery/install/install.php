<?php
/**
 * Gallery_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Gallery_Install {
	private $oNails;
	private $oDB;

	/**
	 * Gallery_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("gallery");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Gallery_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("gallery", "1.5") == false) {
			//1.1
			$this->rapier();

			//1.2
			$this->aruba();

			//1.3
			$this->plato();

			//1.4
			$this->denali();

			//1.5
			$this->hawk();
		}
	}

	/**
	 * Gallery_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("gallery", "1.0");
		$this->oNails->addVersion("gallery_exif", "1.0");
		$this->oNails->addVersion("gallery_user", "1.0");

		$this->oNails->sendLocation("install");
	}

	/**
	 * Gallery_Install::rapier()
	 *
	 * @return null
	 */
	public function rapier() {
		$this->oNails->updateVersion("gallery", "1.1", false, "Added Private setting");
	}

	/**
	 * Gallery_Install::aruba()
	 *
	 * @return
	 */
	public function aruba() {
		$this->oNails->updateVersion("gallery", "1.2");
	}

	/**
	 * Gallery_Install::plato()
	 *
	 * @return
	 */
	public function plato() {
		$this->oNails->updateVersion("gallery", "1.3", false, "Added Private Flag");
	}

	/**
	 * Gallery_Install::denali()
	 *
	 * @return
	 */
	public function denali() {
		$this->oNails->updateVersion("gallery", "1.4", false, "Added Exposure");
	}

	/**
	 * Gallery_Install::hawk()
	 *
	 * @return
	 */
	public function hawk() {
		$this->oNails->updateVersion("gallery", "1.5", false, "Added Comment Support");
	}
}