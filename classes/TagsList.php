<?php

/**
 * Class TagsList
 * Список тагов
 */
class TagsList extends AbstractList
{
    protected $table = 'tags';
    protected $uniq = true;
    protected $key_prefix = 'tag_';
} 