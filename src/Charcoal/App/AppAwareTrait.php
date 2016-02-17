<?php

namespace Charcoal\App;

use \Charcoal\App\AppInterface;

/**
* Implementation, as trait, of the `AppAwareInterface`.
*/
trait AppAwareTrait
{
    /**
     * @var AppInterface $app
     */
    private $app;

    /**
     * @param AppInterface $app The app instance this object depends on.
     * @return AppAwareInterface Chainable
     */
    public function setApp(AppInterface $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return AppInterface
     */
    public function app()
    {
        if ($this->app === null) {
            $this->app = \Charcoal\App\App::instance();
        }
        return $this->app;
    }
}