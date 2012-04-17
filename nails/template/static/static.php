<?php
/**
 * Template_Content
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2012
 * @version $Id$
 * @access public
 */
class Template_Static extends Template_Abstract {
	use Address;

	private $oDB;

	/**
	 * Template_Content::__construct()
	 *
	 * @param mixed $mParams
	 * @param string $cTemplate
	 */
	public function __construct($mParams, $cTemplate = null) {
		$this->setParams($mParams);

		if ($cTemplate) { $this->setTemplate($cTemplate); }

		//get Database
		if (is_object($this->oHammer)) {
			$this->oDB	= $this->oHammer->getDatabase();
		} else {
			$oHammer	= Hammer::getHammer();
			$this->oDB	= $oHammer->getDatabase();
		}
	}

	/**
	 * Template_Content::getStatic()
	 *
	 * @return string
	 */
	public function getStatic() {
		$cReturn	= false;

		if (isset($this->mParams['fullAddress']) && $this->mParams['fullAddress']) {
			$this->oDB->read("SELECT cPage FROM template_static WHERE cPath = ? LIMIT 1", $this->mParams['fullAddress']);
			if ($this->oDB->nextRecord()) { $cReturn = $this->oDB->f('cPage'); }
		}

		return $cReturn;
	}
}