<?php

namespace Controllers;

class Commentary extends \Core\REST
{

    /**
     * Список записей
     */
    public function main()
    {
        $params = array(
            'pid' => $this->_request('pid'),
            'is_actual' => 1
        );
        $items = array();
        foreach (\Models\Commentary::find_by_params($params, 'commentary.id') as $commentary) {
            $items[] = $commentary->getData();
        }
        $this->response = array(
            'success' => true,
            'items' => $items,
        );
        $this->send();
    }


    /**
     * Получение записи
     * @param $id
     */
    public function get($id)
    {
        $commentary = \Models\Commentary::find_by_pk($id);
        if ($commentary) {
            $this->response = array(
                'success' => true,
                'item' => $commentary->getData(),
            );
        } else {
            $this->response = array(
                'success' => false
            );
        }
        $this->send();
    }


    /**
     * Добавление записи
     */
    public function post()
    {
        if ($this->_request('content')) {
            $commentary = new \Models\Commentary;
            $commentary->pid = $this->_request('pid');
            $commentary->content = $this->_request('content');
            $commentary->save();
            $this->response = array(
                'success' => true,
                'item' => $commentary->getData(),
            );
        } else {
            $this->response = array(
                'success' => false
            );
        }
        $this->send();
    }


    /**
     * Редактирование записи
     * @param $id
     */
    public function put($id)
    {
        $commentary = \Models\Commentary::find_by_pk($id);
        if ($commentary && $this->_request('content')) {
            $commentary->content = $this->_request('content');
            $commentary->save();
            $this->response = array(
                'success' => true,
                'item' => $commentary->getData(),
            );
        } else {
            $this->response = array(
                'success' => false
            );
        }
        $this->send();
    }


    /**
     * Удаление записи
     * @param $id
     */
    public function delete($id)
    {
        $commentary = \Models\Commentary::find_by_pk($id);
        if ($commentary) {
            $commentary->delete();
            $this->response = array(
                'success' => true
            );
        } else {
            $this->response = array(
                'success' => false
            );
        }
        $this->send();
    }

}
