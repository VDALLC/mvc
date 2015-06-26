<?php
namespace Vda\Mvc\View;

interface ITemplate
{
    public function addParams($paramsOrKey, $value = null);
    public function render();
    //TODO Add callable type hint
    public function registerFunction($name, $impl);
}
