<?php
/**
 * SCEditor plugin for MyBB forum
 *
 * @author Sam Clarke
 * @created 22/06/12
 * @version 1.4.2
 * @contact sam@sceditor.com
 * @license GPL
 */

if(!defined("IN_MYBB"))
	die("You cannot directly access this file.");

define('SCEDITOR_PLUGIN_VER', '1.4.2');


$plugins->add_hook("pre_output_page",         "sceditor_load", 100);
$plugins->add_hook("global_start",            "sceditor_sidebar_emoticons");
$plugins->add_hook("parse_message",           "sceditor_parse");
$plugins->add_hook("datahandler_user_update", "sceditor_usercp_update");
$plugins->add_hook("usercp_options_end",      "sceditor_usercp_options");

function sceditor_info()
{
	global $lang;

	$lang->load('config_sceditor');

	return array(
		"name"          => $lang->sceditor_title,
		"description"   => $lang->sceditor_desc,
		"website"       => "http://www.sceditor.com/",
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

	$db->write_query('ALTER TABLE `'.TABLE_PREFIX.'users` ADD `sceditor_enable` INT(1) NOT NULL DEFAULT \'1\', ADD `sceditor_sourcemode` INT(1) NOT NULL DEFAULT \'0\';');

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
					"square=Square\n" .
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

	$db->insert_query("settings", array(
		'name'		=> 'sceditor_lang',
		'title'		=> $lang->sceditor_lang_title,
		'description'	=> $lang->sceditor_lang_desc,
		'optionscode'	=> "select\n" .
					"default=Default\n" .
					"en=English (GB)\n" .
					"en-US=English (US)\n" .

					// English is the most common.
					// The rest should be sorted alphabetically.
					"ar=Arabic\n" .
					"nl=Dutch\n" .
					"et=Estonian\n" .
					"fr=French\n" .
					"de=German\n" .
					"no=Norwegian\n" .
					"pt-BR=Brazilian Portuguese\n" .
					"ru=Russian\n" .
					"es=Spanish\n" .
					"sv=Swedish\n" .
					"vi=Vietnamese",
		'value'		=> 'default',
		'disporder'	=> '10',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'sceditor_excluded_themes',
		'title'		=> $lang->sceditor_excluded_themes_title,
		'description'	=> $lang->sceditor_excluded_themes_desc,
		'optionscode'	=> "",
		'value'		=> '',
		'disporder'	=> '11',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'sceditor_enable_user_choice',
		'title'		=> $lang->sceditor_enable_user_choice_title,
		'description'	=> $lang->sceditor_enable_user_choice_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '12',
		'gid'		=> $groupid
	));

	$db->insert_query("settings", array(
		'name'		=> 'sceditor_enable_sidebar_emoticons',
		'title'		=> $lang->sceditor_enable_sidebar_emoticons_title,
		'description'	=> $lang->sceditor_enable_sidebar_emoticons_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> '13',
		'gid'		=> $groupid
	));

	rebuild_settings();
}

function sceditor_is_installed()
{
	global $db;

	$query = $db->simple_select("settinggroups", "COUNT(*) as rows", "name = 'sceditor'");
	$rows  = $db->fetch_field($query, "rows");

	return ($rows > 0);
}

function sceditor_uninstall()
{
	global $db;

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

	$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` DROP `sceditor_enable`");
	$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` DROP `sceditor_sourcemode`");
}

function sceditor_activate()
{
	include_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets(
		'usercp_options',
		'#<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showcodebuttons" id="showcodebuttons" value="1" {\$showcodebuttonscheck} /></td>[\r\n]{1,2}<td><span class="smalltext"><label for="showcodebuttons">{\$lang->show_codebuttons}</label></span>#',
		'{showeditorplaceholder}'
	);
}

function sceditor_deactivate()
{
	include_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets(
		'usercp_options',
		'#{showeditorplaceholder}#',
		'<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showcodebuttons" id="showcodebuttons" value="1" {$showcodebuttonscheck} /></td>
<td><span class="smalltext"><label for="showcodebuttons">{$lang->show_codebuttons}</label></span>'
	);
}

function sceditor_load($page)
{
	global $lang, $mybb, $cache, $theme;

	if(!$mybb->settings['enablesceditor'] || !$mybb->user['sceditor_enable'])
		return false;

	// check if editor should be enabled on this theme
	if(strrpos("," . strtolower($mybb->settings['sceditor_excluded_themes']) . ",", "," . strtolower($theme['name']) . ",") !== false)
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
	$sceditor_lang_url  = '';
	$jquery             = '';
	$sceditor_lang      = ($mybb->settings['sceditor_lang'] === 'default' ? 'en' : $mybb->settings['sceditor_lang']);
	$mybb_emoticons     = json_encode(array( 'dropdown' => $smilies ));
	$sceditor_autofocus = (THIS_SCRIPT != "showthread.php" ? 'true' : 'false');


	// Use users language if available
	if(in_array($lang->settings['htmllang'], array('en', 'en-US', 'en-GB', 'ar', 'nl', 'et', 'fr', 'de', 'no', 'pt-BR', 'ru', 'es', 'sv', 'vi')))
		$sceditor_lang = $lang->settings['htmllang'];

	// en-GB is just called en by SCEditor
	if($sceditor_lang === 'en-GB')
		$sceditor_lang = 'en';

	if($mybb->settings['sceditor_include_jquery'])
		$jquery = '<script src="jscripts/sceditor/jquery-1.8.2.min.js"></script>';

	if($mybb->settings['sceditor_include_jquery'] && $mybb->settings['sceditor_enable_jquery_noconflict'])
		$jqueryNoConflict = '$.noConflict();';

	if($sceditor_lang !== 'default')
		$sceditor_lang_url = '<script src="jscripts/sceditor/languages/' . $sceditor_lang . '.js?ver='.SCEDITOR_PLUGIN_VER.'"></script>';


	$js = '	' . $jquery . '
		<script>
			' . $jqueryNoConflict  . '
			var sceditor_opts = {
				lang:        "' . $sceditor_lang . '",
				emoticons:   ' . $mybb_emoticons . ',
				autofocus:   ' . $sceditor_autofocus . ',
				lang:        "' . $sceditor_lang . '",
				partialmode: false,
				sourcemode:  ' . $mybb->user['sceditor_sourcemode'] . '
			};
		</script>
		<link rel="stylesheet" href="jscripts/sceditor/themes/' . $mybb->settings['sceditor_theme'] . '.min.css?ver='.SCEDITOR_PLUGIN_VER.'" type="text/css" media="all" />
		<script src="jscripts/sceditor/jquery.sceditor.bbcode.min.js?ver='.SCEDITOR_PLUGIN_VER.'"></script>
		' . $sceditor_lang_url . '
		<script src="jscripts/sceditor/jquery.sceditor.mybb.helper.js?ver='.SCEDITOR_PLUGIN_VER.'"></script>';

	// strip the default editor
	$page = str_replace(build_mycode_inserter(THIS_SCRIPT === 'usercp.php' ? 'signature' : 'message'), "", $page);

	// add the editors JS
	return str_replace('</head>', $js . '</head>', $page);
}

function sceditor_sidebar_emoticons()
{
	global $mybb;

	if(THIS_SCRIPT === "usercp.php")
		if($mybb->input['action'] != "editsig" || !$mybb->settings['enablesceditor_signature'])
			return false;

	if($mybb->settings['enablesceditor'] && $mybb->user['sceditor_enable'])
		$mybb->user['showcodebuttons'] = $mybb->settings['sceditor_enable_sidebar_emoticons'];
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

function sceditor_usercp_update($obj)
{
	global $mybb;

	if (isset($mybb->input['showeditor']))
	{
		$obj->user_update_data['sceditor_enable']     = (int)$mybb->input['showeditor'] <  2 ? 1 : 0;
		$obj->user_update_data['sceditor_sourcemode'] = (int)$mybb->input['showeditor'] == 1 ? 1 : 0;
		$obj->user_update_data['showcodebuttons']     = (int)$mybb->input['showeditor'] == 2 ? 1 : 0;
	}
}

function sceditor_usercp_options()
{
	global $templates, $mybb, $lang;

	$lang->load("sceditor");

	if($mybb->settings['sceditor_enable_user_choice'])
	{
		$sceditor_selected = $mybb->user['sceditor_enable'] && !$mybb->user['sceditor_sourcemode'];
		$none_selected     = !$mybb->user['sceditor_enable'] && !$mybb->user['sceditor_sourcemode'] && !$mybb->user['showcodebuttons'];

		$html = "	<td valign=\"top\" colspan=\"2\">
					<span class=\"smalltext\">{$lang->showeditor}</span>
				</td>
			</tr>
			<tr>
				<td colspan=\"2\">
					<select name=\"showeditor\">
						<option value=\"0\" " . ($sceditor_selected ? 'selected="selected"' : '') . ">
							{$lang->showeditor_sceditor}
						</option>
						<option value=\"1\" " . ($mybb->user['sceditor_sourcemode'] ? 'selected="selected"' : '') . ">
							{$lang->showeditor_sceditor_source}
						</option>
						<option value=\"2\" " . ($mybb->user['showcodebuttons'] ? 'selected="selected"' : '') . ">
							{$lang->showeditor_mycode}
						</option>
						<option value=\"3\" " . ($none_selected ? 'selected="selected"' : '') . ">
							{$lang->showeditor_none}
						</option>
					</select>";

		$templates->cache['usercp_options'] = str_replace('{showeditorplaceholder}', $html, $templates->cache['usercp_options']);
	}
	else
		$templates->cache['usercp_options'] = str_replace('{showeditorplaceholder}', '', $templates->cache['usercp_options']);
}
