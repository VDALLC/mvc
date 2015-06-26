<?php
namespace Vda\Mvc\Routing;

use Vda\Http\Request;

class CleanUrlControllerRoute extends AbstractControllerRoute
{
    protected $urlPrefix;

    public function __construct(
        $defaultControllerName,
        $controllerNamespace = '',
        $urlPrefix = '/',
        array $rewriteTable = array()
    ) {
        parent::__construct($defaultControllerName, $controllerNamespace, $rewriteTable);

        $this->urlPrefix = $urlPrefix;
    }

    public function match(Request $request)
    {
        return 0 === strpos($request->path(), $this->urlPrefix);
    }

    protected function pickParams(Request $request)
    {
        $path = $request->rewroteUri() ? : $request->path();

        $parts = parse_url(substr($path, strlen($this->urlPrefix)));
        $this->params = array_filter(explode('/', trim($parts['path'], '/')));

        if (empty($this->params)) {
            $this->params = array($this->fallbackControllerName);
        }

        return true;
    }
}
