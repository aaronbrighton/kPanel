<?php
if (isset($_POST['whattodo']))
{
	if ($_POST['whattodo'] == 'delete')
	{
		// Delete packages.
		if (isset($_POST['option']))
		{
			// User has confirmed the delete.
			$packagesToDelete = explode(',', $_POST['packagesInQuestion']);
			//$errorString = '';
			foreach ($packagesToDelete as $key => $value)
			{
				if ($value != '')
				{
					$query = mysql_query("SELECT * FROM `packages` WHERE `id` = '".mysql_real_escape_string($value)."'");
					$row = mysql_fetch_array($query);
					
					if ($row['owner'] == $user['id'] || $user['package'] == 1)
					{
						// We are allowed to delete the selected package.
						if (check_referrer('index.php?module=usermanagement&page=removepackages', $setting['port']))
						{
							// Proper referrer.
							$query2 = mysql_query("SELECT * FROM `users` WHERE `package` = '".mysql_real_escape_string($value)."'");
							$numRows = mysql_num_rows($query2);
							
							if ($numRows > 0)
							{
								// One or more users has been assigned this package.
								while ($row2 = mysql_fetch_array($query2))
								{
									
									$errorString = $errorString . "<br />You can delete this package, as <span style='font-weight:bold'>".$row2['username']."</span> has been assigned it.";	
									
								}
							}
							
							// Done Error/Perm checks. //
							if (!isset($errorString))
							{
								// We actually can delete the package, that took alot!
								if ($query = mysql_query("DELETE FROM `packages` WHERE `id` = '".$row['id']."'"))
								{
									// Success
								}
								else
								{
									// Failed to delete package.
									?>
                                    <p>
                                        <span style="font-weight:bold;">Manage Packages</span>
                                    </p>                    
                                    <div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;">
                                    <?php echo mysql_error(); ?>
                                    </div>
									<br />
                                    <?php
								}
							}
							else
							{
								// Darn it we still cant...something went wrong.
								?>
                                <p>
                                    <span style="font-weight:bold;">Manage Packages</span>
                                </p>                    
                                <div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;">
                                <?php echo $errorString; ?>
                                </div>
                                <br />
                                <?php
								
							}
						}
					}
				}
				//$errorString = '';
			}
			echo "<meta http-equiv='refresh' content='0;URL=index.php?module=usermanagement&page=packages'>";
			exit;
		}
		else
		{
			foreach ($_POST as $key => $value)
			{
				if(is_int($key))
				{
					$packagesInQuestion = $packagesInQuestion . $key .',';
					$good = true;
				}
			}
			if (!isset($good))
			{
				$errorString = $errorString . '<br />You must select a package to delete.';
			}
			$packagesToDelete = explode(',', $packagesInQuestion);
			foreach ($packagesToDelete as $key => $value)
			{
				// Check to make sure we are allowed to delete, what is trying to be deleted.
				if ($value != '')
				{
					$query = mysql_query("SELECT * FROM `packages` WHERE `id` = '".mysql_real_escape_string($value)."'");
					$row = mysql_fetch_array($query);
					
					if ($row['owner'] == 0)
					{
						// User is trying to delete a package that he does not own.
						if (isset($errorString))
						{
							$errorString = $errorString . "<br />You cannot delete <span style='font-weight:bold'>".$row['name']."</span>, it is a default package.";
						}
						else
						{
							$errorString = $errorString . "You cannot delete <span style='font-weight:bold'>".$row['name']."</span>, it is a default package.";
						}
					}
					else
					{
						if ($row['owner'] != $user['id'] || $user['package'] == 1)
						{
							// The user doesnt own this package, how did this happen?  (HaX0r)
							if (isset($errorString))
							{
								$errorString = $errorString . "<br />You can't delete ".$row['name']." as you do not own it.";
							}
							else
							{
								$errorString = "<br />You can't delete ".$row['name']." as you do not own it.";
							}
						}
						
						$query = mysql_query("SELECT * FROM `users` WHERE `package` = '".mysql_real_escape_string($value)."'");
						$numRows = mysql_num_rows($query);
						
						if ($numRows > 0)
						{
							// One or more users has been assigned this package.
							while ($row = mysql_fetch_array($query))
							{
								if (isset($errorString))
								{
									$errorString = $errorString . "<br />You can delete this package, as <span style='font-weight:bold'>".$row['username']."</span> has been assigned it.";
								}
								else
								{
									$errorString = "You can delete this package, as <span style='font-weight:bold'>".$row['username']."</span> has been assigned it.";
								}
							}
						}
					}
				}
			}
			// Needs to verify the delete.
				if (isset($errorString))
				{
					// Error occured
					?>
            <p>
                <span style="font-weight:bold;">Manage Packages</span>
            </p>                    
                    <div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;">
                                <?php echo $errorString; ?>
                            </div>
                            <br />
                            <div align="center"><a href="index.php?module=usermanagement&page=packages">Go Back</a></div>
                    <?php
				}
				else
				{
				// No errors occured, lets ask to confirm.
			?>
            <p>
                <span style="font-weight:bold;">Manage Packages</span>
            </p>
			<div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ffb400;background-color:#ffdf93;font-weight:bold;">
            	<p>Are you sure you want to delete the following packages?</p>
                	<p>
                    <?php
					foreach ($_POST as $key => $value)
					{
						if (is_int($key))
						{
							$query = mysql_query("SELECT `name` FROM `packages` WHERE `id` = '".mysql_real_escape_string($key)."'");
							$row = mysql_fetch_array($query);
						?>
                        <span style="font-weight:bold;"><?php echo $row['name']; ?></span><br />
                         <?php
						}
					}	
					?>
            		</p>
            </div>
            <br />
            <div align="center">
            <form method="post" action="">
            	<input type="hidden" name="whattodo" value="<?php echo $_POST['whattodo']; ?>" />
                <input type="hidden" name="packagesInQuestion" value="<?php echo $packagesInQuestion; ?>" />
                <input type="hidden" name="option" value="" />
            	<input type="button" name="btnSubmit" value="Delete" onclick="submit_confirm(1, this.form)" /> <input type="button" name="cancel" value="Cancel" onclick="cancel_confirm(2)" />
            </div>
            </form>
            <?php
			}
		}
	}
}
?>