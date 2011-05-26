<<<<<<< HEAD
<?php
class Rest extends Nails {
	function __construct() {
		parent::__construct();

=======
<?php 
class Rest extends Nails {
	function __construct() {
		parent::__construct();
		
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
		//do the upgrade
		if ($this->checkVersion("rest", "1.0") == false) {
			//1.0
			$this->addVersion("rest", "1.0");
		}
	}
<<<<<<< HEAD

	function get($mRequest) {
	}

	function post($mRequest) {
	}

	function delete($mRequest) {
	}
=======
	
	function get($mRequest) {
	}
	
	function post($mRequest) {
	}
	
	function delete($mRequest) {
	}
	
	function 
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
}