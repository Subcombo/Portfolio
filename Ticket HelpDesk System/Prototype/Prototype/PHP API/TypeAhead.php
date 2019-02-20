<?php namespace typeahead;

function softwareName(String $string): array {
	return array("Microsoft Windows", "Microsoft Word", "Microsoft Excel", "Microsoft Access", "Microsoft Edge", "Mozilla Firefox", "Apple Safari", "Apple macOS", "Spotify");
}

function softwareVersion(String $string): array {
	return array("XP", "7", "10");
}

function softwareRegNo(String $string): array {
	return array("HS7FE-S7D6F-HJS73-JS738-NS823", "JSUE3-NSK26-MKAY7S-MASHE-KS723");
}

function hardwareType(String $string): array {
	return array("Mouse", "Keyboard", "Smartphone", "Personal Computer");
}

function hardwareMake(String $string): array {
	return array("Hewlett-Packard", "Dell", "Apple", "Lenovo", "Logitech");
}

function hardwareModel(String $string): array {
	return array("G5", "MX Master", "MX Anywhere", "Party Collection", "Trackman Marble");
}

function hardwareSerialNo(String $string): array {
	return array("ABC123", "DEF465", "OMG098");
}

?>