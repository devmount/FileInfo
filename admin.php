<?php

/**
 * moziloCMS Plugin: FileInfoAdmin
 *
 * Offers a list of all registered files with an overview of information
 * and administration tools like resetting or deleting file infos.
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

// only allow moziloCMS administration environment
if (!defined('IS_ADMIN') or !IS_ADMIN) {
    die();
}

// instantiate FileInfoAdmin class
$FileInfoAdmin = new FileInfoAdmin($plugin);
// handle post input
$FileInfoAdmin->checkPost();
// return admin content
return $FileInfoAdmin->getContentAdmin();

/**
 * FileInfoAdmin Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <kontakt@devmount.de>
 * @license  GPL v3
 * @link     https://github.com/devmount/FileInfo
 */
class FileInfoAdmin extends FileInfo
{
    // language
    public $admin_lang;
    // plugin settings
    private $_settings;
    // PLUGIN_SELF_DIR from FileInfo
    private $_self_dir;
    // PLUGIN_SELF_URL from FileInfo
    private $_self_url;

    /**
     * constructor
     *
     * @param object $plugin FileInfo plugin object
     */
    function FileInfoAdmin($plugin)
    {
        $this->admin_lang = $plugin->admin_lang;
        $this->_settings = $plugin->settings;
        $this->_self_dir = $plugin->PLUGIN_SELF_DIR;
        $this->_self_url = $plugin->PLUGIN_SELF_URL;
    }

    /**
     * creates plugin administration area content
     *
     * @return string HTML output
     */
    function getContentAdmin()
    {
        global $CatPage;

        // get all registered files
        $catfiles = array_diff(
            scandir($this->_self_dir . 'data', 1),
            array('..', '.')
        );

        // build (category => file1, file2) structure
        $sortedfiles = array();

        foreach ($catfiles as $catfile) {
            list($cat, $file) = explode('%3A', $catfile);
            $sortedfiles[$cat][] = substr($file, 0, -4);
        }

        // Template CSS
        $content = '
            <style>
            .admin-header {
                position: relative;
                width: 96%;
                margin: 0 auto;
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
                margin: -0.4em 0 5px -0.8em;
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
            ul {
                width: 98%;
                padding: 0;
                margin: 0 auto;
            }
            .admin-li table {
                width: 100%;
            }
            .admin-li table tr:hover td {
                background: #fff;
            }
            .img-button {

            }
            .icon-reset {}
            .icon-delete {}
            .icon-refresh {}
            </style>
        ';

        // build Template
        $content .= '
            <div class="admin-header ">
            <span>'
                . $this->admin_lang->getLanguageValue(
                    'admin_header',
                    self::PLUGIN_TITLE
                )
            . '</span>
            <a
                class="img-button icon-refresh"
                title="refresh"
                onclick="window.location.reload()"
            >
                Refresh
            </a>
            <a href="' . self::PLUGIN_DOCU . '" target="_blank">
                <img style="float:right;" src="' . self::LOGO_URL . '" />
            </a>
            </div>
        ';

        // find all categories
        foreach ($sortedfiles as $cat => $files) {
            $content .= '
            <ul>
                <li class="mo-in-ul-li ui-widget-content admin-li">
                    <div class="admin-subheader">'
                    . urldecode($cat)
                    . '</div>
                    <table cellspacing="0" cellpadding="4px">
                        <colgroup>
                            <col style="width:*;">
                            <col style="width:80px;">
                            <col style="width:80px;">
                            <col style="width:80px;">
                            <col style="width:80px;">
                        </colgroup>
                        <tr>
                            <th>'
                            . $this->admin_lang->getLanguageValue('admin_filename')
                            . '</th>
                            <th style="text-align:center;">'
                            . $this->admin_lang->getLanguageValue('admin_filetype')
                            . '</th>
                            <th style="text-align:center;">'
                            . $this->admin_lang->getLanguageValue('admin_filesize')
                            . '</th>
                            <th style="text-align:center;">'
                            . $this->admin_lang->getLanguageValue('admin_filecount')
                            . '</th>
                            <th>'
                            . $this->admin_lang->getLanguageValue('admin_action')
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
                $count = $this->getCount($this->_self_dir . 'data/' . $catfile);
                $maxcount = $this->getMaxCount();
                $percentcount = round($count/$maxcount*100, 1);

                // calculate percentage of maximum size
                $size = filesize($url);
                $maxsize = $this->getMaxSize();
                $percentsize = round($size/$maxsize*100, 1);

                $content .= '
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
                        <td>
                            <form
                                id="fileinforeset"
                                action="' . URL_BASE . ADMIN_DIR_NAME . '/index.php"
                                method="post"
                            >
                                <input type="hidden" name="pluginadmin"
                                    value="' . PLUGINADMIN . '"
                                />
                                <input type="hidden" name="action"
                                    value="' . ACTION . '"
                                />
                                <input type="hidden" name="r"
                                    value="' . $catfile . '"
                                />
                            </form>
                            <a
                                class="img-button icon-reset"
                                title="reset"
                                onclick="
                                    if(confirm(\'reset?\'))
                                    document.getElementById(\'fileinforeset\')
                                        .submit()"
                            >reset</a>
                            <form
                                id="fileinfodelete"
                                action="' . URL_BASE . ADMIN_DIR_NAME . '/index.php"
                                method="post"
                            >
                                <input type="hidden" name="pluginadmin"
                                    value="' . PLUGINADMIN . '"
                                />
                                <input type="hidden" name="action"
                                    value="' . ACTION . '"
                                />
                                <input type="hidden" name="d"
                                    value="' . $catfile . '"
                                />
                            </form>
                            <a
                                class="img-button icon-delete"
                                title="delete"
                                onclick="
                                    if(confirm(\'delete?\'))
                                    document.getElementById(\'fileinfodelete\')
                                        .submit()"
                            >delete</a>
                        </td>
                    </tr>';
            }
            $content .= '</table>';
            $content .= '</li></ul>';
        }

        return $content;
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
            scandir($this->_self_dir . 'data', 1),
            array('..', '.')
        );

        // initialize counter
        $max = 0;

        // compare current max with each download number
        foreach ($catfiles as $catfile) {
            $count = intval(
                $this->getCount(
                    $this->_self_dir . 'data/' . substr($catfile, 0, -4)
                )
            );
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
            scandir($this->_self_dir . 'data', 1),
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

    /**
     * checks and handles post variables
     *
     * @return boolean success
     */
    function checkPost()
    {
        // handle actions
        $reset = getRequestValue('r', "post", false);
        $delete = getRequestValue('d', "post", false);
        if ($reset != '') {
            $catfile = $reset;
            return $this->resetCount($catfile);
        }
        if ($delete != '') {
            $catfile = $delete;
            return $this->deleteCount($catfile);
        }
    }

    /**
     * resets the download counts of given file to 0
     *
     * @param string $catfile file to reset count
     *
     * @return boolean success
     */
    protected function resetCount($catfile)
    {
        return Database::saveArray(
            $this->_self_dir . 'data/' . $catfile . '.php',
            '0'
        );
    }

    /**
     * deletes the db file of given file
     *
     * @param string $catfile file to delete db file
     *
     * @return boolean success
     */
    protected function deleteCount($catfile)
    {
        return Database::deleteFile($this->_self_dir . 'data/' . $catfile . '.php');
    }
}

?>