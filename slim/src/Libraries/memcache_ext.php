<?php

namespace App\Libraries;

use App;
use Exception;
use Memcached;

/**
 * Class memcache_ext
 * This class embodies domain specific memcache functionality allowing a register of domain keys to be kept
 * this allows the cache to be debugged and selectively deleted from internal.care-for-it.com
 */
class memcache_ext extends Memcached
{
	/**
	 * @var Memcached $memcache_obj The memcache object
	 */
	static public $memcache_obj;

	/**
	 * @var int The maximum memcache size
	 */
	const MAX_SIZE = (20 * 1024 * 1024) - 42;

	static function cache()
	{
		if( NULL == self::$memcache_obj ) {
			if( class_exists('Memcached') ) {
				self::$memcache_obj = new Memcached;
				if(!is_array(MEMCACHE_IP)) {
					self::$memcache_obj->addServer(MEMCACHE_IP, 11211);
				} else {
					foreach(MEMCACHE_IP as $memcache_server) {
						self::$memcache_obj->addServer($memcache_server, 11211);
					}
				}
			} else {
				die('Cannot instantiate memcache_ext!');
			}
		}

		return self::$memcache_obj;
	}

	/**
	 * Flush the cache for a domain
	 * @return Boolean success of deletion
	 */
	static function domain_delete_all()
	{
		$keys = array_keys(self::domain_get_keys());
		return self::domain_delete($keys);
	}

	/**
	 * Get stats from the memcache instance
	 * We use this to interrogate keys for a domain
	 * @param String $type
	 * @return mixed
	 */
	static function stats($type, $slabid = NULL, $limit = 100)
	{
		if( self::$memcache_obj == NULL ) self::cache();
		return self::$memcache_obj->getExtendedStats($type, $slabid, $limit);
	}

	/**
	 * Add a key value pair for a domain with memcache flags and expiry time
	 * @param string $key keyname
	 * @param mixed $var value to store
	 * @param bool $flag memcache flags - compressed by default
	 * @param int $expire seconds to persist
	 * @return Boolean added both meta and data
	 */
	static function domain_add($key, $var, $flag = FALSE, $expire = 3600){
		try{
			$stored_size = strlen(serialize($var));
			if( $stored_size > self::MAX_SIZE ){
				throw new Exception("Exceeded Memcache daemon size storage limits trying to store key $key with $stored_size bytes");
			}

			if (NULL == self::$memcache_obj) self::cache();
			// add values but keep their names in a registry for the domain
			// add the key for the domain
			// add the key and value
			$data = "Added by " . basename(debug_backtrace()[0]['file'], ".php") . "/" . debug_backtrace()[1]['function'] . " on " . date("d/m/Y H:i:s", time()) . " expires " . date("d/m/Y H:i:s", time() + $expire);
			// disable error reporting for out cache store fails as objects are often too big
			$er = error_reporting();
			error_reporting(0);
			$try = self::$memcache_obj->set($key, $var, $expire) & self::$memcache_obj->set("meta_" . DATABASE . "_" . $key, $data, $expire);
			if ( !$try) {
				// add cannot overwrite - so replace if it fails
				$try = self::$memcache_obj->replace($key, $var, $flag, $expire) & self::$memcache_obj->replace("meta_" . DATABASE . "_" . $key, $data, $flag, $expire);
				if( ! $try ){
					$try = self::$memcache_obj->get($key);
					if($try != $var){
						throw new Exception("Exceeded Memcache daemon size storage limits trying to store key $key with $stored_size bytes");
					}
				}
			}
			error_reporting($er);
			return $try;
		}
		catch(Exception $e){
            //	newrelic_notice_error($e->getMessage());
			echo $e->getMessage();
			return FALSE;
		}
	}

	/**
	 * delete from cache for a domain by key
	 * @param mixed array or string keyname
	 * @return bool success true if something was deleted
	 */
	static function domain_delete($keys){
		if( ! is_array($keys)) $keys = [$keys];
		$success = false;
		foreach($keys as $key){
			$success |= self::$memcache_obj->delete("meta_" . DATABASE . "_" . $key) && self::$memcache_obj->delete($key);
		}
		return $success;
	}

	/**
	 * Get the value stored in memcache for a domain
	 * @param $key
	 * @return mixed OR FALSE
	 */
	static function domain_get($key){
		if (NULL == self::$memcache_obj) self::cache();
		// development env and ops, opsalpha and opsbeta never cache
		if( in_array(DATABASE, ['ops', 'opsbeta', 'opsalpha']) ) return FALSE;
		return self::$memcache_obj->get($key);
	}

	/**
	 * Get all the keys stored for a domain
	 * @return array
	 */
	static function domain_get_keys(){
		if (NULL == self::$memcache_obj) self::cache();
		$domain = DATABASE;

		$slabs = self::stats('slabs');
		$limit = 30000;
		$keysFound = [];
		foreach ($slabs as $serverSlabs) {
			foreach ($serverSlabs as $slabId => $slabMeta) {
				// skip past non ids
				if( ! is_numeric($slabId)) continue;

				try {
					$cacheDump = self::stats('cachedump', (int) $slabId, 1000);
				} catch (Exception $e) { continue; }

				if (!is_array($cacheDump)) continue;

				foreach ($cacheDump as $dump) {

					if (!is_array($dump)) continue;

					foreach ($dump as $key => $value) {
						$i = strpos($key, "meta_$domain");
						if(0 === $i){
							$keysFound[substr($key, strlen("meta_$domain") + 1)] = self::domain_get($key);
						}
						// if we reach the limit just return the truncated data
						if (count($keysFound) == $limit) { return $keysFound; }
					}
				}
			}
		}
		// return list
		return $keysFound;
	}
}