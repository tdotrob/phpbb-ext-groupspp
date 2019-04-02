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
	'TOPICS_PER_PAGE'		=> 'Groups++ topics per page',
	'ACP_GROUPSPP_SAVED'	=> 'Groups++ settings has been saved.',
	'ADD_NEW'				=> 'Add new',
	'NEW_NAME'				=> 'New Groups++ group/forum name',
	'FORUM_ALREADY_EXISTS'	=> 'Forum with that name already exists.',
	'GROUP_ALREADY_EXISTS'	=> 'Group with that name already exists.',
	'ACP_GROUPSPP_ADDED'	=> 'Group and forum has been added.',
));
