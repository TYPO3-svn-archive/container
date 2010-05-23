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
	 * 
	 * @param string $className
	 * @param array $constructorDependencies
	 * @param array $setterDependencies
	 * @param boolean if the class has a method "injectExtensionSettings"
	 */
	public function __construct($className, $extensionKey, array $constructorDependencies, array $setterDependencies, $hasInjectExtensionSettingsMethod=FALSE) {
		$this->className = $className;
		$this->setterDependencies = $setterDependencies;
		$this->constructorDependencies = $constructorDependencies;
		$this->extensionKey = $extensionKey;
		$this->hasInjectExtensionSettingsMethod=$hasInjectExtensionSettingsMethod;
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

	
	
}