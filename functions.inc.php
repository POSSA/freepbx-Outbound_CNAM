<?php /* $Id */

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//This file is part of FreePBX.
//
//    This is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    This module is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    see <http://www.gnu.org/licenses/>.
//



//  check for settings and return
function outcnam_config($id) {
	$sql = "SELECT * FROM outcnam_config WHERE `outcnam_index` = '$id'";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	return is_array($results)?$results:array();
}

// store settings
function outcnam_edit($id,$post){
	global $db;

	$var1 = $db->escapeSimple($post['enable_cdr']);
	$var2 = $db->escapeSimple($post['enable_rpid']);
	$var3 = $db->escapeSimple($post['scheme']);

	$results = sql("
		UPDATE outcnam_config 
		SET 
			enable_cdr = '$var1', 
			enable_rpid = '$var2', 
			scheme = '$var3'
		WHERE outcnam_index = '$id'");

	needreload();
}

function outcnam_hookGet_config($engine) {

	// This generates the dialplan
	global $ext;
	global $asterisk_conf;
	global $astman;
	switch($engine) {
		case "asterisk":
			$config = outcnam_config(1);
			$context = "macro-dialout-trunk";
			$exten = "s";

			$spice_position = -4;

			if ($config[0]['enable_cdr']=='CHECKED' || $config[0]['enable_rpid']=='CHECKED') {
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('CIDSFSCHEME', base64_encode($config[0]['scheme'])),"",$spice_position);
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('temp1', '${CALLERID(name)}'),"",$spice_position);
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('CALLERID(name)', ''),"",$spice_position);
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('temp2', '${CALLERID(number)}'),"",$spice_position);
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('CALLERID(number)', '${DIAL_NUMBER}'),"",$spice_position);
				$ext->splice($context, $exten, 'customtrunk', new ext_agi('/var/www/html/admin/modules/superfecta/agi/superfecta.agi'),"",$spice_position);
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('CALLERID(name)', '${temp1}'),"",$spice_position);
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('CALLERID(number)', '${temp2}'),"",$spice_position);

				
				
			}
			if ($config[0]['enable_cdr']=='CHECKED' ) {
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('CDR(accountcode,r)', '${lookupcid}'),"",$spice_position);
			}
			if ( $config[0]['enable_rpid']=='CHECKED') {
				$ext->splice($context, $exten, 'customtrunk', new ext_setvar('CONNECTEDLINE(name,i)', '${lookupcid}'),"",$spice_position);
			}
		
		break;
	}
}

		
function outcnam_vercheck() {
	$newver = false;
	if ( function_exists(outcnam_xml2array)){
		$module_local = outcnam_xml2array("modules/outcnam/module.xml");
		$module_remote = outcnam_xml2array("https://raw.github.com/POSSA/freepbx-Outbound_CNAM/master/module.xml");

		
		if ( $module_remote[module][version] > $module_local[module][version])
			{
			$newver = true;
			}
		return ($newver);
		}
	}

//Parse XML file into an array
function outcnam_xml2array($url, $get_attributes = 1, $priority = 'tag')  {
	$contents = "";
	if (!function_exists('xml_parser_create'))
	{
		return array ();
	}
	$parser = xml_parser_create('');
	if(!($fp = @ fopen($url, 'rb')))
	{
		return array ();
	}
	while(!feof($fp))
	{
		$contents .= fread($fp, 8192);
	}
	fclose($fp);
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);
	if(!$xml_values)
	{
		return; //Hmm...
	}
	$xml_array = array ();
	$parents = array ();
	$opened_tags = array ();
	$arr = array ();
	$current = & $xml_array;
	$repeated_tag_index = array ();
	foreach ($xml_values as $data)
	{
		unset ($attributes, $value);
		extract($data);
		$result = array ();
		$attributes_data = array ();
		if (isset ($value))
		{
			if($priority == 'tag')
			{
				$result = $value;
			}
			else
			{
				$result['value'] = $value;
			}
		}
		if(isset($attributes) and $get_attributes)
		{
			foreach($attributes as $attr => $val)
			{
				if($priority == 'tag')
				{
					$attributes_data[$attr] = $val;
				}
				else
				{
					$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
		}
		if ($type == "open")
		{
			$parent[$level -1] = & $current;
			if(!is_array($current) or (!in_array($tag, array_keys($current))))
			{
				$current[$tag] = $result;
				if($attributes_data)
				{
					$current[$tag . '_attr'] = $attributes_data;
				}
				$repeated_tag_index[$tag . '_' . $level] = 1;
				$current = & $current[$tag];
			}
			else
			{
				if (isset ($current[$tag][0]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array($current[$tag],$result);
					$repeated_tag_index[$tag . '_' . $level] = 2;
					if(isset($current[$tag . '_attr']))
					{
						$current[$tag]['0_attr'] = $current[$tag . '_attr'];
						unset ($current[$tag . '_attr']);
					}
				}
				$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
				$current = & $current[$tag][$last_item_index];
			}
		}
		else if($type == "complete")
		{
			if(!isset ($current[$tag]))
			{
				$current[$tag] = $result;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				if($priority == 'tag' and $attributes_data)
				{
					$current[$tag . '_attr'] = $attributes_data;
				}
			}
			else
			{
				if (isset ($current[$tag][0]) and is_array($current[$tag]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					if ($priority == 'tag' and $get_attributes and $attributes_data)
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array($current[$tag],$result);
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $get_attributes)
					{
						if (isset ($current[$tag . '_attr']))
						{
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
						if ($attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
					}
					$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
				}
			}
		}
		else if($type == 'close')
		{
			$current = & $parent[$level -1];
		}
	}
	return ($xml_array);
}