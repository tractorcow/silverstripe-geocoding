<?php

/**
 * @package geocoding
 */
class CachedGeocodingService implements IGeocodingService {
	
	/**
	 * @var IGeocodingService
	 */
	protected $backend = null;
	
	function __construct(IGeocodingService $backend) {
		$this->backend = $backend;
	}
	
	/**
	 * Gets the cache object
	 * 
	 * @return Zend_Cache_Frontend
	 */
	protected function getCache() {
		return SS_Cache::factory('GeocodingService');
	}
	
	public function geocode($address) {
		
		// Generate unique key for address
		$address = $this->normaliseAddress($address);
		$addressKey = md5('CachedGeocodingService_' . $address);
		
		// Check if cached
		$result = unserialize($this->getCache()->load($addressKey));
		if($result) return $result;
		
		// generate result, and check if it's a cachable result
		$result = $this->backend->geocode($address);
		if($result['Cache']) {
			$oneWeek = 3600 * 24 * 7;
			$this->getCache()->save(serialize($result), $addressKey, array(), $oneWeek);
		}
		return $result;
	}

	public function isOverLimit() {
		return $this->backend->isOverLimit();
	}

	public function normaliseAddress($address) {
		return $this->backend->normaliseAddress($address);
	}

}
