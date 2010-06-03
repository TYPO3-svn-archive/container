<?php

require_once (t3lib_extMgm::extPath ( 'container' ) . 'Classes/ClassInfo.php');

/**
 * TYPO3 Dependency Injection container
 * 
 * @author Daniel Pötzinger
 */
class Tx_Container_ClassInfoFactory {
	
	/**
	 * internal flag that is set during analyse of the class
	 * 
	 * @var unknown_type
	 */
	private $hasInjectExtensionSettingsMethod = FALSE;
	
	/**
	 * Factory metod that builds a ClassInfo Object for the given classname - using reflection
	 * 
	 * @param string $className
	 */
	public function buildClassInfoFromClassName($className) {
		try {
			$reflectedClass = new ReflectionClass ( $className );
		} catch ( Exception $e ) {
			throw new Exception ( 'Could not analyse class:' . $className . ' maybe not loaded or no autoloader?' );
		}
		$cd = $this->getConstructorDependencies ( $reflectedClass );
		$is = $this->getSetterDependencies ( $reflectedClass );
		$key = $this->getExtensionKey ( $className );
		$parents = $this->getParents ( $reflectedClass );
		$isSingleton = in_array ( 't3lib_Singleton', $parents );
		return new Tx_Container_ClassInfo ( $className, $key, $cd, $is, $this->hasInjectExtensionSettingsMethod, $parents, $isSingleton );
	}
	
	/**
	 * 
	 * @param ReflectionClass $reflectedClass
	 * @return array With classnames and interfaces of this Class
	 */
	private function getParents(ReflectionClass $reflectedClass) {
		$parents = $reflectedClass->getInterfaceNames ();		
		$parent = $reflectedClass->getParentClass ();
		if ($parent) {
			$parents[] = $parent->getName();
		}
		return $parents;
	}
	
	/**
	 * detects the extension key from classname
	 * @return string
	 */
	private function getExtensionKey($className) {
		list ( $first, $key, $last ) = explode ( '_', $className, 3 );
		return $this->convertCamelCaseToLowerCaseUnderscored ( $key );
	}
	/**
	 * 
	 * @param string $string
	 */
	private static function convertCamelCaseToLowerCaseUnderscored($string) {
		static $conversionMap = array ();
		if (! isset ( $conversionMap [$string] )) {
			$conversionMap [$string] = strtolower ( preg_replace ( '/(?<=\w)([A-Z])/', '_\\1', $string ) );
		}
		return $conversionMap [$string];
	}
	
	/*
	 * @param ReflectionClass $reflectedClass
	 * @returns array of parameter infos for constructor  k=>dependency,defaultvalue
	 */
	private function getConstructorDependencies(ReflectionClass $reflectedClass) {
		$reflectionMethod = $reflectedClass->getConstructor ();
		if (! is_object ( $reflectionMethod )) {
			return array ();
		}
		$result = array ();
		foreach ( $reflectionMethod->getParameters () as $k => $reflectionParameter ) {
			/* @var $reflectionParameter ReflectionParameter */
			$info = array ();
			if ($reflectionParameter->getClass ()) {
				$info ['dependency'] = $reflectionParameter->getClass ()->getName ();
			}
			if ($reflectionParameter->isOptional ()) {
				$info ['defaultValue'] = $reflectionParameter->getDefaultValue ();
			}
			$result [$k] = $info;
		}
		return $result;
	}
	
	/**
	 * returns array ( methodName => dependency ) and 
	 * uses a side effect to set $this->hasInjectExtensionSettingsMethod = TRUE;
	 * @param ReflectionClass $reflectedClass
	 */
	private function getSetterDependencies(ReflectionClass $reflectedClass) {
		$this->hasInjectExtensionSettingsMethod = FALSE;
		$result = array ();
		$reflectionMethods = $reflectedClass->getMethods ();
		if (is_array ( $reflectionMethods )) {
			foreach ( $reflectionMethods as $reflectionMethod ) {
				if ($reflectionMethod->isPublic () && $this->isInjectSetter ( $reflectionMethod )) {
					$reflectionParameter = $reflectionMethod->getParameters ();
					if ($reflectionMethod->getName () == 'injectExtensionSettings') {
						$this->hasInjectExtensionSettingsMethod = TRUE;
					} elseif (isset ( $reflectionParameter [0] )) {
						if (! $reflectionParameter [0]->getClass ()) {
							throw new Exception ( 'Method is marked as setter for Dependency Injection, but doesnt have a type annotation' );
						}
						$result [$reflectionMethod->getName ()] = $reflectionParameter [0]->getClass ()->getName ();
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * 
	 * @param ReflectionParameter $reflectionMethod
	 */
	private function isInjectSetter(ReflectionMethod $reflectionMethod) {
		if (substr ( $reflectionMethod->getName (), 0, 6 ) == 'inject') {
			return true;
		}
		return strpos ( $reflectionMethod->getDocComment (), '@inject' );
	}
}