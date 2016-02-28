<?php

namespace Core;

abstract class AR
{
    protected $data = array(); // данные объекта
    protected $errors = array(); // ошибки валидации
    protected $fields = array(); // поля объекта
    protected $relations = array(); // связи с другими объектами

    static public $found_rows = null; // кол-во записей соответсвующих условию поиска без учета LIMIT

    public function __construct($data = array())
    {
        $this->getFields();
        $this->setData($data);
    }


    /**
     * Получения свойств объекта
     */
    public function __get($key)
    {
        // атрибуты самого объекта
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        // связи с другими объектами
        if (array_key_exists($key, $this->relations)) {
            $name = '_' . $key;
            $className = '\Models\\' . $this->relations[$key][0];
            $fieldName = $this->relations[$key][1];
            if (empty($this->$name)) {
                $this->$name = $className::find_by_pk($this->$fieldName);
            }
            return $this->$name;
        }
    }


    /**
     * Присвоение свойств объекту
     */
    public function __set($key, $value)
    {
        if (substr($key, 0, 1) == '_') {
            $this->$key = $value;
        } else {
            $this->data[$key] = $value;
        }
    }


    /**
     * Сохранение записи в БД
     */
    public function save()
    {
        $data = array();
        // создаем массиы данных, которые будут сохранены
        foreach ($this->fields as $fieldName => $fieldData) {
            if (array_key_exists($fieldName, $this->data)) {
                $data[$fieldName] = $this->data[$fieldName];
            }
        }
        // сохранение данных объекта
        if (!empty($data)) {
            if ($this->id) { // редактирование существующего
                db::update(static::table, $data, $this->id);
            } else { // создание нового
                $this->id = db::insert(static::table, $data);
            }
        }
    }


    /**
     * Удаление записи из БД
     */
    public function delete()
    {
        $this->is_actual = 0;
        $this->save();
    }


    /**
     * Восстановление записи
     */
    public function recover()
    {
        $this->is_actual = 1;
        $this->save();
    }


    /**
     * Отображение всех свойств объекта
     */
    public function debug()
    {
        echo '<pre>' . print_r($this->data, true) . '</pre>';
    }


    /**
     * Заполнение всех атрибутов объекта
     * @param array $data
     */
    public function setData($data = array())
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

    }


    /**
     * Полученеи всех атрибутов объекта
     * @param bool $transform - делать преобразование данных или нет
     * @return array
     */
    public function getData($transform = true)
    {
        if ($transform) {
            $data = array();
            foreach (array_keys($this->data) as $key) {
                $data[$key] = $this->$key;
            }
            return $data;
        } else {
            return $this->data;
        }
    }


    /**
     * Полученеи всех атрибутов объекта в формате JSON
     * @return string
     */
    public function getJson()
    {
        return json_encode($this->data);
    }


    /**
     * Валидация объекта
     */
    public function validate()
    {
        $this->errors = array();
        return empty($this->errors);
    }


    /**
     * Получение ошибок валидации
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }


    /**
     * Поиск записи по первичному ключу
     * @param $value - значение ключа
     * @param string $field - первичный ключ
     * @return null
     */
    public static function find_by_pk($value, $field = 'id')
    {
        $params = is_array($value) ? $value : array($field => $value);
        $items = static::find_by_params($params);
        return empty($items) ? null : $items[0];
    }


    /**
     * Поиск объектов по параметрам
     *
     * @param array $params - параметры
     * @param string $order_by - порядок сортировки
     * @param int $limit - количество возвращаемы записей
     * @param int $offset - сдвиг выборки
     * @return array - список объектов
     */
    public static function find_by_params($params = array(), $order_by = null, $limit = 999, $offset = 0)
    {
        $_conditions = $_params = array();
        foreach ($params as $field => $value) {
            if (is_null($value)) {
                $_conditions[] = $field . ' is null';
            } else {
                if (!substr_count($field, '.')) {
                    $field = static::table . '.' . $field;
                }
                $key = str_replace('.', '_', $field);
                $_conditions[] = $field . ' = :' . $key;
                $_params[$key] = $value;
            }
        }
        return static::find($_conditions, $_params, $order_by, $limit, $offset);
    }


    /**
     * Поиск объектов, по указанному условию условию
     *
     * @param array $conditions - условия
     * @param array $params - параметры
     * @param string $order_by - порядок сортировки
     * @param int $limit - количество возвращаемы записей
     * @param int $offset - сдвиг выборки
     * @return array - список объектов
     */
    public static function find($conditions = array(), $params = array(), $order_by = null, $limit = 999, $offset = 0)
    {
        $sql = "select *
                from " . static::table . "
                where " . (!empty($conditions) ? implode(' and ', $conditions) : "1 = 1") . "
                order by " . ($order_by ? $order_by : '1') . "
                limit $limit offset $offset";
        return self::find_by_sql($sql, $params);
    }


    /**
     * Поиск объектов, по sql-запросу
     *
     * @param $sql - запрос
     * @param $params - параметры запроса
     * @return array - список объектов
     */
    public static function find_by_sql($sql, $params = array())
    {
        $modelName = get_called_class();
        $items = array();
        foreach (\Core\DB::select($sql, $params) as $itemData) {
            $items[] = new $modelName($itemData);
        }
        return $items;
    }


    /**
     * Получение списка колонок таблицы
     * @return array
     */
    public function getFields()
    {
        $this->fields = \Core\DB::fields(static::table);
        return $this->fields;
    }


}