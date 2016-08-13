<?php

// Configuration.
$cacheEnabled = false;
$pathToPhpFastCache = "tempDir/phpfastcache.php";
$cacheTimeInSeconds = 60;

$debug = false;

// Functions

function d( $message ) {
	global $debug;
	if( $debug ) error_log( $message );
}

function resolveUuid( $nameToResolve ) {

	if( !isset( $nameToResolve ) || empty( $nameToResolve ) ) return null;

	try {

		$options = array(
			CURLOPT_RETURNTRANSFER => true,   // return web page
			CURLOPT_HEADER         => false,  // don't return headers
			CURLOPT_FOLLOWLOCATION => true,   // follow redirects
			CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
			CURLOPT_ENCODING       => "",     // handle compressed
			CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
			CURLOPT_CONNECTTIMEOUT => 3,    // time-out on connect
			CURLOPT_TIMEOUT        => 3,    // time-out on response
			CURLOPT_USERAGENT 	   => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36'
		);

		$ch = curl_init( "https://api.minepay.net/mojang/v1/name/" . urlencode( $nameToResolve ) );
		curl_setopt_array($ch, $options);
		$content  = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if( curl_errno($ch) || $httpcode != 200 ) {
			return null;
		}

		curl_close($ch);

		return json_decode( $content, true )['id'];

	} catch ( Exception $e ) {
		return null;
	}

}

// App
$cache = null;

// Resolve cname - max hops 15.
$resolved = $_SERVER['HTTP_HOST'];
for( $i = 0; $i < 16; $i++ ) {
	
	if( $i >= 15 ) {
		header( "Location: https://minecraftly.com/" );
		exit;
	}
	
	$dnsResolvedArray = dns_get_record( $resolved, DNS_CNAME );
	
	if( isset( $dnsResolvedArray ) && count( $dnsResolvedArray ) === 1 && $dnsResolvedArray ) {
		
		$resolvedNew = $dnsResolvedArray[0]['target'];
		if( strtolower( $resolvedNew ) === "m.ly" ) break;
		if( isset( $resolvedNew ) && !empty( $resolvedNew ) ) $resolved = $resolvedNew;
		//header( "x-test-header-$i: " . $dnsResolvedArray[0]['target'] );
		
	} else {
		break;
	}

}

// Check resolved name and get name/uuid from it.
if( !isset( $resolved ) || empty( $resolved ) ) $resolved = $_SERVER['HTTP_HOST'];

$name = explode( ".", $resolved )[0];
$uuid = null;

d( "Name: $name" );

// Set up cache.
if( $cacheEnabled ) {

	require( $pathToPhpFastCache );

	phpFastCache::setup("path", dirname(__FILE__));
	phpFastCache::setup("securityKey", "cache");

	$cache = phpfastcache();
}

$page = null;

// Check the name is even valid before going any further.
$name = str_replace( "-", "", $name );
$isUuid = preg_match( "/[0-9a-f]{32}/", $name ) === 1;
$isName = preg_match( "/[a-zA-Z0-9_]{1,16}/", $name ) === 1;

if( $name === "m" || ( !$isUuid && !$isName ) ) {
	header( "Location: https://minecraftly.com/" );
	exit;
}

// If it's already an UUID, just redirect.
if( $isUuid ) {
	header( "Location: https://minecraftly.com/u/" . urlencode( $name ) );
	exit;
}

// If caching is enabled, get whatever the cache might have (potentially null).
if( $cacheEnabled ) {
	$uuid = $cache->get( $name );
}

// Resolve the UUID if it's null and cache it if caching is enabled.
if( !isset( $uuid ) || empty( $uuid ) ) {
	$uuid = resolveUuid( $name );

	if( isset( $uuid ) && !empty( $uuid ) && $cacheEnabled ) {
		$cache->set( $name, $uuid, $cacheTimeInSeconds );
	}

}

d( "Final UUID: " . $uuid );

// Finally redirect them to the resolved uuid.
if( !isset( $uuid ) || empty( $uuid ) ) {
	header( "Location: https://minecraftly.com/" );
} else {
	header( "Location: https://minecraftly.com/u/" . urlencode( $uuid ) );
}

exit;