<?php
/**
 * Database_Interface
 *
 * @package Database
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
interface Database_Interface {
	/**
	 * completeQuery()
	 *
	 * @desc This takes the query that has ? or ?<num> and turns it into valid SQL
	 * @param string $cSQL The query with its ?
	 * @param mixed $mEscape this can be either a string or an array
	 * @return string
	 */
	function completeQuery($cSQL, $mEscape);

	/**
	 * clean
	 *
	 * @desc This strips anything that we dont want from the query, e.g. '3 OR 2'
	 * @param string $cInput e.g '3 OR 2'
	 * @return string
	 */
	function clean($cInput);

	/**
	 * insertID()
	 *
	 * @desc This returns the insert ID {pretty obvious really}
	 */
	function insertID();

	/**
	 * nextRecord()
	 *
	 * @desc This returns the next record in the result set, can be used with if/while
	 */
	function nextRecord();

	/**
	 * queryWrite()
	 *
	 * @desc This uses the completed query and passes it to the db
	 * @param string $cSQL
	 * @param mixed $mEscape
	 * @return null
	 */
	function queryWrite($cSQL, $mEscape = false);

	/**
	 * queryRead()
	 *
	 * @desc This uses the completed query and passes it to the db
	 * @param string $cSQL
	 * @param mixed $mEscape
	 * @return null
	 */
	function queryRead($cSQL, $mEscape = false);
}