<?php
/**
 * Nails
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: nails.php 133 2009-10-28 13:00:07Z keloran $
 * @access public
 */
class Nails extends Hammer {
	static $aTables;
	static $aVersions;
	static $oNails;
	static $aVersionsAgain;

	public $oDB;
	public $cConfigDatabase;
	public $cIP;
	public $aFilterDebug;

	private $bVersions	= false;
	private static $oXML;

	/**
	 * Nails::__construct()
	 *
	 */
	public function __construct($aFilter = null) {
		parent::__construct();

		//Get the XML
		if (is_null(self::$oXML)) { self::$oXML = new XML(); }

		//get the database object
		if (is_null($this->oDB)) { $this->oDB = $this->getDatabase(); }

		//set the address, this is needed in nails, becasue it sometimes gets lost
		$this->setAddress($aFilter);
		$this->aFilterDebug = $aFilter;

		//This is for the new method of getting versions to work the new way
		if (file_exists(SITEPATH . "/versions.xml")) { $this->bVersions	= true; }
	}

	/**
	 * Nails::checkInstalled()
	 *
	 * @desc This checks to see if the library has been installed
	 * @param string $cNailTable
	 * @param string $cNail this is for usage with the XML method
	 * @return bool
	 */
	public function checkInstalled($cNailTable) {
		$bReturn	= false;

		//XML installer
		$bXML	= $this->checkXML($cNailTable);
		if ($bXML) { $bReturn = true; }

		return $bReturn;
	}

	/**
	 * Nails::checkXML()
	 *
	 * @param string $cNail
	 * @return bool
	 */
	private function checkXML($cNail) {
		if (is_null(self::$oXML)) { self::$oXML	= new XML(); }
		$bXML			= false;

		$oXML			= self::$oXML;
		$oXML->cRoot	= "install";
		$oXML->setFile("installed");
		$bXML			= $oXML->getElement($cNail);

		return $bXML;
	}

	/**
	 * Nails::addTable()
	 *
	 * @desc This inserts the table has to be done with standard SQL
	 * @param string $cSQL
	 * @return null
	 */
	public function addTable($cSQL) {
		$this->oDB->write("SET UNIQUE_CHECKS=0, FOREIGN_KEY_CHECKS=0");
			$this->oDB->write($cSQL);
		$this->oDB->write("SET UNIQUE_CHECKS=1, FOREIGN_KEY_CHECKS=1");
	}

	/**
	 * Nails::modifyTable()
	 *
	 * @desc This alters the table must be done with standard SQL
	 * @param string $cSQL
	 * @todo Same as addTable atm, might change later
	 * @return null
	 */
	public function modifyTable($cSQL) {
		$this->oDB->write($cSQL);
	}

	/**
	 * Nails::groupsInstalled()
	 *
	 * @desc This checks to see if the groups table exists
	 * @return bool
	 */
	public function groupsInstalled() {
		$bReturn	= false;
		$this->oDB->read("SELECT iGroupID FROM users_groups WHERE cGroup = 'Banned' LIMIT 1");
		if ($this->oDB->nextRecord()) {
			$bReturn = true;
		}

		return $bReturn;
	}

	/**
	 * Nails::addGroups()
	 *
	 * @desc Add a group to the database
	 * @param mixed $mGroups
	 * @return null
	 */
	public function addGroups($mGroups) {
		$aGroups	= false;
		$bModify	= false;

		$this->oDB->read("SHOW INDEX FROM users_groups");
		while ($this->oDB->nextRecord()) {
			$aGroups[] = $this->oDB->f("Column_name");
		}

		if (is_array($mGroups)) {
			$iGroups	= count($mGroups);

			for ($i = 0; $i < $iGroups; $i++) {
				$cGroup	= "b" . ucfirst($mGroups[$i]);

				if (in_array($cGroup, $aGroups) == false) {
					$this->modifyTable("ALTER TABLE `users_groups` ADD `" . $cGroup . "` BOOL NOT NULL");
					$this->modifyTable("ALTER TABLE `users_groups` ADD INDEX (`" . $cGroup . "`)");

					$bModify	= true;
				}
			}
		} else {
			$cGroup	= "b" . ucfirst($mGroups);

			if (in_array($cGroup, $aGroups) == false) {
				$this->modifyTable("ALTER TABLE `users_groups` ADD `" . $cGroup . "` BOOL NOT NULL");
				$this->modifyTable("ALTER TABLE `users_groups` ADD INDEX (`" . $cGroup . "`)");

				$bModify	= true;
			}
		}

		if ($bModify) {
			$this->oDB->write("OPTIMIZE TABLE `users_groups`");
		}
	}

	/**
	 * Nails::createGroup()
	 *
	 * @desc Create a group
	 * @param string $cGroup
	 * @return null
	 */
	public function createGroup($cGroup) {
		$this->oDB->write("INSERT INTO `users_groups` (cGroup) VALUES (?)", ucfirst($cGroup));
	}

	/**
	 * Nails::addIndexs()
	 *
	 * @desc Adds an index
	 * @param string $cTable
	 * @param mixed $mIndex
	 * @return null
	 */
	public function addIndexs($cTable, $mIndex) {
		$aIndex		= false;
		$bModified	= false;
		$i			= 0;

		$this->oDB->read("SHOW INDEX FROM " . $cTable);
		while ($this->oDB->nextRecord()) {
			$aIndex[$i]	= $this->oDB->f('Column_name');
			$i++;
		}

		if (is_array($mIndex)) {
			$iIndexs = count($mIndex);

			for ($i = 0; $i < $iIndexs; $i++) {
				if (in_array($mIndex[$i], $aIndex) == false) {
					$this->modifyTable("ALTER TABLE `" . $cTable . "` ADD INDEX (`" . $mIndex[$i] . "`)");
					$bModified	= true;
				}
			}
		} else {
			if (in_array($mIndex, $aIndex) == false) {
				$this->modifyTable("ALTER TABLE `" . $cTable . "` ADD INDEX (`" . $mIndex . "`)");
				$bModified	= true;
			}
		}

		if ($bModified) {
			$this->oDB->write("OPTIMIZE TABLE `" . $cTable . "`");
		}
	}

	/**
	 * Nails::addAbility()
	 *
	 * @desc Add an ability to a group, e.g. forums added so your users/admins need abilitys
	 * @param string $cGroup
	 * @param mixed $mAbility
	 * @return null
	 */
	public function addAbility($cGroup, $mAbility) {
		//since its not always a capital at front of request, but is always in DB
		$cGroup = ucfirst($cGroup);

		if (is_array($mAbility)) {
			$iAbility	= count($mAbility);

			for ($i = 0; $i < $iAbility; $i++) {
				$bAbility = "b" . ucfirst($mAbility[$i]);
                $aEscape = array($cGroup);

				$this->oDB->write("UPDATE `users_groups` SET " . $bAbility . " = 1 WHERE cGroup LIKE (?) LIMIT 1", $aEscape);
			}
		} else {
			$bAbility = "b" . ucfirst($mAbility);
            $aEscape = array($cGroup);

            $this->oDB->write("UPDATE `users_groups` SET " . $bAbility . " = 1 WHERE cGroup LIKE (?) LIMIT 1", $aEscape);
		}

		$this->oDB->write("OPTIMIZE TABLE `user_groups`");
	}

	/**
	 * Nails::checkXMLVersion()
	 *
	 * @param string $cLibrary
	 * @param string $cVersion
	 * @return bool
	 */
	private function checkXMLVersion($cLibrary, $cVersion) {
		if (is_null(self::$oXML)) { self::$oXML = new XML(); }
		$bReturn	= false;

		$oXML			= self::$oXML;
		$oXML->setFile("installed");
		$oXML->cRoot	= "install";
		$aLibrary		= $oXML->getElement("version", $cLibrary);

		if ($aLibrary) {
			if (isset($aLibrary['version'])) {
				$cOldVersion	= $aLibrary['version'];
				if ($cOldVersion >= $cVersion) {
					$bReturn	= true;
				}
			}
		}

		return $bReturn;
	}

	/**
	* Nails::updateVersion()
	*
	* @desc Update the verson to the new version
	* @param string	$cLibrary	The library name
	* @param string	$cVersion	The Version number
	* @param mixed	$mSQL		The SQL to do the update, if there is any (this might move to the 4th param)
	* @param string	$cChangelog	The changelog for this version of the library
	* @return false
	*/
	public function updateVersion($cLibrary, $cVersion, $mSQL = false, $cChangelog = false) {
		if ($this->checkVersion($cLibrary, $cVersion) == false) {
			//update the table with the stuff you want todo, it might not actually have a database update, just a version update
			if ($mSQL) {
				if (is_array($mSQL)) {
					krsort($mSQL);
					foreach ($mSQL as $cSQL) {
						$this->oDB->write($cSQL);
					}
				} else {
					$this->oDB->write($mSQL);
				}
			}

			//do the update
			return $this->updateXML($cLibrary, $cVersion, $cChangelog);
		}

		return false;
	}

	/**
	 * Nails::updateXML()
	 *
	 * @param string $cLibrary
	 * @param string $cVersion
	 * @param string $cChangelog
	 * @return true
	 */
	private function updateXML($cLibrary, $cVersion, $cChangelog = null) {
		if (is_null(self::$oXML)) { self::$oXML = new XML(); }

		$oXML			= self::$oXML;
		$oXML->cRoot	= "install";
		$oXML->setFile("installed");
		$oXML->updateElement("version", $cVersion, $cLibrary);

		//add the changelog
		if ($cChangelog) { $oXML->updateElement("changelog", $cChangelog, $cLibrary); }
		return true;
	}

	/**
	*
	* @desc Adds the version to the database
	* @param string $cLibrary
	* @param string $cVersion
	* @return false
	*/
	public function addVersion($cLibrary, $cVersion) {
		if ($this->checkXMLVersion($cLibrary, $cVersion) == false) {
			$this->addXML($cLibrary, $cVersion);
			return true;
		}

		return false;
	}

	/**
	 * Nails::addXML()
	 *
	 * @param string $cLibrary
	 * @param string $cVersion
	 * @return null
	 */
	private function addXML($cLibrary, $cVersion) {
		if (is_null(self::$oXML)) { self::$oXML = new XML(); }

		$oXML			= self::$oXML;
		$oXML->cRoot	= "install";
		$oXML->setFile("installed");

		$oXML->addElement("version", $cVersion, $cLibrary);
		$oXML->addElement("changelog", "Inital", $cLibrary);
	}

	/**
	 * Nails::insertRecord()
	 *
	 * @param string $cSQL
	 * @param array $aParams
	 * @return null
	 */
	public function insertRecord($cSQL, $aParams) {
		$this->oDB->write($cSQL, $aParams);
	}

	/**
	 * Nails::addTableRefence()
	 *
	 * @param string $cTable
	 * @return null
	 */
	public function addTableRefence($cTable) {
		self::$aTables[] = $cTable;
	}
}
