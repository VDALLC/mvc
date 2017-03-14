<?php
namespace Vda\Mvc\Controller;

use Exception;
use Vda\App\ClientException;
use Vda\Http\IResponse;
use Vda\Http\Request;
use Vda\Http\Response;
use Vda\Mvc\Exception\AccessDeniedException;
use Vda\Mvc\Exception\BadRequestException;
use Vda\Mvc\Exception\RouteNotFoundException;
use Vda\Mvc\View\ITemplate;
use Vda\Mvc\View\ITemplateService;
use Vda\Util\ParamStore\IParamStore;

abstract class AbstractController implements IController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var IParamStore
     */
    protected $config;

    /**
     * Name of controller.
     *
     * Used to verify request and for search templates in file system.
     *
     * @var string
     */
    protected $name;

    protected $defaultAction = 'index';

    /**
     * @var ITemplateService
     */
    protected $templateService;

    public function __construct(ITemplateService $templateService)
    {
        if (empty($this->name)) {
            throw new Exception('You must define controller name');
        }

        $this->templateService = $templateService;
    }

    public function handle(array $params, Request $request)
    {
        $this->request = $request;
        $section = array_shift($params);
        $action = $this->overrideAction(array_shift($params), $params);
        $status = 200;

        try {
            $this->init();
            $result = $this->beforeHandle($action, $params);
            if (is_null($result)) {
                // It is hard to properly handle exception if Route will check controller name (section).
                // Kind of hack, since it is very convenient to handle errors in same controller.
                if ($section != $this->name) {
                    throw new RouteNotFoundException("Unable to find handler for action '{$section}/{$action}'");
                }
                // Only call action handler if pre-handler does not return any result
                $result = $this->handleAction($action, $params);
            }
        } catch (\Exception $e) {
            $result = $this->handleError($e);
            $section = 'errors';
            $action = $result['action'];
            $status = $result['status'];
        }

        $result = $this->afterHandle($action, $result);

        return $this->resultToResponse($result, $action, $section, $status);
    }

    protected function handleError(Exception $exception)
    {
        if ($exception instanceof RouteNotFoundException) {
            $result = [
                'action'  => '404',
                'status'  => 404,
                'message' => $exception->getMessage(),
            ];
        } elseif ($exception instanceof AccessDeniedException) {
            $result = [
                'action'  => '403',
                'status'  => 403,
                'message' => $exception->getMessage(),
            ];
        } elseif ($exception instanceof BadRequestException) {
            $result = [
                'action'  => '400',
                'status'  => 400,
                'message' => $exception->getMessage(),
            ];
        } elseif ($exception instanceof ClientException) {
            $result = [
                'action'  => 'logic',
                'status'  => 200, // 400?
                'message' => $exception->getClientMessage(),
            ];
        } else {
            $result = [
                'action'  => '500',
                'status'  => 500,
                'message' => 'Internal error',
            ];
        }

        if ($this->config && $this->config->getBool('debug/enable')) {
            $result['originalMessage'] = $exception->getMessage();
            $result['trace'] = $exception->getTraceAsString();
        }

        return $result;
    }

    protected function overrideAction($action)
    {
        if (empty($action)) {
            $action = $this->defaultAction;
        }

        return $action;
    }

    /**
     * Init helper variables.
     *
     * Override this method to initialize some variables such as currentUser.
     *
     * @see beforeHandle()
     *
     */
    protected function init()
    {

    }

    /**
     * Check pre action condition.
     *
     * Override this method to check desired conditions. If this method anything but null,
     * handle method will not be called.
     *
     * @see init()
     *
     * @param $action
     * @param $params
     * @return mixed
     */
    protected function beforeHandle($action, $params)
    {
        return null;
    }

    protected function afterHandle($action, $result)
    {
        return $result;
    }

    protected function handleAction($action, array $params)
    {
        $rc = new \ReflectionClass($this);
        $method = $this->getActionHandler($rc, $action, $params);

        $this->checkActionHandler($method, $action, $rc);

        return $method->invokeArgs($this, $params);
    }

    /**
     * @param \ReflectionMethod|null $method
     * @param string $action
     * @param \ReflectionClass $rc
     * @throws RouteNotFoundException
     */
    protected function checkActionHandler($method, $action, \ReflectionClass $rc)
    {
        if (empty($method)) {
            throw new RouteNotFoundException(
                "Unable to find handler for action '{$action}' in class '{$rc->getName()}'"
            );
        }
    }

    protected function getActionHandler(\ReflectionClass $rc, $action, array $params)
    {
        $action = str_replace(' ', '', str_replace('-', '', $action));
        $numParams = count($params);

        $method = $this->fetchMethod($rc, $this->request->method() . $action, $numParams);

        if (empty($method)) {
            $method = $this->fetchMethod($rc, 'process' . $action, $numParams);
        }

        return $method;
    }

    protected function fetchMethod(\ReflectionClass $rc, $methodName, $numParams)
    {
        if (!$rc->hasMethod($methodName)) {
            return null;
        }

        $method = $rc->getMethod($methodName);

        if (!$method->isPublic() || $method->isStatic()) {
            return null;
        }

        $numRequiredParams = 0;
        foreach ($method->getParameters() as $param) {
            if (!$param->isOptional()) {
                $numRequiredParams++;
            }
        }

        if ($numRequiredParams > $numParams) {
            return null;
        }

        return $method;
    }

    protected function resultToResponse($result, $action, $section, $status)
    {
        if ($result instanceof IResponse) {
            return $result;
        }

        if (is_array($result) || is_null($result)) {
            $result = $this->createDefaultTemplate(
                $this->getTemplateName($action, $section),
                $result ?: []
            );
        }

        if ($result instanceof ITemplate) {
            $result = $result->render();
        }

        return new Response($result, $status);
    }

    protected function getTemplateName($action, $section, $extension = '.html.twig')
    {
        return $section . DIRECTORY_SEPARATOR . $action . $extension;
    }

    protected function createDefaultTemplate($templateName, array $params = [])
    {
        $template = $this->templateService->createTemplate($templateName);

        $template->addParams($params);

        return $template;
    }
}
