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
		$this->oDB	= $this->oHammer->getDatabase();
	}

	/**
	 * Template_Content::getStatic()
	 *
	 * @return string
	 */
	public function getStatic() {
		printRead($this->mParams);
	}
}