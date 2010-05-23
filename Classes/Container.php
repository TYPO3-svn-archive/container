<?php

require_once ( t3lib_extMgm::extPath('container') . 'Classes/ClassInfoFactory.php');

/**
 * TYPO3 Dependency Injection container
 * 
 * @author Daniel Pötzinger
 */
class Tx_Container_Container {
	
	/**
	 * PHP singleton impelementation
	 * 
	 * @var tx_container_container
	 */
	static private $containerInstance = null; 
	
	/**
	 * Returns an instance of the container singleton.
	 * 
	 * @return Tx_Container_Container
	 */
    static public function getContainer() {
         if (null === self::$containerInstance) {
             self::$containerInstance = new self;
         }
         return self::$containerInstance;
     } 
     
     /**
      * internal cache for classinfos
      * 
      * @var array
      */
     private $level1ClassInfoCache=array();
     
     /**
      * registered alternative implementations of a class
      * e.g. used to know the class for a AbstractClass or a Dependency
      * 
      * @var array
      */
     private $alternativeImplementation;
     
     /**
      * reference to the classinfofactory, that analyses dependencys
      * @var classInfoFactory
      */
     private $classInfoFactory;
     
     /**
	 * holds references of singletons
	 * @var array
	 */ 
	private $instances = array();
     
	
	/**
	 * Constructor is protected since container should
	 * be a singleton. 
	 * 
	 * @see getContainer()
	 * @param void
	 * @return void
	 */
    protected function __construct() {
     	$this->classInfoFactory = new tx_container_classinfofactory();
    }
     
    private function __clone() {}
		
	/**
	 * gets an instance of the given class
	 * @param string $className
	 * @return object
	 */
	public function getInstance($className) {	
		$givenConstructorArguments=array();
		if (func_num_args() > 1) {
				$givenConstructorArguments = func_get_args();
				array_shift($givenConstructorArguments);
		}	

		return $this->getInstanceFromClassName($className,  $givenConstructorArguments, 0);		
	}
	
	/**
	 * register a classname that should be used if a dependency is required.
	 * e.g. used to define default class for a interface
	 * 
	 * @param string $className
	 * @param string $alternativeClassName
	 */
	public function registerImplementation($className,$alternativeClassName) {
		$this->alternativeImplementation[$className] = $alternativeClassName;
	}
	
	
	/**
	 * gets an instance of the given class
	 * @param string $className
	 * @param array $givenConstructorArguments
	 */
	protected function getInstanceFromClassName($className, array $givenConstructorArguments=array(), $level=0) {
		if ($level > 30) {
			throw new Exception('level too big - cyclomatic dependency? '.$className);
		}
			// Get final classname
		$className = self::getClassName($className);
		
		if ($className == 'Tx_Container_Container') {
			return $this;
		}
		
		if (isset($this->instances[$className])) {
			return $this->instances[$className]; // it's a singleton, get the existing instance
		} 
	
		$requiredConstructorArguments = $this->getClassInfo($className)->getConstructorDependencies();	
		$constructorArguments = $this->getConstructorArguments($requiredConstructorArguments, $givenConstructorArguments,$level);
		$instance = $this->newObject($className, $constructorArguments);			
		$this->handleSetterInjection($instance, $this->getClassInfo($className)->getSetterDependencies());
		if ($this->getClassInfo($className)->hasInjectExtensionSettingsMethod() && $this->getClassInfo($className)->getExtensionKey()) {
			$instance->injectExtensionSettings($this->getExtensionSettings($this->getClassInfo($className)->getExtensionKey()));
		}
		
		if ($instance instanceof t3lib_Singleton) {
					// it's a singleton, save the instance for later reuse
				$this->instances[$className] = $instance;
		}	
		return $instance;		
	}
	
	/**
	 * returns the extension settings from ext_conf_template
	 * 
	 * @param $key
	 */
	private function getExtensionSettings($key) {
		$array = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$key]);
		if (!is_array($array)) {
			return array();
		}
		return $array;
	}
	
	/**
	 * TODO add real cyclic detecting based on call tree
	 *  a 
	 *  --b
	 *  --c
	 *  	--a //cyclic 
	 *  	--b //non cyclic
	 */
	
	/**
	 * returns a object of the given type, called with the constructor arguments.
	 * For speed improvements reflection is avoided
	 * 
	 * @param string $className
	 * @param array $constructorArguments
	 */
	protected function newObject($className, array $constructorArguments) {
		switch (count($constructorArguments)) {
			case 0:
				return new $className;
			break;
			case 1:
				return new $className($constructorArguments[0]);
			break;
			case 2:
				return new $className($constructorArguments[0], $constructorArguments[1]);
			break;
			case 3:
				return new $className($constructorArguments[0], $constructorArguments[1], $constructorArguments[2]);
			break;
			default:
				$reflectedClass = new ReflectionClass($className);		
				return $reflectedClass->newInstanceArgs($constructorArguments);
			break;
		}
	}
	
	/**
	 * gets array of parameter that can be used to call a constructor
	 * 
	 * @param ReflectionParameter[] $requiredConstructorArguments
	 * @param array $givenConstructorArguments
	 * @return array
	 */
	protected function getConstructorArguments(array $requiredConstructorArgumentsInfos, array $givenConstructorArguments, $level) {
		$parameters=array();
		
		foreach ($requiredConstructorArgumentsInfos as $k => $info) {
			
			if (isset($givenConstructorArguments[$k]) && !is_null($givenConstructorArguments[$k])) {
				$parameter = $givenConstructorArguments[$k];
			} 
			elseif (isset($info['defaultValue'])) {
				$parameter = $info['defaultValue'];
			}
			elseif (isset($info['dependency'])) {
				$parameter = $this->getInstanceFromClassName($info['dependency'], array(), $level+1);
			}		
			else {
				throw new InvalidArgumentException('not a correct info array of constructor dependencies was passed!');
			}	
			$parameters[] = $parameter;			
		}	
		return 	$parameters;
	}
	
	
	/**
	 * Returns the class name for a new instance, taking into account the
	 * class-extension API.
	 *
	 * @param	string		Base class name to evaluate
	 * @return	string		Final class name to instantiate with "new [classname]"
	 */
	protected function getClassName($className) {
		if (isset($this->alternativeImplementation[$className])) {
			$className = $this->alternativeImplementation[$className];
		}
		return $this->getClassNameXClass($className);
	}
	
	/**
	 * Returns the class name for a new instance, taking into account the
	 * class-extension API.
	 *
	 * @param	string		Base class name to evaluate
	 * @return	string		Final class name to instantiate with "new [classname]"
	 */
	protected function getClassNameXClass($className) {
		return (class_exists($className) && class_exists('ux_' . $className, false) ? self::getClassName('ux_' . $className) : $className);
	}
	
	/**
	 * does inject dependecies in the given methods
	 * 
	 * @param object $instance
	 * @param array $setterMethods
	 */
	protected function handleSetterInjection($instance, array $setterMethods) {
		foreach ($setterMethods as $method => $dependency) {
			$instance-> $method ( $this->getInstanceFromClassName($dependency));
		}
	}
	
	/**
	 * TODO - Level2 (database or filesystem based) cache + cache warmup + cache clear on cache clear in backend
	 * 
	 * @param string $className
	 * @return tx_container_reflectioninfo
	 */
	protected function getClassInfo($className) {
		if (!isset($this->level1ClassInfoCache[$className])) {
			$this->level1ClassInfoCache[$className]= $this->classInfoFactory->buildClassInfoFromClassName($className);
		}
		return $this->level1ClassInfoCache[$className];	
	}
}