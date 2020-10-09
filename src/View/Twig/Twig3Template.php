<?php
namespace Vda\Mvc\View\Twig;

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;
use Vda\Mvc\View\ITemplate;

class Twig3Template implements ITemplate
{
    protected static $defaults = [
        'viewPath' => 'views',
        'cache' => null,
        'auto_reload' => true,
        'debug' => false,
    ];

    protected $fileName;
    protected $params = [];

    protected $twig;

    public function __construct(string $templateFilename, array $options = [])
    {
        $options = \array_merge(self::$defaults, $options);

        $this->fileName = $templateFilename;

        if ($options['viewPath'] instanceof LoaderInterface) {
            $loader = $options['viewPath'];
        } elseif (\is_string($options['viewPath']) || \is_array($options['viewPath'])) {
            $loader = new FilesystemLoader($options['viewPath']);
        } else {
            throw new \InvalidArgumentException(
                'Invalid $templatePath value: ' . \print_r($options['viewPath'], true)
            );
        }

        $this->twig = new Environment($loader, $options);

        if ($options['debug']) {
            $this->twig->addExtension(new DebugExtension());
        }
    }

    public function registerFunction($name, callable $impl)
    {
        $this->twig->addFunction(new TwigFunction($name, $impl));
    }

    public function render()
    {
        return $this->twig->render($this->fileName, $this->params);
    }

    public function addParams(array $params)
    {
        $this->params = \array_merge($this->params, $params);
    }

    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }
}
