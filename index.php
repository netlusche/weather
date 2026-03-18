<?php
require_once 'getweather.php';

if (isset($_GET['action']) && $_GET['action'] === 'suggest') {
  header('Content-Type: application/json; charset=utf-8');
  $query = isset($_GET['q']) ? (string) $_GET['q'] : '';
  echo json_encode(getLocationSuggestions($apiKey, $query), JSON_UNESCAPED_UNICODE);
  exit;
}

$cities = getCities();
$searchInputValue = getSearchInputValue();
$selectedLocation = resolveLocation($cities);
$weatherResult = getWeather($apiKey, $selectedLocation);
$forecastResult = getForecast($selectedLocation, $apiKey);
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
  $currentMetrics = getCurrentWeatherMetrics($weather);
  $dailyForecasts = getDailyForecastSummaries($forecast, 5);
  $forecastEntries = getForecastEntries($forecast);
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
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Wetterabfrage</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="page-shell">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Wetterübersicht</p>
        <h1>Wetterabfrage</h1>
        <p class="intro">
          Wähle einen Ort per Select oder tippe im Suchfeld los. Standardmäßig bekommst du eine kompakte 5-Tage-Übersicht und kannst die komplette 3-Stunden-Vorhersage bei Bedarf erweitern.
        </p>
      </div>

      <form action="index.php" method="post" class="search-panel" id="weather-form">
        <div class="field-group">
          <label for="location-select">Ort auswählen</label>
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
          <label for="location-search">Ort suchen</label>
          <div class="field-wrap">
            <input
              type="text"
              name="location_search"
              id="location-search"
              value="<?php echo e($searchInputValue); ?>"
              placeholder="Ort eingeben"
              autocomplete="off"
              aria-autocomplete="list"
              aria-expanded="false"
              aria-controls="location-suggestions"
            >
            <div class="suggestions-panel" id="location-suggestions" hidden></div>
          </div>
        </div>

        <button type="submit">Wetter anzeigen</button>
      </form>
    </section>

    <?php if ($errorMessage !== null): ?>
      <section class="status-card">
        <p class="status-label">Aktuell nicht verfügbar</p>
        <h2>Die Wetterdaten konnten nicht geladen werden.</h2>
        <p><?php echo e($errorMessage); ?></p>
      </section>
    <?php else: ?>
      <section class="weather-overview">
        <article class="current-card">
          <div class="current-header">
            <div>
              <p class="status-label">Aktueller Stand</p>
              <h2><?php echo e($weather->name); ?></h2>
              <p class="subtle-text">Aktualisiert am <?php echo e($updatedAt); ?> Uhr</p>
            </div>
            <?php if ($currentIconUrl !== ''): ?>
              <img class="weather-icon weather-icon-current" src="<?php echo e($currentIconUrl); ?>" alt="<?php echo e($weatherDescription); ?>">
            <?php else: ?>
              <div class="weather-symbol" aria-hidden="true"><?php echo e($currentSymbol); ?></div>
            <?php endif; ?>
          </div>

          <div class="temperature-row">
            <div class="temperature-block">
              <span class="temperature-value"><?php echo e(round((float) $weather->main->temp)); ?>°</span>
              <span class="temperature-unit">Celsius</span>
            </div>
            <p class="weather-description"><?php echo e(ucfirst($weatherDescription)); ?></p>
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
              <p class="status-label">Nächste Tage</p>
              <h2>5-Tage-Übersicht</h2>
            </div>
            <p class="subtle-text">Kompakt und schnell lesbar</p>
          </div>

          <div class="daily-grid">
            <?php foreach ($dailyForecasts as $day): ?>
              <article class="day-card">
                <p class="day-title"><?php echo e($day['weekday']); ?></p>
                <p class="day-date"><?php echo e($day['date_label']); ?></p>
                <?php if (!empty($day['icon_url'])): ?>
                  <img class="weather-icon weather-icon-day" src="<?php echo e($day['icon_url']); ?>" alt="<?php echo e($day['description']); ?>">
                <?php else: ?>
                  <div class="day-symbol" aria-hidden="true"><?php echo e($day['symbol']); ?></div>
                <?php endif; ?>
                <p class="day-temp"><?php echo e($day['temp_max']); ?>° <span>/ <?php echo e($day['temp_min']); ?>°</span></p>
                <p class="day-description"><?php echo e(ucfirst($day['description'])); ?></p>
              </article>
            <?php endforeach; ?>
          </div>

          <details class="details-panel">
            <summary>Komplette 3-Stunden-Vorhersage anzeigen</summary>
            <div class="details-grid">
              <?php foreach ($forecastEntries as $entry): ?>
                <article class="detail-card">
                  <div class="detail-head">
                    <p><?php echo e($entry['weekday']); ?>, <?php echo e($entry['date']); ?></p>
                    <strong><?php echo e($entry['time']); ?> Uhr</strong>
                  </div>
                  <div class="detail-body">
                    <?php if (!empty($entry['icon_url'])): ?>
                      <img class="weather-icon weather-icon-detail" src="<?php echo e($entry['icon_url']); ?>" alt="<?php echo e($entry['description']); ?>">
                    <?php else: ?>
                      <span class="detail-symbol" aria-hidden="true"><?php echo e($entry['symbol']); ?></span>
                    <?php endif; ?>
                    <div>
                      <p class="detail-temp"><?php echo e($entry['temperature']); ?>°C</p>
                      <p class="detail-description"><?php echo e(ucfirst($entry['description'])); ?></p>
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
    const selectInput = document.getElementById('location-select');
    const selectTrigger = document.getElementById('location-select-trigger');
    const selectLabel = document.getElementById('location-select-label');
    const selectOverlay = document.getElementById('location-select-list');
    const selectOptions = Array.from(document.querySelectorAll('.select-option'));
    const search = document.getElementById('location-search');
    const suggestionsPanel = document.getElementById('location-suggestions');
    let activeRequest = null;

    function closeSelectOverlay() {
      selectOverlay.hidden = true;
      selectTrigger.setAttribute('aria-expanded', 'false');
    }

    function openSelectOverlay() {
      selectOverlay.hidden = false;
      selectTrigger.setAttribute('aria-expanded', 'true');
    }

    function setSelectedOption(value) {
      selectInput.value = value;
      selectLabel.textContent = value;

      selectOptions.forEach((option) => {
        const isSelected = option.dataset.value === value;
        option.classList.toggle('is-selected', isSelected);
        option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
      });
    }

    selectTrigger.addEventListener('click', () => {
      if (selectOverlay.hidden) {
        openSelectOverlay();
      } else {
        closeSelectOverlay();
      }
    });

    selectOptions.forEach((option) => {
      option.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        setSelectedOption(option.dataset.value);
        search.value = '';
        closeSelectOverlay();
        window.setTimeout(() => {
          form.requestSubmit();
        }, 0);
      });
    });

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
        const response = await fetch(`index.php?action=suggest&q=${encodeURIComponent(query)}`, {
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
        search.value = selectInput.value;
      }
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('.field-wrap')) {
        hideSuggestions();
      }

      if (!event.target.closest('.custom-select')) {
        closeSelectOverlay();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeSelectOverlay();
        hideSuggestions();
      }
    });
  </script>
</body>
</html>
