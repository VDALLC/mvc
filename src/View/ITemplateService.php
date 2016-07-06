<?php
namespace Vda\Mvc\View;

interface ITemplateService
{
    /**
     * Create a configured template object instance
     *
     * @param string $name
     * @param array $options Optional parameters for template
     * @return ITemplate
     */
    public function createTemplate($name, array $options = []);
}
