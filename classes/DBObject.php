<?php

/**
 * Class DBObject
 * Класс для работы с обьектами баз данных , например таблицами
 */
abstract class DBObject
{
    /**
     * Таблица в которой хранятся обьект
     * @var string
     */
    protected $table;

    /**
     * Префикс ключевого поля
     * @var string
     */
    protected $key_prefix;
    /**
     * Обьект подключения к базе данных
     * @var PDO
     */
    protected $DB_LINK;
    /**
     *
    */
    public function __construct($DB_LINK)
    {
        $this->DB_LINK = $DB_LINK;
        $this->tryExistsAndCreate();
    }

    /*
     * Проверить если таблица и создать её если нет
     */
    public function tryExistsAndCreate()
    {
        try {
            $sql = 'SHOW TABLES'; // простой запрос и это хорошо потому что закешируется сервером БД
            $has_table = false;
            foreach ($this->DB_LINK->query($sql) as $row) {
                if ($row[0] == $this->table) {
                    $has_table = true;
                    break;
                }
            }
            if (!$has_table) {
                $this->createTable();
            }
        } catch (PDOException $e) {
            echo $e->getMessage(); // Пишим не молчим что проблема
        }
    }

    /**
     * Создание таблицы если надо
     * @abstract
     */
    abstract function createTable();

    /**
     * Возращает имя таблицы
     * @return string имя таблицы
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Возращает ключевое поле
     * @return string префикс ключевого поля
     */
    public function getKeyPrefix()
    {
        return $this->key_prefix;
    }
}