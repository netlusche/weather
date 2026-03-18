<?php
  require_once "getweather.php";
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Wetterabfrage</title>

  <!-- Hier könnten Sie Ihre CSS-Datei einbinden -->
  <link rel="stylesheet" href="style.css"> 
  
</head>
<body>
  <h1>Wetterabfrage</h1>

  <form action="index.php" method="post">
    <b>Ort auswählen:</b>
    <select name="location">
      <?php
        // Array mit Städtenamen abrufen
        $cities = getCities();

        // Städtenamen als Optionen in select-Liste einfügen
        foreach ($cities as $city) {
          echo "<option value='$city'";
          if ($_POST['location'] == $city) {
          	echo " selected";
          }
          echo ">$city</option>";
        }
      ?>
    </select>
    <input type="submit" value="Wetter abrufen">
  </form>

  <?php
    if (isset($_POST['location'])) {
      $location = $_POST['location'];
    } else {
      $location = "Bielefeld";
    }
      // Wetter abrufen
      $weather = getWeather($apiKey, $location);
      $symbol = getWeatherSymbol($weather->weather[0]->description);

      // Wetterdaten ausgeben
      echo "<table><tr><td id=\"noBorderTD\"><span class=\"emoji\">&#x1f321;</span></td><td id=\"noBorderTD\">Die aktuelle <b>Temperatur</b> in <b>" . htmlentities($weather->name). "</b> ist <b>" . htmlentities(round($weather->main->temp)) . "°C</b>.</td></tr></table>";
      echo "<table><tr><td id=\"noBorderTD\">Das <b>Wetter</b> ist derzeit: <b>" . $weather->weather[0]->description . "</b>.</td><td id=\"noBorderTD\"><span class=\"emoji\">" . $symbol . "</span></td></tr></table>";
      echo "<table><tr><td id=\"noBorderTD\">Und so wird das <b>Wetter</b> in <b>" . htmlentities($weather->name). "</b> in den nächsten Tagen</b>:</td></tr></table>";
      
      // Debugging-Asgaben Today:
      // echo "<pre><b>Debugging Wetter von heute:</b> <br />";
      // var_dump($weather);
      // echo "</pre>";
      
      // Forecast abrufen
	    $forecast = getForecast($location,$apiKey);
		  printForecast($forecast);
      
      // Debugging Ausgaben Forecast:
      // echo "<pre> <b>Debugging Vorhersage:</b> <br />";
      // var_dump($forecast);
      // print_r(array_slice($forecast->list, 0, 10));
      // echo "</pre>";
  ?>
</body>
</html>
