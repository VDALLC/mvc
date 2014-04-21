<?php
namespace Vda\Mvc;

use Vda\Http\IResponse;
use Vda\Http\Request;

interface IRequestHandler
{
    /**
     * Match and handle request.
     *
     * Handler is not required to handle any request. If it won't match request, then
     * it should return null to give a chance to another handlers. But primary app
     * handler MUST be configured in a such way to handle any requests. If a primary
     * app handler returns null, than entire app may show exception message in any form.
     *
     * See also Vda\Mvc\Controller\AbstractController::handle() for exception handling.
     *
     * @param Request $request
     * @return IResponse
     */
    public function handle(Request $request);
}
