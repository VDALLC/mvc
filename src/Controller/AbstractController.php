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

        // this is just a default value of resultToResponse(), not a final status
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

            $result = $this->afterHandle($action, $result);
            return $this->resultToResponse($result, $section . '/' . $action, $status);
        } catch (Exception $ex) {
            $section = 'errors';
            $result = $this->handleError($ex);
            $status = $result['status'];
            $action = $result['action'];

            $result = $this->afterHandle($action, $result);
            return $this->resultToResponse($result, $section . '/' . $action, $status);
        }
    }

    protected function handleError(Exception $exception)
    {
        if ($exception instanceof RouteNotFoundException) {
            $template = '404';
            $status = 404;
            $result = array(
                'message' => $exception->getMessage(),
            );
        } elseif ($exception instanceof AccessDeniedException) {
            $template = '403';
            $status = 403;
            $result = array(
                'message' => $exception->getMessage(),
            );
        } elseif ($exception instanceof BadRequestException) {
            $template = '400';
            $status = 400;
            $result = array(
                'message' => $exception->getMessage(),
            );
        } elseif ($exception instanceof ClientException) {
            $template = 'logic';
            $status = 200; // 400?
            $result = array(
                'message' => $exception->getClientMessage(),
            );
        } else {
            $template = '500';
            $status = 500;
            $result = array(
                'message' => 'Internal error',
            );
        }

        $result['status'] = $status;
        $result['action'] = $template;

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

    protected function afterHandle($action, $res)
    {
        return $res;
    }

    protected function handleAction($action, array $params)
    {
        $rc = new \ReflectionClass($this);
        $method = $this->getActionHandler($rc, $action, $params);

        if (empty($method)) {
            throw new RouteNotFoundException(
                "Unable to find handler for action '{$action}' in class '{$rc->getName()}'"
            );
        }

        return $method->invokeArgs($this, $params);
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

    protected function resultToResponse($result, $template, $status = 200)
    {
        if ($result instanceof IResponse) {
            return $result;
        } else if ($result instanceof ITemplate) {
            return new Response($result->render(), $status);
        } else if (is_array($result) || is_null($result)) {
            return new Response(
                $this->createDefaultTemplate($template, (array)$result)->render(),
                $status
            );
        } else {
            return new Response($result, $status);
        }
    }

    protected function createDefaultTemplate($action, array $params = [])
    {
        $template = $this->templateService->createTemplate($action);

        $template->addParams($params);

        return $template;
    }
}
