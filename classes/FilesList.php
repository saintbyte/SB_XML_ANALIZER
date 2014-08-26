<?php

/**
 * Class FilesList Список файлов , список не уникальный
 */
class FilesList extends AbstractList
{
    protected $table = 'files';
    protected $uniq = false;
    protected $key_prefix = 'file_';
}