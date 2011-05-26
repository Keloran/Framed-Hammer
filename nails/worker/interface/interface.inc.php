<?php
/**
 * Worker_Interface
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
interface Worker_Interface {
	/**
	 * getOptions
	 *
	 * @desc This gets the options that the worker needs
	 * @return mixed
	 */
	function getOptions();

	/**
	 * getName()
	 *
	 * @desc Gets the name of the job, so that it can easily be passed back to the user
	 * @return string
	 */
	function getName();

	/**
	 * doTask()
	 *
	 * @desc Does the required task
	 * @return null
	 */
	function doTask();

	/**
	 * setStatus()
	 *
	 * @desc Sends the status of hte task back to the system, so that it
	 * can work out the total progress
	 * @param int $iStatus The current level, e.g. 5 = 5%
	 * @param string $cStatus The reason for the change, e.g. Changed to PDF
	 * @return null
	 */
	function setStatus($iStatus, $cStatus);
}