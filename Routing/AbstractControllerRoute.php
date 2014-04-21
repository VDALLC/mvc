<?php
namespace Vda\Mvc\Routing;

use Vda\Http\Request;
use Vda\Mvc\Controller\IController;
use Vda\Mvc\IRequestHandler;

/**
 *
 * @todo makeUrl() actually tied to url dispatcher functionality, so
 *      makeUrl should be implemented here and IRoute should
 *      be available in views through Registry or some Factory.
 */
abstract class AbstractControllerRoute implements IRequestHandler
{
    protected $fallbackControllerName;
    protected $controllerNamespace;

    protected $rewriteTable;

    protected $params;

    public function __construct(
        $defaultControllerName,
        $controllerNamespace = '',
        array $rewriteTable = array()
    ) {
        $this->fallbackControllerName = $defaultControllerName;
        $this->controllerNamespace = $controllerNamespace;
        $this->rewriteTable = $rewriteTable;
    }

    public function handle(Request $request)
    {
        if ($this->match($request)) {
            $request = $this->rewrite($request);
            $this->pickParams($request);
            return $this->fetchController()->handle($this->params, $request);
        } else {
            return null;
        }
    }

    protected function rewrite(Request $request)
    {
        $request->setRewroteUri($request->path());
        foreach ($this->rewriteTable as $pattern => $replacement) {
            $uri = preg_replace($pattern, $replacement, $request->rewroteUri());
            if ($uri != $request->rewroteUri()) {
                $request->setRewroteUri($uri);
            }
        }

        return $request;
    }

    public function getControllerName()
    {
        return $this->params[0];
    }

    /**
     * @return IController
     */
    public function fetchController()
    {
        $class = $this->controllerClassName($this->getControllerName());
        if (!class_exists($class)) {
            $class = $this->controllerClassName($this->fallbackControllerName);
        }
        // assumed controllers has default constructor
        return new $class();
    }

    protected function controllerClassName($name)
    {
        $c = str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
        return $this->controllerNamespace . '\\' . $c . 'Controller';
    }

    abstract public function match(Request $request);

    abstract protected function pickParams(Request $request);
}
