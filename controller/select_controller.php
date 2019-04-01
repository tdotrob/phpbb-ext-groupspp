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

use senky\groupspp\ext as constants;

class select_controller
{
	protected $helper;
	protected $user;
	protected $db;
	protected $template;
	protected $language;
	protected $request;
	protected $groups_table;
	protected $user_groups_table;
	protected $root_path;
	protected $php_ext;
	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\request\request $request, $groups_table, $user_groups_table, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->db = $db;
		$this->template = $template;
		$this->language = $language;
		$this->request = $request;
		$this->groups_table = $groups_table;
		$this->user_groups_table = $user_groups_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function select()
	{
		$this->language->add_lang('select', 'senky/groupspp');
		add_form_key('senky_groupspp_select');

		$sql = 'SELECT ug.group_id, g.group_name
			FROM ' . $this->user_groups_table . ' ug
			LEFT JOIN ' . $this->groups_table . ' g
				ON (g.group_id = ug.group_id)
			WHERE ug.user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$user_groups = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_groups[$row['group_id']] = $row['group_name'];
		}
		$this->db->sql_freeresult($result);

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('senky_groupspp_select'))
			{
				return $this->helper->message('GROUPSPP_FORM_INVALID', [$this->helper->route('senky_groupspp_select')]);
			}

			if (!function_exists('group_user_del'))
			{
				include($this->root_path . 'includes/functions_user.' . $this->php_ext);
			}
			$submit_groups = $this->request->variable('groups', [0 => 0]);

			// find groups to remove
			foreach ($user_groups as $group_id => $group_name)
			{
				if (!in_array($group_id, $submit_groups) && !in_array($group_name, constants::DEFAULT_GROUPS))
				{
					group_user_del($group_id, [$this->user->data['user_id']], false, false, false);
				}
			}

			// find groups to add
			foreach ($submit_groups as $group_id)
			{
				if (!isset($user_groups[$group_id]))
				{
					group_user_add($group_id, [$this->user->data['user_id']]);
				}
			}

			return $this->helper->message('GROUPSPP_SUCCESS', [$this->helper->route('senky_groupspp_select')]);
		}

		$sql = 'SELECT group_id, group_name
			FROM ' . $this->groups_table . '
			WHERE ' . $this->db->sql_in_set('group_name', constants::DEFAULT_GROUPS, true) . '
			ORDER BY group_name ASC';
		$result = $this->db->sql_query($sql);
		$groups = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($groups as $group)
		{
			$this->template->assign_block_vars('groups', [
				'GROUP_ID'		=> $group['group_id'],
				'GROUP_NAME'	=> $group['group_name'],
				'S_IS_MEMBER'	=> isset($user_groups[$group['group_id']]),
			]);
		}

		return $this->helper->render('groupspp_select.html', '');
	}
}
