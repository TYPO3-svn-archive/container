<?php

/**
 * Value object containing the relevant informations for a class,
 * this object is build by the classInfoFactory - or could also be restored from a cache
 * 
 * @author Daniel Pötzinger
 */
class Tx_Container_Classinfo {
	/**
	 * The classname of the class where the infos belong to
	 * @var string
	 */
	private $className;
	
	/**
	 * The constructor Dependencies for the class in the format:
	 * 	 array( array('dependency' => <classname>,'defaultvalue' => <mixed>), ... )
	 * 
	 * @var array
	 */
	private $constructorDependencies;
	
	/**
	 * All setter injections in the format
	 * 	array (<nameOfMethod> => <classname> )
	 * 
	 * @var array
	 */
	private $setterDependencies;
	
	/**
	 * Flag that indicated if a method "injectExtensionSettings" exists in the class
	 * 
	 * @var boolean
	 */
	private $hasInjectExtensionSettingsMethod;
	
	/**
	 * the key of the extension where this class belongs to
	 * 
	 * @var string
	 */
	private $extensionKey;
	
	/**
	 * @var array
	 */
	private $parents;
	
	/**
	 * 
	 * @var boolean
	 */
	private $isSingleton;
	
	/**
	 * 
	 * @param string $className
	 * @param array $constructorDependencies
	 * @param array $setterDependencies
	 * @param boolean if the class has a method "injectExtensionSettings"
	 */
	public function __construct($className, $extensionKey, array $constructorDependencies, array $setterDependencies, $hasInjectExtensionSettingsMethod=FALSE, array $parents=array(), $isSingleton=FALSE) {
		$this->className = $className;
		$this->setterDependencies = $setterDependencies;
		$this->constructorDependencies = $constructorDependencies;
		$this->extensionKey = $extensionKey;
		$this->hasInjectExtensionSettingsMethod=$hasInjectExtensionSettingsMethod;
		$this->parents=$parents;
		$this->isSingleton = $isSingleton;
	}
	
	/**
	 * @return the $className
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @return the $constructorDependencies
	 */
	public function getConstructorDependencies() {
		return $this->constructorDependencies;
	}

	/**
	 * @return the $setterDependencies
	 */
	public function getSetterDependencies() {
		return $this->setterDependencies;
	}
	
	/**
	 * @return the $setterDependencies
	 */
	public function hasSetterDependencies() {
		return (count($this->setterDependencies) > 0);
	}
	
	/**
	 * @return the $extensionKey
	 */
	public function getExtensionKey() {
		return $this->extensionKey;
	}

	/**
	 * @return boolean
	 */
	public function hasInjectExtensionSettingsMethod() {
		return $this->hasInjectExtensionSettingsMethod;
	}

	/**
	 * @return the $parents
	 */
	public function getParents() {
		return $this->parents;
	}

	/**
	 * @return the $isSingleton
	 */
	public function getIsSingleton() {
		return $this->isSingleton;
	}
	
}