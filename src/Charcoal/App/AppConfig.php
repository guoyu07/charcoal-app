<?php

namespace Charcoal\App;

use Exception;
use InvalidArgumentException;

use Charcoal\Config\AbstractConfig;

use Charcoal\App\Config\CacheConfig;
use Charcoal\App\Config\FilesystemConfig;
use Charcoal\App\Config\LoggerConfig;

/**
 * Charcoal App configuration
 */
class AppConfig extends AbstractConfig
{
    /**
     * @var string $timezone
     */
    private $timezone;

    /**
     * The application's name.
     *
     * @var string $projectName
     */
    private $projectName;

    /**
     * The base path for the Charcoal installation.
     *
     * @var string $basePath
     */
    private $basePath;

    /**
     * The base URL (public) for the Charcoal installation.
     *
     * @var string $baseUrl
     */
    private $baseUrl;

    /**
     * The path to the public / web directory.
     *
     * @var string $publicPath
     */
    private $publicPath;

    /**
     * The path to the storage directory.
     *
     * @var string $storagePath
     */
    private $storagePath;

    /**
     * @var boolean $devMode
     */
    private $devMode = false;

    /**
     * @var array $routes
     */
    private $routes = [];

    /**
     * @var array $routables
     */
    private $routables = [];

    /**
     * @var array $handlers
     */
    private $handlers = [];

    /**
     * @var array $modules
     */
    private $modules = [];

    /**
     * @var CacheConfig $cache
     */
    private $cache;

    /**
     * @var LoggerConfig $logger
     */
    private $logger;

    /**
     * @var ViewConfig $view
     */
    protected $view;

    /**
     * @var array $databases
     */
    private $databases = [];

    /**
     * @var string $defaultDatabase
     */
    private $defaultDatabase;

    /**
     * @var array $filesystem
     */
    private $filesystem;

    /**
     * Default app-config values.
     *
     * @return array
     */
    public function defaults()
    {
        /** @var string $baseDir Presume that Charcoal App _is_ the application */
        $baseDir = rtrim(realpath(__DIR__.'/../../../'), '/').'/';

        return [
            'project_name'     => '',
            'base_path'        => $baseDir,
            'public_path'      => null,
            'storage_path'     => null,
            'timezone'         => 'UTC',
            'routes'           => [],
            'routables'        => [],
            'handlers'         => [],
            'modules'          => [],
            'translator'       => [],
            'cache'            => [],
            'logger'           => [],
            'view'             => [],
            'databases'        => [],
            'default_database' => 'default',
            'dev_mode'         => false
        ];
    }

    /**
     * Set the application's absolute root path.
     *
     * Resolves symlinks with realpath() and ensure trailing slash.
     *
     * @deprecated In favor of {self::setBasePath()}
     * @param  string $path The absolute path to the application's root directory.
     * @return AppConfig Chainable
     */
    public function setROOT($path)
    {
        trigger_error('setROOT() is deprecated. Use setBasePath() instead.', E_USER_DEPRECATED);

        $this->setBasePath($path);

        return $this;
    }

    /**
     * Retrieve the application's absolute root path.
     *
     * @deprecated In favor of {self::basePath()}
     * @return string The absolute path to the application's root directory.
     */
    public function ROOT()
    {
        trigger_error('ROOT() is deprecated. Use basePath() instead.', E_USER_DEPRECATED);

        return $this->basePath();
    }

    /**
     * Set the application's fully qualified base URL to the public web directory.
     *
     * @deprecated In favor of {self::setBaseUrl()}
     * @param  string $uri The base URI to the application's web directory.
     * @return AppConfig Chainable
     */
    public function setURL($uri)
    {
        trigger_error('setURL() is deprecated. Use setBaseUrl() instead.', E_USER_DEPRECATED);

        $this->setBaseUrl($uri);

        return $this;
    }

    /**
     * Retrieve the application's fully qualified base URL to the public web directory.
     *
     * @deprecated In favor of {self::baseUrl()}
     * @return string The base URI to the application's web directory.
     */
    public function URL()
    {
        trigger_error('URL() is deprecated. Use baseUrl() instead.', E_USER_DEPRECATED);

        return $this->baseUrl();
    }

    /**
     * Set the application's absolute root path.
     *
     * Resolves symlinks with realpath() and ensure trailing slash.
     *
     * @param  string $path The absolute path to the application's root directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return AppConfig Chainable
     */
    public function setBasePath($path)
    {
        if ($path === null) {
            throw new InvalidArgumentException(
                'The base path is required.'
            );
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The base path must be a string'
            );
        }

        $this->basePath = rtrim(realpath($path), '\\/').DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Retrieve the application's absolute root path.
     *
     * @return string The absolute path to the application's root directory.
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Set the application's absolute path to the public web directory.
     *
     * @param  string $path The path to the application's public directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return AppConfig Chainable
     */
    public function setPublicPath($path)
    {
        if ($path === null) {
            $this->publicPath = null;

            return $this;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The public path must be a string'
            );
        }

        $this->publicPath = rtrim(realpath($path), '\\/').DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Retrieve the application's absolute path to the public web directory.
     *
     * @return string The absolute path to the application's public directory.
     */
    public function publicPath()
    {
        if (!isset($this->publicPath)) {
            return $this->basePath().'www'.DIRECTORY_SEPARATOR;
        }

        return $this->publicPath;
    }

    /**
     * Set the application's absolute path to the storage directory.
     *
     * @param  string|null $path The path to the application's storage directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return $this
     */
    public function setStoragePath($path)
    {
        if ($path === null) {
            $this->storagePath = null;

            return $this;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The storage path must be a string'
            );
        }

        $this->storagePath = rtrim(realpath($path), '\\/').DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Get the path to the storage directory.
     *
     * Note that the storage space is outside of the public access.
     *
     * @return string
     */
    public function storagePath()
    {
        if (!isset($this->storagePath)) {
            return $this->basePath().'uploads'.DIRECTORY_SEPARATOR;
        }

        return $this->storagePath;
    }

    /**
     * Set the application's fully qualified base URL to the public web directory.
     *
     * @param  string|\Psr\Http\Message\UriInterface $uri The base URI to the application's web directory.
     * @return AppConfig Chainable
     */
    public function setBaseUrl($uri)
    {
        $this->baseUrl = \Slim\Http\Uri::createFromString($uri);

        return $this;
    }

    /**
     * Retrieve the application's fully qualified base URL to the public web directory.
     *
     * @return string|\Psr\Http\Message\UriInterface The base URI to the application's web directory.
     */
    public function baseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set the application's default timezone.
     *
     * @param  string $timezone The timezone string.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return AppConfig Chainable
     */
    public function setTimezone($timezone)
    {
        if (!is_string($timezone)) {
            throw new InvalidArgumentException(
                'Timezone must be a string.'
            );
        }
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Retrieve the application's default timezone.
     *
     * Will be used by the PHP date and date-time functions.
     *
     * @return string
     */
    public function timezone()
    {
        if (isset($this->timezone)) {
            return $this->timezone;
        } else {
            return 'UTC';
        }
    }

    /**
     * Sets the project name.
     *
     * @param string|null $projectName The project name.
     * @throws InvalidArgumentException If the project argument is not a string (or null).
     * @return AppConfig Chainable
     */
    public function setProjectName($projectName)
    {
        if ($projectName === null) {
            $this->projectName = null;
            return $this;
        }
        if (!is_string($projectName)) {
            throw new InvalidArgumentException(
                'Project name must be a string'
            );
        }
        $this->projectName = $projectName;
        return $this;
    }

    /**
     * @return string
     */
    public function projectName()
    {
        if ($this->projectName === null) {
            return $this->baseUrl();
        }
        return $this->projectName;
    }

    /**
     * @param boolean $devMode The "dev mode" flag.
     * @return AppConfig Chainable
     */
    public function setDevMode($devMode)
    {
        $this->devMode = !!$devMode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function devMode()
    {
        return !!$this->devMode;
    }

    /**
     * @param array $translator The translator configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setTranslator(array $translator)
    {
        if (!isset($this->translator)) {
            $this->translator = [];
        }

        $toIterate = [ 'locales', 'translations' ];
        foreach ($translator as $key => $val) {
            if (in_array($key, $toIterate) && isset($this->translator[$key])) {
                $this->translator[$key] = array_merge($this->translator[$key], $val);
            } else {
                $this->translator[$key] = $val;
            }
        }

        return $this;
    }

    /**
     * @param array $view The view configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setView(array $view)
    {
        if (!isset($this->view)) {
            $this->view = [];
        }

        $this->view = array_merge($this->view, $view);

        return $this;
    }

    /**
     * Parse the application's route configuration.
     *
     * @see    \Charcoal\Admin\Config::setRoutes() For a similar implementation.
     * @param  array $routes The route configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setRoutes(array $routes)
    {
        if (!isset($this->routes)) {
            $this->routes = [];
        }

        $toIterate = [ 'templates', 'actions', 'scripts' ];
        foreach ($routes as $key => $val) {
            if (in_array($key, $toIterate) && isset($this->routes[$key])) {
                $this->routes[$key] = array_merge($this->routes[$key], $val);
            } else {
                $this->routes[$key] = $val;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * @param array $routables The routable configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setRoutables(array $routables)
    {
        $this->routables = $routables;
        return $this;
    }

    /**
     * @return array
     */
    public function routables()
    {
        return $this->routables;
    }

    /**
     * Define custom response and error handlers.
     *
     * Slim provides five standard handlers:
     * - "foundHandler"
     * - "notFoundHandler"
     * - "notAllowedHandler"
     * - "errorHandler"
     * - "phpErrorHandler"
     *
     * @param array $handlers The handlers configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = $handlers;
        return $this;
    }

    /**
     * @return array
     */
    public function handlers()
    {
        return $this->handlers;
    }

    /**
     * Set the configuration modules.
     *
     * The modules are defined in a `key`=>`\Charcoal\App\Module\ModuleConfig` structure.
     *
     * @param array $modules The module configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;
        return $this;
    }

    /**
     * @return array
     */
    public function modules()
    {
        return $this->modules;
    }

    /**
     * @param array|CacheConfig $cache The application global cache config.
     * @throws InvalidArgumentException If the argument is not an array or a config.
     * @return AppConfig Chainable
     */
    public function setCache($cache)
    {
        if ($cache instanceof CacheConfig) {
            $this->cache = $cache;
            $this->cache->addDelegate($this);
        } elseif (is_array($cache)) {
            $this->cache = new CacheConfig($cache, [$this]);
        } else {
            throw new InvalidArgumentException(
                'Cache must be an array of config options or a CacheConfig object.'
            );
        }
        return $this;
    }

    /**
     * Get the application's global `CacheConfig`.
     *
     * @return CacheConfig
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * @param array|LoggerConfig $logger The global logger config.
     * @throws InvalidArgumentException If the argument is not an array or a config.
     * @return AppConfig Chainable
     */
    public function setLogger($logger)
    {
        if ($logger instanceof LoggerConfig) {
            $this->logger = $logger;
            $this->logger->addDelegate($this);
        } elseif (is_array($logger)) {
            $this->logger = new LoggerConfig($logger, [$this]);
        } else {
            throw new InvalidArgumentException(
                'Logger must be an array of config options or a LoggerConfig object.'
            );
        }
        return $this;
    }

    /**
     * Get the application's global `LoggerConfig`
     *
     * @return LoggerConfig
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * @param array $databases The avaiable databases config.
     * @return Config Chainable
     */
    public function setDatabases(array $databases)
    {
        $this->databases = $databases;
        return $this;
    }

    /**
     * @throws Exception If trying to access this method and no databases were set.
     * @return array
     */
    public function databases()
    {
        if ($this->databases === null) {
            throw new Exception(
                'Invalid app config: Databases are not set.'
            );
        }
        return $this->databases;
    }

    /**
     * @param string $ident The ident of the database to return the configuration of.
     * @throws InvalidArgumentException If the ident argument is not a string.
     * @throws Exception If trying to access an invalid database.
     * @return array
     */
    public function databaseConfig($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Invalid app config: default database must be a string.'
            );
        }
        $databases = $this->databases();
        if (!isset($databases[$ident])) {
            throw new Exception(
                sprintf('Invalid app config: no database configuration matches "%s".', $ident)
            );
        }
        return $databases[$ident];
    }

    /**
     * @param string $defaultDatabase The default database ident.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return AppConfig Chainable
     */
    public function setDefaultDatabase($defaultDatabase)
    {
        if (!is_string($defaultDatabase)) {
            throw new InvalidArgumentException(
                'Invalid app config: Default database must be a string.'
            );
        }
        $this->defaultDatabase = $defaultDatabase;
        return $this;
    }

    /**
     * @param string $ident  The database ident.
     * @param array  $config The database options.
     * @throws InvalidArgumentException If the arguments are invalid.
     * @return AppConfig Chainable
     */
    public function addDatabase($ident, array $config)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Invalid app config: database ident must be a string.'
            );
        }

        if ($this->databases === null) {
            $this->databases = [];
        }
        $this->databases[$ident] = $config;
        return $this;
    }

    /**
     * @throws Exception If trying to access this method before a setter.
     * @return mixed
     */
    public function defaultDatabase()
    {
        if ($this->defaultDatabase === null) {
            throw new Exception(
                'Invalid app config: default database is not set.'
            );
        }
        return $this->defaultDatabase;
    }

    /**
     * @param array|FilesystemConfig $filesystem The application global cache config.
     * @throws InvalidArgumentException If the argument is not an array or a config.
     * @return AppConfig Chainable
     */
    public function setFilesystem($filesystem)
    {
        if ($cache instanceof FilesystemConfig) {
            $this->filesystem = $filesystem;
            $this->filesystem->addDelegate($this);
        } elseif (is_array($cache)) {
            $this->filesystem = new FileSystemConfig($filesystem, [$this]);
        } else {
            throw new InvalidArgumentException(
                'Filesystem must be an array of config options or a FilesystemConfig object.'
            );
        }
        return $this;
    }

    /**
     * Get the application's global `FilesystemConfig`
     *
     * @return FilesystemConfig
     */
    public function filesystem()
    {
        return $this->filesystem;
    }
}
