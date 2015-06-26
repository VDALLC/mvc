<?php

class JsonTemplate implements \Vda\Mvc\View\ITemplate
{
    protected $data = array();

    public function addParams($paramsOrKey, $value = null)
    {
        if (is_array($paramsOrKey)) {
            $this->data = $paramsOrKey;
        } else {
            $this->data[$paramsOrKey] = $value;
        }
    }

    public function render()
    {
        return json_encode($this->data);
    }

    public function registerFunction($name, $impl) {}
}

class IndexController extends \Vda\Mvc\Controller\AbstractController
{
    protected $name = 'index';

    public function getIndex($id)
    {
        return [
            'id'    => $id,
        ];
    }

    protected function createDefaultTemplate($template, $params, $ext = null)
    {
        $res = new JsonTemplate();
        $res->addParams($params);
        return $res;
    }
}

class AbstractControllerTest extends PHPUnit_Framework_TestCase
{
    public function testIndexControllerHandlesGetRequest()
    {
        $controller = new IndexController();
        $res = $controller->handle(['index', 'index', '4'], \Vda\Http\Request::create(''));
        $this->assertInstanceOf(\Vda\Http\Response::class, $res);
        $this->assertEquals('{"id":"4"}', $res->getBody());
    }
}
