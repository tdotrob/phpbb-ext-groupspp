<?php
/**
 *
 * Groups++. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Jakub Senko
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace senky\groupspp\migrations;

class m1_acp extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'acp'
				AND module_langname = 'ACP_GROUPSPP_TITLE'";
		$result = $this->db->sql_query($sql);
		$module_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id;
	}

	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('senky_groupspp_per_page', 10)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_GROUPSPP_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_GROUPSPP_TITLE',
				array(
					'module_basename'	=> '\senky\groupspp\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
