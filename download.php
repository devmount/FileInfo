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
 * @author   DEVMOUNT <mail@devmount.de>
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
    $return_url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    $catfile = filter_var($_POST['catfile'], FILTER_SANITIZE_URL);

    // load current counter value
    $count = intval(Database::loadArray('data/' . $catfile . '.php'));
    $count++;

    // save incremented counter value
    Database::saveArray('data/' . $catfile . '.php', $count);

    // redirect to file to be downloaded
    header('Location: ' . $return_url);
}

?>