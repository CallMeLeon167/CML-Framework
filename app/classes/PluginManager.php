<?php

namespace CML\Classes;

class PluginManager
{
    use Functions\Functions;

    /**
     * @var array $plugins An array to store the plugins.
     */
    protected array $plugins = [];

    /**
     * @var Router $app The application's router.
     */
    protected Router $app;

    /**
     * PluginManager constructor.
     *
     * @param Router $app The Router instance.
     */
    public function __construct(Router $app)
    {
        $this->app = $app;
        $this->loadPlugins($this->getRootPath('/plugins'));
        $this->registerPlugins();
    }

    /**
     * Loads the plugins from the specified plugin directory.
     *
     * @param string $pluginDir The directory path where the plugins are located.
     */
    private function loadPlugins(string $pluginDir)
    {
        $pluginFiles = $this->getPluginFiles($pluginDir);
        foreach ($pluginFiles as $pluginFile) {
            require_once $pluginFile;
            $className = basename($pluginFile, '.php');
            if (class_exists($className)) {
                $plugin = new $className();
                if (method_exists($plugin, 'register')) {
                    $this->plugins[] = $plugin;
                    $this->extractPluginHeader($pluginFile);
                }
            }
        }
    }

    /**
     * Recursively retrieves all PHP files within a directory.
     *
     * @param string $dir The directory to search for PHP files.
     * @return array An array of PHP file paths.
     */
    private function getPluginFiles(string $dir): array
    {
        $files = [];
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $files = array_merge($files, $this->getPluginFiles($file));
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * Extracts the plugin header information from a plugin file and adds it to the application's plugin info.
     *
     * @param string $pluginFile The path to the plugin file.
     */
    private function extractPluginHeader(string $pluginFile)
    {
        $content = file_get_contents($pluginFile);
        preg_match('/\*\s*Plugin Name:\s*(.*)/', $content, $name);
        preg_match('/\*\s*Version:\s*(.*)/', $content, $version);
        preg_match('/\*\s*Description:\s*(.*)/', $content, $description);

        $pluginInfo = [
            'name' => $name[1] ?? 'Unknown',
            'version' => $version[1] ?? 'Unknown',
            'description' => $description[1] ?? 'No description'
        ];

        $this->app->addPluginInfo($pluginInfo);
    }

    /**
     * Registers all the plugins in the PluginManager.
     *
     * This method iterates over the plugins array and calls the register() method on each plugin,
     * passing the application instance and the PluginManager instance as arguments.
     */
    private function registerPlugins()
    {
        foreach ($this->plugins as $plugin) {
            $plugin->register($this->app, $this);
        }
    }
}
