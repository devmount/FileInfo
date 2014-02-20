<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

$FileInfoAdmin = new FileInfoAdmin($plugin);
return $FileInfoAdmin->getContentAdmin();

class FileInfoAdmin extends FileInfo {

    public $admin_lang;
    private $_settings;
    private $_self_dir;

    function FileInfoAdmin($plugin)
    {
        $this->admin_lang = $plugin->admin_lang;
        $this->_settings = $plugin->settings;
        $this->_self_dir = $plugin->PLUGIN_SELF_DIR;
    }

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
        $template = '
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
            </style>
        ';

        // build Template
        $template .= '
            <div class="admin-header ">
            <span>'
                . $this->admin_lang->getLanguageValue(
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
            $template .= '</li></ul>';
            $template .= '</table>';
        }


        $content = '';
        $content .= '<input type="button" value="Reload Page" onClick="window.location.reload()">';
        $content .= $template;
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
            $count = intval($this->getCount(
                $this->_self_dir . 'data/' . substr($catfile, 0, -4))
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
}

?>