<?php
/**
 * SCEditor plugin for MyBB forum
 *
 * @author Sam Clarke
 * @created 22/06/12
 * @version 1.4.0.1
 * @contact sam@sceditor.com
 * @license GPL
 */

// TODO: Work out how to add user option to enable/disable the editor

if(!defined("IN_MYBB"))
	die("You cannot directly access this file.");

define('SCEDITOR_PLUGIN_VER', '1.4.0.1');

$plugins->add_hook("pre_output_page", "sceditor_load", 100);
$plugins->add_hook("parse_message",   "sceditor_parse");

function sceditor_info()
{
	global $lang;

	$lang->load('config_sceditor');

	return array(
		"name"          => $lang->sceditor_title,
		"description"   => $lang->sceditor_desc,
		"website"       => "http://sceditor.samclarke.com",
		"author"        => "Sam Clarke",
		"authorsite"    => "http://www.samclarke.com/",
		"version"       => SCEDITOR_PLUGIN_VER,
		"guid"          => "aaaec079369b70e0915fdd2b959093e3",
		"compatibility" => "16*"
	);
}

function sceditor_install()
{
	global $db, $lang;

	$lang->load('config_sceditor');

	// clean up any old installation
	sceditor_uninstall();

	//$db->write_query('ALTER TABLE `'.TABLE_PREFIX.'users` ADD `sceditor_enable` INT(1) NOT NULL DEFAULT \'1\', ADD `sceditor_sourcemode` INT(1) NOT NULL DEFAULT \'0\';');

	$query  = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$dorder = $db->fetch_field($query, "rows") + 1;

	$groupid = $db->insert_query("settinggroups", array(
		'name'		=> 'sceditor',
		'title'		=> 'SCEditor',
		'description'	=> 'Settings related to the SCEditor WYSIWYG BBCode editor.',
		'disporder'	=> $dorder,
		'isdefault'	=> '0'
	));


	$db->insert_query("settings", array(
		'name'		=> 'enablesceditor',
		'title'		=> $lang->enablesceditor_title,
		'description'	=> $lang->enablesceditor_desc,
		'optionscode'	=> 'onoff',
		'value'		=> '1',
		'disporder'	=> '1',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'enablesceditor_newpost',
		'title'		=> $lang->enablesceditor_newpost_title,
		'description'	=> $lang->enablesceditor_newpost_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '2',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'enablesceditor_quickreply',
		'title'		=> $lang->enablesceditor_quickreply_title,
		'description'	=> $lang->enablesceditor_quickreply_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '3',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'enablesceditor_signature',
		'title'		=> $lang->enablesceditor_signature_title,
		'description'	=> $lang->enablesceditor_signature_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '4',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'enablesceditor_pm',
		'title'		=> $lang->enablesceditor_pm_title,
		'description'	=> $lang->enablesceditor_pm_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '5',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'enablesceditor_event',
		'title'		=> $lang->enablesceditor_event_title,
		'description'	=> $lang->enablesceditor_event_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '6',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'sceditor_theme',
		'title'		=> $lang->sceditor_theme_title,
		'description'	=> $lang->sceditor_theme_desc,
		'optionscode'	=> "select\n" .
					"default=Default\n" .
					"modern=Modern\n" .
					"office-toolbar=Office Toolbar\n" .
					"office=Office",
		'value'		=> 'default',
		'disporder'	=> '7',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'sceditor_include_jquery',
		'title'		=> $lang->sceditor_include_jquery_title,
		'description'	=> $lang->sceditor_include_jquery_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '8',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'sceditor_enable_jquery_noconflict',
		'title'		=> $lang->sceditor_enable_jquery_noconflict_title,
		'description'	=> $lang->sceditor_enable_jquery_noconflict_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '9',
		'gid'		=> $groupid
	));

/*	$db->insert_query("settings", array(
		'name'		=> 'sceditor_enable_user_choice',
		'title'		=> $lang->sceditor_enable_user_choice_title,
		'description'	=> $lang->sceditor_enable_user_choice_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '10',
		'gid'		=> $groupid
	));*/

	$db->insert_query("settings", array(
		'name'		=> 'sceditor_lang',
		'title'		=> $lang->sceditor_lang_title,
		'description'	=> $lang->sceditor_lang_desc,
		'optionscode'	=> "select\n" .
					"default=Default\n" .
					"en=English (GB)\n" .
					"en-US=English (US)\n" .
					"de=German\n" .
					"fr=French\n" .
					"pt-BR=Brazilian Portuguese\n" .
					"nl=Dutch\n" .
					"ru=Russian\n" .
					"et=Estonian\n" .
					"no=Norwegian\n" .
					"sv=Swedish\n" .
					"es=Spanish\n" .
					"vi=Vietnamese",
		'value'		=> 'default',
		'disporder'	=> '10',
		'gid'		=> $groupid
	));

	rebuild_settings();
}

function sceditor_is_installed()
{
	global $db;

	$query = $db->simple_select("settinggroups", "COUNT(*) as rows", "name = 'sceditor'");
	$rows = $db->fetch_field($query, "rows");

	return ($rows > 0);
}

function sceditor_uninstall()
{
	global $db;

	//$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` DROP `sceditor_enable`, DROP `sceditor_sourcemode`;");

	$db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN(
		'enablesceditor',
		'enablesceditor_newpost',
		'enablesceditor_quickreply',
		'enablesceditor_signature',
		'enablesceditor_pm',
		'enablesceditor_event',
		'sceditor_theme'
	)");

	$db->delete_query("settinggroups", "name = 'sceditor'");
}

function sceditor_activate()
{
}

function sceditor_deactivate()
{
	include_once MYBB_ROOT."inc/adminfunctions_templates.php";

	// left to remove old versions, not needed anymore
	find_replace_templatesets(
		"misc_smilies_popup_smilie",
		"#".preg_quote('onclick="insertSmilie(\'{$smilie[\'insert\']}\', \'{$smilie[\'image\']}\');"')."#i",
		'onclick="insertSmilie(\'{$smilie[\'insert\']}\');"',
		0
	);

}

function sceditor_load($page)
{
	global $lang, $mybb, $cache;

	if(!$mybb->settings['enablesceditor'])
		return false;

	if(THIS_SCRIPT == "misc.php" && $mybb->input['action'] == "smilies")
	{
		$page = str_replace("function insertSmilie(code)", "function insertSmilie(code, src)", $page);
		$page = str_replace("editor.performInsert(code, \"\", true, false);", "editor.performInsert(code, src);", $page);

		return $page;
	}

	switch(THIS_SCRIPT)
	{
		case "newreply.php":
		case "newthread.php":
		case "editpost.php":
			if(!$mybb->settings['enablesceditor_newpost'])
				return false;
			break;
		case "showthread.php":
			if(!$mybb->settings['enablesceditor_quickreply'])
				return false;
			break;
		case "usercp.php":
			if($mybb->input['action'] != "editsig" || !$mybb->settings['enablesceditor_signature'])
				return false;
			break;
		case "private.php":
			if(!$mybb->settings['enablesceditor_pm'])
				return false;
			break;
		case "calendar.php":
			if($mybb->input['action'] != "addevent" || !$mybb->settings['enablesceditor_event'])
				return false;
			break;
		default:
			return false;
	}


	// sort and output emoticons
	$smilie_cache = $cache->read("smilies");
	$smilies = array();
	foreach($smilie_cache as $smilie)
		$smilies[$smilie['find']] = $smilie['image'];

	function smilie_len_cmp($a, $b)
	{
		if (strlen($a) == strlen($b))
			return 0;

		if (strlen($a) > strlen($b))
			return -1;

		return 1;
	}

	uksort($smilies, "smilie_len_cmp");



	$jqueryNoConflict   = '';
	$lang               = '';
	$jquery             = '';
	$sceditor_lang      = ($mybb->settings['sceditor_lang'] === 'default' ? 'en' : $mybb->settings['sceditor_lang']);
	$mybb_emoticons     = json_encode(array( 'dropdown' => $smilies ));
	$sceditor_autofocus = (THIS_SCRIPT != "showthread.php" ? 'true' : 'false');


	if($mybb->settings['sceditor_include_jquery'])
		$jquery = '<script src="jscripts/sceditor/jquery-1.8.2.min.js?ver='.SCEDITOR_PLUGIN_VER.'"></script>';

	if($mybb->settings['sceditor_include_jquery'] && $mybb->settings['sceditor_enable_jquery_noconflict'])
		$jqueryNoConflict = '$.noConflict();';

	if($mybb->settings['sceditor_lang'] && $mybb->settings['sceditor_lang'] !== 'default')
		$lang = '<script src="jscripts/sceditor/languages/' . $mybb->settings['sceditor_lang'] . '.js?ver='.SCEDITOR_PLUGIN_VER.'"></script>';


	$js = '	' . $jquery . '
		<script>
			' . $jqueryNoConflict  . '
			var sceditor_lang      = "' . $sceditor_lang . '";
			var mybb_emoticons     = ' . $mybb_emoticons . ';
			var sceditor_autofocus = ' . $sceditor_autofocus . ';
		</script>
		<link rel="stylesheet" href="jscripts/sceditor/themes/' . $mybb->settings['sceditor_theme'] . '.min.css?ver='.SCEDITOR_PLUGIN_VER.'" type="text/css" media="all" />
		<script src="jscripts/sceditor/jquery.sceditor.min.js?ver='.SCEDITOR_PLUGIN_VER.'"></script>
		' . $lang . '
		<script src="jscripts/sceditor/jquery.sceditor.mybb.helper.js?ver='.SCEDITOR_PLUGIN_VER.'"></script>';


	// strip the default editor
	$page = preg_replace('/<!-- start: codebuttons -->.*?<!-- end: codebuttons -->/ism', "", $page);

	// add the editors JS
	return str_replace('</head>', $js . '</head>', $page);
}

function sceditor_parse($message)
{
	// Add support for extra BBCode tags
	$message = preg_replace("/\[sup](.*?)\[\/sup\]/si", '<sup>$1</sup>', $message);
	$message = preg_replace("/\[sub](.*?)\[\/sub\]/si", '<sub>$1</sub>', $message);
	$message = preg_replace("/\[youtube](?:http:\/\/)?([a-zA-Z0-9_\-]+)\[\/youtube\]/si", '<iframe width="560" height="315" src="http://www.youtube.com/embed/$1?wmode=opaque" frameborder="0" allowfullscreen></iframe>', $message);

	// Sometimes if text is copied, it can have a font stack which is valid in SCEditor. So add support
	// for fonts with spaces and commas
	$message = preg_replace("/\[font=([a-z0-9 ,\-_]+)](.*?)\[\/font\]/si", '<span style="font-family: $1">$2</span>', $message);

	return $message;
}
