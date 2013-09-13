Outbound CNAM module is being uninstalled.<br>
<?php



// drop the table
$sql = "DROP TABLE IF EXISTS outcnam_config";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_freepbx( "Can not delete table: " . $check->getMessage() .  "\n");
}

?>
