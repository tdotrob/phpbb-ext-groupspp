<?php
/**
 *
 * Groups++. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Jakub Senko
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_GROUPSPP_TITLE'	=> 'groupspp Module',
	'ACP_GROUPSPP'			=> 'groupspp Settings',

	'LOG_ACP_GROUPSPP_SETTINGS'		=> '<strong>Groups++ settings updated</strong>',
));
