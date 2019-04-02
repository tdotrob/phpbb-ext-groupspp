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

class view_controller
{
	protected $helper;
	protected $user;
	protected $db;
	protected $template;
	protected $language;
	protected $request;
	protected $cache;
	protected $config;
	protected $auth;
	protected $content_visibility;
	protected $pagination;
	protected $topics_table;
	protected $groups_table;
	protected $user_groups_table;
	protected $forums_table;
	protected $topics_track_table;
	protected $forums_track_table;
	protected $zebra_table;
	protected $root_path;
	protected $php_ext;
	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\content_visibility $content_visibility, \phpbb\pagination $pagination, $topics_table, $groups_table, $user_groups_table, $forums_table, $topics_track_table, $forums_track_table, $zebra_table, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->db = $db;
		$this->template = $template;
		$this->language = $language;
		$this->request = $request;
		$this->cache = $cache;
		$this->config = $config;
		$this->auth = $auth;
		$this->content_visibility = $content_visibility;
		$this->pagination = $pagination;
		$this->topics_table = $topics_table;
		$this->groups_table = $groups_table;
		$this->user_groups_table = $user_groups_table;
		$this->forums_table = $forums_table;
		$this->topics_track_table = $topics_track_table;
		$this->forums_track_table = $forums_track_table;
		$this->zebra_table = $zebra_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function view($is_index = false)
	{
		// only logged in users!
		$this->language->add_lang('viewforum');
		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			login_box('', $this->language->lang('LOGIN_VIEWFORUM'));
		}

		// load additional dependencies
		if (!function_exists('topic_status'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}

		// add lang
		$this->language->add_lang('view', 'senky/groupspp');

		// where do we start?
		$start = $this->request->variable('start', 0);

		// select user groups
		$sql = 'SELECT g.group_name
			FROM ' . $this->user_groups_table . ' ug
			LEFT JOIN ' . $this->groups_table . ' g
				ON (g.group_id = ug.group_id)
			WHERE ug.user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$user_group_names = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_group_names[] = $row['group_name'];
		}
		$this->db->sql_freeresult($result);

		// select user foes
		$sql = 'SELECT zebra_id
			FROM ' . $this->zebra_table . '
			WHERE user_id = ' . $this->user->data['user_id'] . '
				AND foe = 1';
		$result = $this->db->sql_query($sql);
		$user_foes = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_foes[] = $row['zebra_id'];
		}
		$this->db->sql_freeresult($result);

		// prepare main query (for pagination for now)
		$sql_ary = [
			'SELECT'	=> 'COUNT(t.topic_id) as topics_count',
			'FROM'		=> [$this->topics_table => 't'],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [$this->forums_table => 'f'],
					'ON'	=> 'f.forum_id = t.forum_id',
				]
			],
			'WHERE'	=> $this->db->sql_in_set('f.forum_name', $user_group_names, false, true) . '
				AND ' . $this->db->sql_in_set('t.topic_poster', $user_foes, true, true) . '
				AND topic_visibility = ' . ITEM_APPROVED,
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$topics_count = $this->db->sql_fetchfield('topics_count');
		$this->db->sql_freeresult($result);

		// obtain icons and validate pagination start
		$icons = $this->cache->obtain_icons();
		$start = $this->pagination->validate_start($start, $this->config['senky_groupspp_per_page'], $topics_count);

		// edit query to select all we need
		$sql_ary['SELECT'] = 't.*, f.forum_name, f.enable_icons, tt.mark_time, ft.mark_time AS forum_mark_time';
		$sql_ary['LEFT_JOIN'][] = [
			'FROM'	=> [$this->forums_track_table => 'ft'],
			'ON'	=> 'ft.forum_id = t.forum_id AND ft.user_id = ' . (int) $this->user->data['user_id']
		];
		$sql_ary['LEFT_JOIN'][] = [
			'FROM' => [$this->topics_track_table => 'tt'],
			'ON'	=> 'tt.topic_id = t.topic_id AND tt.user_id = ' . $this->user->data['user_id']
		];
		$sql_ary['ORDER_BY'] = 'topic_last_post_time DESC';
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query_limit($sql, $this->config['senky_groupspp_per_page'], $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// is topic unread?
			$topic_tracking_info = false;
			if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
			{
				$rowset = [$row['topic_id'] => $row];
				$topic_tracking_info = get_topic_tracking($row['forum_id'], [$row['topic_id']], $rowset, [$row['forum_id'] => $row['forum_mark_time']]);
			}
			else if ($this->config['load_anon_lastread'] || $this->user->data['is_registered'])
			{
				$topic_tracking_info = get_complete_topic_tracking($row['forum_id'], [$row['topic_id']]);
			}

			// make sure we only count visible replies
			$replies = $this->content_visibility->get_count('topic_posts', $row, $row['forum_id']) - 1;

			// moved topic fix and unread topic status
			if ($row['topic_status'] == ITEM_MOVED)
			{
				$row['topic_id'] = $row['topic_moved_id'];
				$unread_topic = false;
			}
			else
			{
				$unread_topic = !empty($topic_tracking_info[$row['topic_id']]) && $row['topic_last_post_time'] > $topic_tracking_info[$row['topic_id']];
			}

			// generate folder img and alt
			$folder_img = $folder_alt = $topic_type = '';
			topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

			$view_topic_url_params = 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'];
			$this->template->assign_block_vars('topicrow', [
				'TOPIC_IMG_STYLE'		=> $folder_img,
				'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
				'TOPIC_FOLDER_IMG_ALT'	=> $folder_alt,
				'S_UNREAD_TOPIC'		=> $unread_topic,
				'U_NEWEST_POST'			=> $this->auth->acl_get('f_read', $row['forum_id']) ? append_sid($this->root_path . 'viewtopic.' . $this->php_ext, $view_topic_url_params . '&amp;view=unread') . '#unread' : false,
				'U_VIEW_TOPIC'			=> $this->auth->acl_get('f_read', $row['forum_id']) ? append_sid($this->root_path . 'viewtopic.' . $this->php_ext, $view_topic_url_params) : false,
				'TOPIC_TITLE'			=> censor_text($row['topic_title']),
				'LAST_POST_AUTHOR_FULL'	=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
				'U_LAST_POST'			=> $this->auth->acl_get('f_read', $row['forum_id'])  ? append_sid($this->root_path . 'viewtopic.' . $this->php_ext, $view_topic_url_params . '&amp;p=' . $row['topic_last_post_id']) . '#p' . $row['topic_last_post_id'] : false,
				'LAST_POST_TIME'		=> $this->user->format_date($row['topic_last_post_time']),
				'U_VIEW_FORUM'			=> append_sid($this->root_path . 'viewforum.' . $this->php_ext, 'f=' . $row['forum_id']),
				'FORUM_NAME'			=> $row['forum_name'],
				'REPLIES'				=> $replies,
				'S_HAS_POLL'			=> $row['poll_start'],
				'ATTACH_ICON_IMG'		=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $this->user->img('icon_topic_attach', $this->language->lang('TOTAL_ATTACHMENTS')) : '',
				'TOPIC_AUTHOR_FULL'		=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
				'FIRST_POST_TIME'		=> $this->user->format_date($row['topic_time']),
				'VIEWS'					=> $row['topic_views'],
				'S_TOPIC_ICONS'			=> $row['enable_icons'],
			]);
		}
		$this->db->sql_freeresult($result);

		$base_url = $is_index ? append_sid($this->root_path . 'index.' . $this->php_ext) : ['routes' => ['senky_groupspp_view', 'senky_groupspp_view'], 'params' => []];
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $topics_count, $this->config['senky_groupspp_per_page'], $start);
		$this->template->assign_vars([
			'TOTAL_TOPICS'		=> $topics_count,
			'U_SELECT_GROUPS'	=> $this->helper->route('senky_groupspp_select'),
		]);

		if (!$is_index)
		{
			return $this->helper->render('groupspp_view.html', $this->language->lang('GROUPSPP_VIEW') . ($start ? ' - ' . $this->language->lang('PAGE_TITLE_NUMBER', $this->pagination->get_on_page($this->config['senky_groupspp_per_page'], $start)) : ''));
		}
	}
}
