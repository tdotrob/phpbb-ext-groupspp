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

class acp_controller
{
	protected $language;
	protected $request;
	protected $template;
	protected $db;
	protected $cache;
	protected $config;
	protected $forums_table;
	protected $groups_table;
	protected $root_path;
	protected $php_ext;
	protected $u_action;
	public function __construct(\phpbb\language\language $language, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\config\config $config, $forums_table, $groups_table, $root_path, $php_ext)
	{
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->db = $db;
		$this->cache = $cache;
		$this->config = $config;
		$this->forums_table = $forums_table;
		$this->groups_table = $groups_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function display_options()
	{
		$this->language->add_lang('acp', 'senky/groupspp');
		add_form_key('senky_groupspp_acp');
		$errors = [];

		if ($this->request->is_set_post('settings'))
		{
			$this->config->set('senky_groupspp_per_page', $this->request->variable('per_page', 0));

			trigger_error($this->language->lang('ACP_GROUPSPP_SAVED') . adm_back_link($this->u_action));
		}

		$new_name = $this->request->variable('new_name', '', true);
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('senky_groupspp_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			$sql = 'SELECT forum_name
				FROM ' . $this->forums_table . "
				WHERE forum_name = '" . $this->db->sql_escape($new_name) . "'";
			$result = $this->db->sql_query($sql);
			$forum_name = $this->db->sql_fetchfield('forum_name');
			$this->db->sql_freeresult($result);
			if (!empty($forum_name))
			{
				$errors[] = $this->language->lang('FORUM_ALREADY_EXISTS');
			}

			$default_groups = array_map(function($group_name) {
				return $this->language->lang('G_' . $group_name);
			}, constants::DEFAULT_GROUPS);

			$sql = 'SELECT group_name
				FROM ' . $this->groups_table . "
				WHERE group_name = '" . $this->db->sql_escape($new_name) . "'";
			$result = $this->db->sql_query($sql);
			$group_name = $this->db->sql_fetchfield('group_name');
			$this->db->sql_freeresult($result);
			if (!empty($group_name) || in_array($new_name, $default_groups))
			{
				$errors[] = $this->language->lang('GROUP_ALREADY_EXISTS');
			}

			if (empty($errors))
			{
				// create group
				$group_id = 0;

				if (!function_exists('group_create'))
				{
					include($this->root_path . 'includes/functions_user.' . $this->php_ext);
				}
				$errors = group_create($group_id, GROUP_FREE, $new_name, '', [
					'group_rank'			=> 0,
					'group_colour'			=> '',
					'group_avatar'			=> '',
					'group_avatar_type'		=> '',
					'group_avatar_width'	=> 0,
					'group_avatar_height'	=> 0,
					'group_receive_pm'		=> 0,
					'group_legend'			=> 0,
					'group_teampage'		=> 0,
					'group_message_limit'	=> 0,
					'group_max_recipients'	=> 0,
					'group_founder_manage'	=> 0,
					'group_skip_auth'		=> 0,
				]);

				if (empty($errors))
				{
					// create forum
					if (!class_exists('acp_forums'))
					{
						include($this->root_path . 'includes/acp/acp_forums.' . $this->php_ext);
					}
					$acp_forums = new \acp_forums;

					$forum_data = [
						'parent_id'				=> 0,
						'forum_type'			=> FORUM_POST,
						'type_action'			=> '',
						'forum_status'			=> ITEM_UNLOCKED,
						'forum_parents'			=> '',
						'forum_name'			=> $new_name,
						'forum_link'			=> '',
						'forum_link_track'		=> false,
						'forum_desc'			=> '',
						'forum_desc_uid'		=> '',
						'forum_desc_options'	=> 7,
						'forum_desc_bitfield'	=> '',
						'forum_rules'			=> '',
						'forum_rules_uid'		=> '',
						'forum_rules_options'	=> 7,
						'forum_rules_bitfield'	=> '',
						'forum_rules_link'		=> '',
						'forum_image'			=> '',
						'forum_style'			=> 0,
						'display_subforum_list'	=> false,
						'display_on_index'		=> false,
						'forum_topics_per_page'	=> 0,
						'enable_indexing'		=> true,
						'enable_icons'			=> true,
						'enable_prune'			=> false,
						'enable_post_review'	=> true,
						'enable_quick_reply'	=> true,
						'enable_shadow_prune'	=> false,
						'prune_days'			=> 7,
						'prune_viewed'			=> 7,
						'prune_freq'			=> 1,
						'prune_old_polls'		=> false,
						'prune_announce'		=> false,
						'prune_sticky'			=> false,
						'prune_shadow_days'		=> 7,
						'prune_shadow_freq'		=> 1,
						'forum_password'		=> '',
						'forum_password_confirm'=> '',
						'forum_password_unset'	=> false,
						'forum_options'			=> 0,
						'show_active'			=> true,
					];
					$errors = $acp_forums->update_forum_data($forum_data);
					if (empty($errors))
					{
						if (!class_exists('auth_admin'))
						{
							include($this->root_path . 'includes/acp/auth.' . $this->php_ext);
						}
						$auth_admin = new \auth_admin;
						$auth_admin->acl_set('group', $forum_data['forum_id'], $group_id, [
							'f_list'			=> 1,
							'f_list_topics' 	=> 1,
							'f_read'			=> 1,
							'f_search'			=> 1,
							'f_subscribe'		=> 1,
							'f_print'			=> 1,
							'f_email'			=> 1,
							'f_bump'			=> 1,
							'f_user_lock'		=> 0,
							'f_download'		=> 1,
							'f_report'			=> 1,
							'f_post'			=> 1,
							'f_sticky'			=> 0,
							'f_announce'		=> 0,
							'f_announce_global'	=> 0,
							'f_reply'			=> 1,
							'f_edit'			=> 0,
							'f_delete'			=> 0,
							'f_softdelete'		=> 0,
							'f_ignoreflood' 	=> 0,
							'f_postcount'		=> 1,
							'f_noapprove'		=> 1,
							'f_attach'			=> 1,
							'f_icons'			=> 1,
							'f_bbcode'			=> 1,
							'f_flash'			=> 0,
							'f_img'				=> 1,
							'f_sigs'			=> 1,
							'f_smilies'			=> 1,
							'f_poll'			=> 1,
							'f_vote'			=> 1,
							'f_votechg'			=> 1,
						], 0);

						$this->cache->destroy('sql', FORUMS_TABLE);

						trigger_error($this->language->lang('ACP_GROUPSPP_ADDED') . adm_back_link($this->u_action));
					}
					else
					{
						// if forum creation wasn't successfull, we need to remove new group
						group_delete($group_id);
					}
				}
			}
		}

		$s_errors = !empty($errors);
		$this->template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
			'S_ERROR'		=> $s_errors,
			'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',
			'NEW_NAME'		=> $new_name,
			'PER_PAGE'		=> $this->config['senky_groupspp_per_page'],
		));
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
