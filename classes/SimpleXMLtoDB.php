<?php

/**
 * Class SimpleXMLtoDB
 * Класс который складывает XML в базу данных через кучу списков и деревьев
 */
class SimpleXMLtoDB
{
    /**
     * Подключение к базе данных
     * @var PDO Обьект PDO
     */
    private $DB_LINK;

    /**
     * Исходный обьект XML
     * @var SimpleXML Обьект SimpleXML
     */
    private $xml;

    private $tags;
    private $tagvalues;
    private $tagattributes;
    private $tagattributesvalues;
    private $tagattrjoin;
    private $filetagjoin;
    private $file_id;

    /**
     * @param $dbh  PDO обьект подключение к базе
     * @param $simpleXML Обьект SimpleXML для сохранения в базу
     */
    public function __construct($dbh, $simpleXML, $filename)
    {
        $this->DB_LINK = $dbh;
        $this->xml = $simpleXML;
        $this->tags = new TagsList($this->DB_LINK);
        $this->tagvalues = new TagValueList($this->DB_LINK);
        $this->tagattributes = new TagAttributesList($this->DB_LINK);
        $this->tagattributesvalues = new TagAttributesValuesList($this->DB_LINK);
        $this->tagattrjoin = new TagAttrJoin($this->DB_LINK);

        $this->filetagjoin = new FileTagTreeJoin($this->DB_LINK);
        $files = new FilesList($this->DB_LINK);
        $this->file_id = $files->add($filename);
    }
    /**
     * Возращает текущий file_id
     * @return ID файла
     *
     */
    public function getFileId()
    {
        return $this->file_id;
    }

    /**
     * разпарсить xml и положить в базу
     */
    public function parse()
    {
        $this->recursiveParse($this->xml, 0);
    }

    /**
     * процедура для рекурсивного вызова
     * @param $xml - Simple XML обьект.
     * @param $parent_id - ID Родителя
     */
    private function recursiveParse($xml, $parent_id)
    {
        $tag_id = $this->tags->getOrAdd($xml->getName());
        $tag_value = trim($xml->__toString()); // Это конечно не совсем тру,  но пробелы для человекочитаемости все портят - так что патчим их
        $tagvalue_id = 0;
        if ($tag_value != '') {
            $tagvalue_id = $this->tagvalues->getOrAdd($xml->__toString());
        }
        $cur_parent_id = $this->filetagjoin->add($parent_id,
            array(
                'file_id' => $this->file_id,
                $this->tags->getKeyPrefix() . 'id' => $tag_id,
                $this->tagvalues->getKeyPrefix() . 'id' => $tagvalue_id
            ));
        //print '<li>' . $xml->getName() . ' ' . $tag_id . '(' . $xml->__toString() . ') ';
        //print '<small>';
        foreach ($xml->attributes() as $attr => $attr_value) {
            $tagAttr_value_id = $this->tagattributes->getOrAdd($attr);
            $tagAttrValue_value_id = $this->tagattributesvalues->getOrAdd($attr_value);
            $this->tagattrjoin->add(array(
                $this->filetagjoin->getKeyPrefix().'id' => $cur_parent_id,
                $this->tagattributes->getKeyPrefix().'id' =>  $tagAttr_value_id,
                $this->tagattributesvalues->getKeyPrefix().'id' =>  $tagAttrValue_value_id
            ));
        }
        if ($xml->count() > 0) {
            foreach ($xml->children() as $ch) {
                $this->recursiveParse($ch, $cur_parent_id);
            }
        }
    }
}