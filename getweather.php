<?php
  // API-Key von OpenWeatherMap
  $apiKey = "REDACTED_OPENWEATHER_API_KEY";

  function getCities() {
    // Array mit Städtenamen
$cities = array(
  "Bielefeld",
  "Berlin",
  "Hamburg",
  "Hannover",
  "Dortmund",
  "Frankfurt",
  "Dresden",
  "München",
  "London",
  "Paris",
  "Rom",
  "New York",
  "Peking",
  "Bangkok",
  "Sydney",
  "Toronto",
  "Abu Dhabi",
  "Amsterdam",
  "Auckland",
  "Belfast",
  "Bogota",
  "Bombay",
  "Brasilia",
  "Brüssel",
  "Budapest",
  "Buenos Aires",
  "Cairo",
  "Canberra",
  "Caracas",
  "Chicago",
  "Dakar",
  "Dhaka",
  "Doha",
  "Dubai",
  "Dublin",
  "Genf",
  "Guatemala",
  "Havanna",
  "Helsinki",
  "Islamabad",
  "Johannesburg",
  "Kapstadt",
  "Kiew",
  "Kinshasa",
  "Kopenhagen",
  "Lagos",
  "Lissabon",
  "Los Angeles",
  "Madrid",
  "Majuro",
  "Manila",
  "Melbourne",
  "Mexico City",
  "Minsk",
  "New Delhi",
  "Oslo",
  "Perth",
  "Prag",
  "Pretoria",
  "Quito",
  "Reykjavik",
  "Rio de Janeiro",
  "Santiago",
  "São Paulo",
  "Seoul",
  "Singapur",
  "Stockholm",
  "Teheran",
  "Tokio",
  "Vancouver",
  "Wien",
  "Zürich"
);


    return $cities;
  }

function getWeatherSymbol($weatherDescription) {
  $symbol = '';

  switch ($weatherDescription) {
    case 'klarer Himmel':
      $symbol = '&#x2600;';
      break;
    case 'wenige Wolken':
      $symbol = '&#x26C5;';
      break;
    case 'verstreute Wolken':
    case 'bewölkt':
      $symbol = '&#x2601;';
      break;
    case 'Regenschauer':
    case 'Regen':
      $symbol = '&#x2614;';
      break;
    case 'Leichter Regen':
      $symbol = '&#x1F326;';
      break;
    case 'Bedeckt':
      $symbol = '&#x2601;';
      break;
    case 'Überwiegend bewölkt':
      $symbol = '&#x26C5;';
      break;
    case 'Ein paar Wolken':
      $symbol = '&#x26C5;';
      break;
    case 'Mäßig bewölkt':
      $symbol = '&#x26C5;';
      break;
    case 'Klarer Himmel':
      $symbol = '&#x2600;';
      break; 
      case 'Mäßiger Schnee':
      $symbol = '&#x2744;';
      break;
    case 'Sonne':
      $symbol = '&#x2600;';
      break;
    case 'Mäßiger Regen':
      $symbol = '&#x1F326;';
      break;  
    case 'Schnee':
      $symbol = '&#x2744;';
      break;
    case 'Gewitter':
      $symbol = '&#x26A1;';
      break;
    case 'Nebel':
    case 'Dunst':
      $symbol = '&#x1F32B;';
      break;
    case 'starke Regenfälle':
      $symbol = '&#x1F327;';
      break;
    default:
      $symbol = '';
  }

  return $symbol;
}

  function getWeather($apiKey, $location) {

    // API-Endpunkt von OpenWeatherMap
    $url = "http://api.openweathermap.org/data/2.5/weather?q=$location&units=metric&lang=de&appid=$apiKey";

    // API-Anfrage senden
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    // Antwort deserilialisieren
    $weather = json_decode($response);

    return $weather;
  }
  
  
function getForecast($location, $apiKey) {

  // API-Endpunkt für Wettervorhersage abrufen
  $url = "http://api.openweathermap.org/data/2.5/forecast?q=$location&units=metric&lang=de&appid=$apiKey";
  
  // echo $url;

  // API-Aufruf durchführen und Antwort abrufen
  $response = file_get_contents($url);

  // Antwort in JSON-Objekt umwandeln
  $forecast = json_decode($response);

  return $forecast;
}

function printForecast($forecast) {
  // Die Wettervorhersage enthält eine Liste von Wetterdaten für jede Stunde der nächsten fünf Tage
  $list = $forecast->list;

   echo "<table>";
    
  // Durchlaufe jedes Wetterdatum in der Liste
  foreach ($list as $weatherData) {
    // Extrahiere Datum und Zeit des Wetterdatums
    $time = $weatherData->dt;
    $date = date("d.m.Y", $time);
    $hour = date("H:i", $time);

    // Extrahiere Wetterbeschreibung und Temperatur
    
    $description = $weatherData->weather[0]->description;
    $symbol = getWeatherSymbol($description);
    $temp = round($weatherData->main->temp);

    // Extrahiere Wochentag aus dem Datum
    $weekday = date("D", $time);

    // Gebe die Daten zeilenweise aus

    echo "<tr><td><b>$weekday</b></td><td><b>$date, $hour</b></td><td>" .$temp. "°C</td><td><span class=\"emoji\">" . $symbol . "</span></td><td>" . $description. "</td></tr>";
   
  }
  
   echo "</table>";
}

?>
