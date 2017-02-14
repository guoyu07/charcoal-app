<?php

namespace Charcoal\App;

// Dependency from Slim
use Slim\Container;

// Dependency from Pimple
use Pimple\ServiceProviderInterface;

// Depedencies from `charcoal-factory`
use Charcoal\Factory\GenericFactory as Factory;

// Service Providers
use Charcoal\App\AppConfig;
use Charcoal\App\ServiceProvider\AppServiceProvider;
use Charcoal\App\ServiceProvider\CacheServiceProvider;
use Charcoal\App\ServiceProvider\DatabaseServiceProvider;
use Charcoal\App\ServiceProvider\FilesystemServiceProvider;
use Charcoal\App\ServiceProvider\LoggerServiceProvider;
use Charcoal\App\ServiceProvider\TranslatorServiceProvider as LegacyTranslatorProvider;

use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\View\ViewServiceProvider;

/**
 * Charcoal App Container
 */
class AppContainer extends Container
{
    /**
     * Create new container
     *
     * @param array $values The parameters or objects.
     */
    public function __construct(array $values = [])
    {
        // Initialize container for Slim and Pimple
        parent::__construct($values);

        // Ensure "config" is set
        $this['config'] = (isset($values['config']) ? $values['config'] : new AppConfig());

        $this->register(new AppServiceProvider());
        $this->register(new CacheServiceProvider());
        $this->register(new DatabaseServiceProvider());
        $this->register(new FilesystemServiceProvider());
        $this->register(new LoggerServiceProvider());
        $this->register(new LegacyTranslatorProvider());
        $this->register(new TranslatorServiceProvider());
        $this->register(new ViewServiceProvider());

        $this->registerProviderFactory();
        $this->registerConfigProviders();
    }

    /**
     * @return void
     */
    private function registerProviderFactory()
    {
        /**
        * @return Factory
        */
        if (!isset($this['provider/factory'])) {
            $this['provider/factory'] = function () {
                return new Factory([
                    'base_class'       => ServiceProviderInterface::class,
                    'resolver_options' => [
                        'suffix' => 'ServiceProvider'
                    ]
                ]);
            };
        }
    }

    /**
     * @return void
     */
    private function registerConfigProviders()
    {
        if (empty($this['config']['service_providers'])) {
            return;
        }

        $providers = $this['config']['service_providers'];

        foreach ($providers as $provider => $values) {
            if (false === $values) {
                continue;
            }

            if (!is_array($values)) {
                $values = [];
            }

            $provider = $this['provider/factory']->create($provider);

            $this->register($provider, $values);
        }
    }
}
