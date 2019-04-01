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

class acp_controller
{
	protected $config;
	protected $language;
	protected $log;
	protected $request;
	protected $template;
	protected $user;
	protected $u_action;
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\log\log $log, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	public function display_options()
	{
		$this->language->add_lang('acp', 'senky/groupspp');
		add_form_key('senky_groupspp_acp');
		$errors = [];

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('senky_groupspp_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			if (empty($errors))
			{
				trigger_error($this->language->lang('ACP_GROUPSPP_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		$s_errors = !empty($errors);
		$this->template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
			'S_ERROR'		=> $s_errors,
			'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',
		));
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
