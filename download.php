<?php

/**
 * FileInfo: download.php
 *
 * Counts the number of downloads of a file and redirects to download
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <kontakt@devmount.de>
 * @license  GPL v3
 * @link     https://github.com/devmount/FileInfo
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

require_once "database.php";

if ($_POST['submit'] != '') {
    // get formula data
    $return_url = $_POST['url'];
    $filename = $_POST['file'];

    // load current counter value
    $count = Database::loadArray('data/' . $filename . '.php');
    if ($count == '') {
        $count = 0;
    }
    $count++;

    // save incremented counter value
    Database::saveArray('data/' . $filename . '.php', $count);

    // redirect to file to be downloaded
    header('Location: ' . $return_url);
}

?>