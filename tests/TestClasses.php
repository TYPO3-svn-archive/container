<?php

/**
 * a  singleton class
 *
 */
class tx_container_tests_singleton implements t3lib_Singleton {
	
}

/**
 * test class A that depends on B and C
 *
 */
class tx_container_tests_a {
	public $b;
	public $c;
	
	public function __construct( tx_container_tests_c $c, tx_container_tests_b $b) {
		$this->b = $b;
		$this->c = $c;
	}	
}
/**
 * test class A that depends on B and C and has a third default parameter in constructor
 *
 */
class tx_container_tests_amixed_array {
	public function __construct(tx_container_tests_b $b, tx_container_tests_c $c, array $myvalue=array()) {
		
	}	
}
/**
 * test class A that depends on B and C and has a third default parameter in constructor
 *
 */
class tx_container_tests_amixed_string {
	public function __construct(tx_container_tests_b $b, tx_container_tests_c $c, $myvalue='test') {
		
	}	
}
/**
 * test class B that depends on C 
 *
 */
class tx_container_tests_b {
	public function __construct(tx_container_tests_c $c) {
		
	}	
}


/**
 * test class C without dependencys
 *
 */
class tx_container_tests_c {
		
}

/**
 * test class B-Child that extends Class B (therfore depends also on Class C)
 *
 */
class tx_container_tests_b_child extends tx_container_tests_b {	
}

interface tx_container_tests_someinterface {
	
}

/**
 * class which implements a Interface
 *
 */
class tx_container_tests_someimplementation implements tx_container_tests_someinterface {	
}

/**
 * test class B-Child that extends Class B (therfore depends also on Class C)
 *
 */
class tx_container_tests_b_child_someimplementation extends tx_container_tests_b implements tx_container_tests_someinterface {	
}

/**
 * class which depends on a Interface
 *
 */
class tx_container_tests_needsinterface {
	public function __construct(tx_container_tests_someinterface $i) {
		
	}	
}

/**
 * classes that depends on each other (death look)
 *
 */
class tx_container_tests_cyclic1 {
	public function __construct(tx_container_tests_cyclic2 $c) {
		
	}		
}

class tx_container_tests_cyclic2 {
	public function __construct(tx_container_tests_cyclic1 $c) {
		
	}		
}

/**
 * class which has setter injections defined
 *
 */
class tx_container_tests_injectmethods {
	public $b;
	public $bchild;
	
	public function injectClassB(tx_container_tests_b $o) {
		$this->b = $o;
	}
	
	/**
	 * @inject
	 * @param tx_container_tests_b $o
	 */
	public function setClassBChild(tx_container_tests_b_child $o) {
		$this->bchild = $o;
	}
}

/**
 * class which needs extenson settings injected
 *
 */
class tx_container_tests_injectsettings {
	public $settings;
	public function injectExtensionSettings(array $settings) {
		$this->settings = $settings;
	}		
}

/**
 * 
 *
 */
class tx_container_tests_resolveablecyclic1 implements t3lib_Singleton {
	public $o;
	public function __construct(tx_container_tests_resolveablecyclic2 $cyclic2) {
		$this->o = $cyclic2;
	}			
}

/**
 * 
 *
 */
class tx_container_tests_resolveablecyclic2 {
	public $o;
	public function injectCyclic1(tx_container_tests_resolveablecyclic1 $o) {
		$this->o = $o;
	}		
}


