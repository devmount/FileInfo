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
        'tag1' => '{FileInfo|<file>|<attribute>}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';
    
    /**
     * set configuration elements, their default values and their configuration
     * parameters
     * 
     * @var array $_confdefault
     *      text     => default, type, maxlength, size, regex
     *      textarea => default, type, cols, rows, regex
     *      password => default, type, maxlength, size, regex, saveasmd5
     *      check    => default, type
     *      radio    => default, type, descriptions
     *      select   => default, type, descriptions, multiselect
     */
    private $_confdefault = array(
        'text' => array(
            'string',
            'text',
            '100',
            '5',
            "/^[0-9]{1,3}$/",
        ),
        'textarea' => array(
            'string',
            'textarea',
            '10',
            '10',
            "/^[a-zA-Z0-9]{1,10}$/",
        ),
        'password' => array(
            'string',
            'password',
            '100',
            '5',
            "/^[a-zA-Z0-9]{8,20}$/",
            true,
        ),
        'check' => array(
            true,
            'check',
        ),
        'radio' => array(
            'red',
            'radio',
            array('red', 'green', 'blue'),
        ),
        'select' => array(
            'bike',
            'select',
            array('car','bike','plane'),
            false,
        ),
    );

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

        // get language labels
        // $label = $this->_cms_lang->getLanguageValue('label');

        // get params
        list($param_file, $param_type)
            = $this->makeUserParaArray($value, false, '|');

        // get conf and set default
        $conf = array();
        foreach ($this->_confdefault as $elem => $default) {
            $conf[$elem] = array(
                ($this->settings->get($elem) == '')
                    ? $default[0]
                    : $this->settings->get($elem),
                $default[1],
            );
        }

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
        // get file source path
        $url = $CatPage->get_pfadFile($cat, $file);

        // initialize return content, begin plugin content
        $content = '<!-- BEGIN ' . self::PLUGIN_TITLE . ' plugin content --> ';
        
        // handle different types
        switch ($param_type) {
        // returns a download link to the given file (necessary for counting)
        case 'link':
            $content .= '<a href ="' . $src . '">' . urldecode($file) . '</a>';
            break;
        
        // returns filetype
        case 'type':
            $content .= $CatPage->get_FileType($file);
            break;
        
        // returns filesize
        case 'size':
            $content .= $this->formatFilesize(filesize($url));
            break;
        
        default:
            return $this->throwError(
                $this->_cms_lang->getLanguageValue(
                    'error_invalid_type',
                    $param_type
                )
            );
            break;
        }

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

        // read configuration values
        foreach ($this->_confdefault as $key => $value) {
            // handle each form type
            switch ($value[1]) {
            case 'text':
                $config[$key] = $this->confText(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'textarea':
                $config[$key] = $this->confTextarea(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'password':
                $config[$key] = $this->confPassword(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    ),
                    $value[5]
                );
                break;

            case 'check':
                $config[$key] = $this->confCheck(
                    $this->_admin_lang->getLanguageValue('config_' . $key)
                );
                break;

            case 'radio':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confRadio(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions
                );
                break;

            case 'select':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confSelect(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions,
                    $value[3]
                );
                break;

            default:
                break;
            }
        }

        // Template CSS
        $css_admin_header = '
            margin: -0.4em -0.8em -5px -0.8em;
            padding: 10px;
            background-color: #234567;
            color: #fff;
            text-shadow: #000 0 1px 3px;
        ';
        $css_admin_header_span = '
            font-size:20px;
            vertical-align: top;
            padding-top: 3px;
            display: inline-block;
        ';
        $css_admin_subheader = '
            margin: -0.4em -0.8em 5px -0.8em;
            padding: 5px 9px;
            background-color: #ddd;
            color: #111;
            text-shadow: #fff 0 1px 2px;
        ';
        $css_admin_li = '
            background: #eee;
        ';
        $css_admin_default = '
            color: #aaa;
            padding-left: 6px;
        ';

        // build Template
        // $config['--template~~'] = '
        //     <div style="' . $css_admin_header . '">
        //     <span style="' . $css_admin_header_span . '">'
        //         . $this->_admin_lang->getLanguageValue(
        //              'admin_header',
        //              self::PLUGIN_TITLE
        //         )
        //     . '</span>
        //     <a href="' . self::PLUGIN_DOCU . '" target="_blank">
        //     <img style="float:right;" src="' . self::LOGO_URL . '" />
        //     </a>
        //     </div>
        // </li>
        // <li class="mo-in-ul-li ui-widget-content" style="' . $css_admin_li . '">
        //     <div style="' . $css_admin_subheader . '">'
        //     . $this->_admin_lang->getLanguageValue('admin_spacing') . '</div>
        //     <div style="margin-bottom:5px;">
        //         {test1_text}
        //         {test1_description}
        //         <span style="' . $css_admin_default .'">
        //             [' . $this->_confdefault['test1'][0] .']
        //         </span>
        //     </div>
        //     <div style="margin-bottom:5px;">
        //         {test2_text}
        //         {test2_description}
        //         <span style="' . $css_admin_default .'">
        //             [' . $this->_confdefault['test2'][0] .']
        //         </span>
        // ';

        return $config;
    }

    /**
     * sets default backend configuration elements, if no plugin.conf.php is 
     * created yet
     * 
     * @return Array configuration
     */
    function getDefaultSettings()
    {
        $config = array('active' => 'true');
        foreach ($this->_confdefault as $elem => $default) {
            $config[$elem] = $default[0];
        }
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
     * creates configuration for text fields
     * 
     * @param string $description Label
     * @param string $maxlength   Maximum number of characters
     * @param string $size        Size
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     * 
     * @return Array  Configuration
     */
    protected function confText(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for textareas
     * 
     * @param string $description Label
     * @param string $cols        Number of columns
     * @param string $rows        Number of rows
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     * 
     * @return Array  Configuration
     */
    protected function confTextarea(
        $description,
        $cols = '',
        $rows = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($cols != '') {
            $conftext['cols'] = $cols;
        }
        if ($rows != '') {
            $conftext['rows'] = $rows;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for password fields
     * 
     * @param string  $description Label
     * @param string  $maxlength   Maximum number of characters
     * @param string  $size        Size
     * @param string  $regex       Regular expression for allowed input
     * @param string  $regex_error Wrong input error message
     * @param boolean $saveasmd5   Safe password as md5 (recommended!)
     * 
     * @return Array   Configuration
     */
    protected function confPassword(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = '',
        $saveasmd5 = true
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        $conftext['saveasmd5'] = $saveasmd5;
        return $conftext;
    }

    /**
     * creates configuration for checkboxes
     * 
     * @param string $description Label
     * 
     * @return Array  Configuration
     */
    protected function confCheck($description)
    {
        // required properties
        return array(
            'type' => 'checkbox',
            'description' => $description,
        );
    }

    /**
     * creates configuration for radio buttons
     * 
     * @param string $description  Label
     * @param string $descriptions Array Single item labels
     * 
     * @return Array Configuration
     */
    protected function confRadio($description, $descriptions)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
        ); 
    }

    /**
     * creates configuration for select fields
     * 
     * @param string  $description  Label
     * @param string  $descriptions Array Single item labels
     * @param boolean $multiple     Enable multiple item selection
     * 
     * @return Array   Configuration
     */
    protected function confSelect($description, $descriptions, $multiple = false)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
            'multiple' => $multiple,
        );
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