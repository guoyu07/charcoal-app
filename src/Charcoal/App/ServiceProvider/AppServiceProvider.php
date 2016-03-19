<?php

namespace Charcoal\App\ServiceProvider;

use \Exception;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependencies from Pimple
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\Action\ActionFactory;
use \Charcoal\App\Route\RouteFactory;
use \Charcoal\App\Script\ScriptFactory;

use \Charcoal\App\Handler\Error;
use \Charcoal\App\Handler\PhpError;
use \Charcoal\App\Handler\Shutdown;
use \Charcoal\App\Handler\NotAllowed;
use \Charcoal\App\Handler\NotFound;

use \Charcoal\App\Template\TemplateFactory;
use \Charcoal\App\Template\TemplateBuilder;
use \Charcoal\App\Template\WidgetFactory;
use \Charcoal\App\Template\WidgetBuilder;

/**
 * Application Service Provider
 *
 * Configures Charcoal and Slim and provides various Charcoal services to a container.
 *
 * ## Services
 * - `logger` `\Psr\Log\Logger`
 *
 * ## Helpers
 * - `logger/config` `\Charcoal\App\Config\LoggerConfig`
 *
 * ## Requirements / Dependencies
 * - `config` A `ConfigInterface` must have been previously registered on the container.
 */
class AppServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerHandlerServices($container);
        $this->registerRouteServices($container);
        $this->registerRequestControllerServices($container);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerHandlerServices(Container $container)
    {
        $appConfig = $container['config'];

        if (!isset($appConfig['handlers'])) {
            return;
        }

        if ((
            !isset($container['notFoundHandler']) ||
            $container['notFoundHandler'] instanceof \Slim\Handlers\NotFound
        )) {
            unset($container['notFoundHandler']);

            /**
             * HTTP 404 (Not Found) handler.
             *
             * @param  Container $container A container instance.
             * @return HandlerInterface
             */
            $container['notFoundHandler'] = function (Container $container) {
                $config  = $container['config'];
                $handler = new NotFound($container);

                if (isset($config['handlers']['notFound'])) {
                    $handler->config()->merge($config['handlers']['notFound']);
                }

                return $handler->init();
            };
        }

        if ((
            !isset($container['notAllowedHandler']) ||
            $container['notAllowedHandler'] instanceof \Slim\Handlers\NotAllowed
        )) {
            unset($container['notAllowedHandler']);

            /**
             * HTTP 405 (Not Allowed) handler.
             *
             * @param  Container $container A container instance.
             * @return HandlerInterface
             */
            $container['notAllowedHandler'] = function (Container $container) {
                $config  = $container['config'];
                $handler = new NotAllowed($container);

                if (isset($config['handlers']['notAllowed'])) {
                    $handler->config()->merge($config['handlers']['notAllowed']);
                }

                return $handler->init();
            };
        }

        if ((
            !isset($container['phpErrorHandler']) ||
            $container['phpErrorHandler'] instanceof \Slim\Handlers\PhpError
        )) {
            unset($container['phpErrorHandler']);

            /**
             * HTTP 500 (Error) handler for PHP 7+ Throwables.
             *
             * @param  Container $container A container instance.
             * @return HandlerInterface
             */
            $container['phpErrorHandler'] = function (Container $container) {
                $config  = $container['config'];
                $handler = new PhpError($container);

                if (isset($config['handlers']['phpError'])) {
                    $handler->config()->merge($config['handlers']['phpError']);
                }

                return $handler->init();
            };
        }

        if ((
            !isset($container['errorHandler']) ||
            $container['errorHandler'] instanceof \Slim\Handlers\Error
        )) {
            unset($container['errorHandler']);

            /**
             * HTTP 500 (Error) handler.
             *
             * @param  Container $container A container instance.
             * @return HandlerInterface
             */
            $container['errorHandler'] = function (Container $container) {
                $config  = $container['config'];
                $handler = new Error($container);

                if (isset($config['handlers']['error'])) {
                    $handler->config()->merge($config['handlers']['error']);
                }

                return $handler->init();
            };
        }

        if (!isset($container['shutdownHandler'])) {
            /**
             * HTTP 503 (Service Unavailable) handler.
             *
             * @param  Container $container A container instance.
             * @return HandlerInterface
             */
            $container['shutdownHandler'] = function (Container $container) {
                $config  = $container['config'];
                $handler = new Shutdown($container);

                if (isset($config['handlers']['shutdown'])) {
                    $handler->config()->merge($config['handlers']['shutdown']);
                }

                return $handler->init();
            };
        }
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerRouteServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return RouteFactory
         */
        $container['route/factory'] = function (Container $container) {
            $routeFactory = new RouteFactory();
            return $routeFactory;
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerRequestControllerServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return ActionFactory
         */
        $container['action/factory'] = function (Container $container) {
            $actionFactory = new ActionFactory();
            return $actionFactory;
        };

        /**
         * @param Container $container A container instance.
         * @return ScriptFactory
         */
        $container['script/factory'] = function (Container $container) {
            $scriptFactory = new ScriptFactory();
            return $scriptFactory;
        };

        /**
         * @param Container $container A container instance.
         * @return TemplateFactory
         */
        $container['template/factory'] = function (Container $container) {
            $templateFactory = new TemplateFactory();
            return $templateFactory;
        };

        /**
         * @param Container $container A container instance.
         * @return TemplateBuilder
         */
        $container['template/builder'] = function (Container $container) {
            $templateBuilder = new TemplateBuilder($container['template/factory'], $container);
            return $templateBuilder;
        };

        /**
         * @param Container $container A container instance.
         * @return WidgetFactory
         */
        $container['widget/factory'] = function (Container $container) {
            $widgetFactory = new WidgetFactory();
            return $widgetFactory;
        };

        /**
         * @param Container $container A container instance.
         * @return TemplateBuilder
         */
        $container['widget/builder'] = function (Container $container) {
            $widgetBuilder = new WidgetBuilder($container['widget/factory'], $container);
            return $widgetBuilder;
        };
    }
}
