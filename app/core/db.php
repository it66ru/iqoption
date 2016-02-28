<?php

namespace Core;

class DB
{
    private static $_conn;
    private static $_fields = array();


    /**
     * Подключение к БД
     * @return PDO
     */
    public static function conn()
    {
        if (!isset(self::$_conn)) {
            $config = $GLOBALS['config']['db'];
            self::$_conn = new \PDO($config['connectionString'], $config['username'], $config['password']);
            self::$_conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$_conn->exec('set names ' . $config['charset']);
        }
        return self::$_conn;
    }


    /**
     * Подключение к БД (синоним для self::conn())
     * @return PDO
     */
    public static function get()
    {
        return self::conn();
    }


    /**
     * Получение списка записей
     *
     * @param $sql - запрос
     * @param $params - параметры запроса
     * @return array - результат запроса
     */
    public static function select($sql, $params = array())
    {
        $sth = self::conn()->prepare($sql);
        $sth->execute($params);
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * Логгирование изменений
     *
     * @param $object_type string - тип объекта(название таблицы)
     * @param $object_id int - идентификатор объекта
     * @param $operation_type string - тип операции (create, read, update, delete, recovery)
     * @param $data array - ассоциативный массив данных, которые учавствуют в операции
     */
    private static function log($object_type, $object_id, $operation_type, $data = array())
    {
        return;
        $logData = array(
            'object_type' => $object_type,
            'object_id' => $object_id,
            'operation_type' => $operation_type,
            'operation_date' => date('Y-m-d H:i:s'),
            'operation_data' => json_encode($data),
            'user_id' => \Models\User::auth() ? \Models\User::auth()->id : null,
        );
        self::insert('log', $logData, false);
    }


    /**
     * Создание записи
     *
     * @param $table string - название таблицы
     * @param $data array - ассоциативный массив данных
     * @param bool $logging - логировать операцию
     * @return int - ID созданной записи
     */
    public static function insert($table, $data, $logging = true)
    {
        foreach ($data as $fieldName => $fieldData) {
            $set[] = $fieldName . ' = :' . $fieldName;
            $params[$fieldName] = $fieldData;
        }
        $sql = "insert into " . $table . " set " . implode(', ', $set);

        $sth = self::conn()->prepare($sql);
        $sth->execute($params);
        $id = self::get()->lastInsertId();

        if ($logging && $sth->rowCount() > 0) {
            self::log($table, $id, 'create', $data);
        }
        return $id;
    }


    /**
     * Обновление записи
     *
     * @param $table string - название таблицы
     * @param $data array - ассоциативный массив данных
     * @param $id int - идентификатор записи
     * @param bool $logging - логировать операцию
     * @return bool - результат выполнения операции
     */
    public static function update($table, $data, $id, $logging = true)
    {
        unset($data['id']);

        foreach ($data as $fieldName => $fieldData) {
            $set[] = $fieldName . ' = :' . $fieldName;
            $params[$fieldName] = $fieldData;
        }
        $sql = "update " . $table . " set " . implode(', ', $set) . " where id = :id";
        $params['id'] = $id;

        $sth = self::conn()->prepare($sql);
        $sth->execute($params);

        if ($logging && $sth->rowCount() > 0) {
            self::log($table, $id, 'update', $data);
        }
        return true;
    }


    /**
     * Выполнение произвольного запроса
     *
     * @param $sql - запрос
     * @param $params - параметры запроса
     * @return int - кол-во измененных строк
     */
    public static function query($sql, $params = array())
    {
        $sth = self::conn()->prepare($sql);
        $sth->execute($params);
        return $sth->rowCount();
    }


    /**
     * Список полей таблицы
     */
    public static function fields($table)
    {
        if (!array_key_exists($table, self::$_fields)) {
            $sql = "select column_name, data_type, column_comment, column_default
                from information_schema.columns
                where table_schema = database()
                    and table_name = :table_name";
            $params = array(
                'table_name' => $table,
            );
            foreach (self::select($sql, $params) as $_) {
                self::$_fields[$table][$_['column_name']] = array(
                    'name' => $_['column_name'],
                    'type' => $_['data_type'],
                    'comment' => $_['column_comment'],
                    'default' => $_['column_default'],
                );
            }
        }
        return self::$_fields[$table];
    }

}