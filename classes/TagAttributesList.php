<?php

/*
 * Список атрибутов хмл нод
 */
class TagAttributesList extends AbstractList
{
    protected $table = 'tagattributes';
    protected $uniq = true;
    protected $key_prefix = 'tagattr_';
} 