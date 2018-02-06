<?php

/**
 * @package geocoding
 */
class GeocodingService implements IGeocodingService
{

    /**
     * Gets the cache object
     *
     * @return Zend_Cache_Frontend
     */
    protected function getCache()
    {
        return SS_Cache::factory('GeocodingService');
    }

    /**
     * Marks as over daily limit
     */
    protected function markLimit()
    {
        $this->getCache()->save(time(), 'dailyLimit', array(), null);
    }

    public function isOverLimit()
    {
        $limit = $this->getCache()->load('dailyLimit');
        if (empty($limit)) {
            return false;
        }

        // It's ok if it's been 24 hours
        return (time() - $limit) > (3600 * 24);
    }

    public function normaliseAddress($address)
    {
        if (is_array($address)) {
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
    public function geocode($address)
    {

        // Don't attempt geocoding if over limit
        if ($this->isOverLimit()) {
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
        if (empty($xml)) {
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
            if (strcmp($status, 'OVER_QUERY_LIMIT') == 0) {
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
            'StreetNumber' => '' . $xml->result->address_component[0]->long_name,
            'StreetName' => '' . $xml->result->address_component[1]->long_name,
            'StreetNameShort' => '' . $xml->result->address_component[1]->short_name,
            'Suburb' => '' . $xml->result->address_component[2]->long_name,
            'Council' => '' . $xml->result->address_component[3]->long_name,
            'CouncilShort' => '' . $xml->result->address_component[3]->short_name,
            'State' => '' . $xml->result->address_component[4]->long_name,
            'StateShort' => '' . $xml->result->address_component[4]->short_name,
            'Country' => '' . $xml->result->address_component[5]->long_name,
            'CountryShort' => '' . $xml->result->address_component[5]->short_name,
            'PostCode' => '' . $xml->result->address_component[6]->long_name,
            'Cache' => true
        );
    }
}
