<?php
namespace Vda\Mvc\Controller;

use Vda\Http\Request;

interface IController
{
    /**
     * @param array $params
     * @param Request $request
     * @return \Vda\Http\IResponse
     */
    public function handle(array $params, Request $request);
}
