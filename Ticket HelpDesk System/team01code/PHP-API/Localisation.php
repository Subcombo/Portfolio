<?php
include_once dirname(__FILE__, 2) . '/PHP-DB/api.php';

function l(String $key)
{
  return localised($key, user_preferred_language());
}

function lx(String $key, String $langName)
{
  return localised($key, $langName);
}

// Localise datetime
function ldt(DateTime $datetime)
{
  $language = user_preferred_language();
  switch ($language)
  {
    case 'en':
      return $datetime->format('d/m/Y H:i');
      break;
    case 'de':
      return $datetime->format('d.m.Y H:i');
      break;
    case 'zh':
      return $datetime->format('y年m月d日 H时i分');
      break;
    case 'ar':
      $standard = ["0","1","2","3","4","5","6","7","8","9"];
      $arabic_symbols = ["٠","١","٢","٣","٤","٥","٦","٧","٨","٩"];
      $str = $datetime->format('d-m-Y H:i');
      return str_replace($standard, $arabic_symbols, $str);
      break;
    default:
    return $datetime->format('Y-m-d H:i');
      break;
  }
}

// Localise date
function ld(DateTime $datetime)
{
  $language = user_preferred_language();
  switch ($language)
  {
    case 'en':
      return $datetime->format('d-m-Y');
      break;
    case 'de':
      return $datetime->format('d.m.Y');
      break;
    case 'zh':
      return $datetime->format('y年m月d日');
      break;
    case 'ar':
      $standard = ["0","1","2","3","4","5","6","7","8","9"];
      $arabic_symbols = ["٠","١","٢","٣","٤","٥","٦","٧","٨","٩"];
      $str = $datetime->format('d-m-Y');
      return str_replace($standard, $arabic_symbols, $str);
      break;
    default:
      return $datetime->format('Y-m-d');
      break;
  }
}

function localised(String $key, String $langName): String
{
	$lang = _read_lang_file($langName);
	if ($langName === "en" && $lang === null) {return localised($key, "en");}
	if ($lang === null) {return "!" . $key . "!";}
	$wordNotFound = !array_key_exists($key, $lang);
	if ($langName === "en" && $wordNotFound) {return "!" . $key . "!";}
	if ($wordNotFound) {return localised($key, "en");}
	return $lang[$key];
}

function user_preferred_language(): String {
  if (isset($_GET['language']) && in_array($_GET['language'], get_available_languages())) return $_GET['language'];
  if (isset($_SESSION['language'])) return $_SESSION['language'];
	return browser_preferred_language();
}

function browser_preferred_language(): String {
	return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}

//constructs an assoc array from $lang . ".lang" in the localisation folder
function _read_lang_file(String $lang): array
{
	$lang = urlencode($lang); //to prevent language header hackery
	$langRaw = rtrim(file_get_contents(dirname(__FILE__, 2) . "/localisation/" . $lang . ".lang"));
	$langJSON = "{" . str_replace("\n", ",", $langRaw) . "}";
	$object = json_decode($langJSON, /*assoc = */ true);
  return ($object !== null ? $object : []);
}

function get_available_languages(): array
{
  // TODO: resolve dynamically from files, maybe?
  return ['en', 'de', 'zh', 'ar'];
}

function is_language_rtl(): bool
{
  return user_preferred_language() === "ar";
}

?>
