<?php

namespace Core;

abstract class REST
{
    public $url = null;
    public $method = null;
    public $request = array();
    public $response = array();
    private $startTime;
    private $endTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->url = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null;
        $this->method = array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : null;
        $input = file_get_contents('php://input');
        $this->request = (array)@json_decode($input, true);
        $this->request = $this->request + $_REQUEST;
    }


    /**
     * Входные данные
     * @param $key
     * @return string|null
     */
    public function _request($key)
    {
        return array_key_exists($key, $this->request) ? $this->request[$key] : null;
    }


    /**
     * Данные из массива заголовков
     * @param $key
     * @return string|null
     */
    public function _headers($key)
    {
        return array_key_exists($key, $this->headers) ? $this->headers[$key] : null;
    }


    /**
     * Ответ
     */
    public function send()
    {
        $this->endTime = microtime(true);
        echo json_encode($this->response);
    }


}