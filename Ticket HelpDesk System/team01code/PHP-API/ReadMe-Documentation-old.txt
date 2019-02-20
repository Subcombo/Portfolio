PHP API Documentation


 §Notes

Basically, the API is for logic and reuse. Logic means anything dynamic, so all translatable text, the current user's username, typeahead/dropdowns, and similar. Reuse means anything which gets copied & pasted onto multiple pages, such as the Bootstrap imports. Some of this functionality is available through the database API directly.

This will get updated as the API evolves, and serves as a reference for what functions are available. If you see a function listed here, you can start using it in web pages right away.

--

Example usage of the API:

<?php
	include 'PHP-API/Example.php';
	include 'PHP-API/Example2.php';

	//code using functions from Example.php and Example2.php
?>

Alternatively, include 'FullAPI.php' (or the appropriate path, such as 'PHP-API/FullAPI'), which itself includes every file in the API (excluding incomplete/broken files which may be present during development).

--

phpinfo.php has some useful stuff, like the keys needed to access the HTML header data. Of course, you shouldn't need to access this directly, if I design my API properly. ;)

Functions prefixed with an underscore (_), are subject to change and so should not be called outside of the file they are declared in. These functions are not documented here. Undocumented functions without an underscore are likely to be experimental or incomplete.

A preceding question mark ('?TypeName') indicates a nullable value (no nullables as of writing).

 $Header.php - implemented

//redirects to login.php if the user is not logged in
function authorise()

//returns HTML header content common to every page
function main_header(): String

//returns HTML for the navigation bar
function navbar(): String

 §Localisation.php - implemented

//returns the localised version of a string. most cases, "example" becomes <?=l("example")?>.
function l($key): String

//returns $key localised with the language for the given name, or $key if a localisation is not found
function localised(String $key, String $langName): String

//returns the user's preferred language (will often match the browser)
function user_preferred_language(): String

//returns the browser's preferred language (ignores user preference)
function browser_preferred_language(): String

//returns the user's preferred language's direction
function is_language_rtl(): bool

//returns a list of available languages
function get_available_languages(): array

 $TypeAhead.php - partially implemented, returns filtered placeholder values, covered by database API

//the following return the corresponding database entries, filtered by prefix.

function software_name($prefix): [String]

function software_version($prefix): [String]

function software_reg_no($prefix): [String]

function hardware_type($prefix): [String]

function hardware_make($prefix): [String]

function hardware_model($prefix): [String]

function hardware_serial_no($prefix): [String]
