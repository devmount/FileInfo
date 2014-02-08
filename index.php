<?php if(!defined('IS_CMS')) die();

/**
 * Plugin:   pluginDraft
 * @author:  HPdesigner (kontakt[at]devmount[dot]de)
 * @version: v0.x.jjjj-mm-dd
 * @license: GPL
 * @see:     Verse
 *           - The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

class pluginDraft extends Plugin {

	// language
	public $admin_lang;
	private $cms_lang;

	// plugin information
	const plugin_author = 'HPdesigner';
	const plugin_docu = 'http://www.devmount.de/Develop/Mozilo%20Plugins/pluginDraft.html';
	const plugin_title = 'pluginDraft';
	const plugin_version = 'v0.x.jjjj-mm-dd';
	const mozilo_version = '2.0';
	private $plugin_tags = array(
		'tag1' => '{pluginDraft|type|<param1>|<param2>}',
	);

	// set configuration elements, their default values and their configuration parameters
	// 		text => default, type, maxlength, size, regex
	// 		textarea => default, type, cols, rows, regex
	// 		password => default, type, maxlength, size, regex, saveasmd5
	// 		check => default, type
	// 		radio => default, type, descriptions
	// 		select => default, type, descriptions, multiselect
	private $confdefault = array(
		'text'		=> array('def','text','100','5',"/^[0-9]{1,3}$/"),
		'textarea'	=> array('def','textarea','10','10',"/^[a-zA-Z0-9]{1,10}$/"),
		'password'	=> array('g35h34ts','password','100','5',"/^[a-zA-Z0-9]{8,20}$/",true),
		'check'		=> array(true,'check'),
		'radio'		=> array('red','radio',array('red','green','blue')),
		'select'	=> array('bike','select',array('car','bike','plane'),false),
	);

	/**
	 * creates plugin content
	 * @param string $value Parameter divided by '|'
	 * @return string HTML output
	 */
	function getContent($value) {

		global $CMS_CONF;
		global $syntax;

		$this->cms_lang = new Language($this->PLUGIN_SELF_DIR . 'lang/cms_language_' . $CMS_CONF->get('cmslanguage') . '.txt');

		// get language labels
		$label = $this->cms_lang->getLanguageValue('label');

		// get params
		list($param_,$param_,$param_) = $this->makeUserParaArray($value,false,"|");

		// get conf and set default
		$conf = array();
		foreach ($this->confdefault as $elem => $default) {
			$conf[$elem] = array(($this->settings->get($elem) == '') ? $default[0] : $this->settings->get($elem),$default[1]);
		}

		// include jquery and pluginDraft javascript
		$syntax->insert_jquery_in_head('jquery');
		$syntax->insert_in_head('<script type="text/javascript" src="' . $this->PLUGIN_SELF_URL . 'js/pluginDraft.js"></script>');

		// initialize return content and default class
		$content = '<!-- BEGIN ' . self::plugin_title . ' plugin content --> ';
		
		// do something awesome here! ...

		$content .= '<!-- END ' . self::plugin_title . ' plugin content --> ';

		return $content;
	}

	/**
	 * sets backend configuration elements and template
	 * @return Array configuration
	 */
	function getConfig() {

		$config = array();

		// read config values
		foreach ($this->confdefault as $key => $value) {
			switch ($value[1]) {
				case 'text': $config[$key] = $this->confText($this->admin_lang->getLanguageValue('config_' . $key), $value[2], $value[3], $value[4], $this->admin_lang->getLanguageValue('config_' . $key . '_error')); break;
				case 'textarea': $config[$key] = $this->confTextarea($this->admin_lang->getLanguageValue('config_' . $key), $value[2], $value[3], $value[4], $this->admin_lang->getLanguageValue('config_' . $key . '_error')); break;
				case 'password': $config[$key] = $this->confPassword($this->admin_lang->getLanguageValue('config_' . $key), $value[2], $value[3], $value[4], $this->admin_lang->getLanguageValue('config_' . $key . '_error'), $value[5]); break;
				case 'check': $config[$key] = $this->confCheck($this->admin_lang->getLanguageValue('config_' . $key)); break;
				case 'radio':
					$descriptions = array();
					foreach ($value[2] as $desc) $descriptions[$desc] = $this->admin_lang->getLanguageValue('config_' . $key . '_' . $desc);
					$config[$key] = $this->confRadio($this->admin_lang->getLanguageValue('config_' . $key),$descriptions); break;
				case 'select': 
					$descriptions = array();
					foreach ($value[2] as $desc) $descriptions[$desc] = $this->admin_lang->getLanguageValue('config_' . $key . '_' . $desc);
					$config[$key] = $this->confSelect($this->admin_lang->getLanguageValue('config_' . $key),$descriptions,$value[3]); break;
				default: break;
			}
		}

		// Template CSS
		$css_admin_header = 'margin: -0.4em -0.8em -5px -0.8em; padding: 10px; background-color: #234567; color: #fff; text-shadow: #000 0 1px 3px;';
		$css_admin_subheader = 'margin: -0.4em -0.8em 5px -0.8em; padding: 5px 9px; background-color: #ddd; color: #111; text-shadow: #fff 0 1px 2px;';
		$css_admin_li = 'background: #eee;';
		$css_admin_default = 'color: #aaa;padding-left: 6px;';

		// build Template
		$config['--template~~'] = '
				<div style="' . $css_admin_header . '"><span style="font-size:20px;vertical-align: top;padding-top: 3px;display: inline-block;">'
				. $this->admin_lang->getLanguageValue('admin_header',self::plugin_title)
				. '</span><a href="' . self::plugin_docu . '" target="_blank"><img style="float:right;" src="http://media.devmount.de/logo_pluginconf.png" /></a></div>
			</li>
			<li class="mo-in-ul-li mo-inline ui-widget-content ui-corner-all ui-helper-clearfix" style="' . $css_admin_li . '">
				<div style="' . $css_admin_subheader . '">' . $this->admin_lang->getLanguageValue('admin_spacing') . '</div>
				<div style="margin-bottom:5px;">{test1_text} {test1_description} <span style="' . $css_admin_default .'">[' . $this->confdefault['test1'][0] .']</span></div>
				<div style="margin-bottom:5px;">{test2_text} {test2_description} <span style="' . $css_admin_default .'">[' . $this->confdefault['test2'][0] .']</span>
		';

		return $config;
	}

	/**
	 * sets default backend configuration elements, if no plugin.conf.php is created yet
	 * @return Array configuration
	 */
	function getDefaultSettings() {
		$config = array('active' => 'true');
		foreach ($this->confdefault as $elem => $default) {
			$config[$elem] = $default[0];
		}
		return $config;
	}

	/**
	 * sets backend plugin information
	 * @return Array information
	 */
	function getInfo() {

		global $ADMIN_CONF;
		$this->admin_lang = new Language($this->PLUGIN_SELF_DIR . 'lang/admin_language_' . $ADMIN_CONF->get('language') . '.txt');

		// build plugin tags
		$tags = array();
		foreach ($this->plugin_tags as $key => $tag) $tags[$tag] = $this->admin_lang->getLanguageValue('tag_' . $key);

		$info = array(
			'<b>' . self::plugin_title . '</b> ' . self::plugin_version,
			self::mozilo_version,
			$this->admin_lang->getLanguageValue('description'), 
			self::plugin_author,
			self::plugin_docu,
			$tags
		);

		return $info;
	}

	/**
	 * creates configuration for text fields
	 * @param string $description Label
	 * @param string $maxlength Maximum number of characters
	 * @param string $size Size
	 * @param string $regex Regular expression for allowed input
	 * @param string $regex_error Wrong input error message
	 * @return Array Configuration
	 */
	protected function confText($description, $maxlength='', $size='', $regex='', $regex_error='') {
		// required properties
		$conftext = array(
			'type' => 'text',
			'description' => $description,
		);
		// optional properties
		if ($maxlength != '') $conftext['maxlength'] = $maxlength;
		if ($size != '') $conftext['size'] = $size;
		if ($regex != '') $conftext['regex'] = $regex;
		if ($regex_error != '') $conftext['regex_error'] = $regex_error;
		return $conftext;
	}

	/**
	 * creates configuration for textareas
	 * @param string $description Label
	 * @param string $cols Number of columns
	 * @param string $rows Number of rows
	 * @param string $regex Regular expression for allowed input
	 * @param string $regex_error Wrong input error message
	 * @return Array Configuration
	 */
	protected function confTextarea($description, $cols='', $rows='', $regex='', $regex_error='') {
		// required properties
		$conftext = array(
			'type' => 'text',
			'description' => $description,
		);
		// optional properties
		if ($cols != '') $conftext['cols'] = $cols;
		if ($rows != '') $conftext['rows'] = $rows;
		if ($regex != '') $conftext['regex'] = $regex;
		if ($regex_error != '') $conftext['regex_error'] = $regex_error;
		return $conftext;
	}

	/**
	 * creates configuration for password fields
	 * @param string $description Label
	 * @param string $maxlength Maximum number of characters
	 * @param string $size Size
	 * @param string $regex Regular expression for allowed input
	 * @param string $regex_error Wrong input error message
	 * @param boolean $saveasmd5 Safe password as md5 (recommended!)
	 * @return Array Configuration
	 */
	protected function confPassword($description, $maxlength='', $size='', $regex='', $regex_error='', $saveasmd5=true) {
		// required properties
		$conftext = array(
			'type' => 'text',
			'description' => $description,
		);
		// optional properties
		if ($maxlength != '') $conftext['maxlength'] = $maxlength;
		if ($size != '') $conftext['size'] = $size;
		if ($regex != '') $conftext['regex'] = $regex;
		$conftext['saveasmd5'] = $saveasmd5;
		return $conftext;
	}

	/**
	 * creates configuration for checkboxes
	 * @param string $description Label
	 * @return Array Configuration
	 */
	protected function confCheck($description) {
		// required properties
		return array(
			'type' => 'checkbox',
			'description' => $description,
		);
	}

	/**
	 * creates configuration for radio buttons
	 * @param string $description Label
	 * @param string $descriptions Array Single item labels
	 * @return Array Configuration
	 */
	protected function confRadio($description, $descriptions) {
		// required properties
		return array(
			'type' => 'select',
			'description' => $description,
			'descriptions' => $descriptions,
		);
	}

	/**
	 * creates configuration for select fields
	 * @param string $description Label
	 * @param string $descriptions Array Single item labels
	 * @param boolean $multiple Enable multiple item selection
	 * @return Array Configuration
	 */
	protected function confSelect($description, $descriptions, $multiple=false) {
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
	 * @param string $text Content of error message
	 * @return string HTML content
	 */
	protected function throwError($text) {
		return '<div class="' . self::plugin_title . 'Error">'
				. '<div>' . $this->cms_lang->getLanguageValue('error') . '</div>'
				. '<span>' . $text. '</span>'
				. '</div>';
	}

}

?>