<?php
namespace Vda\Mvc\View;

use \InvalidArgumentException;
use \Twig_Environment;
use \Twig_LoaderInterface;
use \Twig_Loader_Filesystem;
use \Twig_SimpleFunction;
use \Vda\Util\VarUtil as V;

class TwigTemplate extends Twig_Environment implements ITemplate
{
    protected static $defaults = array(
        'viewPath' => 'app/views',
        'cache' => 'app/var/tpl-cache',
        'auto_reload' => true,
    );

    protected $fileName;
    protected $params = array();

    /**
     * @param string $fileName
     * @param null|string|Twig_LoaderInterface $templatePath
     * @param array $twigOptions
     * @throws InvalidArgumentException
     */
    public function __construct($fileName, $templatePath = null, array $twigOptions = array())
    {
        $this->fileName = $fileName;
        $twigOptions = array_merge(self::$defaults, $twigOptions);

        if (is_null($templatePath)) {
            $loader = new Twig_Loader_Filesystem(self::$defaults['viewPath']);
        } elseif (is_string($templatePath) || is_array($templatePath)) {
            $loader = new Twig_Loader_Filesystem($templatePath);
        } elseif ($templatePath instanceof Twig_LoaderInterface) {
            $loader = $templatePath;
        } else {
            throw new InvalidArgumentException('Invalid $templatePath value: ' . print_r($templatePath, true));
        }

        parent::__construct($loader, $twigOptions);
    }

    public function render($name = null, array $context = array())
    {
        return parent::render(V::ifEmpty($name, $this->fileName), V::ifEmpty($context, $this->params));
    }

    public function addParams($paramsOrKey, $value = null)
    {
        if (is_array($paramsOrKey)) {
            $this->params = array_merge($this->params, $paramsOrKey);
        } else {
            $this->params[$paramsOrKey] = $value;
        }
    }

    public function registerFunction($name, $impl)
    {
        $this->addFunction(new Twig_SimpleFunction($name, $impl));
    }
}
