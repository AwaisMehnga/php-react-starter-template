<?php
require_once __DIR__ . '/../../app/Core/Cache.php';

function collectAssets($manifest, $entryKey, &$js = [], &$css = [], &$visited = []) {
    if (!isset($manifest[$entryKey]) || isset($visited[$entryKey])) return;
    $visited[$entryKey] = true;

    $entry = $manifest[$entryKey];

    if (isset($entry['file']) && !in_array($entry['file'], $js)) {
        $js[] = $entry['file'];
    }

    if (isset($entry['css'])) {
        foreach ($entry['css'] as $cssFile) {
            if (!in_array($cssFile, $css)) {
                $css[] = $cssFile;
            }
        }
    }

    $allImports = array_merge($entry['imports'] ?? [], $entry['dynamicImports'] ?? []);
    foreach ($allImports as $importKey) {
        collectAssets($manifest, $importKey, $js, $css, $visited);
    }
}

// ENV & Inputs

$isDev = true;

var_dump($isDev);

$spaPath = $spa_path ?? null;
$reactRootId = $react_root_id ?? 'react-root';

if (!$spaPath) {
    echo "<!-- No SPA path provided -->";
    return;
}

$manifestPath = 'build/.vite/manifest.json';
if (!file_exists($manifestPath)) {
    echo "<!-- No manifest found -->";
    return;
}

$manifest = json_decode(file_get_contents($manifestPath), true);
$entry = $manifest[$spaPath] ?? null;
if (!$entry) {
    echo "<!-- No entry found for $spaPath -->";
    return;
}

// Cache Setup (only for production)
$jsFiles = [];
$cssFiles = [];

if ($isDev) {
    // Always live collect in dev
    $visited = [];
    collectAssets($manifest, $spaPath, $jsFiles, $cssFiles, $visited);
} else {
    $manifestModified = filemtime($manifestPath);
    $cacheKey = "react_assets_{$spaPath}_{$manifestModified}";
    
    $cachedData = Cache::get($cacheKey);
    if ($cachedData) {
        [$jsFiles, $cssFiles] = $cachedData;
    } else {
        $visited = [];
        collectAssets($manifest, $spaPath, $jsFiles, $cssFiles, $visited);
        Cache::set($cacheKey, [$jsFiles, $cssFiles]);
    }
}
?>

<!-- React Mount Point -->
<div id="<?= htmlspecialchars($reactRootId) ?>"></div>

<?php if ($isDev): ?>
     <script type="module">
        import RefreshRuntime from "http://localhost:3000/@react-refresh";
        RefreshRuntime.injectIntoGlobalHook(window);
        window.$RefreshReg$ = () => {};
        window.$RefreshSig$ = () => (type) => type;
        window.__vite_plugin_react_preamble_installed__ = true;
    </script>
    <script type="module" src="http://localhost:3000/@vite/client"></script>
    <script type="module" src="http://localhost:3000/<?= $spaPath ?>"></script>
<?php else: ?>
    <?php foreach ($cssFiles as $css): ?>
        <link rel="stylesheet" href="/build/<?= htmlspecialchars($css) ?>">
    <?php endforeach; ?>
    <?php foreach ($jsFiles as $js): ?>
        <script type="module" src="/build/<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
