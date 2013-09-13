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

print 'Installing the Outbound CNAM Module<br>';

global $db;
global $amp_conf;


$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$tablename = "outcnam_config";
$cols['outcnam_index'] = "INTEGER NOT NULL";
$cols['enable_cdr'] = "varchar(50) default NULL";
$cols['enable_rpid'] = "varchar(50) default NULL";
$cols['scheme'] = "varchar(50) default NULL";
$cols['route'] = "varchar(50) default NULL";

// create the table and define index
$sql = "CREATE TABLE IF NOT EXISTS $tablename ( temp INTEGER );";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create $tablename table: " . $check->getMessage() .  "\n");
}

//check to see that the proper columns are in the table.
$curret_cols = array();
$sql = "DESC $tablename";
$res = $db->query($sql);
while($row = $res->fetchRow())
{
	if(array_key_exists($row[0],$cols))
	{
		$curret_cols[] = $row[0];
		//make sure it has the latest definition
		$sql = "ALTER TABLE $tablename MODIFY ".$row[0]." ".$cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not update column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
	}
}
//add columns that are not already in the table
foreach($cols as $key=>$val)
{
	if(!in_array($key,$curret_cols))
	{
		$sql = "ALTER TABLE `$tablename` ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not add column ".$key.": " . $check->getMessage() .  "<br>");
		}
		else
		{
			print "Added column $key to $tablename table.<br>";
		}
	}
}

// set default config need a check here to see if defaults already exist
$sql = "INSERT INTO $tablename (outcnam_index) VALUES (1)";
$check = $db->query($sql);
if (DB::IsError($check)) {
        echo "Can not insert default values: " . $check->getMessage() .  "\n";
}

?>