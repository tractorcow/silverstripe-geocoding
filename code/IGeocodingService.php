<?php

/**
 * @package geocoding
 */
interface IGeocodingService
{
    
    /**
     * Is this service over the daily limit?
     * 
     * @return boolean
     */
    public function isOverLimit();
    
    /**
     * Returns resulting geocode in the format
     * 
     * array(
     *    'Success' => true,
     *    'Latitude' => '0.000',
     *	  'Longitude' => '0.000',
     *	  'Error' => '', // error code
     *    'Message' => '' // error message
     * )
     * 
     * @param string $address
     * @return array Result
     */
    public function geocode($address);
    
    
    /**
     * Cleans up an address for geocoding
     * 
     * @param string|array $address
     * @return string
     */
    public function normaliseAddress($address);
}
