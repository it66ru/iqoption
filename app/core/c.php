<?php

namespace Core;

abstract class C
{
    protected $data = array(); // данные для отображения


    /**
     * Рендер шаблонов
     *
     * @param $layout - файл оформления
     * @param $viewFile - файл шаблона
     * @param array $viewData - данные шаблона
     * @return string
     */
    protected function render($layout, $viewFile, $viewData = array())
    {
        $data = empty($viewData) ? $this->data : $viewData;
        $data['content'] = \Core\Sys::render('./app/views/' . $viewFile . '.php', $data);
        if ($layout) {
            return \Core\Sys::render('./app/views/_layouts/' . $layout . '.php', $data);
        } else {
            return $data['content'];
        }
    }


    /**
     * Данные POST-запроса
     * @param $key
     * @return string|null
     */
    public function _post($key)
    {
        return array_key_exists($key, $_POST) ? $_POST[$key] : null;
    }


    /**
     * Данные GET-запроса
     * @param $key
     * @return string|null
     */
    public function _get($key)
    {
        return array_key_exists($key, $_GET) ? $_GET[$key] : null;
    }


    /**
     * Данные из массива $_REQUEST
     */
    public function _request($key)
    {
        return array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : null;
    }


    /**
     * Определение POST-запроса
     */
    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }


    /**
     * Отображение данных для отображения
     */
    protected function debug()
    {
        echo '<pre>' . print_r($this->data, true) . '</pre>';
    }


    /**
     * Редирект
     *
     * @param $url
     * @param int $statusCode
     */
    public function redirect($url, $statusCode = 302)
    {
        header('Location: ' . $url, true, $statusCode);
    }

}