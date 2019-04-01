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
	'GROUPSPP_SELECT'		=> 'Select your groups',
	'GROUPSPP_FORM_INVALID'	=> 'The submitted form is invalid. Please try again.<br><br><a href="%s">Back to group selection</a>',
	'GROUPSPP_SUCCESS'		=> 'Your groups has been updated.<br><br><a href="%s">Back to group selection</a>',
));
