<?php
/**
 *
 * Groups++. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Jakub Senko
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace senky\groupspp\controller;

class main_controller
{
	protected $config;
	protected $helper;
	protected $template;
	protected $language;

	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\language\language $language)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->language = $language;
	}

	public function handle($name)
	{
		$l_message = !$this->config['senky_groupspp_goodbye'] ? 'GROUPSPP_HELLO' : 'GROUPSPP_GOODBYE';
		$this->template->assign_var('GROUPSPP_MESSAGE', $this->language->lang($l_message, $name));

		return $this->helper->render('groupspp_body.html', $name);
	}
}
