<?php

/**
 * Class TagValueList
 * Список значений xml нод
 */
class TagValueList extends AbstractList
{
    protected $table = 'tagvalues';
    protected $uniq = true;
    protected $key_prefix = 'tagvalues_';
} 