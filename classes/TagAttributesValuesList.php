<?php

/**
 * Class TagAttributesValuesList
 * Список значений атрибутов хмл нод
 */
class TagAttributesValuesList extends AbstractList {
    protected $table = 'tagattributesvalues';
    protected $uniq = true;
    protected $key_prefix = 'tagattrvalue_';
} 