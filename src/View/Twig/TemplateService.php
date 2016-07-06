<?php
namespace Vda\Mvc\View\Twig;

use Vda\Mvc\View\ITemplateService;

class TemplateService implements ITemplateService
{
    private $defaultOptions;

    public function __construct(array $defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
    }

    public function createTemplate($name, array $options = [])
    {
        $options = array_merge($this->defaultOptions, $options);

        return new Template($name, $options);
    }
}
