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
	'GROUPSPP_VIEW'		=> 'Your groups topics',
	'NO_TOPICS'			=> 'There are no topics. <a href="%s">Select more groups to see topics here.</a>',
));
