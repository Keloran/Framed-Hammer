<?php
/**
 * Organic
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Organic {
	private static $oOrganic;

	private $oDB;
	private $oNails;

	/**
	 * Organic::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB	= $oNails->getDatabase();
	}

	/**
	 * Organic::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oOrganic)) {
			self::$oOrganic = new Organic($oNails);
		}

		return self::$oOrganic;
	}

	/**
	 * Organic::markOrganic()
	 *
	 * @return
	 */
	public function markOrganic() {
		if (isset($_SERVER['HTTP_REFERER'])) {
			$cSelf		= $_SERVER['HTTP_HOST'];
			$cUnParsed	= $_SERVER['HTTP_REFERER'];

			//parse it
			$aRefer	= parse_url($cUnParsed);

			//Host
			$cHost = false;
			if (isset($aRefer['host'])) {
				$cHost	= $aRefer['host'];
			}

			//Query
			$cOrganic = false;
			if (isset($aRefer['query'])) {
				parse_str($aRefer['query'], $aOrganic);

				//google | bing
				if (isset($aOrganic['q'])) {
					$cOrganic = $aOrganic['q'];
				}

				//yahoo
				if (isset($aOrganic['p'])) {
					$cOrganic = $aOrganic['p'];
				}

				//lycos
				if (isset($aOrganic['query'])) {
					$cOrganic = $aOrganic['query'];
				}
			}

			if ($cHost != $cSelf) {
				$aInsert = array($cHost, $cOrganic, $cUnParsed);
				$this->oDB->write("INSERT INTO organic (dDated, cHost, cOrganic, cUnParsed) VALUES (NOW(), ?, ?, ?)", $aInsert);
			}
		}
	}

	/**
	 * Organic::getOrganics()
	 *
	 * @param int $iLimit
	 * @return array
	 */
	public function getOrganics($iLimit = null) {
		$aReturn	= false;
		$iLimit		= ($iLimit ? $iLimit : 20);
		$i			= 0;

		$this->oDB->read("SELECT dDated, cHost, cOrganic, cUnParsed FROM organic ORDER BY dDated DESC LIMIT " . $iLimit);
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['dated']		= $this->oDB->f('dDated');
			$aReturn[$i]['host']		= $this->oDB->f('cHost');
			$aReturn[$i]['organic']		= $this->oDB->f('cOrganic');
			$aReturn[$i]['original']	= $this->oDB->f('cUnParsed');
			$i++;
		}

		return $aReturn;
	}
}

/**
 * Organic_Exception
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Organic_Exception extends Exception {}