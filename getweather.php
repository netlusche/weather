<?php
function loadApiKey() {
  $envKey = trim((string) getenv('OPENWEATHER_API_KEY'));
  if ($envKey !== '') {
    return $envKey;
  }

  $localConfigPath = __DIR__ . '/config.local.php';
  if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (is_array($localConfig) && isset($localConfig['openweather_api_key'])) {
      $configKey = trim((string) $localConfig['openweather_api_key']);
      if ($configKey !== '') {
        return $configKey;
      }
    }
  }

  return '';
}

$apiKey = loadApiKey();

function getCities() {
  return array(
    'Bielefeld',
    'Berlin',
    'Hamburg',
    'Hannover',
    'Dortmund',
    'Frankfurt',
    'Dresden',
    'München',
    'London',
    'Paris',
    'Rom',
    'New York',
    'Peking',
    'Bangkok',
    'Sydney',
    'Toronto',
    'Abu Dhabi',
    'Amsterdam',
    'Auckland',
    'Belfast',
    'Bogota',
    'Bombay',
    'Brasilia',
    'Brüssel',
    'Budapest',
    'Buenos Aires',
    'Cairo',
    'Canberra',
    'Caracas',
    'Chicago',
    'Dakar',
    'Dhaka',
    'Doha',
    'Dubai',
    'Dublin',
    'Genf',
    'Guatemala',
    'Havanna',
    'Helsinki',
    'Islamabad',
    'Johannesburg',
    'Kapstadt',
    'Kiew',
    'Kinshasa',
    'Kopenhagen',
    'Lagos',
    'Lissabon',
    'Los Angeles',
    'Madrid',
    'Majuro',
    'Manila',
    'Melbourne',
    'Mexico City',
    'Minsk',
    'New Delhi',
    'Oslo',
    'Perth',
    'Prag',
    'Pretoria',
    'Quito',
    'Reykjavik',
    'Rio de Janeiro',
    'Santiago',
    'São Paulo',
    'Seoul',
    'Singapur',
    'Stockholm',
    'Teheran',
    'Tokio',
    'Vancouver',
    'Wien',
    'Zürich'
  );
}

function normalizeCityName($value) {
  $value = trim((string) $value);
  $map = array(
    'ä' => 'ae',
    'ö' => 'oe',
    'ü' => 'ue',
    'ß' => 'ss',
    'Ä' => 'Ae',
    'Ö' => 'Oe',
    'Ü' => 'Ue'
  );

  return strtr($value, $map);
}

function resolveLocation($cities) {
  $defaultCity = 'Bielefeld';
  $searchValue = isset($_POST['location_search']) ? trim((string) $_POST['location_search']) : '';
  $selectValue = isset($_POST['location_select']) ? normalizeCityName($_POST['location_select']) : '';

  if ($searchValue !== '') {
    return $searchValue;
  }

  if ($selectValue === '') {
    return $defaultCity;
  }

  foreach ($cities as $city) {
    if (strcasecmp(normalizeCityName($city), $selectValue) === 0) {
      return $city;
    }
  }

  return $defaultCity;
}

function getSearchInputValue() {
  return isset($_POST['location_search']) ? trim((string) $_POST['location_search']) : '';
}

function getSupportedLanguage($value) {
  $language = strtolower(trim((string) $value));
  $supportedLanguages = array('de', 'en');

  return in_array($language, $supportedLanguages, true) ? $language : 'en';
}

function getBrowserLanguage() {
  if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    return 'en';
  }

  $accepted = explode(',', (string) $_SERVER['HTTP_ACCEPT_LANGUAGE']);
  foreach ($accepted as $entry) {
    $locale = strtolower(trim(explode(';', $entry)[0]));
    $primary = substr($locale, 0, 2);

    if (in_array($primary, array('de', 'en'), true)) {
      return $primary;
    }
  }

  return 'en';
}

function getLanguageValue() {
  if (isset($_POST['ui_lang'])) {
    return getSupportedLanguage($_POST['ui_lang']);
  }

  if (isset($_GET['lang'])) {
    return getSupportedLanguage($_GET['lang']);
  }

  if (isset($_COOKIE['weather_lang'])) {
    return getSupportedLanguage($_COOKIE['weather_lang']);
  }

  return getBrowserLanguage();
}

function fetchWeatherData($url) {
  $ch = curl_init();

  curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => array('Accept: application/json')
  ));

  $response = curl_exec($ch);
  $curlError = curl_error($ch);
  $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

  if ($response === false) {
    return array(
      'data' => null,
      'error' => 'Die Wetterdaten konnten gerade nicht geladen werden.'
    );
  }

  $data = json_decode($response);

  if ($curlError !== '') {
    return array(
      'data' => null,
      'error' => 'Die Wetterdaten konnten gerade nicht geladen werden.'
    );
  }

  if (!is_object($data)) {
    return array(
      'data' => null,
      'error' => 'Die Antwort des Wetterdienstes war ungültig.'
    );
  }

  if ($httpCode >= 400 || (isset($data->cod) && (string) $data->cod !== '200')) {
    $message = isset($data->message) ? ucfirst((string) $data->message) : 'Die Wetterdaten konnten nicht geladen werden.';

    return array(
      'data' => null,
      'error' => $message
    );
  }

  return array(
    'data' => $data,
    'error' => null
  );
}

function fetchJsonData($url) {
  $ch = curl_init();

  curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => array('Accept: application/json')
  ));

  $response = curl_exec($ch);
  $curlError = curl_error($ch);
  $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

  if ($response === false || $curlError !== '') {
    return array(
      'data' => null,
      'error' => 'Die Ortsvorschläge konnten gerade nicht geladen werden.'
    );
  }

  $data = json_decode($response, true);

  if (!is_array($data)) {
    return array(
      'data' => null,
      'error' => 'Die Antwort für Ortsvorschläge war ungültig.'
    );
  }

  if ($httpCode >= 400) {
    return array(
      'data' => null,
      'error' => 'Die Ortsvorschläge konnten gerade nicht geladen werden.'
    );
  }

  return array(
    'data' => $data,
    'error' => null
  );
}

function getWeather($apiKey, $location, $language = 'de') {
  if (trim((string) $apiKey) === '') {
    return array(
      'data' => null,
      'error' => 'Es wurde kein OpenWeatherMap-API-Key konfiguriert.'
    );
  }

  $url = 'https://api.openweathermap.org/data/2.5/weather?q=' . rawurlencode($location) . '&units=metric&lang=' . rawurlencode(getSupportedLanguage($language)) . '&appid=' . rawurlencode($apiKey);

  return fetchWeatherData($url);
}

function getForecast($location, $apiKey, $language = 'de') {
  if (trim((string) $apiKey) === '') {
    return array(
      'data' => null,
      'error' => 'Es wurde kein OpenWeatherMap-API-Key konfiguriert.'
    );
  }

  $url = 'https://api.openweathermap.org/data/2.5/forecast?q=' . rawurlencode($location) . '&units=metric&lang=' . rawurlencode(getSupportedLanguage($language)) . '&appid=' . rawurlencode($apiKey);

  return fetchWeatherData($url);
}

function getLocationSuggestions($apiKey, $query, $limit = 6) {
  $query = trim((string) $query);

  if ($query === '') {
    return array();
  }

  if (trim((string) $apiKey) === '') {
    return array();
  }

  $url = 'https://api.openweathermap.org/geo/1.0/direct?q=' . rawurlencode($query) . '&limit=' . (int) $limit . '&appid=' . rawurlencode($apiKey);
  $result = fetchJsonData($url);

  if ($result['error'] !== null || !is_array($result['data'])) {
    return array();
  }

  $suggestions = array();
  foreach ($result['data'] as $item) {
    if (!is_array($item) || empty($item['name'])) {
      continue;
    }

    $labelParts = array($item['name']);
    if (!empty($item['state'])) {
      $labelParts[] = $item['state'];
    }
    if (!empty($item['country'])) {
      $labelParts[] = $item['country'];
    }

    $label = implode(', ', $labelParts);
    if (!in_array($label, $suggestions, true)) {
      $suggestions[] = $label;
    }
  }

  return $suggestions;
}

function getWeatherSymbol($weatherDescription, $iconCode = '') {
  $description = function_exists('mb_strtolower')
    ? mb_strtolower((string) $weatherDescription, 'UTF-8')
    : strtolower((string) $weatherDescription);
  $iconPrefix = substr((string) $iconCode, 0, 2);

  if (strpos($description, 'gewitter') !== false) {
    return '⛈';
  }

  if (strpos($description, 'schnee') !== false) {
    return '❄';
  }

  if (strpos($description, 'regen') !== false || strpos($description, 'niesel') !== false) {
    return '🌧';
  }

  if (strpos($description, 'nebel') !== false || strpos($description, 'dunst') !== false) {
    return '🌫';
  }

  if (strpos($description, 'wolk') !== false) {
    if ($iconPrefix === '02' || $iconPrefix === '03') {
      return '⛅';
    }

    return '☁';
  }

  if ($iconPrefix === '01') {
    return '☀';
  }

  return '☀';
}

function getWeatherIconUrl($iconCode, $size = '2x') {
  $iconCode = trim((string) $iconCode);

  if ($iconCode === '') {
    return '';
  }

  $iconMap = array(
    '01d' => 'assets/weather-icons/clear-day.svg',
    '01n' => 'assets/weather-icons/clear-night.svg',
    '02d' => 'assets/weather-icons/partly-cloudy-day.svg',
    '02n' => 'assets/weather-icons/partly-cloudy-day.svg',
    '03d' => 'assets/weather-icons/cloudy.svg',
    '03n' => 'assets/weather-icons/cloudy.svg',
    '04d' => 'assets/weather-icons/cloudy.svg',
    '04n' => 'assets/weather-icons/cloudy.svg',
    '09d' => 'assets/weather-icons/rain.svg',
    '09n' => 'assets/weather-icons/rain.svg',
    '10d' => 'assets/weather-icons/rain.svg',
    '10n' => 'assets/weather-icons/rain.svg',
    '11d' => 'assets/weather-icons/thunderstorm.svg',
    '11n' => 'assets/weather-icons/thunderstorm.svg',
    '13d' => 'assets/weather-icons/snow.svg',
    '13n' => 'assets/weather-icons/snow.svg',
    '50d' => 'assets/weather-icons/mist.svg',
    '50n' => 'assets/weather-icons/mist.svg'
  );

  if (isset($iconMap[$iconCode])) {
    return $iconMap[$iconCode];
  }

  return 'assets/weather-icons/clear-day.svg';
}

function formatCityTime($timestamp, $timezoneOffset, $format) {
  return gmdate($format, (int) $timestamp + (int) $timezoneOffset);
}

function getLocalizedWeekdayShort($timestamp, $timezoneOffset, $language) {
  $weekday = gmdate('D', (int) $timestamp + (int) $timezoneOffset);
  $maps = array(
    'de' => array(
      'Mon' => 'Mo',
      'Tue' => 'Di',
      'Wed' => 'Mi',
      'Thu' => 'Do',
      'Fri' => 'Fr',
      'Sat' => 'Sa',
      'Sun' => 'So'
    ),
    'en' => array(
      'Mon' => 'Mon',
      'Tue' => 'Tue',
      'Wed' => 'Wed',
      'Thu' => 'Thu',
      'Fri' => 'Fri',
      'Sat' => 'Sat',
      'Sun' => 'Sun'
    )
  );

  $language = getSupportedLanguage($language);

  return isset($maps[$language][$weekday]) ? $maps[$language][$weekday] : $weekday;
}

function getCurrentWeatherMetrics($weather, $language = 'de') {
  if (!isset($weather->main) || !isset($weather->wind)) {
    return array();
  }

  $labels = array(
    'de' => array(
      'feels_like' => 'Gefühlt',
      'humidity' => 'Luftfeuchte',
      'wind' => 'Wind',
      'pressure' => 'Druck'
    ),
    'en' => array(
      'feels_like' => 'Feels like',
      'humidity' => 'Humidity',
      'wind' => 'Wind',
      'pressure' => 'Pressure'
    )
  );

  $language = getSupportedLanguage($language);
  $set = $labels[$language];

  return array(
    array(
      'label' => $set['feels_like'],
      'value' => round((float) $weather->main->feels_like) . '°C'
    ),
    array(
      'label' => $set['humidity'],
      'value' => (int) $weather->main->humidity . '%'
    ),
    array(
      'label' => $set['wind'],
      'value' => round((float) $weather->wind->speed, 1) . ' m/s'
    ),
    array(
      'label' => $set['pressure'],
      'value' => (int) $weather->main->pressure . ' hPa'
    )
  );
}

function getDailyForecastSummaries($forecast, $limit = 5, $language = 'de') {
  if (!isset($forecast->list) || !is_array($forecast->list)) {
    return array();
  }

  $timezoneOffset = isset($forecast->city->timezone) ? (int) $forecast->city->timezone : 0;
  $groups = array();

  foreach ($forecast->list as $entry) {
    $timestamp = isset($entry->dt) ? (int) $entry->dt : 0;
    $dateKey = formatCityTime($timestamp, $timezoneOffset, 'Y-m-d');
    $hourOfDay = (int) formatCityTime($timestamp, $timezoneOffset, 'G');
    $temperature = isset($entry->main->temp) ? (float) $entry->main->temp : 0.0;
    $icon = isset($entry->weather[0]->icon) ? (string) $entry->weather[0]->icon : '';
    $description = isset($entry->weather[0]->description) ? (string) $entry->weather[0]->description : '';

    if (!isset($groups[$dateKey])) {
      $groups[$dateKey] = array(
        'date_label' => formatCityTime($timestamp, $timezoneOffset, 'd.m.'),
        'weekday' => getLocalizedWeekdayShort($timestamp, $timezoneOffset, $language),
        'temp_min' => $temperature,
        'temp_max' => $temperature,
        'description' => $description,
        'icon' => $icon,
        'best_hour_distance' => 24
      );
    }

    $groups[$dateKey]['temp_min'] = min($groups[$dateKey]['temp_min'], $temperature);
    $groups[$dateKey]['temp_max'] = max($groups[$dateKey]['temp_max'], $temperature);

    $distanceToMidday = abs(12 - $hourOfDay);
    if ($distanceToMidday <= $groups[$dateKey]['best_hour_distance']) {
      $groups[$dateKey]['best_hour_distance'] = $distanceToMidday;
      $groups[$dateKey]['description'] = $description;
      $groups[$dateKey]['icon'] = $icon;
    }
  }

  $summaries = array();
  foreach ($groups as $day) {
    $summaries[] = array(
      'weekday' => $day['weekday'],
      'date_label' => $day['date_label'],
      'temp_min' => round($day['temp_min']),
      'temp_max' => round($day['temp_max']),
      'description' => $day['description'],
      'symbol' => getWeatherSymbol($day['description'], $day['icon']),
      'icon_url' => getWeatherIconUrl($day['icon'])
    );
  }

  return array_slice($summaries, 0, $limit);
}

function getForecastEntries($forecast, $language = 'de') {
  if (!isset($forecast->list) || !is_array($forecast->list)) {
    return array();
  }

  $timezoneOffset = isset($forecast->city->timezone) ? (int) $forecast->city->timezone : 0;
  $entries = array();

  foreach ($forecast->list as $entry) {
    $timestamp = isset($entry->dt) ? (int) $entry->dt : 0;
    $description = isset($entry->weather[0]->description) ? (string) $entry->weather[0]->description : '';
    $icon = isset($entry->weather[0]->icon) ? (string) $entry->weather[0]->icon : '';

    $entries[] = array(
      'weekday' => getLocalizedWeekdayShort($timestamp, $timezoneOffset, $language),
      'date' => formatCityTime($timestamp, $timezoneOffset, 'd.m.Y'),
      'time' => formatCityTime($timestamp, $timezoneOffset, 'H:i'),
      'temperature' => round((float) $entry->main->temp),
      'description' => $description,
      'symbol' => getWeatherSymbol($description, $icon),
      'icon_url' => getWeatherIconUrl($icon)
    );
  }

  return $entries;
}
?>
