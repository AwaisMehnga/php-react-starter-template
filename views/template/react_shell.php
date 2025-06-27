<?php
// Load application configuration
$config = require __DIR__ . '/../../config/app.php';

// Determine environment
$isDev = $config['env'] === 'dev';
$devServer = $config['frontend']['dev_server'];
$buildPath = $config['frontend']['build_path'];
?>

<div id="root"></div>

<?php if ($isDev): ?>
  <!-- Development mode (Vite HMR) -->
  <script type="module">
    import RefreshRuntime from "<?= $devServer ?>/@react-refresh"
    RefreshRuntime.injectIntoGlobalHook(window)
    window.$RefreshReg$ = () => { }
    window.$RefreshSig$ = () => (type) => type
    window.__vite_plugin_react_preamble_installed__ = true
  </script>

  <script type="module" src="<?= $devServer ?>/@vite/client"></script>
  <script type="module" src="<?= $devServer ?>/src/main.jsx"></script>

<?php else: ?>
  <!-- Production build -->
  <link rel="stylesheet" href="<?= $buildPath ?>/style.css" />
  <script type="module" src="<?= $buildPath ?>/main.js"></script>
<?php endif; ?>