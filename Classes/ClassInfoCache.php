<?php

require_once (t3lib_extMgm::extPath ( 'container' ) . 'Classes/ClassInfo.php');

/**
 * Simple Cache for classInfos
 * 
 * @author Daniel Pötzinger
 */
class Tx_Container_ClassInfoCache {
	
	/**
	 * 
	 * @var array
	 */
	private $level1Cache=array();
	
	/**
	 * 
	 * @var t3lib_cache_frontend_VariableFrontend
	 */
	private $level2Cache;
	
	/**
	 * constructor
	 */
	public function __construct() {
		$this->initializeLevel2Cache();
	}
	
	/**
	 * checks if cacheentry exists for id
	 * @param string $id
	 */
	public function has($id) {
		return isset($this->level1Cache[$id]) || $this->level2Cache->has($id);
	}
	
	/**
	 * Gets the cache for the id
	 * @param string $id
	 */
	public function get($id) {
		if (!isset($this->level1Cache[$id])) {
			$this->level1Cache[$id] = $this->level2Cache->get($id);
		}
		return $this->level1Cache[$id];
	}
	
	/**
	 * sets the cache for the id
	 * 
	 * @param $id
	 * @param $value
	 */
	public function set($id,$value) {
		$this->level1Cache[$id]=$value;
		$this->level2Cache->set($id,$value);
	}
	
	
	/**
	 * Initialize the TYPO3 second level cache
	 */
	private function initializeLevel2Cache() {
		t3lib_cache::initializeCachingFramework();
		$backend = 't3lib_cache_backend_FileBackend';
		$frontend = 't3lib_cache_frontend_VariableFrontend';
		$config = array('defaultLifetime' => 3600);
		if ($GLOBALS['typo3CacheManager']->hasCache('Tx_Container_ClassInfoCache')) {
			$this->level2Cache = $GLOBALS['typo3CacheManager']->getCache('Tx_Container_ClassInfoCache') ;
		} else {
			try {
				$this->level2Cache = $GLOBALS['typo3CacheFactory']->create('Tx_Container_ClassInfoCache', $frontend, $backend, $config);
			} catch (Exception $e) {
				throw new LogicException('cache init [Tx_Container_ClassInfoCache/'.$frontend.'/'.$backend.'] failed:'.get_class($e).' - '.$e->getMessage());
			}
		}
	}
	
}