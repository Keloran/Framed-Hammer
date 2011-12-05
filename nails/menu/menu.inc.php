<?php
/**
 * Menu
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Menu implements Nails_Interface {
	private $oDB	= false;
	private $oNails	= false;

	private static $oMenu;

	/**
	 * Menu::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();
	}

	/**
	 * Menu::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	public static function getInstance(Nails $oNails) {
		if (is_null(self::$oMenu)) {
			self::$oMenu = new Menu($oNails);
		}

		return self::$oMenu;
	}

	/**
	 * Menu::addMenu()
	 *
	 * @param string $cTitle
	 * @param string $cLink
	 * @param int $iParent
	 * @param int $iSort
	 * @param string $cPage
	 * @return bool
	 */
	public function addMenu($cTitle, $cLink, $iParent = false, $iSort = false, $cPage = false) {
		$cPage		= $cPage 	? $cPage 	: "";
		$iSort		= $iSort 	? $iSort 	: "0";
		$iParent	= $iParent	? $iParent	: "0";

		$aWrite	= array($cPage, $cTitle, $cLink, $iSort, $iParent);
		$this->oDB->write("INSERT INTO `menu` (cPage, cTitle, cLink, iSort, iParentID) VALUES (?, ?, ?, ?, ?)", $aWrite);

		return true;
	}

	/**
	 * Menu::getMenu()
	 *
	 * @param string $cPage
	 * @return array
	 */
	public function getMenu($cPage = false) {
		$cPage	= $cPage ? $cPage : " ";
		$aMenu	= false;
		$i		= 0;

		$this->oDB->read("SELECT cTitle, cLink, iSort, iMenuID, iChildren FROM menu WHERE cPage = '?' AND iParentID = 0 ORDER BY iSort DESC, iMenuID ASC", $cPage);
		while ($this->oDB->nextRecord()) {
			$aMenu[$i]['link']		= $this->oDB->f('cLink');
			$aMenu[$i]['title']		= $this->oDB->f('cTitle');
			$aMenu[$i]['sort']		= $this->oDB->f('iSort');

			if ($this->oDB->f('iChildren') > 0) { //this is to stop constant loops, ugleh i know
				$aMenu[$i]['children']	= $this->getSubs($this->oDB->f('iMenuID'), $cPage);
			}

			$i++;
		}

		return $aMenu;
	}

	/**
	 * Menu::getSubs()
	 *
	 * @param int $iParentID
	 * @param string $cPage
	 * @return array
	 */
	private function getSubs($iParentID, $cPage = false) {
		$cPage		= $cPage ? $cPage : " ";
		$aReturn	= array();
		$i			= 0;

		$aRead = array($cPage, $iParentID);
		$this->oDB->read("SELECT cTitle, cLink, iSort, iMenuID, iChildren FROM menu WHERE cPage = '?' AND iParentID = '?' ORDER BY iSort DESC, iMenuID ASC", $aRead);
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['link']		= $this->oDB->f('cLink');
			$aReturn[$i]['title']		= $this->oDB->f('cTitle');
			$aReturn[$i]['sort']		= $this->oDB->f('iSort');

			if ($this->oDB->f('iChildren')) {
				$aReturn[$i]['children']	= $this->getSubs($this->oDB->f('iMenuID'), $cPage);
			}

			$i++;
		}

		return $aReturn;
	}

	/**
	 * Menu::getBread()
	 *
	 * @return array
	 */
	public function getBread() {
		$aReturn	= array();
		$i			= 0;

		//Home
		$aReturn[$i]['title']	= "Home";
		$aReturn[$i]['link']	= "/";
		$i++;

		if ($this->cPage) { //Page
			$aReturn[$i]['title']	= ucfirst($this->cPage);
			$aReturn[$i]['link']	= "/" . $this->cPage;
			$i++;
		}

		if ($this->cAction) { //Action
			$aReturn[$i]['title']	= ucfirst($this->cAction);
			$aReturn[$i]['link']	= "/" . $this->cPage . "/" . $this->cAction;
			$i++;
		}

		if ($this->cChoice) { //Choice [A-Z]
			$aReturn[$i]['title']	= ucfirst($this->cChoice);
			$aReturn[$i]['link']	= "/" . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice;
			$i++;
		}

		if ($this->iItem) { //Item [0-9]
			$aReturn[$i]['title']	= $this->iItem;
			$aReturn[$i]['link']	= "/" . $this->cPage . "/" . $this->cAction . "/" . $this->iItem;
		}

		if ($this->iPage) { //Pagination
			$aReturn[$i]['title']	= "Page " . $this->iPage;

			if ($this->cChoice) {
				$aReturn[$i]['link']	= "/" . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/" . $this->iPage;
			} else if ($this->iItem) {
				$aReturn[$i]['link']	= "/" . $this->cPage . "/" . $this->cAction . "/" . $this->iItem . "/" . $this->iPage;
			} else if ($this->cAction) {
				$aReturn[$i]['link']	= "/" . $this->cPage . "/" . $this->cAction . "/" . $this->iPage;
			} else {
				$aReturn[$i]['link']	= "/" . $this->cPage . "/" . $this->iPage;
			}

			$i++;
		}

		return $aReturn;
	}
}