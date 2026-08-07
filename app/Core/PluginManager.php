<?php
namespace Neo\Core;

class PluginManager
{
    protected $app;
    protected $plugins = [];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Load và Boot plugins
     */
    public function loadPlugins($dir)
    {
        if (!is_dir($dir))
            return;

        $folders = scandir($dir);

        // Phase 1: Discovery & Register
        foreach ($folders as $folder) {
            if ($folder == "." || $folder == "..")
                continue;

            $pluginPath = $dir . "/" . $folder;
            $entryFile = $pluginPath . "/index.php";
            $jsonFile = $pluginPath . "/plugin.json";

            // Support Old Way (index.php only)
            if (file_exists($entryFile) && !file_exists($jsonFile)) {
                $this->registerLegacy($entryFile);
                continue;
            }

            // Support New Way (plugin.json + Class)
            if (file_exists($jsonFile)) {
                $this->registerPlugin($pluginPath, $jsonFile);
            }
        }

        // Phase 2: Boot
        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, 'boot')) {
                $plugin->boot();
            }
        }
    }

    protected function registerLegacy($file)
    {
        $app = $this->app; // Scope cho require
        require_once $file;
    }

    protected function registerPlugin($path, $jsonFile)
    {
        $meta = json_decode(file_get_contents($jsonFile), true);
        if (!$meta || !isset($meta['entry']))
            return;

        $entryClass = $meta['entry']; // Namespace\To\Class
        $file = $path . '/' . ($meta['file'] ?? 'Plugin.php');

        if (file_exists($file)) {
            require_once $file;
            if (class_exists($entryClass)) {
                $instance = new $entryClass($this->app);
                $this->plugins[$meta['name'] ?? $entryClass] = $instance;

                if (method_exists($instance, 'register')) {
                    $instance->register();
                }
            }
        }
    }
}
