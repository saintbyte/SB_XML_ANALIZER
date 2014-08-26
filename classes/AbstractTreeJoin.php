<?php

/**
 * Class AbstractTreeJoin
 * Дерево, класс от которого надо все остально создавать
 */
abstract class AbstractTreeJoin extends AbstractJoin
{
    protected $table;
    protected $classes;
    protected $parent;
    protected $parent_key;
    protected $key_fields;

    public function __construct($dbh)
    {
        $this->parent_key = 'parent_id';
        parent::__construct($dbh);
    }

    public function createTable()
    {
        $sql = '
                CREATE TABLE IF NOT EXISTS ' . $this->table . ' (
                     `' . $this->getKeyPrefix() . 'id` int(11) NOT NULL AUTO_INCREMENT,';
        $sql .= ' ' . $this->parent_key . ' int(11) NOT NULL,';
        foreach ($this->key_fields as $field) {
            $sql .= ' ' . $field . ' int(11) NOT NULL,';
        }
        $sql .= 'PRIMARY KEY (`' . $this->getKeyPrefix() . 'id`)';
        $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        $this->DB_LINK->beginTransaction();
        $this->DB_LINK->query($sql);

        $this->DB_LINK->commit();
    }

    /**
     * Добавление данных в дерево
     * @param int $parent ID родителя
     * @param $inr_array - Данные для добавления
     * @return string|void - ID добавленого элемента
     */
    public function add($parent, $inr_array)
    {
        $sql = 'INSERT INTO ' . $this->table . '(' . $this->getKeyPrefix() . 'id,' . $this->parent_key . ',' . join(',', $this->key_fields) . ') VALUES("",';
        $sql .= ':' . $this->parent_key . ',';
        foreach ($this->key_fields as $field) {
            $fields[] = ':' . $field;
        }
        $sql .= join(',', $fields);
        $sql .= ')';
        $sth = $this->DB_LINK->prepare($sql);
        $sth->bindParam(':' . $this->parent_key, $parent);
        foreach ($this->key_fields as $field) {
            $sth->bindParam(':' . $field, $inr_array[$field]);
        }
        $this->DB_LINK->beginTransaction();
        $sth->execute();
        $id = $this->DB_LINK->lastInsertId();
        $this->DB_LINK->commit();
        return $id;
    }
    /**
     * Получение данных
     * @param $param ID родитель
     * @param $get_arr массив  ключ-значение ключ поле, значение параметр для поиска
     * @return array данные
     */
    public function get($parent, $get_arr)
    {
        $sql = 'SELECT ';
        foreach ($this->classes as $klass) {
            $instance = new $klass($this->DB_LINK);
            if (is_subclass_of($instance, 'AbstractList')) $sql .= ' IFNULL(' . $instance->getTable() . '.value,"") AS ' . $instance->getTable() . '_value,';
            // Заменяем NULL на пустую строку
        }
        $sql .= $this->getTable() . '.' . $this->getKeyPrefix() . 'id AS id'; //
        $sql .= ' FROM ' . $this->table . ' ';
        foreach ($this->classes as $klass) {
            $instance = new $klass($this->DB_LINK);

            $this->key_fields[] = $instance->getKeyPrefix() . 'id';
            $sql .= ' LEFT JOIN ' . $instance->getTable() . ' ON ' . $this->table . '.' . $instance->getKeyPrefix() . 'id=' . $instance->getTable() . '.' . $instance->getKeyPrefix() . 'id ';

        }
        $sql .= ' WHERE ';
        $sql .= ' ' . $this->table . '.' . $this->parent_key . '=:' . $this->parent_key . ' ';
        if (count($get_arr) > 0) $sql .= ' AND ';
        $where_arr = array();
        foreach ($get_arr as $item => $value) {
            $where_arr[] = $this->table . '.' . $item . '=:' . $item;
        }
        $sql .= join(' AND ', $where_arr);
        //die($sql);
        $sth = $this->DB_LINK->prepare($sql);
        $sth->bindParam(':' . $this->parent_key, $parent);
        foreach ($get_arr as $item => $value) {
            $sth->bindParam(':' . $item, $value);
        }
        $qrh = $sth->execute();
        if (!$qrh) {
            return array();
        }
        return $sth->fetchAll(PDO::FETCH_BOTH);
    }
} 