<?php

require_once ( t3lib_extMgm::extPath('container') . 'Classes/ClassInfoFactory.php');

/**
 * TYPO3 Dependency Injection container
 * Initial Usage:
 *  $container = Tx_Container_Container::getContainer()
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
      * internal cache for classinfos
      * 
      * @var Tx_Container_ClassInfoCache
      */
     private $cache;
     
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
	private $singletonInstances = array();
	
	/**
	 * holds references of objects that still needs setter injection processing
	 * @var array
	 */ 
	private $setterInjectionRegistry = array();
	
	
	/**
	 * Constructor is protected since container should
	 * be a singleton. 
	 * 
	 * @see getContainer()
	 * @param void
	 * @return void
	 */
    protected function __construct() {
     	$this->classInfoFactory = new Tx_Container_ClassInfoFactory();
     	$this->cache = new Tx_Container_ClassInfoCache();
    }
    
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
		$object = $this->getInstanceFromClassName($className,  $givenConstructorArguments, 0);
		$this->processSetterInjectionRegistry();
		return $object;					
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
	private function getInstanceFromClassName($className, array $givenConstructorArguments=array(), $level=0) {
		if ($level > 30) {
			throw new Exception('level too big - cyclomatic dependency? '.$className);
		}		
		if ($className == 'Tx_Container_Container') {
			return $this;
		}		
		if (isset($this->singletonInstances[$className])) {
			return $this->singletonInstances[$className];
		}
		
		$className = self::getClassName($className);
		$classInfo = $this->getClassInfo($className);
		
		$requiredConstructorArguments = $classInfo->getConstructorDependencies();
		$constructorArguments = $this->getConstructorArguments($requiredConstructorArguments, $givenConstructorArguments,$level);
		$instance = $this->newObject($className, $constructorArguments);	
		if ($classInfo->hasSetterDependencies()) {
			$this->setterInjectionRegistry[]=array($instance, $classInfo->getSetterDependencies(), $level);
		}		
		if ($classInfo->hasInjectExtensionSettingsMethod() && $classInfo->getExtensionKey()) {
			$instance->injectExtensionSettings($this->getExtensionSettings($classInfo->getExtensionKey()));
		}
		if ($classInfo->getIsSingleton()) {					
				$this->singletonInstances[$className] = $instance;
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
	 * returns a object of the given type, called with the constructor arguments.
	 * For speed improvements reflection is avoided
	 * 
	 * @param string $className
	 * @param array $constructorArguments
	 */
	private function newObject($className, array $constructorArguments) {
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
	private function getConstructorArguments(array $requiredConstructorArgumentsInfos, array $givenConstructorArguments, $level) {
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
		
		if (substr($className, -9) === 'Interface') {
			$className = substr($className, 0, -9);
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
	private function getClassNameXClass($className) {
		return (class_exists($className) && class_exists('ux_' . $className, false) ? self::getClassName('ux_' . $className) : $className);
	}
	
	/**
	 * do inject dependecies to object $instance using the given methods
	 * 
	 * @param object $instance
	 * @param array $setterMethods
	 * @param integer $level
	 */
	private function handleSetterInjection($instance, array $setterMethods, $level) {
		foreach ($setterMethods as $method => $dependency) {
			$instance-> $method ( $this->getInstanceFromClassName($dependency, array(), $level+1));
		}
	}
	
	/**
	 * Gets Classinfos for the className - using the cache and the factory
	 * @param string $className
	 * @return Tx_Container_Classinfo
	 */
	private function getClassInfo($className) {
		if (!$this->cache->has($className)) {
			$this->cache->set($className, $this->classInfoFactory->buildClassInfoFromClassName($className));
		}
		return $this->cache->get($className);
	}
	
	/**
	 * does setter injection based on the data in $this->setterInjectionRegistry
	 * Its done as long till no setters are left
	 * @return void
	 */
	private function processSetterInjectionRegistry() {		
		while (count($this->setterInjectionRegistry)>0) {
			$currentSetterData = $this->setterInjectionRegistry;
			$this->setterInjectionRegistry = array();
			foreach ($currentSetterData as $setterInjectionData) {
				$this->handleSetterInjection($setterInjectionData[0], $setterInjectionData[1], $setterInjectionData[2]);
			}
		}
	}	
}