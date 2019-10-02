<?php if(!defined('IS_CMS')) die();

/**
 * FileInfo: download.php
 *
 * Counts the number of downloads of a file and redirects to download
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3
 * @link     https://github.com/devmount/FileInfo
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

require_once "database.php";

$downloadableFiles = call_user_func(function(){
    global $CatPage;
    
    $dir = __DIR__.'/data/';
    $rtn = [];
    
    $databaseFiles = array_filter(scandir($dir), function($value) use ($dir) {
        return is_file($dir . $value);
    });

    foreach($databaseFiles as $databaseFile) {
        $slug = basename($databaseFile, '.php');
        
        $parts = $CatPage->split_CatPage_fromSyntax($slug, TRUE);
                    
        if($CatPage->exists_File($parts[0], $parts[1])) {

            $id = $slug;

            $rtn[$id] = $CatPage->get_pfadFile( $parts[0] , $parts[1]);
        }            
    }

    return $rtn;
});

if ($_POST['submit'] != '') {
    // get formula data
    $downloadableFileID = $_POST['downloadable_file_id'];

    if(!array_key_exists($downloadableFileID, $downloadableFiles)) {
        die('File not found');
    }

    $dbfile = __DIR__ . '/data/' . $downloadableFileID . '.php';

    $file = $downloadableFiles[$downloadableFileID];
    $pathinfo = pathinfo($file);
    $filename  = $pathinfo['basename'];
    $file_ext   = $pathinfo['extension'];
    $file_size  = filesize($file);

    if( !file_exists($file) ) die("File not found");

    // load current counter value
    $count = intval(Database::loadArray($dbfile));
    $count++;

    // save incremented counter value
    Database::saveArray($dbfile, $count);

    // set the headers, prevent caching
    header("Pragma: public");
    header("Expires: -1");
    header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    // set the mime type based on extension
    $mtype_default = "application/octet-stream";
    $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );
    $mtype = isset($mime_types[$file_ext]) ? $mime_types[$file_ext] : $mtype_default;
    header("Content-Type: " . $mtype);
    header("Content-Length: $file_size");
    readfile($file);
    exit();
}

?>