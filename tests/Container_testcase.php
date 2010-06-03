<?php

include_once(dirname(__FILE__).'/TestClasses.php');


/**
 * TYPO3 Dependency Injection container tests
 * 
 * @author Daniel Pötzinger
 */
class Tx_Container_Tests_Container_testcase extends tx_phpunit_testcase {
	
	private $container;
	
	public function setUp() {
		$this->container = Tx_Container_Container::getContainer();
		
	}
	
	public function test_canGetSimpleClass() {
		$o = $this->container->getInstance('tx_container_tests_c');		
		$this->assertType('tx_container_tests_c', $o);
	}
	
	public function test_canGetClassWithaDependency() {
		$o = $this->container->getInstance('tx_container_tests_b');
		$this->assertType('tx_container_tests_b', $o);
	}
	
	public function test_canGetClassWithTwoLevelDependency() {
		$o = $this->container->getInstance('tx_container_tests_a');
		$this->assertType('tx_container_tests_a', $o);
	}
	
	public function test_canGetClassWithTwoLevelMixedArrayDependency() {
		$o = $this->container->getInstance('tx_container_tests_amixed_array');
		$this->assertType('tx_container_tests_amixed_array', $o);
	}
	
	public function test_canGetClassWithTwoLevelMixedStringDependency() {
		$o = $this->container->getInstance('tx_container_tests_amixed_string');
		$this->assertType('tx_container_tests_amixed_string', $o);
	}
	
	public function test_canGetClassWithGivenParameters() {
		$mock = $this->getMock('tx_container_tests_c');
		
		$o = $this->container->getInstance('tx_container_tests_a', $mock);
		$this->assertType('tx_container_tests_a', $o);
		$this->assertSame($mock, $o->c);
		
	}
	
	public function test_canGetSingleton() {
		
		$o1 = $this->container->getInstance('tx_container_tests_singleton');
		$o2 = $this->container->getInstance('tx_container_tests_singleton');
		
		$this->assertSame($o1, $o2);
		
	}

	/**
     * @expectedException Exception
     */
	public function test_canDetectCyclicDependency() {
		$o = $this->container->getInstance('tx_container_tests_cyclic1');
		
	}
	
	/**
     * @expectedException Exception
     */
	public function test_canThrowExcpetionIfNotKnown() {
		$o = $this->container->getInstance('nonextistingclass_bla');
		
	}
	
	/**
     * 
     */
	public function test_canGetChildClass() {
		$o = $this->container->getInstance('tx_container_tests_b_child');
		$this->assertType('tx_container_tests_b_child', $o);
	}
	
	/**
     * 
     */
	public function test_canInjectSetterInClass() {
		$o = $this->container->getInstance('tx_container_tests_injectmethods');
		$this->assertType('tx_container_tests_injectmethods', $o);
		$this->assertType('tx_container_tests_b', $o->b);
		$this->assertType('tx_container_tests_b_child', $o->bchild);
	}
	
	/**
     * 
     */
	public function test_canInjectInterfaceInClass() {
		$this->container->registerImplementation('tx_container_tests_someinterface', 'tx_container_tests_someimplementation');
		$o = $this->container->getInstance('tx_container_tests_needsinterface');
		$this->assertType('tx_container_tests_needsinterface', $o);		
	}
	/**
     * 
     */
	public function test_canInjectExtensionSettingsInClass() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['container'];
		$fixture = array('mytest'=> 'foo');
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['container']=serialize($fixture);
		$o = $this->container->getInstance('tx_container_tests_injectsettings');
		$this->assertEquals($fixture, $o->settings);	
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['container']=$backup;	
	}
	
	public function test_canBuildCyclicDependenciesWithSetter() {
		$o = $this->container->getInstance('tx_container_tests_resolveablecyclic1');	
		$this->assertType('tx_container_tests_resolveablecyclic1', $o);	
		$this->assertType('tx_container_tests_resolveablecyclic1', $o->o->o);		
	}
	
	
	
}


?>