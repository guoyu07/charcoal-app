<?php

namespace Charcoal\App\Script;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 * Script are actions called from the CLI.
 *
 * Typically, with the `charcoal` bin.
 */
interface ScriptInterface
{

    /**
     * @param string $ident The script identifier string.
     * @return ScriptInterface Chainable
     */
    public function set_ident($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param string $description The script description.
     * @return ScriptInterface Chainable
     */
    public function set_description($description);

    /**
     * @return string
     */
    public function description();

    /**
     * @param array $arguments The script arguments array, as [key=>value].
     * @return ScriptInterface Chainable
     */
    public function set_arguments(array $arguments);

    /**
     * @param string $argument_ident The argument identifier.
     * @param array  $argument       The argument options.
     * @return ScriptInterface Chainable
     */
    public function add_argument($argument_ident, array $argument);

    /**
     * @return array $arguments
     */
    public function arguments();

    /**
     * @param string $argument_ident The argument identifier to retrieve options from.
     * @return array
     */
    public function argument($argument_ident);

    /**
     * Get an argument either from argument list (if set) or else from an input prompt.
     *
     * @param string $arg_name The argument identifier to read from list or input.
     * @return array
     */
    public function arg_or_input($arg_name);

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response);
}
