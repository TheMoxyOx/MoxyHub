<?php
// $Id$

define('ICON_STYLE', 'iconstyle-v1');
define('REPLACE_LANGUAGE_STRING', 0);
define('REPLACE_LANGUAGE_FILE',   1);

class Template {
	// contains the HTML to be generated
	var $Content = '';
	// contains Header changable HTML
	var $Page_Title;
	var $Page_Header;
	// contains Module changable HTML
	var $UsesModule = false;
	var $Page_Crumbs;
	var $Page_Action;
	// internal settings
	var $Module;
	var $Authorised;
	var $Name;
	var $List;
	var $User;
	var $DB;
	// other settings
	var $ModulePath;
	var $SystemPath;
	var $Extensions;
	var $PageStyles = array();
	var $PageScripts = array();
	var $PageDashBlock;

	// the amount of recent items to show in the main nav bar.
	const ICON_RECENT_LIMIT = 5;

	const HEADER_DEFAULT_IMG = 'assets/images/header_bg.gif';
	const LOGO_DEFAULT_IMG = 'assets/images/copper_logo.png';
	const HOME_DEFAULT_IMG = 'assets/images/login/login_img_default.jpg';

	function &Template() {
	}

	function Clear() {
		$this->Content		= '';
		$this->Page_Title  = null;
		$this->Page_Header = null;
		$this->UsesModule  = false;
		$this->Page_Crumbs = null;
		$this->Page_Action = null;
		$this->Module	  = null;
		$this->Authorised  = 0;
		$this->Name		= null;
		$this->List		= null;
		$this->User		= null;
		$this->ModulePath  = null;
		$this->SystemPath  = null;
		$this->Extensions  = null;
	}

	function Initialise($module, $authorised, $name, $list, $user, $db) {
		$this->Module	 = $module;
		$this->Authorised = $authorised;
		$this->Name	   = $name;
		$this->List	   = $list;
		$this->User	   = $user;
		$this->DB		 = $db;
		$this->ModulePath = sprintf(CU_MODULE_TEMPLATES, $module);
		$this->SystemPath = CU_TEMPLATE_PATH;
		$this->Extension  = CU_TEMPLATE_EXT;

		$moduleStylesheet = dirname(CU_SYSTEM_PATH).'/assets/styles/'.$module.'.css';
		if (is_file($moduleStylesheet))
			$this->PageStyles[] = "@import \"assets/styles/$module.css\";";
	}

	function addStyle($stylesheet) {
		$this->PageStyles[] = "@import \"assets/styles/$stylesheet\";";
	}

	function addScript($script) {
		$this->PageScripts[] = "<script type=\"text/javascript\" src=\"assets/js/$script\"></script>";
	}

	function setHeader($title = null, $header = null) {
		// Removed page title.
		$this->Page_Header = $header;
	}

	function setModule($moduleName, $crumbs, $action) {
		$name = constant("MSG_".strtoupper($moduleName));
		$this->Page_Title  = $name . ' - ' .$crumbs;
		$this->UsesModule  = true;
		$this->Page_Crumbs = '|&nbsp;'.$crumbs;

		if ($moduleName == 'authorisation') 
		{
			$this->Page_Title  = MSG_LOGIN;
			$this->Page_Crumbs = NULL;
		}

		if (is_array($action)) 
		{
			$template = 'module_action';
			$template = $this->SystemPath.$template.$this->Extension;
			$template = file_get_contents($template);
			foreach ($action as $value) 
			{
				$action_template = (empty($actionmenu)) ? 'module_action_item_first' : 'module_action_item';
				$action_template = $this->SystemPath.$action_template.$this->Extension;
				$action_template = file_get_contents($action_template);
				$actionmenu .= str_replace('{ACTION}', $value, $action_template);
			}
			$template = str_replace('{ACTION}', $actionmenu, $template);

			$this->Page_Action = $template;
		}
	}

	function setDash($dashContent) {
		$this->PageDashBlock = $dashContent;
	}

	function addToContent($contents)
	{
		$this->Content .= $contents;
	}

	// used to be called append, changed to be consistent with the module class
	function setTemplate($template = null, $variables = null) {
		// If we're appending, we know its a module appending to the template. 
		$template = $this->ModulePath.$template.CU_TEMPLATE_EXT;
		$content = file_get_contents($template);

		if (is_array($variables))
		{
			foreach ($variables as $key => $value)
				$content = str_replace('{'.$key.'}', $value, $content);
		}

		$content = $this->TokenReplace($content);
		$this->Content .= $content;
	}

	function getTemplate($template = null, $variables = null) {
		$template = $this->ModulePath . $template . CU_TEMPLATE_EXT;
		$content = file_get_contents($template);

		if (is_array($variables))
		{
			foreach ($variables as $key => $value)
				$content = str_replace('{'.$key.'}', $value, $content);
		}

		$content = $this->TokenReplace($content);
		return $content;
	}

	/* this one tries to map the requested template to a module file */
	function includeTemplate($template, $vars = array(), $add_to_content = FALSE) 
	{
		$template = $this->ModulePath . $template . CU_TEMPLATE_EXT;
		return $this->includeFile($template, $vars, $add_to_content);
	}

	/* this one tries to take a direct file */
	protected function includeFile($template, $vars = array(), $add_to_content = FALSE)
	{
		foreach ($vars as $name => $var)
		{
			$$name = $var;
		}

		// start output buffering
		ob_start();
		include($template);

		// now get that content
		$contents = ob_get_contents();
		ob_end_clean();

		// we still want to do standard language constant replacements.
		$contents = Template::replaceLanguage($contents);
		
		if ($add_to_content)
		{
			$this->Content .= $contents;
		}
		
		return $contents;
	}

	function Render() {
		// add in an exception that hides the header detail for the login.
		// allows us to keep the login simple but still use module concepts
		if($this->Module!="authorisation"){
			$output = $this->getSystemHeader();
			if ($this->UsesModule)
			{
				$output .= $this->getModuleHeader();
				$output .= $this->Content;
				$output .= $this->getModuleFooter();
			}
			else
				$output .= $this->Content;
			$output .= $this->getSystemFooter();
		} else {
			$output .= $this->Content;
		}
		echo $output;
	}

	function RenderPopup() {
		$output = $this->getPopupHeader();

		if ($this->UsesModule)
		{
			$output .= $this->getModuleHeader();
			$output .= $this->Content;
			$output .= $this->getModuleFooter();
		}
		else
			$output .= $this->Content;

		$output .= $this->getSystemFooter();
		echo $output;
	}

	function RenderOnlyContent() {
		echo $this->Content;
	}

	function TokenReplace($html) {
	//  Replace any tokens in the HTML parameter with the values defined in the language file. 
	//	 This saves us from manually entering the required tokens in the code. 
		$tokens = get_defined_constants();
		preg_match_all('/MSG_\w+/', $html, $matches, PREG_PATTERN_ORDER);
		foreach ($matches[0] as $value)
		{ 
			if (!isset($tokens[$value])) $tokens[$value] = '{'.$value.'}';
			$html = str_replace('{'.$value.'}', $tokens[$value], $html);
		}
		return $html;
		}

	function getPopupHeader() {
		$template = 'popup_header';
		$template = $this->SystemPath.$template.$this->Extension;
		$template = file_get_contents($template);
		$template = str_replace('{TITLE}', $this->Page_Title, $template);
		return $template;
	}

	function getSystemHeader() {

		$tokens = array();
		$tokens['{MODULE}'] = constant(MSG_.strtoupper($this->Module));
		$tokens['{TITLE}'] = $this->Page_Title;
		$tokens['{HEADER}'] = $this->Page_Header;
		$tokens['{BREADCRUMBS}'] = $this->Page_Crumbs;
		$tokens['{actions}'] = $this->Page_Action;
		$tokens['{LOGO}'] = Template::get_appearance_img('logo');
		$tokens['{HEADER_BG}'] = Template::get_appearance_img('header');
		$tokens['{ICONS}'] = NULL;
		$tokens['{FULLNAME}'] = NULL;
		$tokens['{LOGOUT}'] = NULL;
		$tokens['{SEARCH_AREA}'] = NULL;
		$tokens['{SEARCH_SCRIPT}'] = NULL;
		$tokens['{STOPWATCH}'] = NULL;
		$tokens['{STYLES}'] = implode("\n", $this->PageStyles);
		$tokens['{SCRIPTS}'] = implode("\n", $this->PageScripts);
		$tokens['{DASH}'] = $this->PageDashBlock;
		
		if ($this->User->ID > 0)
		{
			$tokens['{FULLNAME}'] = $this->User->Name();
			$tokens['{STOPWATCH}'] = $this->getStopWatch();
		}

		// Pass some server-side settings through to the JS code.
		$res = $this->DB->QuerySingle( sprintf( CU_SQL_GET_SETTING, 'DefaultLanguage' ) );
		$language = ( $res ) ? $res['Value'] : 'en';   // Default to English.

		$res = $this->DB->QuerySingle( sprintf( CU_SQL_GET_SETTING, 'WeekStart' ) );
		$weekStart = ( $res ) ? $res['Value'] : CU_WEEK_START; // Default to system.php setting.

		Response::addToJavascript('settings', array(
				'language' => $language, 
				'weekStart' => $weekStart,
			)
		);
			
		Response::addToJavascript('language', array(
				'msgPause' => MSG_PAUSE, 
				'msgResume' => MSG_RESUME, 
				'msgToday' => MSG_TODAY,
				// take the first letter of each day
				'dayNames' => array(substr(MSG_MONDAY, 0, 1), substr(MSG_TUESDAY, 0, 1), substr(MSG_WEDNESDAY, 0, 1), substr(MSG_THURSDAY, 0, 1), substr(MSG_FRIDAY, 0, 1), substr(MSG_SATURDAY, 0, 1), substr(MSG_SUNDAY, 0, 1)),
				'monthNames' => array(MSG_JANUARY, MSG_FEBRUARY, MSG_MARCH, MSG_APRIL, MSG_MAY, MSG_JUNE, MSG_JULY, MSG_AUGUST, MSG_SEPTEMBER, MSG_OCTOBER, MSG_NOVEMBER, MSG_DECEMBER),
				'MSG_CONFIRM_ITEM_DELETE_TITLE' => MSG_CONFIRM_ITEM_DELETE_TITLE,
				'MSG_CONFIRM_ITEM_DELETE_BODY' => MSG_CONFIRM_ITEM_DELETE_BODY,
			)
		);

		$tokens['{CHARSET}']						= CHARSET;
		$tokens['{MSG_ABOUT_COPPER}'] 	= MSG_ABOUT_COPPER;

		$tokens['{JS_DATA}'] = Response::getJavascript();

		// get the icon style and icons.
		if (!defined('ICON_STYLE'))
		{
			define('ICON_STYLE', 'icon_style-v0');
		}
		$tokens['{ICON_STYLE}'] = ICON_STYLE;
		$tokens['{ICONS}']			= $this->getIconList();
		$tokens['{USER_MODULE}'] = CopperUser::current()->Module;
		$tokens['{CU_PRODUCT_VERSION}'] = CU_PRODUCT_VERSION;
		
		$template = $this->includeFile($this->SystemPath . 'system_header' . $this->Extension, array('alerts' => Alerts::get_for_current_user()));
		$template = str_replace(array_keys($tokens), array_values($tokens), $template);
		return $template;
	}

	function getIconList() {
		// Build module icon list.
		$iconStr = '';
			
	  if ($this->Authorised)
	  {
		  if ( is_array($this->List) )
		  {
				if (ICON_STYLE == 'iconstyle-v1') 
				{
					// key is the human readable (ie capitalised), value is the plain string.
					$key = substr(md5($this->User->Fullname.$this->User->PasswordHash),2,8);
					$url = split ("index\.php",Request::server(SCRIPT_NAME_VAR));

					$query_bits = array();
					$query_bits['key'] = $key;
					$query_bits['show'] = (Request::get('show') == null) ? 'all' : Request::get('show');
					$query_bits['userid'] = (Request::get('userID', Request::R_INT) == null) ? CopperUser::current()->ID : Request::get('userID', Request::R_INT);
					$query_bits['completed'] = (Request::get('completed') == null) ? '0' : (Request::get('completed') == null);

					$ical_url = 'webcal://' . Request::server(SERVER_NAME_VAR) . $url[0] . 'system/ical_springboard.php?';
					$parts = array();
					foreach($query_bits as $key => $val)
					{
						$parts[] = $key . '=' . $val;
					}
					
					$data = array('ICAL_LINK' => $ical_url . implode('&amp;', $parts));
					$general_log_params = array(
						'where' => array('Action' => 'view', 'UserID' => $this->User->ID), 
						'limit' => self::ICON_RECENT_LIMIT, 
						'orderby' => array('Timestamp DESC'),
						'orderby' => 'Timestamp DESC',
						'groupby' => 'ContextID', // doesn't work atm, as we aren't specifying how to sort on the grouped set. so for now just have repeats
					);
					
					foreach ($this->List as $key => $value)
					{
						switch($value)
						{
							case 'projects':
								$params = array_merge_recursive($general_log_params, array('where' => array('Context' => 'project')));
								$data['recent_items'] = new ActivityLogs($params);
								break;
							case 'files':
								$params = array_merge_recursive($general_log_params, array('where' => array('Context' => 'file')));
								$data['recent_items'] = new ActivityLogs($params);
								break;
							case 'springboard':
								$params = array_merge_recursive($general_log_params, array('where' => array('Context' => 'task')));
								$data['recent_items'] = new ActivityLogs($params);
								break;
							case 'clients':
								$params = array_merge_recursive($general_log_params, array('where' => array('Context' => 'client')));
								$data['recent_items'] = new ActivityLogs($params);
								break;
							case 'calendar':
							case 'contacts':
							case 'reports':
							case 'administration':
							default:
								$data['recent_items'] = array();
							
						}

						$data['active'] = ($this->Module == $value) ? 'active' : '';
						$iconStr .= $this->includeFile($this->SystemPath . 'nav_icons/' . $value . $this->Extension, $data);
					}
					
				} else 
				{ // revert to the old style icons
					$module = constant('MSG_'.strtoupper($this->Module));
					$out = "\$('head-module').innerHTML='".$module."';";
					foreach ($this->List as $key => $value)
					{
						$key = constant('MSG_'.strtoupper($key));

						$over = "\$('head-module').innerHTML='$key';";
						$iconStr .= '<li id="dash-nav-'.$value.'">';
						$iconStr .= '<a href="index.php?module='.$value.'" onmouseover="'.$over.'" onmouseout="'.$out.'">';
						$iconStr .= "<span>$key</span></a></li>\n";
					}
				}
			}
		}

		return $iconStr;
	}

	/**
	 * Takes some source, and replaces all the language strings. Note that the language strings must be in caps,
	 * enclosed in {}, and  must begin with MSG. (mmmm tasty tasty MSG.)
	 * 
	 * @param $source either the string to replace, or the source file to read and replace
	 * @param @type constant defining whether you are replacing in a string or a file. 
	 * 							One of REPLACE_LANGUAGE_STRING or REPLACE_LANGUAGE_FILE
	 * @return the replaced string, or null on error.
	 * @todo FILE type replace NYI.
	 */
	public static function replaceLanguage($source, $type = REPLACE_LANGUAGE_STRING)
	{
		if ($type == REPLACE_LANGUAGE_STRING)
		{
			return preg_replace_callback(
				'/\{(MSG_[A-Z0-9_]*)\}/',
        create_function(
            '$matches',
            'return defined($matches[1]) ? constant($matches[1]) : $matches[1];'
				),
				$source
			);

		} else if ($type == REPLACE_LANGUAGE_FiLE) {
			// NYI. This should get a file, _and_ replace.
			return file_get_contents($source);
		} else {
			return null;
		}
	}
	

	function getSystemFooter() 
	{
		return $this->includeFile($this->SystemPath . 'system_footer' . $this->Extension);
	}

	function getModuleHeader() {
		$template = 'module_header';
		$template = $this->SystemPath.$template.$this->Extension;
		$template = file_get_contents($template);
		$template = str_replace('{BREADCRUMBS}', $this->Page_Crumbs, $template);
		$template = str_replace('{ACTIONMENU}', $this->Page_Action, $template);
		return $template;
	}

	function getModuleFooter() {
		$template = 'module_footer';
		$template = $this->SystemPath.$template.$this->Extension;
		$html = file_get_contents($template);
		$html = str_replace('{MSG_CANCEL}', MSG_CANCEL, $html);
		$html = str_replace('{MSG_PROCEED}', MSG_PROCEED, $html);
		return $html;
	}

	function getStopWatch() {
		$weekMode = (CU_WEEK_START == 'Sunday') ? 0 : 1;
		$day = $this->DB->QuerySingle(sprintf(CU_SQL_GET_ACTIVITY_DAY, date('Y-m-d'), $this->User->ID));
		$week = $this->DB->QuerySingle(sprintf(CU_SQL_GET_ACTIVITY_WEEK, $weekMode, $this->User->ID, date('Y-m-d')));
		$tokens['{txtDayHours}'] = $day['Hours'];
		$tokens['{txtWeekHours}'] = $week['Hours'];

		$timers = new TimerLogs(array('where' => array('UserID' => CopperUser::current()->ID), 'orderby' => 'Updated DESC', 'limit' => 1));
		$timer = (count($timers) == 1) ? $timers[0] : new TimerLog(null);

		Response::addToJavascript('timer', array(
			'enabled' => $timer->exists, 
			'paused' => $timer->Paused == 1 ? true : false, 
			'updated' => $timer->Updated, 
			'elapsed' => array('hours' => $timer->elapsed_hours, 'minutes' => $timer->elapsed_minutes, 'seconds' => $timer->elapsed_seconds), 
			'taskid' => $timer->TaskID
		));

		return $this->includeFile( $this->SystemPath . 'stopwatch' . $this->Extension, array('timer' => $timer));
	}
	
	// type will be home / header / logo
	public static function get_appearance_img($type)
	{
		if (Settings::get($type))
		{
			return Upload::get_url(Settings::get($type));
		}

		if ($ret = self::check_for_existing_img($type))
		{
			return $ret;
		}
		
		switch($type)
		{
			case 'header':	return Template::HEADER_DEFAULT_IMG;
			case 'logo':		return Template::LOGO_DEFAULT_IMG;
			case 'home':		return Template::HOME_DEFAULT_IMG;
			default: 				return '#';
		}
	}
	
	public static function check_for_existing_img($type)
	{
		$aiu = new AppearanceImageUpload();
		switch($type)
		{
			case 'logo':
				$res = $aiu->create_from_filesystem('assets/uploads/logo.gif');
				break;
			case 'header':
				$res = $aiu->create_from_filesystem('assets/uploads/header_bg.gif');
				break;
			case 'home':
				$res = $aiu->create_from_filesystem('assets/uploads/home.jpg');
				break;
			default:
				$res = null;
		}
		
		if (is_array($res))
		{
			Settings::set($type, $res['filename']);
			return Upload::get_url($res['filename']);
		} else {
			return null;
		}

	}
}
 
