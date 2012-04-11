<?php
/**
 * Form_Interface
 *
 * @package Form
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
interface Form_Interface {
	/**
	 * Form_Interface::addElement()
	 *
	 * @desc This adds the element to the element array
	 * @param string $cType This sets the type of element, e.g. type="textarea"
	 * @return null
	 */
	public function addElement($cType);

	/**
	 * Form_Interface::setElementClass()
	 *
	 * @desc This sets the elements class class="class"
	 * @param string $cClass
	 * @return null
	 */
	public function setClass($cClass);

	/**
	 * Form_Interface::setID()
	 *
	 * @desc This sets the ID for the element, it overwrites the default of the Name
	 * @param string $cID
	 * @return null
	 */
	public function setID($cID);

	/**
	 * Form_Interface::setError()
	 *
	 * @desc This sets the error of the element, e.g. <error> or error="error"
	 * @param string $cError
	 * @return null
	 */
	public function setError($cError = null);

	/**
	 * Form_Interface::setPlaceHolder()
	 *
	 * @desc This sets the placeholder for the element placeholder="placeholder"
	 * @param string $cPlaceHolder
	 * @return null
	 */
	public function setPlaceHolder($cPlaceHolder);

	/**
	* Form_Interface::setLabel()
	*
	* @desc This sets the label of the element by default its the name of the element
	* @param string $cLabel
	* @return null
	*/
	public function setLabel($cLabel);

	/**
	 * Form_Interface::setValue()
	 *
	 * @desc This sets the value for the element
	 * @param string $cValue
	 * @return null
	 */
	public function setValue($cValue);
}