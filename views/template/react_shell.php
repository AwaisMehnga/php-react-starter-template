<?php
putenv("APP_ENV=prod");
$_ENV["APP_ENV"] = "prod";
$isDev = ($_ENV['APP_ENV'] ?? 'prod') === 'dev';
?>

<div id="<?= $reactRootId ?>"></div>

<?php if ($isDev): ?>
    <script type="module">
        import RefreshRuntime from "http://localhost:3000/@react-refresh";
        RefreshRuntime.injectIntoGlobalHook(window);
        window.$RefreshReg$ = () => {};
        window.$RefreshSig$ = () => (type) => type;
        window.__vite_plugin_react_preamble_installed__ = true;
    </script>
    <script type="module" src="http://localhost:3000/@vite/client"></script>
    <script type="module" src="http://localhost:3000/modules/<?= $spaName ?>/app.jsx"></script>
<?php else: ?>
    <link rel="stylesheet" href="/build/<?= $spaName ?>/style.css" />
    <script type="module" src="/build/<?= $spaName ?>/main.js"></script>
<?php endif; ?>
