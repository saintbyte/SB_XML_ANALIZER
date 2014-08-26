<?php

/**
 Дерево Хмл нодов , с файлами
 */
class FileTagTreeJoin extends AbstractTreeJoin
{
    protected $classes = array('FilesList', 'TagsList', 'TagValueList');
    protected $table = 'filetagtreejoin';
    protected $key_prefix = 'filetagtree_';
} 