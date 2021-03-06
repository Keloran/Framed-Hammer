<?php
/**
 * StaticPages
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class StaticPages {
	//Traits
	use Address, Mailer;

	private $oNails		= false;

	public $cStaticPage		= false;
	public $iStatic			= false;
	public $cStaticTitle	= false;
	private $bSite			= false;

	static $oStatic		= false;

	/**
	 * StaticPages::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails) {
		$this->oNails		= $oNails;

		if ($this->getParam("static")) {
			$this->cStaticPage	= $this->getParam("static");
			$this->bSite		= false;
		} else if ($this->getParam("site")) {
			$this->cStaticPage	= $this->getParam("site");
			$this->bSite		= true;
		}

		$this->informAdmin();
	}

	/**
	 * StaticPages::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		if (!self::$oStatic) {
			self::$oStatic = new StaticPages($oNails);
		}

		return self::$oStatic;
	}

	/**
	 * StaticPages::getStatic()
	 *
	 * @return string
	 */
	public function getStatic() {
		if ($this->bSite) {
			$cReturn = "<h1>Sorry " . $this->cStaticPage . " is offline</h1>\n";
		} else {
			$cReturn = "<h1>Sorry Site is offline</h1>\n";
		}

		$cReturn .= "An admin has been informed and will endevor to get it back online fast";

		return $cReturn;
	}

	/**
	 * StaticPages::getTitle()
	 *
	 * @return string
	 */
	public function getTitle() {
		if ($this->bSite) {
			$cReturn	= "Site Offline: " . $this->cStaticPage;
		} else {
			$cReturn	= "Site Offline";
		}

		return $cReturn;
	}

	/**
	 * StaticPages::getHead()
	 *
	 * @return
	 */
	public function getHead() {}

	/**
	 * StaticPages::informAdmin()
	 *
	 * @return
	 */
	private function informAdmin() {
		$oXML			= $this->oNails->getXML();
		if (!is_object($oXML)) { $oXML = new XML(); } //mainly for IDEs

		$oXML->setFile("config");
		$oXML->cRoot	= "config";

		$cEmail			= $oXML->getElement("email");
		$cAddress		= $oXML->getElement("address");
		$cTitle			= $oXML->getElement("title");

		$cText			= $this->cStaticPage . " is offline, go fix it";

		$a = array($cEmail, $cAddress, $cTitle, $cText);
		//printRead($a);die();

		$this->sendMail($cEmail, "Site Offline", $cText, $cText, "emergency@" . $cAddress, $cTitle);
	}
}
