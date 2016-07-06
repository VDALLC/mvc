<?php
namespace Vda\Mvc\View;

interface ITemplate
{
    /**
     * Register a function to be called from templates
     *
     * @param string $name
     * @param callable $impl
     */
    public function registerFunction($name, callable $impl);

    /**
     * Pass a single parameter to the template
     * @param string $name
     * @param mixed $value
     */
    public function addParam($name, $value);

    /**
     * Pass an associative array of params to the template
     *
     * @param array $params
     */
    public function addParams(array $params);

    /**
     * Render the template
     *
     * @return string
     */
    public function render();
}
