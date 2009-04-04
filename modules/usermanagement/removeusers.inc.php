<p>
	<span style="font-weight:bold;">User Management</span>
</p>
<br />
<?php
if (isset($_POST['whattodo']))
{
	// User has selected someusers and wants to do some stuff.
	if ($_POST['whattodo'] == 'delete')
	{
		// Someone wants to delete some users.
		if (isset($_POST['option']))
		{

			if ($_POST['option'] == 1)
			{

				// Keep Files
				$usersToDelete = explode(',', $_POST['usersInQuestion']);
				foreach ($usersToDelete as $key => $value)
				{
				if ($value == '')
				{
				
				}
				else
				{
					if ($query = mysql_query("SELECT * FROM `users` WHERE `id` = '".mysql_real_escape_string($value)."'"))
					{
						// User exists in the db. (Just checking).
						$row = mysql_fetch_array($query);
						$systemQuota = shell_exec("/usr/bin/sudo repquota -u /");
						preg_match("/".$user['server_account']."(.*)(--|\+-)(\s+)(\d+)(\s+)(\d+)/", $systemQuota, $matches);
						$storageUsed = $matches['4']*1024;
						$storageTotal = $matches['6']*1024;
						$storagePercent = ($storageUsed/$storageTotal)*100;
						
						$backupFilename = "backup-".$row['username']."_".date('H.iA')."-".date('m.d.Y').".zip";
						
						shell_exec("/usr/bin/sudo zip -r /var/www/katrina/tmp/".$backupFilename." /var/www/".$row['server_account']); // Backup files (zip).
						
						if (filesize("/var/www/katrina/tmp/".$backupFilename) > ($storageTotal - $storageUsed))
						{
							// The user does not have enough space to backup this user. Don't delete the user.
							$error = true;
							?>
                            <div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;">
									Failed to remove user <span style="font-weight:bold"><?php echo $row['username']; ?></span>, you do not have enough room to store this backup, user remains.
								</div>
								<br />
                            <?php
							shell_exec("/usr/bin/sudo rm -rf /var/www/katrina/tmp/".$backupFilename);
						}
						else
						{
							
							shell_exec("/usr/bin/sudo deluser ".$row['server_account']); // Delete account
							$systemUserList = shell_exec("cat /etc/passwd");
							if (!preg_match("/".$row['server_account'].":x(.*)/", $systemUserList))
							{
								// Account was successfully deleted.
								shell_exec("/usr/bin/sudo mv /var/www/katrina/tmp/".$backupFilename." /var/www/".$user['server_account']."/backups/".$backupFilename); // Backup files (zip).
								shell_exec("/usr/bin/sudo chown ".$user['server_account'].":".$user['server_account']." /var/www/".$user['server_account']."/backups/".$backupFilename); // SECURITY...Make sure this file belongs to the right user.
								shell_exec("/usr/bin/sudo rm -rf /var/www/katrina/tmp/".$backupFilename); // Delete the file from the panel's tmp.
								//shell_exec("sudo tar cf /var/www/".$user['server_account']."/backups/backup-".$row['username']."_".date('H.i')."-".date('m.d.Y').".tar /var/www/".$row['server_account']); // Backup files (tar).
								//shell_exec("sudo gzip /var/www/".$user['server_account']."/backups/backup-".$row['username']."_".date('H.i')."-".date('m.d.Y').".tar");
								
								shell_exec("/usr/bin/sudo rm -rf /var/www/".$row['server_account']); // Delete files.
								if (mysql_query("DELETE FROM `users` WHERE `id` = '".mysql_real_escape_string($value)."'"))
								{
									// User successfully removed from the databse.
								}
							}
							else
							{
								// User still exists, somethign went wrong.
								$error = true;
								?>
								<div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;">
									Failed to remove user <?php echo $row['username']; ?>, user remains.
								</div>
								<br />
								<?php	
								
							}
						}
					}
				}
				}
				
			}
			else if ($_POST['option'] == 2)
			{
				// Delete Files
				$usersToDelete = explode(',', $_POST['usersInQuestion']);
				foreach ($usersToDelete as $key => $value)
				{
				if ($value == '')
				{
				
				}
				else
				{
					if ($query = mysql_query("SELECT `server_account` FROM `users` WHERE `id` = '".mysql_real_escape_string($value)."'"))
					{
						// User exists in the db. (Just checking).
						$row = mysql_fetch_array($query);
						shell_exec("/usr/bin/sudo deluser ".$row['server_account']); // Delete user.
						shell_exec("/usr/bin/sudo rm -rf /var/www/".$row['server_account']); // Delete files.
						$systemUserList = shell_exec("cat /etc/passwd");
						if (preg_match("/".$row['server_account'].":x(.*)/", $systemUserList))
						{
							// User still exists, somethign went wrong.
							?>
                            <div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;">
                                Failed to remove user <?php $row['username']; ?>, user remains.
                            </div>
                            <br />
                            <?php	
						}
						else
						{
							if (mysql_query("DELETE FROM `users` WHERE `id` = '".mysql_real_escape_string($value)."'"))
							{
								// User successfully removed from the databse.

							}
						}
					}
				}
				}
			}
			
			if ($error != true)
			{
				echo "<meta http-equiv='refresh' content='0;URL=index.php?module=usermanagement'>";
				exit;
			}
			
		}
		else
		{
			?>
            <form method="post" action="">
            <div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ffb400;background-color:#ffdf93;font-weight:bold;">
            	<p>Are you sure you want to delete the following users?</p>
                	<p>
                    <?php
					foreach ($_POST as $key => $value)
					{
						if (is_int($key))
						{
							$usersInQuestion = $usersInQuestion . $key .',';
							$query = mysql_query("SELECT `username` FROM `users` WHERE `id` = '".$key."'");
							$row = mysql_fetch_array($query);
						?>
                        <span style="font-weight:bold;"><?php echo $row['username']; ?></span><br />
                         <?php
						}
					}	
					?>
            		</p>
            </div>
            <br />
            <div align="center">
            	<input type="hidden" name="whattodo" value="<?php echo $_POST['whattodo']; ?>" />
            	<input type="hidden" name="usersInQuestion" value="<?php echo $usersInQuestion; ?>" />
                <input type="hidden" name="option" value="" />
            	<input type="button" value="Delete and Backup Files" onclick="submit_confirm(1, this.form)" /> <input type="button" value="Delete and Remove Files" onclick="submit_confirm(2, this.form)" /><br />
                <input type="button" name="cancel" value="Cancel" onclick="cancel_confirm(1)" />
            </div>
            <br />
            </form>
            <?php
		}
	}
}
?>