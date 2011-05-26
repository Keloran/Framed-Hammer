<?php
/**
 * RSS
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class RSS {
	/** Objects */
	private $oNails;
	private $oDB;
	private $oChild;

	/** Variables */
	private $cType;
	private $cFilter;

	/** Static */
	private static $oRSS;

	/**
	 * RSS::__construct()
	 *
	 * @param Nails $oNails
	 * @return null;
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB	= $oNails->getDatabase();
		$this->cType	= $oNails->cPage;
		$this->cFilter	= $oNails->cFilter;

		if ($this->cType) {
			$cType		= "RSS_" . $this->cType;
			$this->oChild	= new $cType($oNails, $cFilter);
		}
	}

	/**
	 * RSS::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	public static function getInstance(Nails $oNails) {
		if (is_null(self::$oRSS)) {
			self::$oRSS	= new RSS($oNails);
		}

		return self::$oRSS;
	}

	public function __destruct() {
		$this->oDB	= null;
		$this->oNails	= null;

		unset($this->oDB);
		unset($this->oNails);
	}
}
