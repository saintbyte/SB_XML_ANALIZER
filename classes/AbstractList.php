<?php

/**
 * Class Abstractlist
 * Абстрактный класс списков
 */
abstract class AbstractList extends DBObject
{
    /**
     * Префикс у ключевого поля, например file_id
     * Исключительно чтоб структрура БД была понятна
     * @var string
     */
    protected $key_prefix = '';
    /**
     * Таблица в которой хранятся список
     * @var string
     */
    protected $table;

    /**
     * уникальный список или нет
     * @var bool
     */
    protected $uniq = false;

    /**
     * Конструктор абстркного класса списка
     * @param object $DB_LINK - подключение к базе в виде обьекта PDO
     */
    public function __construct($DB_LINK)
    {
        parent::__construct($DB_LINK);
    }

    /**
     * Создание таблицы для хранения списка
     */
    public function createTable()
    {
        $sql = '
                CREATE TABLE IF NOT EXISTS ' . $this->table . ' (
                     `' . $this->key_prefix . 'id` int(11) NOT NULL AUTO_INCREMENT,
                     `value` varchar(255) NOT NULL,
                     PRIMARY KEY (`' . $this->key_prefix . 'id`)';
        if ($this->uniq) {
            $sql .= ' , ';
            $sql .= 'UNIQUE KEY `value` (`value`)';
        }
        $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        $this->DB_LINK->beginTransaction();
        $this->DB_LINK->query($sql);
        $this->DB_LINK->commit();
    }

    /**
     * Получить все элементы списка
     * @return array
     */
    public function getAll()
    {
        $result = array();
        $sql = 'SELECT * FROM ' . $this->table . '';
        $qhr = $this->DB_LINK->query($sql);
        if (!$qhr) return array();
        foreach ($qhr as $row) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Получить ID по значению
     * @param $value
     */
    public function getByValue($value)
    {
        $sql = 'SELECT ' . $this->key_prefix . 'id FROM ' . $this->table . ' WHERE value=?';
        try {
            $qh = $this->DB_LINK->prepare($sql);
            $qhr = $qh->execute(array($value));
            if (!$qhr) {
                return 0;
            }
            $result = $qh->fetch(PDO::FETCH_BOTH);
            return $result[0];
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Найти и вернуть id , если нет добавить и тоже вернуть id
     * @param $value
     * @return int ID элемента списка
     */
    public function getOrAdd($value)
    {
        if (!$this->uniq) $this->add($value); // Особого смысла в неуникальном списке
        $id = $this->getByValue($value);
        if ($id > 0) return $id;
        return $this->add($value);
    }

    /**
     * Добавляет элемент в список
     * @param $value - значение элемента списка
     * @return int ID нового элемента списка
     */
    public function add($value)
    {
        $sql = 'INSERT INTO ' . $this->table . '(value) VALUES(?)';
        try {
            $qh = $this->DB_LINK->prepare($sql);
            $this->DB_LINK->beginTransaction();
            $qh->execute(array($value));
            $id = $this->DB_LINK->lastInsertId();
            $this->DB_LINK->commit();
        } catch (PDOException $e) {
            return 0;
        }
        return $id;
    }
} 