<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace senky\groupspp;

class ext extends \phpbb\extension\base
{
	const DEFAULT_GROUPS = ['ADMINISTRATORS', 'BOTS', 'GLOBAL_MODERATORS', 'GUESTS', 'NEWLY_REGISTERED', 'REGISTERED', 'REGISTERED_COPPA'];
}