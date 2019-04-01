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

class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\senky\groupspp\acp\main_module',
			'title'		=> 'ACP_GROUPSPP_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_GROUPSPP',
					'auth'	=> 'ext_senky/groupspp && acl_a_board',
					'cat'	=> array('ACP_GROUPSPP_TITLE')
				),
			),
		);
	}
}
