<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Translator - sets up the hooks
 *
 * @author     PoPoutdoor <PoPoutdoor@gmail.com>
 * @package    Ushahidi - Translator
 */

class translator {

	/**
	 * Registers the main event add method
	 */

	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		$this->post_data = null; //initialize this for later use
	}

	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		// Only add the events if we are on that controller
//		if (Router::$controller == 'admin/manage')
//		{
			plugin::add_stylesheet('translator/views/css/translator');
			
			// Hook into admin/manage page view
			// Add a Sub-Nav Link
			Event::add('ushahidi_action.nav_admin_manage', array($this, '_translator_link'));
//		}
	}

	public function _translator_link()
	{
		$this_sub_page = Event::$data;
		echo ($this_sub_page == "translator") ? Kohana::lang('translator.translator') : "<a href=\"".url::site()."admin/manage/translator\">".Kohana::lang('translator.translator')."</a>";
	}
}

new translator;
