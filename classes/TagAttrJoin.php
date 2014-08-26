<?php

/**
 * Class TagAttrJoin
 * Связь Атрибутов с их значениями и нодами в дереве
 */
class TagAttrJoin extends AbstractJoin
{
    protected $classes = array('FileTagTreeJoin', 'TagAttributesList', 'TagAttributesValuesList');
    protected $table = 'tagattrjoin';
    protected $key_prefix = 'tagattrjoin_';
} 