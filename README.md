# Weather

A PHP weather dashboard powered by the OpenWeather API. The project started as a simple weather lookup and has evolved into a themed, interactive UI with live location suggestions, multiple visual themes, and responsive layouts.

## Features

- Current weather conditions for the selected city
- Compact 5-day overview plus expandable 3-hour forecast details
- Predefined city selector with custom overlay UI
- Free-text location search with live suggestions from the OpenWeather geocoding API
- Immediate loading on city selection or suggestion click
- Responsive layout optimized for desktop and mobile
- Built-in language switching for German and English
- Browser-language based default UI language with English as fallback
- Theme selector with persistent client-side theme storage

## Available Themes

- `Standard - light`
- `Standard - dark`
- `Cyberpunk`
  Uses neon blue and neon pink accents, a more stylized techno type treatment, and a subtle animated background.
- `Matrix`
  Uses green terminal-style visuals with animated falling character rain in the background.
- `LCARS`
  Uses a Star Trek inspired interface language with stronger yellow and blue accents plus subtle animated background bands.

## Project Structure

- `index.php`
  Main page, layout, theme/language controls, and client-side interactions
- `getweather.php`
  Weather/geocoding API calls, helpers, forecast grouping, and localization helpers
- `style.css`
  Full styling for layout, responsive behavior, overlays, and themes
- `assets/weather-icons/`
  Local weather icon set used by the app
- `LICENSE`
  GNU General Public License v3

## Requirements

- PHP with the `curl` extension enabled
- Internet access for OpenWeather weather data and geocoding requests

## Local Development

Start a local PHP server from the project directory:

```bash
php -S 127.0.0.1:8000
```

Then open:

```text
http://127.0.0.1:8000
```

## Configuration

The app supports two safe ways to provide the OpenWeather API key:

1. Environment variable

```bash
export OPENWEATHER_API_KEY="your_api_key_here"
```

2. Local PHP config file

Copy the example file and add your personal key:

```bash
cp config.local.example.php config.local.php
```

Then edit `config.local.php` and insert your key.

The local config file is ignored by Git and is meant for private development only.

The app checks configuration in this order:

- `OPENWEATHER_API_KEY`
- `config.local.php`

If neither is present, the weather API requests will fail until a key is configured.

## Security Notes

- Never commit a real API key to the repository.
- `config.local.php` is intentionally ignored via `.gitignore`.
- If a key was ever committed publicly, rotate it in your OpenWeather account immediately.

## UX Notes

- The location selector is a custom overlay instead of a native browser select.
- The search field supports free text entries and live suggestions.
- Clicking a suggestion loads the weather immediately.
- Clicking a location in the selector also loads the weather immediately.
- The top utility bar keeps theme and language controls accessible across screen sizes.

## License

This project is licensed under the GNU General Public License v3. See [LICENSE](/Users/frank/Codex/Weather/LICENSE) for details.
