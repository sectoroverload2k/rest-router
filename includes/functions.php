<?php

function gen_oauth_creds() {
	// Get a whole bunch of random characters from the OS
	$fp = fopen('/dev/urandom','rb');
	$entropy = fread($fp, 32);
	fclose($fp);

	// Takes our binary entropy, and concatenates a string which represents the current time to the microsecond
	$entropy .= uniqid(mt_rand(), true);
	// Hash the binary entropy
	$hash = hash('sha512', $entropy);

	// Chop and send the first 80 characters back to the client
	return array(
		'consumer_key' => substr($hash, 0, 32),
		'shared_secret' => substr($hash, 32, 48)
	);
}

function pre($v){echo '<pre>';print_r($v);echo '</pre>';}
