<?php
namespace Vda\Mvc\View;

interface ITemplateService
{
    /**
     * Create a configured template object instance
     *
     * @param string $name
     * @return ITemplate
     */
    public function createTemplate($name);
}
