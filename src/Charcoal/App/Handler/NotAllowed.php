<?php

namespace Charcoal\App\Handler;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependency from Slim
use \Slim\Http\Body;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\Catalog\CatalogInterface;

// Local Dependencies
use \Charcoal\App\Handler\AbstractHandler;

/**
 * Not Allowed Handler
 *
 * Enhanced version of {@see \Slim\Handlers\NotAllowed}.
 *
 * It outputs a simple message in either JSON, XML,
 * or HTML based on the Accept header.
 */
class NotAllowed extends AbstractHandler
{
    /**
     * HTTP methods allowed by the current request.
     *
     * @var array $methods
     */
    protected $methods;

    /**
     * Invoke "Not Allowed" Handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object.
     * @param  ResponseInterface      $response The most recent Response object.
     * @param  string[]               $methods  Allowed HTTP methods.
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $methods)
    {
        $this->setMethods($methods);

        if ($request->getMethod() === 'OPTIONS') {
            $status = 200;
            $contentType = 'text/plain';
            $output = $this->renderPlainOutput();
        } else {
            $status = 405;
            $contentType = $this->determineContentType($request);
            switch ($contentType) {
                case 'application/json':
                    $output = $this->renderJsonOutput();
                    break;

                case 'text/xml':
                case 'application/xml':
                    $output = $this->renderXmlOutput();
                    break;

                case 'text/html':
                    $output = $this->renderHtmlOutput();
                    break;
            }
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);
        $allow = implode(', ', $methods);

        return $response
                ->withStatus($status)
                ->withHeader('Content-type', $contentType)
                ->withHeader('Allow', $allow)
                ->withBody($body);
    }

    /**
     * Set the HTTP methods allowed by the current request.
     *
     * @param  array $methods Case-sensitive array of methods.
     * @return NotAllowed Chainable
     */
    protected function setMethods(array $methods)
    {
        $this->methods = implode(', ', $methods);

        return $this;
    }

    /**
     * Retrieves the HTTP methods allowed by the current request.
     *
     * @return string Returns the allowed request methods.
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Render Plain/Text Error
     *
     * @return string
     */
    protected function renderPlainOutput()
    {
        $message = $this->catalog()->translate('allowed-methods-1').' '.$this->methods();

        return $this->render($message);
    }

    /**
     * Render JSON Error
     *
     * @return string
     */
    protected function renderJsonOutput()
    {
        $message = $this->catalog()->translate('allowed-methods-2').' '.$this->methods();

        return $this->render('{"message":"'.$message.'"}');
    }

    /**
     * Render XML Error
     *
     * @return string
     */
    protected function renderXmlOutput()
    {
        $message = $this->catalog()->translate('allowed-methods-2').' '.$this->methods();

        return $this->render('<root><message>'.$message.'</message></root>');
    }

    /**
     * Render title of error
     *
     * @return string
     */
    public function messageTitle()
    {
        return $this->catalog()->entry('method-not-allowed');
    }

    /**
     * Render body of HTML error
     *
     * @return string
     */
    public function renderHtmlMessage()
    {
        $title   = $this->messageTitle();
        $notice  = $this->catalog()->entry('allowed-methods-2');
        $methods = $this->methods();
        $message = '<h1>'.$title."</h1>\n\t\t<p>".$notice.' <strong>'.$methods."</strong></p>\n";

        return $message;
    }

    /**
     * Sets a translation catalog instance on the object.
     *
     * @param  CatalogInterface $catalog A translation catalog object.
     * @return NotAllowed Chainable
     */
    public function setCatalog(CatalogInterface $catalog)
    {
        parent::setCatalog($catalog);

        $messages = [
            'method-not-allowed' => [
                'en' => 'Method not allowed.',
                'fr' => 'Méthode non autorisée.',
                'es' => 'Método no permitido.'
            ],
            'allowed-methods-1' => [
                'en' => 'Allowed methods:',
                'fr' => 'Méthodes permises:',
                'es' => 'Métodos permitidos:'
            ],
            'allowed-methods-2' => [
                'en' => 'Method not allowed. Must be one of:',
                'fr' => 'Méthode non autorisée. Doit être:',
                'es' => 'Método no permitido. Debe ser uno de:'
            ]
        ];

        foreach ($messages as $key => $entry) {
            if (!$this->catalog()->hasEntry($key)) {
                $this->catalog()->addEntry($key, $entry);
            }
        }

        return $this;
    }
}
