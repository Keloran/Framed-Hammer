<?php
/**
 * Template_Interface
 *
 * @version $Id: interface.inc.php 969 2010-04-22 18:51:14Z keloran $
 * @author keloran
 */
interface Template_Interface {
	/**
	 * Template_Interface::setVars
	 *
	 * @desc This sets the variables
	 * @params string $cName
	 * @params mixed $mVars
	 * @return null
	 */
	public function setVars($cName, $mVars);

	/**
	 * Template_Interface::setParams
	 *
	 * @desc Sets the params
	 * @param mixed $mParams
	 * @return null
	 */
	public function setParams($mParams);

	/**
	 * Template_Interface::createTemplate
	 *
	 * @desc This creates teh template, usually all this does is add the hammer var
	 * @return null
	 */
	public function createTemplate();

	/**
	 * Template_Interface::renderTemplate
	 *
	 * @desc This renders the template
	 * @return string
	 */
	public function renderTemplate();
}