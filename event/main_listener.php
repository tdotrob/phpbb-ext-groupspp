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
			'core.user_setup'					=> 'load_language_on_setup',
			'core.page_header'					=> 'add_page_header_link',
			'core.viewtopic_modify_post_row'	=> 'add_foe_viewtopic',
		);
	}

	protected $helper;
	protected $template;
	protected $user;
	protected $root_path;
	protected $php_ext;
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
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
			'U_GROUPSPP_SELECT'	=> $this->helper->route('senky_groupspp_select'),
			'U_GROUPSPP_VIEW'	=> $this->helper->route('senky_groupspp_view'),
		));
	}

	public function add_foe_viewtopic($event)
	{
		$post_row = $event['post_row'];
		$post_row['S_ADD_FOE'] = !$event['row']['foe'] && $event['poster_id'] != $this->user->data['user_id'];
		$post_row['U_ADD_FOE'] = append_sid($this->root_path . 'ucp.' . $this->php_ext, 'i=zebra&amp;mode=foes&amp;add=' . urlencode(htmlspecialchars_decode($event['user_poster_data']['username'])));
		$event['post_row'] = $post_row;
	}
}
