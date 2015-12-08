<?php

namespace Charcoal\App\Route;

// Local namespace dependencies
use \Charcoal\App\Route\RouteConfig;

/**
 *
 */
class ScriptRouteConfig extends RouteConfig
{
    /**
     * @var array $script_data
     */
    private $script_data = [];

    /**
     * Set the action data.
     *
     * @param array $script_data The route data.
     * @return ActionRouteConfig Chainable
     */
    public function set_script_data(array $script_data)
    {
        $this->script_data = $script_data;
        return $this;
    }

    /**
     * Get the action data.
     *
     * @return array
     */
    public function script_data()
    {
        return $this->script_data;
    }
}
