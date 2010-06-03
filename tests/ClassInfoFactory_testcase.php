<?php

include_once(dirname(__FILE__).'/TestClasses.php');


/**
 * TYPO3 Dependency Injection container tests
 * 
 * @author Daniel Pötzinger
 */
class Tx_Container_Tests_ClassInfoFactory_testcase extends tx_phpunit_testcase {
	
	/**
	 * 
	 * @var Tx_Container_ClassInfoFactory
	 */
	private $ClassInfoFactory;
	
	/**
	 * 
	 */
	public function setUp() {
		$this->ClassInfoFactory = new Tx_Container_ClassInfoFactory();		
	}
	
	/**
	 * @test
	 */
	public function canDetectSingleton() {
		$classInfo = $this->ClassInfoFactory->buildClassInfoFromClassName('tx_container_tests_singleton');
		$this->assertTrue($classInfo->getIsSingleton());
	}
	
	/**
	 * @test
	 */
	public function canDetectInterface() {
		$classInfo = $this->ClassInfoFactory->buildClassInfoFromClassName('tx_container_tests_someimplementation');
		$this->assertContains('tx_container_tests_someinterface', $classInfo->getParents());
		
		$classInfo = $this->ClassInfoFactory->buildClassInfoFromClassName('tx_container_tests_c');
		$this->assertEquals(array(), $classInfo->getParents());
	}
	
	/**
	 * @test
	 */
	public function canDetectInheritance() {
		$classInfo = $this->ClassInfoFactory->buildClassInfoFromClassName('tx_container_tests_b_child');
		$this->assertContains('tx_container_tests_b', $classInfo->getParents());
		
		$classInfo = $this->ClassInfoFactory->buildClassInfoFromClassName('tx_container_tests_b_child_someimplementation');
		$this->assertContains('tx_container_tests_b', $classInfo->getParents());
		$this->assertContains('tx_container_tests_someinterface', $classInfo->getParents());
		
	}
	
	
	
	
}


?>