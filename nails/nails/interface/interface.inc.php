<?php
/**
 * Nails_Interface
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id: abstract.inc.php 545 2010-02-25 11:11:39Z keloran $
 * @access public
 */
interface Nails_Interface {
	/**
	 * getInstance()
	 *
	 * @desc This gets an instance of the object
	 * @param object $oNails This must be a Nails object
	 * @return object
	 */
	static function getInstance(Nails $oNails);
}