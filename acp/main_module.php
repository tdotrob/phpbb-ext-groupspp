<?php
/**
 *
 * Groups++. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Jakub Senko
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace senky\groupspp\acp;

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;
	public function main($id, $mode)
	{
		global $phpbb_container;
		$acp_controller = $phpbb_container->get('senky.groupspp.controller.acp');
		$language = $phpbb_container->get('language');

		$this->tpl_name = 'acp_groupspp_body';
		$this->page_title = $language->lang('ACP_GROUPSPP_TITLE');

		$acp_controller->set_page_url($this->u_action);
		$acp_controller->display_options();
	}
}
