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

The app supports an environment variable for the OpenWeather API key:

```bash
export OPENWEATHER_API_KEY="your_api_key_here"
```

If no environment variable is present, the application falls back to the key currently defined in [getweather.php](/Users/frank/Codex/Weather/getweather.php).

## UX Notes

- The location selector is a custom overlay instead of a native browser select.
- The search field supports free text entries and live suggestions.
- Clicking a suggestion loads the weather immediately.
- Clicking a location in the selector also loads the weather immediately.
- The top utility bar keeps theme and language controls accessible across screen sizes.

## License

This project is licensed under the GNU General Public License v3. See [LICENSE](/Users/frank/Codex/Weather/LICENSE) for details.
