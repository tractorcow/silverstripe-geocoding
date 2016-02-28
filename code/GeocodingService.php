<?php

/**
 * @package geocoding
 */
class GeocodingService implements IGeocodingService {
	
	/**
	 * Gets the cache object
	 * 
	 * @return Zend_Cache_Frontend
	 */
	protected function getCache() {
		return SS_Cache::factory('GeocodingService');
	}
	
	/**
	 * Marks as over daily limit
	 */
	protected function markLimit() {
		$this->getCache()->save((string)time(), 'dailyLimit', array(), null);
	}
	
	
	/**
	 * Check if this service is over the daily limit.
	 * 
	 * @return bool True if this service has exceeded it's limit within the last 24 hours.
	 * False if the service is allowed to make additional requests.
	 */
	public function isOverLimit() {
		$limit = $this->getCache()->load('dailyLimit');
		if(empty($limit)) return false;
		
		// This service has exceeded the limit if the last limit was reached less than 24 hours ago 
		$lastLimited = time() - $limit;
		return $lastLimited < (3600 * 24);
	}
	
	public function normaliseAddress($address) {
		if(is_array($address)) {
			$address = implode(', ', $address);
		}
		return trim(preg_replace('/\n+/', ', ', $address));
	}
	
	/**
	 * Returns resulting geocode in the format
	 * 
	 * array(
	 *    'Success' => true,
	 *    'Latitude' => '0.000',
	 *	  'Longitude' => '0.000',
	 *	  'Error' => '', // error code (if error)
	 *    'Message' => '', // error message (if error)
	 *	  'Cache' => true // true if this result can be reproduced (cached)
	 * )
	 * 
	 * Success and Cache are always required.
	 * If success, Latitude and Longitude are required.
	 * If failure, Error and Message are required.
	 * 
	 * @param string|array $address Address, or list of components
	 * @return array Result
	 */
	public function geocode($address) {
		
		// Don't attempt geocoding if over limit
		if($this->isOverLimit()) {
			return array(
				'Success' => false,
				'Error' => 'OVER_QUERY_LIMIT',
				'Message' => 'Google geocoding service is over the daily limit. Please try again later.',
				'Cache' => false // Don't cache broken results
			);
		}
		
		// Geocode
		$address = $this->normaliseAddress($address);
        $requestURL = "http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=" . urlencode($address);
        $xml = simplexml_load_file($requestURL);
		
		// Check if there is a result
		if(empty($xml)) {
			return array(
				'Success' => false,
				'Error' => 'UNKNOWN_ERROR',
				'Message' => "Could not call google api at url $requestURL",
				'Cache' => false // Retry later
			);
		}

		// Check if result has specified error
        $status = (string)$xml->status;
        if (strcmp($status, "OK") != 0) {
			// check if limit hasbeen breached
			$cache = true;  // failed results should still be cacheable
			if(strcmp($status, 'OVER_QUERY_LIMIT') == 0) {
				$cache = false; // Don't cache over limit values
				$this->markLimit();
			}
			return array(
				'Success' => false,
				'Error' => $status,
				'Message' => "Google error code: $status at url $requestURL",
				'Cache' => $cache
			);
		}

        $coordinates = $xml->result->geometry->location;
		
		return array(
			'Success' => true,
			'Latitude' => floatval($coordinates->lat),
			'Longitude' => floatval($coordinates->lng),
			'Cache' => true
		);
	}
}
