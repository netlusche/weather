# Weather

Kleine PHP-Webanwendung zur Wetterabfrage mit aktueller Temperatur und 5-Tage-Vorhersage ueber die OpenWeatherMap-API.

## Funktionen

- Auswahl einer Stadt aus einer vordefinierten Liste
- Anzeige der aktuellen Temperatur und Wetterbeschreibung
- Ausgabe einer Wettervorhersage in Tabellenform
- Einfache Darstellung mit CSS

## Voraussetzungen

- PHP mit aktivierter `curl`-Erweiterung
- Internetzugang fuer die API-Anfragen an OpenWeatherMap

## Projektstruktur

- `index.php`: Startseite und Ausgabe der Wetterdaten
- `getweather.php`: API-Zugriffe, Hilfsfunktionen und Forecast-Ausgabe
- `style.css`: Einfaches Styling der Oberflaeche

## Lokale Nutzung

1. Repository klonen oder Dateien herunterladen
2. Im Projektverzeichnis einen PHP-Server starten:

```bash
php -S localhost:8000
```

3. Im Browser `http://localhost:8000` aufrufen

## Konfiguration

Der OpenWeatherMap-API-Key ist aktuell direkt in [`getweather.php`](/Users/frank/Codex/Weather/getweather.php) hinterlegt. Fuer produktive Nutzung sollte er in eine Umgebungsvariable oder eine nicht versionierte Konfigurationsdatei ausgelagert werden.

## Hinweise

- Die Anwendung nutzt derzeit HTTP-Endpunkte fuer die API-Aufrufe.
- Fehlerbehandlung fuer fehlgeschlagene API-Antworten ist nur eingeschraenkt vorhanden.

## Lizenz

Dieses Projekt steht unter der GNU General Public License v3. Details siehe [`LICENSE`](/Users/frank/Codex/Weather/LICENSE).
