<?php
// Load .env manually if needed
// require_once __DIR__ . '/../vendor/autoload.php';
// Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();
putenv("APP_ENV=dev");
$_ENV["APP_ENV"] = "dev";

$isDev = ($_ENV['APP_ENV'] ?? 'prod') === 'dev';
?>


<div id="root"></div>

<?php if ($isDev): ?>
  <!-- Development mode (Vite HMR) -->
   <script type="module">
    import RefreshRuntime from "http://localhost:3000/@react-refresh"
    RefreshRuntime.injectIntoGlobalHook(window)
    window.$RefreshReg$ = () => {}
    window.$RefreshSig$ = () => (type) => type
    window.__vite_plugin_react_preamble_installed__ = true
</script>
  <script type="module" src="http://localhost:3000/@vite/client"></script>
<script type="module" src="http://localhost:3000/src/main.jsx"></script>

<?php else: ?>
  <!-- Production build -->
  <link rel="stylesheet" href="/build/style.css" />
  <script type="module" src="/build/main.js"></script>
<?php endif; ?>
