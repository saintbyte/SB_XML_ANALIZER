<?php

/**
 * Класс для связи по ключевым полям
 */
abstract class AbstractJoin extends DBObject
{
    /**
     * Таблица
     * @var string
     */
    protected $table;

    /**
     * Какие классы связывать
     * @var  array
     */
    protected $classes;

    /**
     * Список полей , исключительно чтоб кешировать данные
     * @var array
     */
    protected $key_fields;

    /**
     * Префикс ключевого поля
     * @var string
     */
    protected $key_prefix;


    /**
     * Конструтор обьекта
     * @param $dbh PDO
     */
    public function __construct($dbh)
    {
        foreach ($this->classes as $klass)
        {
            $instance = new $klass($dbh);
            $this->key_fields[] = $instance->getKeyPrefix().'id';
        }
        parent::__construct($dbh);
    }

    /**
     * Создаем таблицу для обьекта
     */
    public function createTable()
    {
        $sql = '
                CREATE TABLE IF NOT EXISTS ' . $this->table . ' (
                     `'.$this->key_prefix.'id` int(11) NOT NULL AUTO_INCREMENT,';
        foreach ($this->key_fields as $field) {
               $sql .= ' ' .  $field . ' int(11) NOT NULL,';
        }
        $sql .= 'PRIMARY KEY (`'.$this->key_prefix.'id`)';
        $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        $this->DB_LINK->beginTransaction();
        $this->DB_LINK->query($sql);
        $this->DB_LINK->commit();
    }

    /**
     * Добавление данных в таблицу
     * @param $inr_array массив ключ-значение для добавление в таблицу
     */
    public function add($inr_array)
    {
        $sql = 'INSERT INTO '.$this->table.'('.$this->key_prefix.'id,'.join(',',$this->key_fields).') VALUES("",';
        foreach ($this->key_fields as $field) {  $fields[] = ':'.$field; }
        $sql .= join(',',$fields);
        $sql .= ')';
        $sth =  $this->DB_LINK->prepare($sql);
        foreach ($this->key_fields as $field) {  $sth->bindParam(':'.$field, $inr_array[$field]);}
        $this->DB_LINK->beginTransaction();
        $sth->execute();
        $this->DB_LINK->commit();
    }

    /**
     * Получение данных
     * @param $get_arr массив  ключ-значение ключ поле, значение параметр для поиска
     * @return array данные
     */
    public function get($get_arr)
    {
        $sql = 'SELECT';
        foreach ($this->classes as $klass) {
            $instance = new $klass($this->DB_LINK);
            // Довольно грязноватый хак, но смысла юзать
            if (is_subclass_of($instance,'AbstractList'))  $sql .= ' IFNULL('.$instance->getTable() . '.value,"") AS ' . $instance->getTable() . '_value,';
            // Заменяем NULL на пустую строку на уровне мускуля
        }
        $sql .= $this->getTable() . '.'.$this->getKeyPrefix().'id AS id'; //
        $sql .= ' FROM ' . $this->table . ' ';
        foreach ($this->classes as $klass) {
            $instance = new $klass($this->DB_LINK);
            $this->key_fields[] = $instance->getKeyPrefix() . 'id';
            $sql .= ' LEFT JOIN ' . $instance->getTable() . ' ON ' . $this->table . '.' . $instance->getKeyPrefix() . 'id=' . $instance->getTable() . '.' . $instance->getKeyPrefix() . 'id ';
        }

        $sql .= ' WHERE  ';
        $where_arr = array();
        foreach ($get_arr as $item => $value) {
            $where_arr[] = $this->table . '.' . $item . '=:' . $item;
        }
        $sql .= join(' AND ', $where_arr);
        //die($sql);
        $sth = $this->DB_LINK->prepare($sql);
        foreach ($get_arr as $item => $value) {
            $sth->bindParam(':' . $item, $value);
        }
        $qrh = $sth->execute();
        if (!$qrh) { return array(); }
        return $sth->fetchAll(PDO::FETCH_BOTH);
    }
} 