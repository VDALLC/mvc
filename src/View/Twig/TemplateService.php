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
        $options = \array_merge($this->defaultOptions, $options);

        if (\class_exists(\Twig_Environment::class)) {
            return new Template($name, $options);
        }

        return new Twig3Template($name, $options);
    }
}
