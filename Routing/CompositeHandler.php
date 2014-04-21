<?php
namespace Vda\Mvc\Routing;

use Vda\Http\Request;
use Vda\Mvc\IRequestHandler;

class CompositeHandler implements IRequestHandler
{
    /**
     * @var IRequestHandler[]
     */
    protected $handlers;

    /**
     * @param IRequestHandler[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle(Request $request)
    {
        foreach ($this->handlers as $handler) {
            $res = $handler->handle($request);
            if ($res) {
                return $res;
            }
        }

        return null;
    }
}
