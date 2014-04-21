<?php
namespace Vda\Mvc\Routing;

use Vda\Http\Request;
use Vda\Mvc\IRequestHandler;

class PatternCallbackRoute implements IRequestHandler
{
    protected $pattern, $callback;

    public function __construct($pattern, $callback)
    {
        $this->pattern = $pattern;
        $this->callback = $callback;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function match(Request $request)
    {
        // TODO: Implement match() method.
    }

    public function handle(Request $request)
    {
        if ($this->match($request)) {
            // TODO: Implement route() method.
        }
    }
}
