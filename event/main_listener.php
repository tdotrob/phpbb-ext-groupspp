<?php
/**
 *
 * Groups++. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Jakub Senko
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace senky\groupspp\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array(
			'core.user_setup'							=> 'load_language_on_setup',
			'core.page_header'							=> 'add_page_header_link',
		);
	}

	protected $helper;
	protected $template;
	protected $language;
	protected $php_ext;
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\language\language $language, $php_ext)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->language = $language;
		$this->php_ext = $php_ext;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'senky/groupspp',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link()
	{
		$this->template->assign_vars(array(
			'U_GROUPSPP_PAGE'	=> $this->helper->route('senky_groupspp_controller', array('name' => 'world')),
		));
	}
}
