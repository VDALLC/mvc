<?php
namespace Vda\Mvc\View\Twig;

use Vda\Mvc\View\ITemplateService;

class TemplateService implements ITemplateService
{
    private $templateOptions;

    public function __construct(array $templateOptions)
    {
        $this->templateOptions = $templateOptions;
    }

    public function createTemplate($templateFilename)
    {
        return new Template($templateFilename, $this->templateOptions);
    }
}
