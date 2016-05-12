<?php

namespace Charcoal\App\Template;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

/**
 * Build templates from config, with a TemplateFactory
 */
class TemplateBuilder
{

    /**
     * @var FactoryInterface $factory
     */
    protected $factory;

    /**
     * A Pimple dependency-injection container to fulfill the required services.
     * @var Container $container
     */
    protected $container;

    /**
     * @param FactoryInterface $factory   An object factory.
     * @param Container        $container The DI container.
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    /**
     * @param array|\ArrayAccess $options The form group build options / config.
     * @return TemplateInterface The "built" Template object.
     */
    public function build($options)
    {
        $container = $this->container;
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType, [
            'logger'    =>  $container['logger'],
            'view'      =>  $container['view']
        ]);
        $obj->setDependencies($container);
        $obj->setData($options);
        return $obj;
    }
}