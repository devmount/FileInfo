<?php

/**
 * moziloCMS Plugin: FileInfo
 *
 * Reads special file information like type, size
 * Counts number of downloads for each file
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <kontakt@devmount.de>
 * @license  GPL v3
 * @version  GIT: v0.1.2014-02-18
 * @link     https://github.com/devmount/FileInfo
 * @link     http://devmount.de/Develop/Mozilo%20Plugins/FileInfo.html
 * @see      Many are the plans in a person’s heart,
 *           but it is the Lord’s purpose that prevails.
 *            - The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

// only allow moziloCMS environment
if (!defined('IS_CMS')) {
    die();
}

// add database class
require_once "database.php";

/**
 * FileInfo Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <kontakt@devmount.de>
 * @license  GPL v3
 * @link     https://github.com/devmount/FileInfo
 */
class FileInfo extends Plugin
{
    // language
    private $_admin_lang;
    private $_cms_lang;

    // plugin information
    const PLUGIN_AUTHOR  = 'HPdesigner';
    const PLUGIN_DOCU
        = 'http://devmount.de/Develop/Mozilo%20Plugins/FileInfo.html';
    const PLUGIN_TITLE   = 'FileInfo';
    const PLUGIN_VERSION = 'v0.1.2014-02-18';
    const MOZILO_VERSION = '2.0';
    private $_plugin_tags = array(
        'tag' => '{FileInfo|<file>|<template>|<linktext>}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';

    /**
     * creates plugin content
     *
     * @param string $value Parameter divided by '|'
     *
     * @return string HTML output
     */
    function getContent($value)
    {
        global $CMS_CONF;
        global $syntax;
        global $CatPage;

        $this->_cms_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/cms_language_'
            . $CMS_CONF->get('cmslanguage')
            . '.txt'
        );

        // get params
        list($param_file, $param_template, $param_linktext)
            = $this->makeUserParaArray($value, false, '|');

        // check if cat:file construct is correct
        if (!strpos($param_file, '%3A')) {
            return $this->throwError(
                $this->_cms_lang->getLanguageValue(
                    'error_invalid_input',
                    urldecode($param_file)
                )
            );
        }

        // get category and file name
        list($cat, $file) = explode('%3A', $param_file);

        // check if file exists
        if (!$CatPage->exists_File($cat, $file)) {
            return $this->throwError(
                $this->_cms_lang->getLanguageValue(
                    'error_invalid_file',
                    urldecode($file),
                    urldecode($cat)
                )
            );
        }

        // get file source url
        $src = $CatPage->get_srcFile($cat, $file);
        // get file path url
        $url = $CatPage->get_pfadFile($cat, $file);

        // set markers
        $marker = array('#LINK#','#TYPE#','#SIZE#','#COUNT#','#DATE#');
        // set type contents
        $types = array(
            $this->getLink($src, $param_file, $param_linktext), // #LINK#
            $this->getType($file),                              // #TYPE#
            $this->formatFilesize(filesize($url)),              // #SIZE#
            $this->getCount($param_file),                       // #COUNT#
            $this->formatFiledate(filectime($url)),             // #DATE#
        );

        // initialize return content, begin plugin content
        $content = '<!-- BEGIN ' . self::PLUGIN_TITLE . ' plugin content --> ';

        // fill template with content
        if ($param_template == '') {
            $param_template = '#LINK#';
        }
        $content .= str_replace($marker, $types, $param_template);

        // end plugin content
        $content .= '<!-- END ' . self::PLUGIN_TITLE . ' plugin content --> ';

        return $content;
    }

    /**
     * sets backend configuration elements and template
     *
     * @return Array configuration
     */
    function getConfig()
    {
        global $CatPage;

        $config = array();

        // get all registered files
        $catfiles = array_diff(
            scandir($this->PLUGIN_SELF_DIR . 'data', 1),
            array('..', '.')
        );

        // build (category => file1, file2) structure
        $sortedfiles = array();

        foreach ($catfiles as $catfile) {
            list($cat, $file) = explode('%3A', $catfile);
            $sortedfiles[$cat][] = substr($file, 0, -4);
        }

        // Template CSS
        $template = '
            <style>
            .admin-header {
                margin: -0.4em -0.8em -5px -0.8em;
                padding: 10px;
                background-color: #234567;
                color: #fff;
                text-shadow: #000 0 1px 3px;
            }
            .admin-header span {
                font-size:20px;
                vertical-align: top;
                padding-top: 3px;
                display: inline-block;
            }
            .admin-subheader {
                margin: -0.4em -0.8em 5px -0.8em;
                padding: 5px 9px;
                background-color: #ddd;
                color: #111;
                text-shadow: #fff 0 1px 2px;
            }
            .admin-li {
                background: #eee;
            }
            .admin-default {
                color: #aaa;
                padding-left: 6px;
            }
            .admin-link {
                text-decoration:none;
            }
            .admin-link:hover {
                color:#666;
            }
            .admin-li table tr:hover td {
                background: #fff;
            }
            </style>
        ';

        // build Template
        $template .= '
            <div class="admin-header">
            <span>'
                . $this->_admin_lang->getLanguageValue(
                    'admin_header',
                    self::PLUGIN_TITLE
                )
            . '</span>
            <a href="' . self::PLUGIN_DOCU . '" target="_blank">
            <img style="float:right;" src="' . self::LOGO_URL . '" />
            </a>
            </div>
        ';

        // find all categories
        foreach ($sortedfiles as $cat => $files) {
            $template .= '
                </li>
                <li class="mo-in-ul-li ui-widget-content admin-li">
                    <div class="admin-subheader">'
                    . urldecode($cat)
                    . '</div>
                    <table width="100%" cellspacing="0" cellpadding="4px">
                        <colgroup>
                            <col style="width:*;">
                            <col style="width:80px;">
                            <col style="width:80px;">
                            <col style="width:80px;">
                        </colgroup>
                        <tr>
                            <th>'
                            . $this->_admin_lang->getLanguageValue('admin_filename')
                            . '</th>
                            <th style="text-align:center;">'
                            . $this->_admin_lang->getLanguageValue('admin_filetype')
                            . '</th>
                            <th style="text-align:center;">'
                            . $this->_admin_lang->getLanguageValue('admin_filesize')
                            . '</th>
                            <th style="text-align:center;">'
                            . $this->_admin_lang->getLanguageValue('admin_filecount')
                            . '</th>
                        </tr>
                ';

            // find all files in current category
            foreach ($files as $filename) {
                // get filepaths
                $url = $CatPage->get_pfadFile($cat, $filename);
                $src = $CatPage->get_srcFile($cat, $filename);

                // rebuild catfile form
                $catfile = $cat . '%3A' . $filename;

                // calculate percentage of maximum counts
                $count = $this->getCount($catfile);
                $maxcount = $this->getMaxCount();
                $percentcount = round($count/$maxcount*100, 1);

                // calculate percentage of maximum size
                $size = filesize($url);
                $maxsize = $this->getMaxSize();
                $percentsize = round($size/$maxsize*100, 1);

                $template .= '
                    <tr>
                        <td>
                            <a href="' . $src . '" class="admin-link">'
                            . urldecode($filename)
                            . '</a>
                        </td>
                        <td style="text-align:center;padding-right:10px;">'
                            . $this->getType(urldecode($filename))
                        . '</td>
                        <td style="text-align:right;padding-right:10px;">
                            <div style="
                                padding: 1px 4px;
                                background: linear-gradient(
                                    to left,
                                    #abcdef ' . $percentsize . '%,
                                    transparent ' . $percentsize . '%
                                );
                            ">'
                            . $this->formatFilesize($size)
                            . '</div>
                        </td>
                        <td>
                            <div style="
                                padding: 1px 4px;
                                background: linear-gradient(
                                    to right,
                                    #abcdef ' . $percentcount . '%,
                                    transparent ' . $percentcount . '%
                                );
                            ">'
                            . $count
                            . '</div>
                        </td>
                    </tr>';
            }
            $template .= '</table>';
        }

        $template .= '<div>';
        $config['--template~~'] = $template;

        return $config;
    }

    /**
     * sets backend plugin information
     *
     * @return Array information
     */
    function getInfo()
    {
        global $ADMIN_CONF;
        $this->_admin_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/admin_language_'
            . $ADMIN_CONF->get('language')
            . '.txt'
        );

        // build plugin tags
        $tags = array();
        foreach ($this->_plugin_tags as $key => $tag) {
            $tags[$tag] = $this->_admin_lang->getLanguageValue('tag_' . $key);
        }

        $info = array(
            '<b>' . self::PLUGIN_TITLE . '</b> ' . self::PLUGIN_VERSION,
            self::MOZILO_VERSION,
            $this->_admin_lang->getLanguageValue(
                'description',
                htmlspecialchars($this->_plugin_tags['tag'])
            ),
            self::PLUGIN_AUTHOR,
            self::PLUGIN_DOCU,
            $tags
        );

        return $info;
    }

    /**
     * builds formula with download link
     *
     * @param string $src      url of download file
     * @param string $catfile  url coded cat:filename
     * @param string $linktext optional text for download link
     *
     * @return html formula
     */
    protected function getLink($src, $catfile, $linktext = '')
    {
        list($cat, $file) = explode('%3A', $catfile);
        $text = ($linktext == '') ? urldecode($file) : $linktext;
        return '<form
                    class="FileInfoDownload"
                    action="' . $this->PLUGIN_SELF_URL . 'download.php"
                    method="post"
                >
                    <input name="url" type="hidden" value="' . $src . '" />
                    <input name="catfile" type="hidden" value="' . $catfile . '" />
                    <input name="submit" type="submit" value="'. $text . '"/>
                </form>';
    }

    /**
     * gets current hit count of given file
     *
     * @param string $catfile name of file
     *
     * @return string number of hits
     */
    protected function getCount($catfile)
    {
        $count = Database::loadArray(
            $this->PLUGIN_SELF_DIR . 'data/' . $catfile . '.php'
        );
        return ($count == '') ? '0' : $count;
    }

    /**
     * gets type extension of given file
     *
     * @param string $file name of file
     *
     * @return html uppercase file type
     */
    protected function getType($file)
    {
        global $CatPage;
        $type
            = '<span style="text-transform:uppercase;">'
            . substr($CatPage->get_FileType($file), 1)
            . '</span>';
        return $type;
    }

    /**
     * throws styled error message
     *
     * @param string $text Content of error message
     *
     * @return string HTML content
     */
    protected function throwError($text)
    {
        return '<div class="' . self::PLUGIN_TITLE . 'Error">'
            . '<div>' . $this->_cms_lang->getLanguageValue('error') . '</div>'
            . '<span>' . $text. '</span>'
            . '</div>';
    }

    /**
     * returns filesize with unit, like 5,32 M
     *
     * @param integer $bytes    number of bytes
     * @param integer $decimals number of decimals
     *
     * @return string formatted filesize
     */
    protected function formatFilesize($bytes, $decimals = 2)
    {
        // $sz = 'BKMGTP';
        $sz = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return
            sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    /**
     * returns formatted filedate
     *
     * @param integer $tstamp timestamp to format
     * @param string  $format optional date format
     *
     * @return string formatted filedate
     */
    protected function formatFiledate($tstamp, $format = 'd.m.Y')
    {
        return date($format, $tstamp);
    }

    /**
     * finds maximum download number of all files
     *
     * @return int maximum download number
     */
    protected function getMaxCount()
    {
        // get all registered files
        $catfiles = array_diff(
            scandir($this->PLUGIN_SELF_DIR . 'data', 1),
            array('..', '.')
        );

        // initialize counter
        $max = 0;

        // compare current max with each download number
        foreach ($catfiles as $catfile) {
            $count = intval($this->getCount(substr($catfile, 0, -4)));
            if ($count > $max) {
                $max = $count;
            }
        }

        return $max;
    }

    /**
     * finds maximum filezise of all files
     *
     * @return int maximum filesize
     */
    protected function getMaxSize()
    {
        global $CatPage;

        // get all registered files
        $catfiles = array_diff(
            scandir($this->PLUGIN_SELF_DIR . 'data', 1),
            array('..', '.')
        );

        // initialize counter
        $max = 0;

        // compare current max with each download number
        foreach ($catfiles as $catfile) {
            list($cat, $file) = explode('%3A', $catfile);
            $filename = substr($file, 0, -4);
            $url = $CatPage->get_pfadFile($cat, $filename);

            $size = intval(filesize($url));
            if ($size > $max) {
                $max = $size;
            }
        }

        return $max;
    }

}

?>