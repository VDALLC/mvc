<?php
namespace Vda\Mvc\View\Twig;

use InvalidArgumentException;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twig_LoaderInterface;
use Twig_SimpleFunction;
use Vda\Mvc\View\ITemplate;

class Template implements ITemplate
{
    protected static $defaults = [
        'templateExt' => '.html.twig',
        'viewPath' => 'views',
        'cache' => null,
        'auto_reload' => true,
        'debug' => false,
    ];

    protected $fileName;
    protected $params = [];

    protected $twig;

    /**
     * @param string $templateFilename
     * @param array $twigOptions
     * @throws InvalidArgumentException
     */
    public function __construct($templateFilename, array $options = [])
    {
        $options = array_merge(self::$defaults, $options);

        $this->fileName = $templateFilename . $options['templateExt'];

        if ($options['viewPath'] instanceof Twig_LoaderInterface) {
            $loader = $options['viewPath'];
        } elseif (is_string($options['viewPath']) || is_array($options['viewPath'])) {
            $loader = new Twig_Loader_Filesystem($options['viewPath']);
        } else {
            throw new InvalidArgumentException(
                'Invalid $templatePath value: ' . print_r($templatePath, true)
            );
        }

        $this->twig = new Twig_Environment($loader, $options);

        if ($options['debug']) {
            $this->twig->addExtension(new Twig_Extension_Debug());
        }
    }

    public function registerFunction($name, callable $impl)
    {
        $this->twig->addFunction(new Twig_SimpleFunction($name, $impl));
    }

    public function render()
    {
        return $this->twig->render($this->fileName, $this->params);
    }

    public function addParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
    }

    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }
}
