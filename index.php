<?php
/**
 * Автолонгинг классов
 * @param $class
 */
function __autoload($class)
{
    $classPath = strtr($class, array('_' => '/'));
    $classPath = 'classes/' . $classPath . '.php';
    if (!file_exists($classPath)) {
        die('cant load file:' . $classPath . ' for ' . $class);
    }
    include($classPath);
}


$dbh = new PDO('mysql:host=localhost;dbname=test', 'test', '123', array(PDO::ATTR_PERSISTENT => true));

// name , ["type"]=>  string(8) "text/xml" error = 0
$filename = '';
$xml_uploaded_data = '';
$cur_file_id = intval($_GET['cur_file_id']);
if ($_FILES['file']) {
    $xml_uploaded_data = file_get_contents($_FILES['file']['tmp_name']);
    $filename = $_FILES['file']['name'];
}

if ($xml_uploaded_data != '') {
    libxml_use_internal_errors(true);
    $sxe = simplexml_load_string($xml_uploaded_data);
    if (!$sxe)
    {
        $xml_errors = libxml_get_errors();
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Ajax
            $xml_errors_str = '';
            foreach($xml_errors as $err) { $xml_errors_str .= $err->message."\r\n";  }
            die('Ошибки XML:'.$xml_errors_str);
        }
    }
    if ($sxe) {
        $s2b = new SimpleXMLtoDB($dbh, $sxe, $filename);
        $s2b->parse();
        $cur_file_id = $s2b->getFileId();
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Ajax
            die( $cur_file_id );
        }
    }
}
if ($_GET['file_id']) {
    $s = '';
    $node_id = intval($_GET['node_id']);
    $file_id = intval($_GET['file_id']);
    $ftj = new FileTagTreeJoin($dbh);
    $atrJoin = new TagAttrJoin($dbh);
    $tags = $ftj->get($node_id, array('file_id' => $file_id));
    $s .= '<ul>';
    foreach ($tags as $tag) {
        $ta = $atrJoin->get(array(
            $ftj->getKeyPrefix() . 'id' => $tag['id']
        ));
        $s .= '<li data-id="' . $tag['id'] . '" data-file-id="' . $file_id . '" class="noclicked">';
        $s .= '<span>';
        $s .= $tag['tags_value'];
        if ($tag['tagvalues_value'] != '') {
            $s .= '(' . $tag['tagvalues_value'] . ')';
        }
        $s .= '</span>';
        foreach ($ta as $attr) {
            $s .= '<br ><small>' . $attr['tagattributes_value'] . '=>' . $attr['tagattributesvalues_value'] . '</small>';
        }
        $s .= '<div></div>';
        $s .= '</li>';
    }
    $s .= '</ul>';
    die($s);
}
$files_obj = new FilesList($dbh);
$files = array_slice(array_reverse($files_obj->getAll()), 0, 5); // Типа 5 последних
$filelist_str = '<ul>';
foreach($files as $fl) {
$filelist_str .= '<li><a href="" data-id="'. $fl['file_id'].'">'. $fl['value'].'</a></li>';
}
$filelist_str .= '</ul>';
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Ajax
    if ($_GET['filelist'])
    {
        die($filelist_str);
    }
}
include('templates/index.html');