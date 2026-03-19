<?php
require_once __DIR__ . '/getweather.php';

$uiLanguage = getLanguageValue();
setcookie('weather_lang', $uiLanguage, time() + (60 * 60 * 24 * 365), '/');

function getTranslations() {
  return array(
    'de' => array(
      'page_title' => 'Wetterabfrage',
      'eyebrow' => 'Wetterübersicht',
      'headline' => 'Wetterabfrage',
      'intro' => 'Wähle einen Ort per Select oder tippe im Suchfeld los. Standardmäßig bekommst du eine kompakte 5-Tage-Übersicht und kannst die komplette 3-Stunden-Vorhersage bei Bedarf erweitern.',
      'theme' => 'Theme',
      'language' => 'Sprache',
      'theme_light' => 'Light',
      'theme_dark' => 'Dark',
      'theme_cyberpunk' => 'Cyberpunk',
      'theme_matrix' => 'Matrix',
      'theme_lcars' => 'LCARS',
      'language_de' => 'Deutsch',
      'language_en' => 'English',
      'location_select' => 'Ort auswählen',
      'location_search' => 'Ort suchen',
      'search_placeholder' => 'Ort eingeben',
      'submit' => 'Wetter anzeigen',
      'status_unavailable' => 'Aktuell nicht verfügbar',
      'status_error_title' => 'Die Wetterdaten konnten nicht geladen werden.',
      'current_status' => 'Aktueller Stand',
      'updated_at' => 'Aktualisiert am',
      'celsius' => 'Celsius',
      'next_days' => 'Nächste Tage',
      'forecast_title' => '5-Tage-Übersicht',
      'forecast_subtitle' => 'Kompakt und schnell lesbar',
      'details_summary' => 'Komplette 3-Stunden-Vorhersage anzeigen'
    ),
    'en' => array(
      'page_title' => 'Weather Lookup',
      'eyebrow' => 'Weather Overview',
      'headline' => 'Weather Lookup',
      'intro' => 'Choose a city from the selector or start typing in the search field. By default, you get a compact 5-day overview and can expand the full 3-hour forecast when needed.',
      'theme' => 'Theme',
      'language' => 'Language',
      'theme_light' => 'Light',
      'theme_dark' => 'Dark',
      'theme_cyberpunk' => 'Cyberpunk',
      'theme_matrix' => 'Matrix',
      'theme_lcars' => 'LCARS',
      'language_de' => 'Deutsch',
      'language_en' => 'English',
      'location_select' => 'Choose location',
      'location_search' => 'Search location',
      'search_placeholder' => 'Enter location',
      'submit' => 'Show weather',
      'status_unavailable' => 'Currently unavailable',
      'status_error_title' => 'The weather data could not be loaded.',
      'current_status' => 'Current conditions',
      'updated_at' => 'Updated at',
      'celsius' => 'Celsius',
      'next_days' => 'Next days',
      'forecast_title' => '5-day overview',
      'forecast_subtitle' => 'Compact and easy to scan',
      'details_summary' => 'Show full 3-hour forecast'
    )
  );
}

function t($key, $translations, $language) {
  if (isset($translations[$language][$key])) {
    return $translations[$language][$key];
  }

  return isset($translations['de'][$key]) ? $translations['de'][$key] : $key;
}

function formatUiText($value) {
  if (function_exists('mb_convert_case')) {
    return mb_convert_case((string) $value, MB_CASE_TITLE, 'UTF-8');
  }

  return ucfirst((string) $value);
}

if (isset($_GET['action']) && $_GET['action'] === 'suggest') {
  header('Content-Type: application/json; charset=utf-8');
  $query = isset($_GET['q']) ? (string) $_GET['q'] : '';
  echo json_encode(getLocationSuggestions($apiKey, $query), JSON_UNESCAPED_UNICODE);
  exit;
}

$translations = getTranslations();
$themeOptions = array(
  'dark' => t('theme_dark', $translations, $uiLanguage),
  'light' => t('theme_light', $translations, $uiLanguage),
  'cyberpunk' => t('theme_cyberpunk', $translations, $uiLanguage),
  'matrix' => t('theme_matrix', $translations, $uiLanguage),
  'lcars' => t('theme_lcars', $translations, $uiLanguage)
);
$languageOptions = array(
  'de' => t('language_de', $translations, $uiLanguage),
  'en' => t('language_en', $translations, $uiLanguage)
);

$cities = getCities();
$basePath = getBasePath();
$cookiePath = $basePath !== '' ? $basePath : '/';
$searchInputValue = getSearchInputValue();
$selectedLocation = resolveLocation($cities);
setcookie('weather_location', $selectedLocation, time() + (60 * 60 * 24 * 365), $cookiePath);
$weatherResult = getWeather($apiKey, $selectedLocation, $uiLanguage);
$forecastResult = getForecast($selectedLocation, $apiKey, $uiLanguage);
$errorMessage = $weatherResult['error'] ?: $forecastResult['error'];
$weather = $weatherResult['data'];
$forecast = $forecastResult['data'];
$currentMetrics = array();
$dailyForecasts = array();
$forecastEntries = array();
$currentSymbol = '☀';
$currentIconUrl = '';
$updatedAt = '';
$weatherDescription = '';

if ($weather !== null && $forecast !== null && $errorMessage === null) {
  $currentMetrics = getCurrentWeatherMetrics($weather, $uiLanguage);
  $dailyForecasts = getDailyForecastSummaries($forecast, 5, $uiLanguage);
  $forecastEntries = getForecastEntries($forecast, $uiLanguage);
  $weatherDescription = isset($weather->weather[0]->description) ? (string) $weather->weather[0]->description : '';
  $icon = isset($weather->weather[0]->icon) ? (string) $weather->weather[0]->icon : '';
  $currentSymbol = getWeatherSymbol($weatherDescription, $icon);
  $currentIconUrl = getWeatherIconUrl($icon, '4x');
  $timezoneOffset = isset($weather->timezone) ? (int) $weather->timezone : 0;
  $updatedTimestamp = isset($weather->dt) ? (int) $weather->dt : time();
  $updatedAt = formatCityTime($updatedTimestamp, $timezoneOffset, 'd.m.Y, H:i');
}

function e($value) {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="<?php echo e($uiLanguage); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e(t('page_title', $translations, $uiLanguage)); ?></title>
  <script>
    const savedTheme = localStorage.getItem('weatherTheme') || 'dark';
    document.documentElement.dataset.theme = savedTheme;
  </script>
  <link rel="stylesheet" href="<?php echo e(assetPath('style.css')); ?>">
</head>
<body>
  <div class="matrix-rain" id="matrix-rain" aria-hidden="true"></div>
  <div class="top-utility-bar">
    <div class="utility-inner">
      <div class="display-controls">
        <div class="mini-control">
          <span class="control-label inline-label"><?php echo e(t('theme', $translations, $uiLanguage)); ?></span>
          <div class="field-wrap field-select custom-select compact-select inline-select" id="theme-select-wrap">
            <input type="hidden" id="theme-select" value="dark">
            <button
              type="button"
              class="select-trigger compact-trigger"
              id="theme-select-trigger"
              aria-haspopup="listbox"
              aria-expanded="false"
              aria-controls="theme-select-list"
            >
              <span id="theme-select-label"><?php echo e($themeOptions['dark']); ?></span>
            </button>
            <div class="select-overlay compact-overlay" id="theme-select-list" role="listbox" hidden>
              <?php foreach ($themeOptions as $themeValue => $themeLabel): ?>
                <button
                  type="button"
                  class="select-option compact-option<?php echo $themeValue === 'dark' ? ' is-selected' : ''; ?>"
                  data-value="<?php echo e($themeValue); ?>"
                  role="option"
                  aria-selected="<?php echo $themeValue === 'dark' ? 'true' : 'false'; ?>"
                >
                  <?php echo e($themeLabel); ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="mini-control">
          <span class="control-label inline-label"><?php echo e(t('language', $translations, $uiLanguage)); ?></span>
          <div class="field-wrap field-select custom-select compact-select inline-select" id="language-select-wrap">
            <button
              type="button"
              class="select-trigger compact-trigger"
              id="language-select-trigger"
              aria-haspopup="listbox"
              aria-expanded="false"
              aria-controls="language-select-list"
            >
              <span id="language-select-label"><?php echo e($languageOptions[$uiLanguage]); ?></span>
            </button>
            <div class="select-overlay compact-overlay" id="language-select-list" role="listbox" hidden>
              <?php foreach ($languageOptions as $languageValue => $languageLabel): ?>
                <button
                  type="button"
                  class="select-option compact-option<?php echo $languageValue === $uiLanguage ? ' is-selected' : ''; ?>"
                  data-value="<?php echo e($languageValue); ?>"
                  role="option"
                  aria-selected="<?php echo $languageValue === $uiLanguage ? 'true' : 'false'; ?>"
                >
                  <?php echo e($languageLabel); ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <main class="page-shell">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow"><?php echo e(t('eyebrow', $translations, $uiLanguage)); ?></p>
        <h1><?php echo e(t('headline', $translations, $uiLanguage)); ?></h1>
        <p class="intro">
          <?php echo e(t('intro', $translations, $uiLanguage)); ?>
        </p>
      </div>

      <form action="<?php echo e(assetPath('index.php')); ?>" method="post" class="search-panel" id="weather-form">
        <input type="hidden" name="ui_lang" id="ui-lang" value="<?php echo e($uiLanguage); ?>">

          <div class="field-group">
            <label for="location-select"><?php echo e(t('location_select', $translations, $uiLanguage)); ?></label>
            <div class="field-wrap field-select custom-select" id="location-select-wrap">
              <input type="hidden" name="location_select" id="location-select" value="<?php echo e($selectedLocation); ?>">
              <button
                type="button"
                class="select-trigger"
                id="location-select-trigger"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="location-select-list"
              >
                <span id="location-select-label"><?php echo e($selectedLocation); ?></span>
              </button>
              <div class="select-overlay" id="location-select-list" role="listbox" hidden>
                <?php foreach ($cities as $city): ?>
                  <button
                    type="button"
                    class="select-option<?php echo $selectedLocation === $city ? ' is-selected' : ''; ?>"
                    data-value="<?php echo e($city); ?>"
                    role="option"
                    aria-selected="<?php echo $selectedLocation === $city ? 'true' : 'false'; ?>"
                  >
                    <?php echo e($city); ?>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="field-group">
            <label for="location-search"><?php echo e(t('location_search', $translations, $uiLanguage)); ?></label>
            <div class="field-wrap search-field-wrap">
              <input
                type="text"
                name="location_search"
                id="location-search"
                value="<?php echo e($searchInputValue); ?>"
                placeholder="<?php echo e(t('search_placeholder', $translations, $uiLanguage)); ?>"
                autocomplete="off"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="location-suggestions"
              >
              <button type="submit" class="search-submit" aria-label="<?php echo e(t('submit', $translations, $uiLanguage)); ?>">
                <span aria-hidden="true">⌕</span>
              </button>
              <div class="suggestions-panel" id="location-suggestions" hidden></div>
            </div>
          </div>
      </form>
    </section>

    <?php if ($errorMessage !== null): ?>
      <section class="status-card">
        <p class="status-label"><?php echo e(t('status_unavailable', $translations, $uiLanguage)); ?></p>
        <h2><?php echo e(t('status_error_title', $translations, $uiLanguage)); ?></h2>
        <p><?php echo e($errorMessage); ?></p>
      </section>
    <?php else: ?>
      <section class="weather-overview">
        <article class="current-card">
          <div class="current-header">
            <div>
              <p class="status-label"><?php echo e(t('current_status', $translations, $uiLanguage)); ?></p>
              <h2><?php echo e($weather->name); ?></h2>
              <p class="subtle-text"><?php echo e(t('updated_at', $translations, $uiLanguage)); ?> <?php echo e($updatedAt); ?><?php echo $uiLanguage === 'de' ? ' Uhr' : ''; ?></p>
            </div>
            <?php if ($currentIconUrl !== ''): ?>
              <img class="weather-icon weather-icon-current" src="<?php echo e(assetPath($currentIconUrl)); ?>" alt="<?php echo e($weatherDescription); ?>">
            <?php else: ?>
              <div class="weather-symbol" aria-hidden="true"><?php echo e($currentSymbol); ?></div>
            <?php endif; ?>
          </div>

          <div class="temperature-row">
            <div class="temperature-block">
              <span class="temperature-value"><?php echo e(round((float) $weather->main->temp)); ?>°</span>
              <span class="temperature-unit"><?php echo e(t('celsius', $translations, $uiLanguage)); ?></span>
            </div>
            <p class="weather-description"><?php echo e(formatUiText($weatherDescription)); ?></p>
          </div>

          <div class="metrics-grid">
            <?php foreach ($currentMetrics as $metric): ?>
              <div class="metric-card">
                <span><?php echo e($metric['label']); ?></span>
                <strong><?php echo e($metric['value']); ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        </article>

        <article class="forecast-card">
          <div class="section-header">
            <div>
              <p class="status-label"><?php echo e(t('next_days', $translations, $uiLanguage)); ?></p>
              <h2><?php echo e(t('forecast_title', $translations, $uiLanguage)); ?></h2>
            </div>
            <p class="subtle-text"><?php echo e(t('forecast_subtitle', $translations, $uiLanguage)); ?></p>
          </div>

          <div class="daily-grid">
            <?php foreach ($dailyForecasts as $day): ?>
              <article class="day-card">
                <p class="day-title"><?php echo e($day['weekday']); ?></p>
                <p class="day-date"><?php echo e($day['date_label']); ?></p>
                <?php if (!empty($day['icon_url'])): ?>
                  <img class="weather-icon weather-icon-day" src="<?php echo e(assetPath($day['icon_url'])); ?>" alt="<?php echo e($day['description']); ?>">
                <?php else: ?>
                  <div class="day-symbol" aria-hidden="true"><?php echo e($day['symbol']); ?></div>
                <?php endif; ?>
                <p class="day-temp"><?php echo e($day['temp_max']); ?>° <span>/ <?php echo e($day['temp_min']); ?>°</span></p>
                <p class="day-description"><?php echo e(formatUiText($day['description'])); ?></p>
              </article>
            <?php endforeach; ?>
          </div>

          <details class="details-panel">
            <summary><?php echo e(t('details_summary', $translations, $uiLanguage)); ?></summary>
            <div class="details-grid">
              <?php foreach ($forecastEntries as $entry): ?>
                <article class="detail-card">
                  <div class="detail-head">
                    <p><?php echo e($entry['weekday']); ?>, <?php echo e($entry['date']); ?></p>
                    <strong><?php echo e($entry['time']); ?> Uhr</strong>
                  </div>
                  <div class="detail-body">
                    <?php if (!empty($entry['icon_url'])): ?>
                      <img class="weather-icon weather-icon-detail" src="<?php echo e(assetPath($entry['icon_url'])); ?>" alt="<?php echo e($entry['description']); ?>">
                    <?php else: ?>
                      <span class="detail-symbol" aria-hidden="true"><?php echo e($entry['symbol']); ?></span>
                    <?php endif; ?>
                    <div>
                      <p class="detail-temp"><?php echo e($entry['temperature']); ?>°C</p>
                      <p class="detail-description"><?php echo e(formatUiText($entry['description'])); ?></p>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </details>
        </article>
      </section>
    <?php endif; ?>
  </main>

  <script>
    const form = document.getElementById('weather-form');
    const matrixRain = document.getElementById('matrix-rain');
    const search = document.getElementById('location-search');
    const suggestionsPanel = document.getElementById('location-suggestions');
    let activeRequest = null;

    function setupOverlaySelect(config) {
      const input = document.getElementById(config.inputId);
      const trigger = document.getElementById(config.triggerId);
      const label = document.getElementById(config.labelId);
      const overlay = document.getElementById(config.overlayId);
      const options = Array.from(overlay.querySelectorAll('.select-option'));

      function close() {
        overlay.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
      }

      function open() {
        overlay.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
      }

      function setValue(value, displayValue = value) {
        input.value = value;
        label.textContent = displayValue;

        options.forEach((option) => {
          const isSelected = option.dataset.value === value;
          option.classList.toggle('is-selected', isSelected);
          option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        });
      }

      trigger.addEventListener('click', () => {
        if (overlay.hidden) {
          open();
        } else {
          close();
        }
      });

      options.forEach((option) => {
        option.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          setValue(option.dataset.value, option.textContent.trim());
          close();
          if (typeof config.onSelect === 'function') {
            config.onSelect(option.dataset.value, option.textContent.trim());
          }
        });
      });

      return { close, open, setValue, overlay, trigger };
    }

    const locationSelect = setupOverlaySelect({
      inputId: 'location-select',
      triggerId: 'location-select-trigger',
      labelId: 'location-select-label',
      overlayId: 'location-select-list',
      onSelect(value) {
        search.value = '';
        window.setTimeout(() => {
          form.requestSubmit();
        }, 0);
      }
    });

    const themeLabels = <?php echo json_encode($themeOptions, JSON_UNESCAPED_UNICODE); ?>;
    const languageSelect = setupOverlaySelect({
      inputId: 'ui-lang',
      triggerId: 'language-select-trigger',
      labelId: 'language-select-label',
      overlayId: 'language-select-list',
      onSelect() {
        form.requestSubmit();
      }
    });

    const themeSelect = setupOverlaySelect({
      inputId: 'theme-select',
      triggerId: 'theme-select-trigger',
      labelId: 'theme-select-label',
      overlayId: 'theme-select-list',
      onSelect(value) {
        localStorage.setItem('weatherTheme', value);
        document.documentElement.dataset.theme = value;
        syncMatrixRain();
      }
    });

    const initialTheme = localStorage.getItem('weatherTheme') || 'dark';
    themeSelect.setValue(initialTheme, themeLabels[initialTheme] || themeLabels.light);
    document.documentElement.dataset.theme = initialTheme;

    const matrixChars = ['0', '1', 'ア', 'ｦ', 'ｶ', 'ﾅ', 'ﾏ', 'ｻ', 'X', 'Z', '+', '@'];
    let matrixBuiltForWidth = 0;

    function buildMatrixRain() {
      const width = window.innerWidth;
      if (!matrixRain || Math.abs(width - matrixBuiltForWidth) < 80) {
        return;
      }

      matrixBuiltForWidth = width;
      const columnCount = Math.max(12, Math.floor(width / 38));
      matrixRain.innerHTML = '';

      for (let i = 0; i < columnCount; i += 1) {
        const column = document.createElement('span');
        column.className = 'matrix-column';
        column.textContent = Array.from({ length: 24 }, () => matrixChars[Math.floor(Math.random() * matrixChars.length)]).join('\n');
        column.style.left = `${(i / columnCount) * 100}%`;
        column.style.animationDelay = `${-Math.random() * 24}s`;
        column.style.animationDuration = `${16 + Math.random() * 16}s`;
        column.style.opacity = `${0.28 + Math.random() * 0.38}`;
        matrixRain.appendChild(column);
      }
    }

    function syncMatrixRain() {
      const isMatrix = document.documentElement.dataset.theme === 'matrix';
      if (!matrixRain) {
        return;
      }

      if (isMatrix) {
        buildMatrixRain();
        matrixRain.hidden = false;
      } else {
        matrixRain.hidden = true;
      }
    }

    window.addEventListener('resize', () => {
      if (document.documentElement.dataset.theme === 'matrix') {
        buildMatrixRain();
      }
    });

    syncMatrixRain();

    function hideSuggestions() {
      suggestionsPanel.hidden = true;
      suggestionsPanel.innerHTML = '';
      search.setAttribute('aria-expanded', 'false');
    }

    function showSuggestions(items) {
      if (!items.length) {
        hideSuggestions();
        return;
      }

      suggestionsPanel.innerHTML = '';
      items.forEach((item) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'suggestion-item';
        button.textContent = item;
        button.addEventListener('click', () => {
          search.value = item;
          hideSuggestions();
          window.setTimeout(() => {
            form.requestSubmit();
          }, 0);
        });
        suggestionsPanel.appendChild(button);
      });

      suggestionsPanel.hidden = false;
      search.setAttribute('aria-expanded', 'true');
    }

    search.addEventListener('input', async () => {
      const query = search.value.trim();

      if (query.length < 2) {
        hideSuggestions();
        return;
      }

      if (activeRequest) {
        activeRequest.abort();
      }

      activeRequest = new AbortController();

      try {
        const response = await fetch(`<?php echo e(assetPath('index.php')); ?>?action=suggest&q=${encodeURIComponent(query)}`, {
          signal: activeRequest.signal
        });

        if (!response.ok) {
          hideSuggestions();
          return;
        }

        const items = await response.json();
        showSuggestions(Array.isArray(items) ? items : []);
      } catch (error) {
        if (error.name !== 'AbortError') {
          hideSuggestions();
        }
      }
    });

    form.addEventListener('submit', () => {
      if (search.value.trim() === '') {
        search.value = document.getElementById('location-select').value;
      }
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('.field-wrap')) {
        hideSuggestions();
      }

      if (!event.target.closest('.custom-select')) {
        locationSelect.close();
        themeSelect.close();
        languageSelect.close();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        locationSelect.close();
        themeSelect.close();
        languageSelect.close();
        hideSuggestions();
      }
    });
  </script>
</body>
</html>
