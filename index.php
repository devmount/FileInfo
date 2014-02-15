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
 * @version  GIT: v0.x.jjjj-mm-dd
 * @link     https://github.com/devmount/FileInfo
 * @link     http://devmount.de/Develop/Mozilo%20Plugins/FileInfo.html
 * @see      Verse
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
    const PLUGIN_VERSION = 'v0.x.jjjj-mm-dd';
    const MOZILO_VERSION = '2.0';
    private $_plugin_tags = array(
        'tag1' => '{FileInfo|<file>|<attribute>|<linktext>}',
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
        $marker = array('#LINK#','#TYPE#','#SIZE#','#COUNT#');
        // set type contents
        $types = array(
            $this->getLink($src, $file, $param_linktext),
            $CatPage->get_FileType($file),
            $this->formatFilesize(filesize($url)),
            $this->getCount($file),
        );

        // initialize return content, begin plugin content
        $content = '<!-- BEGIN ' . self::PLUGIN_TITLE . ' plugin content --> ';

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
        $config = array();
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
            $this->_admin_lang->getLanguageValue('description'),
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
     * @param string $file     name of download file
     * @param string $linktext optional text for download link
     *
     * @return html formula
     */
    protected function getLink($src, $file, $linktext = '')
    {
        $text = ($linktext == '') ? urldecode($file) : $linktext;
        return '<form
                    class="FileInfoDownload"
                    action="' . $this->PLUGIN_SELF_URL . 'download.php"
                    method="post"
                >
                    <input name="url" type="hidden" value="' . $src . '" />
                    <input name="file" type="hidden" value="' . $file . '" />
                    <input name="submit" type="submit" value="'. $text . '"/>
                </form>';
    }

    /**
     * gets current hit count of given file
     *
     * @param string $file name of file
     *
     * @return string number of hits
     */
    protected function getCount($file)
    {
        $count = Database::loadArray(
            $this->PLUGIN_SELF_DIR . 'data/' . $file . '.php'
        );
        return ($count == '') ? '0' : $count;
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

}

?>