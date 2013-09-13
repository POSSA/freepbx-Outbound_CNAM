<?php 

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

// check to see if user has automatic updates enabled in FreePBX settings
$cm =& cronmanager::create($db);
$online_updates = $cm->updates_enabled() ? true : false;

// check dev site to see if new version of module is available
if ($online_updates && $foo = outcnam_vercheck()) {
	print "<br>A <b>new version of this module is available</b> from the <a target='_blank' href='http://pbxossa.org'>PBX Open Source Software Alliance</a><br>";
}

$module_local = outcnam_xml2array("modules/outcnam/module.xml");


// check form and define var for form action
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//if submitting form, update database
if(isset($_POST['submit'])) {
		outcnam_edit(1,$_POST);
		redirect_standard();
	
	}


//  to add right navigation menu enclose output in <div class="rnav"> </div>
/* echo '<div class="rnav">';
echo "menu items";
echo '</div>';
*/
$config = outcnam_config(1);

?>

<h2>Outbound CallerID Name</h2>

<form autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" >
<table>
		<tr>			
			<td colspan="2">			
			    <?php echo _('This module is used in conjunction with the Caller ID Superfecta Module to add Caller ID names to outbound dialled numbers for dispalay on the endpoint with rpid and for the CDR.'); ?>
			</td>			
		</tr>
</table>
<?php

	//if submitting form, update database
	if(isset($_POST['submit'])) {
			outcnam_edit(1,$_POST);
	}

$sql = "SELECT * FROM superfectaconfig WHERE field ='order' ORDER BY value ASC";
$schemes = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);


	$html = "<table>";
	$html .= "<tr><td colspan='2'><h5><a href='#' class='info'>Outbound CNAM Config<span>Config options</span></a><hr></h5></td></tr>";
	$html .= "<tr>";
	$html .= "<td><a href='#' class='info'>Enable CDR<span>If enabled, the CDR AccountCode column will be populated with any found outbound CNAM.</span></a></td>";
	$html .= "<td><input type='checkbox' name='enable_cdr' value='CHECKED' ".$config[0]['enable_cdr']."></td>";
	$html .= "</tr><tr>";
	$html .= "<td><a href='#' class='info'>Enable RPID<span>If enabled, found CNAM will be displayed on rpid enabled endpoints.</span></a></td>";
	$html .= "<td><input type='checkbox' name='enable_rpid' value='CHECKED' ".$config[0]['enable_rpid']."></td>";
	$html .= "</tr><tr>";
	
	$html.='<td><a href="#" class="info">' . _('Scheme') . '<span>' . _("Setup Schemes in CID Superfecta section") . '</span></a>:</td>';

	$html.='<td><select name="scheme">';
	$scheme = $config[0]['scheme'];
	$first = '<option value="ALL|ALL" {$selected}>ALL</option>';
	$has_selected = FALSE;
	foreach ($schemes as $data) {
		if ($scheme == $data['source']) {
			$selected = 'selected';
			$has_selected = TRUE;
		} else {
			$selected = '';
		}
		$name = explode("_", $data['source']);
		$last .= '<option value="' . $data['source'] . '" ' . $selected . '>' . $name[1] . '</option>';
	}
	$selected = ($has_selected) ? 'selected' : '';
	$first = str_replace('{$selected}', $selected, $first);
	$html .= $first . $last;
	$html.= '</select></td></tr>';
	
	
	$html.= "</table>";
	echo $html;

?>
<table>
	<tr>
		<td colspan="2"><br><h6><input name="submit" type="submit" value="<?php echo _("Submit Changes")?>" ></h6></td>
	</tr>
</table>
</form>
<center><br>

<?php
echo '<p align="center" style="font-size:11px;">This module is maintained by the developer community at the <a target="_blank" href="http://pbxossa.org">PBX Open Source Software Alliance</a>. Support, documentation and current versions are available at the module <a target="_blank" href="https://github.com/POSSA/freepbx-lenny_blacklist_mod">dev site</a>.';
echo '<p align="center" style="font-size:11px;">Outbound CNAM Module version: '.$module_local[module][version].'</center>';
?>
