# Google maps geocoding wrapper for Silverstripe

Provides a safe and simple wrapper for geocoding in PHP using the Silverstripe framework.

Features:

* Safe-detection and prevention of daily request limit excession. This should help avoid getting your IP
blocked from Google once you hit your daily limit.

* Caching of requests to prevent unnecessary API calls.

## Credits and Authors

 * Damian Mooyman - <https://github.com/tractorcow/silverstripe-geocoding>

## Requirements

 * SilverStripe 3 or above
 * PHP 5.3

## Installation Instructions

Extract all files into the 'geocoding' folder under your Silverstripe root, or install using composer

```bash
composer require "tractorcow/silverstripe-geocoding": "3.0.*@dev"
```

## Usage

```php

// Instance is explicitly created here, but it's better to have this as an injected dependency
$address = '123 Fake Street, Fakuranga, New Fakeland';
$service = Injector::inst()->get('GeocodingService');
$result = $service->geocode($address);

if($result['Success']) {
	$latitude = $result['Latitude'];
	$longitude = $result['Longitude'];
	echo "Found address at $latitude,$longitude";
} else {
	user_error("Could not geocode address: ".$result['Message'], E_USER_ERROR);
}
```

## Need more help?

Message or email me at damian.mooyman@gmail.com or, well, read the code!

## License

Copyright (c) 2013, Damian Mooyman
All rights reserved.

All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
 * The name of Damian Mooyman may not be used to endorse or promote products
   derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
