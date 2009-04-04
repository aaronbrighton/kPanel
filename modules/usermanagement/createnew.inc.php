<?php
if (isset($_POST['btnSubmit']))
{
	$filterChars = array('/', '\\', ':', '*', '?', '<', '>', '|');
	$inputName = $_POST['txtName'];
	$inputUsername = $_POST['txtUsername'];
	$inputPassword_raw = $_POST['txtPassword'];
	$inputPassword = sha1($inputPassword_raw);
	$inputEmail = $_POST['txtEmail'];
	$inputServerAccount = $_POST['txtServerAccount'];
	$inputServerAccountPassword_raw = $_POST['txtServerAccountPassword'];
	$inputServerAccountPassword = crypt($inputServerAccountPassword_raw, "password");
	$inputServerPath = $_POST['txtServerPath'];
	
	$inputStorage = $_POST['txtStorage'];
	$inputDatabases = $_POST['txtDatabases'];
	
	$inputPackage = $_POST['selectPackage'];
	
	foreach ($_POST as $key => $value)
	{		
		if (is_int($key) && $key != 'txtDatabases' && $key != 'txtStorage' && $key != 'txtName' && $key != 'txtUsername' && $key != 'txtPassword' && $key != 'selectPackage' && $key != 'btnSubmit')
		{
			// Loop through modules.
			if ($value == 'on')
			{
				$module_on[$key] = true;
			}
			else if ($value =='off')
			{
				$module_off[$key] = true;
			}
		}
	}

	$query = mysql_query("SELECT * FROM `users` WHERE `username` = '".mysql_real_escape_string($inputUsername)."'") or die(handle_error(mysql_error(), 'MySQL'));
	
	// Verifying package selection. //
	if ($inputPackage != 'none')
	{
		if ($user['package'] == 1)
		{
			// User is an admin.
			$query_packageCheck = mysql_query("SELECT * FROM `packages` WHERE `id` = '".mysql_real_escape_string($inputPackage)."' && (`owner` = '0' OR `owner` = '".mysql_real_escape_string($user['id'])."')");
			$row_package = mysql_fetch_array($query_packageCheck);
		}
		else
		{
			// User is not an admin.
			$query_packageCheck = mysql_query("SELECT * FROM `packages` WHERE `id` = '".mysql_real_escape_string($inputPackage)."' AND `account_creator` = '0' AND (`owner` = '0' OR `owner` = '".mysql_real_escape_string($user['id'])."')");
			$row_package = mysql_fetch_array($query_packageCheck);
		}
		
		if (mysql_num_rows($query_packageCheck) == 0)
		{
			// The package doesnt exist in the database, or this user is not allowed to create a user with it.
			$errorString = $errorString . '<br />You cannot create a user with the package selected, you do not have permission.';
		}
	}
	else
	{
		// No package was selected.
		$inputPackage = 0;
	}
	
	
	if (isset($module_on))
	{
		foreach ($module_on as $key => $value)
		{
			if ($user['package'] == 1)
			{
				// User is an admin.
				$query_moduleCheck = mysql_query("SELECT * FROM `modules` WHERE `active` = '1' AND `id` = '".$key."'");
			}
			else
			{
				// User is not an admin.
				$query_moduleCheck = mysql_query("SELECT * FROM `modules` WHERE `active` = '1' AND `package` = '0' AND `id` = '".$key."'");
			}
			
			if (mysql_num_rows($query_moduleCheck) == 0)
			{
				// This user can't create a user with this module...
				$errorString = $errorString . '<br />You cannot create a user with the modules selected, you do not have permission.';
			}
		}
	}
	
	// Filtering Strings //
	
	if(eregi('#^[A-Z0-9 ]+$#i', $inputName))
	{
		// THe username contains an inavlid character
		$errorString = $errorString . '<br />The Name entered has characters that are not alpha-numerical.';
	}

	if(eregi('[^a-zA-Z0-9_]', $inputUsername))
	{
		// THe username contains an inavlid character
		$errorString = $errorString . '<br />The Username entered has characters that are not alpha-numerical.';
	}
	
	if(!eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$', $inputEmail))
	{
		// THe username contains an inavlid character
		$errorString = $errorString . '<br />The Email Address is not valid.';
	}
	
	if(eregi('[^0-9_]', $inputStorage))
	{
		// THe username contains an inavlid character
		$errorString = $errorString . '<br />That is not a valid storage amount.';
	}
	else
	{
		// Valid integer check to make sure this user has enough space to support this new account.
		$query_usage = mysql_query("SELECT * FROM `users` WHERE `owner` = '".$user['id']."'");
		$systemQuota = shell_exec("/usr/bin/sudo repquota -u /");
		preg_match("/".$user['server_account']."(.*)(--|\+-)(\s+)(\d+)(\s+)(\d+)/", $systemQuota, $matches);
		$storageUsed = $matches['4'] / 1024;
		$storageTotal = $matches['6'] / 1024;
		$storagePercent = ($storageUsed/$storageTotal)*100;
		if (mysql_num_rows($query_usage) > 0)
		{
			
			// This user owns one or more other users.
			while ($row = mysql_fetch_array($query_usage))
			{
				preg_match("/".$row['server_account']."(.*)(--|\+-)(\s+)(\d+)(\s+)(\d+)/", $systemQuota, $matches);
				$actualStorageUsed  = $actualStorageUsed + $matches['6'];
				$actualStorageTotal = $actualStorageTotal + $matches['6'];
			}
			
		}
		$actualStorageUsed = ($actualStorageUsed + ($storageUsed*1024)) / 1024;
		$actualStorageTotal = $storageTotal;
		$actualStoragePercent = ($actualStorageUsed/$actualStorageTotal)*100;

		if (($actualStorageTotal - $actualStorageUsed) < $inputStorage)
		{
			// User does not have enough storage, to support creating an account with this much storage.
			$errorString = $errorString . '<br />You do not have enough space to create user with this amount of storage.';
		}
	}
	
	if ($query_databases = mysql_query("SELECT * FROM `users` WHERE `owner` = '".$user['id']."'"))
	{
		// Does this purson own other users.
		while ($row_databases = mysql_fetch_array($query_databases))
		{
			// Loop through adding up databases.
			if ($row_databases['packages'] == 0)
			{
				// Use does not belong to a package.
				$total_db_count = $total_db_count + $row_databases['max_databases'];
			}
			else
			{
				// User is part of a package.
				$row_database_package = mysql_query("SELECT * FROM `packages` WHERE `id` = '".$row_databases['package']."'");
				$total_db_count = $total_db_count + $row_database_package['databases'];
			}
		}
	}
	
	$total_db_count = $total_db_count + $user['num_databases'];
	if ($user['package'] == 0)
	{
		// User doesnt belong to any package.
		$self_databases = $user['max_databases'];
	}
	else
	{
		// User belongs to a package.
		$query_self_databases = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '".$user['package']."'"));
		$self_databases = $query_self_databases['databases'];
	}
	
	if(eregi('[^0-9_]', $inputDatabases))
	{
		// THe username contains an inavlid character
		$errorString = $errorString . '<br />That is not a valid number of databases.';
	}
	
	if (($total_db_count+$inputDatabases) > $self_databases)
	{
		$errorString = $errorString . '<br />You can\'t create a user with that many databases.';
	} 
	
	if(eregi('[^a-zA-Z0-9_]', $inputServerAccount))
	{
		// THe username contains an inavlid character
		$errorString = $errorString . '<br />The Server Account entered has characters that are not alpha-numerical.';
	}
	else
	{
		$systemUserList = shell_exec("cat /etc/passwd");
		if (preg_match("/".$inputServerAccount.":x(.*)/", $systemUserList))
		{
			// System Account is already taken.
			$errorString = $errorString . '<br />Server Account Name already taken. (Please choose another Server Account Name.)';
		}
	}
	
	// MySQL and system tasks validation. //
	
	if (mysql_num_rows($query) != 0)
	{
		// Username is already taken for Katrina Panel.
		$errorString = $errorString . '<br />Username for Control Panel account already taken.';
	}
	
	if (!isset($errorString))
	{
		// No errors occured (few...)
		shell_exec("/usr/bin/sudo groupadd ".$inputServerAccount); // Create group.
		shell_exec("/usr/bin/sudo useradd -d /var/www/".$inputServerAccount." -m -k /var/www/skeleton -g ".$inputServerAccount." -p ".$inputServerAccountPassword." ".$inputServerAccount); // Create the account, and folder.
		shell_exec("/usr/bin/sudo quotatool -u ".$inputServerAccount." -bq ".$inputStorage."M -l '".$inputStorage." Mb' /"); // Set quota limits for the user.
		
		$systemUserList = shell_exec("cat /etc/passwd");
		if (!preg_match("/".$inputServerAccount.":x(.*)/", $systemUserList))
		{
			// System Account was failed to be created.
			shell_exec("/usr/bin/sudo deluser --remove-home");
			$fatalError = "An error occured while creating the user from the shell.";
		}
		else
		{
			if ($inputPackage == 'none')
			{
				if (isset($module_on))
				{
					foreach ($module_on as $key => $value)
					{
						$modules = $modules . $key . ',';
					}
				}
			}
			
			// Time to do database stuff.
			if ($query = mysql_query("INSERT INTO `users` (`id`, `owner`, `username`, `password`, `name`, `email`, `last_login`, `last_login_ip`, `server_account`, `num_databases`, `max_databases`, `package`, `allow`, `server_path`) VALUES(NULL, '".mysql_real_escape_string($user['id'])."', '".mysql_real_escape_string($inputUsername)."', '".mysql_real_escape_string($inputPassword)."', '".mysql_real_escape_string($inputName)."', '".mysql_real_escape_string($inputEmail)."', '0000-00-00', '', '".mysql_real_escape_string($inputServerAccount)."', '0', '".mysql_real_escape_string($inputDatabases)."', '".mysql_real_escape_string($inputPackage)."', '".mysql_real_escape_string($modules)."', '".mysql_real_escape_string($setting['home_dirs_prefix']).mysql_real_escape_string($inputServerAccount)."')") or die(handle_error(mysql_error(), 'MySQL')))
			{
				// Query succeeded.
				$success = true;
			}
			else
			{
				// Query failed, undo creation of user.
				shell_exec("/usr/bin/sudo deluser --remove-home");
				$fatalError = "An error occured attempting to enter the user into the database, '".mysql_error()."'";
			}
		}
	}
	
}
?>
<p>
	<span style="font-weight:bold;">Create New User</span>
</p>
<?php
if ($success == true)
{
?>

<div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#06ff00;background-color:#a2ff98;">
	User has been successfully created!
</div>
<br />
<table cellspacing="1" cellpadding="5" align="center" width="400">
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Name
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php echo $inputName; ?>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Username
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php echo $inputUsername; ?>
        </td>
    </tr>
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Password
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php echo $inputPassword_raw; ?>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Email Address
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php echo $inputEmail; ?>
        </td>
    </tr>
    <tr >
    	<td>&nbsp;</td>
        <td></td>
    </tr>
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Server Account
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php echo $inputServerAccount; ?>
        </td>
    </tr>
    <tr  style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Server Account Password
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php echo $inputServerAccountPassword_raw; ?>
        </td>
    </tr>
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Storage (MB)
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php echo $inputStorage; ?>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Databases
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php echo $inputDatabases; ?>
        </td>
    </tr>
    <tr >
    	<td>&nbsp;</td>
        <td></td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Package
        </td>
        <td align="right" style="vertical-align:middle;">
        	<?php 
			if ($row_package['name'] != 0)
			{
				echo $row_package['name'];
			}
			else
			{
				echo '-- None --';
			} 
			?>
        </td>
    </tr>
    <?php
	if ($user['package'] == 1)
	{
		// User is an admin.
		$query = mysql_query("SELECT * FROM `modules` WHERE `active` = '1'");
	}
	else
	{
		// User is not an admin.
		$query = mysql_query("SELECT * FROM `modules` WHERE `active` = '1' AND `package` = '0'");
	}
    $m = 0;
	while ($row = mysql_fetch_array($query))
	{
		if (isset($module_on))
		{
			$defaults = $module_on;
		}
		else if ($row_default['modules'] != '0')
		{
			$defaults = explode(',', $row_default['modules']);
		}
		
		if ($m == 0)
		{
			$style = " style=\"background-color:#e1e1e1;\"";
			$m=1;
		}
		else if ($m == 1)
		{
			$style = " style=\"background-color:#f1f1f1;\"";
			$m=0;
		}
		?>
	<tr<?php echo $style; ?>>
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	<?php echo $row['name']; ?>
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="checkbox" <?php if (isset($defaults)) { if (isset($defaults[$row['id']])) { ?>checked="checked"<?php } } else { ?>checked="checked"<?php } ?> disabled="disabled" />
        </td>
    </tr>
        <?php
	}
	?>
    <tr style="background-color:#f1f1f1;">
    	<td colspan="3" align="center" style="vertical-align:center;">
        	<a href="index.php?module=usermanagement" style="font-weight:bold;">Done!</a>
        </td>
    </tr>
</table>
<?php
}
else if ($success != true)
{

if (isset($errorString))
{
?>
<div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;">
	An error occured:
    <span style="font-weight:bold;"><?php echo $errorString; ?></span>
</div>
<br />
<?php
}

if (isset($fatalError))
{
?>
<div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;">
	An error occured:
    <span style="font-weight:bold;"><?php echo $fatalError; ?>
<br />
<?php
}

if (isset($_GET['package']))
{
	if ($user['package'] == 0)
	{
		$query = mysql_query("SELECT * FROM `packages` WHERE (`owner` = '".mysql_real_escape_string($user['id'])."' OR `owner` = '0') AND `id` = '".$_GET['package']."'");
	}
	else
	{
		$query = mysql_query("SELECT * FROM `packages` WHERE (`owner` = '".mysql_real_escape_string($user['id'])."' OR `owner` = '0') AND `account_creator` = '0' AND `id` = '".$_GET['package']."'");
	}
	
	if (mysql_num_rows($query) == 0)
	{
		// No results for the package being requested (HaX0R).
		$query = mysql_query("SELECT * FROM `packages` WHERE `owner` = '".mysql_real_escape_string($user['id'])."' AND `default` = '1'");
		if (mysql_num_rows($query) == 1)
		{
			// Their is a default, specified by the user.
		}
		else
		{
			$query = mysql_query("SELECT * FROM `packages` WHERE `owner` = '0' AND `default` = '1'");
		}	
	}
	$row_default = mysql_fetch_array($query);
}
else
{
	$query = mysql_query("SELECT * FROM `packages` WHERE `owner` = '".mysql_real_escape_string($user['id'])."' AND `default` = '1'");
	if (mysql_num_rows($query) == 1)
	{
		// Their is a default, specified by the user.
		$row_default = mysql_fetch_array($query);
	}
	else
	{
		$query = mysql_query("SELECT * FROM `packages` WHERE `owner` = '0' AND `default` = '1'");
		$row_default = mysql_fetch_array($query);
	}
}
?>
<form name="createnewuser" method="post" action="index.php?module=usermanagement&page=createnew">
<table cellspacing="1" cellpadding="5" align="center" width="400">
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Name
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtName" value="<?php if (isset($_GET['name'])){ echo $_GET['name']; } else { echo $inputName; }?>" size="30" maxlength="63" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Desired Username
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtUsername" value="<?php if (isset($_GET['username'])){ echo $_GET['username']; } else { echo $inputUsername; }?>" size="30" onkeyup="user_pass_change_cp(1, this.form);" onchange="user_pass_change_cp(1, this.form);" maxlength="63" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Desired Password
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtPassword" value="<?php if (isset($_GET['password'])){ echo $_GET['password']; } else { echo $inputPassword_raw; }?>" size="30" onkeyup="user_pass_change_cp(2, this.form);" onchange="user_pass_change_cp(1, this.form);" maxlength="63" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Email Address
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtEmail" value="<?php if (isset($_GET['email'])){ echo $_GET['email']; } else { echo $inputEmail; }?>" size="30" maxlength="255" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr>
    	<td>&nbsp;</td>
        <td></td>
    </tr>
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Server Account
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtServerAccount" value="<?php if (isset($_GET['username'])){ echo $_GET['username']; } else { echo $inputUsername; }?>" size="30" maxlength="32" readonly="readonly" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Server Account Password
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtServerAccountPassword" value="<?php if (isset($_GET['password'])){ echo $_GET['password']; } else { echo $inputPassword_raw; }?>" size="30" maxlength="32" readonly="readonly" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Storage (MB)
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtStorage" value="<?php if (isset($inputStorage)){ echo $inputStorage; } else { echo $row_default['space']; }?>" onchange="noPackage(this.form)" size="5" maxlength="63" style="text-align:right;" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	# of Databases (Max. 9999)
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtDatabases" value="<?php if (isset($inputDatabases)){ echo $inputDatabases; } else { echo $row_default['databases']; }?>" onchange="noPackage(this.form)" size="5" maxlength="4" style="text-align:right;" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr>
    	<td>&nbsp;</td>
        <td></td>
    </tr>
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Package
        </td>
        <td align="right" style="vertical-align:middle;">
        	<select name="selectPackage" style="width:210px;">
            <?php if (!isset($inputPackage)) { ?><option value="none">-- None --</option><?php } ?>
				<?php
				if (isset($inputPackage))
				{
					if ($inputPackage == 'none')
					{
						?>
                        <option value="none" selected="selected">-- None --</option>
                        <?php
					}
					else
					{
						?>
                        <option value="none">-- None --</option>
                        <?php
						if ($user['package'] == 1)
						{
							// User is an admin
							$query = mysql_query("SELECT * FROM `packages` WHERE `owner` = '0' AND `id` = '".mysql_real_escape_string($inputPackage)."'");
							$row = mysql_fetch_array($query);
							
							?>
                            <option value="<?php echo $row['id']; ?>" onclick="change_package(<?php echo $row['id']; ?>, this.form)" selected="selected"><?php echo $row['name']; ?></option>
                            <?php
							
						}
						else
						{
							$query = mysql_query("SELECT * FROM `packages` WHERE `id` = '".mysql_real_escape_string($inputPackage)."' AND `account_creator` = '0' AND (`owner` = '0' OR `owner` = '".mysql_real_escape_string($user['id'])."')");
							$row = mysql_fetch_array($query);
							
							?>
                            <option value="<?php echo $row['id']; ?>" onclick="change_package(<?php echo $row['id']; ?>, this.form)" selected="selected"><?php echo $row['name']; ?></option>
                            <?php
						}
						
					}
				}
				else if (isset($row_default))
				{
				?>
                <option value="<?php echo $row_default['id']; ?>" onclick="change_package(<?php echo $row_default['id']; ?>, this.form)" selected="selected"><?php echo $row_default['name']; ?></option>
                <?php
				}
				
				if ($user['package'] == 1)
				{
					$query = mysql_query("SELECT * FROM `packages` WHERE `owner` = '0' OR `owner` = '".mysql_real_escape_string($user['id'])."'");
				}
				else
				{
					
					$query = mysql_query("SELECT * FROM `packages` WHERE `account_creator` = '0' AND (`owner` = '0' OR `owner` = '".mysql_real_escape_string($user['id'])."')");
				}
				while ($row = mysql_fetch_array($query))
				{
					if (!isset($inputPackage))
					{
						if ($row_default['id'] != $row['id'])
						{
						?>
						<option value="<?php echo $row['id']; ?>" onclick="change_package(<?php echo $row['id']; ?>, this.form)"><?php echo $row['name']; ?></option>
						<?php
						}
					}
					else
					{
						if ($inputPackage != $row['id'])
						{
						?>
                        
						<option value="<?php echo $row['id']; ?>" onclick="change_package(<?php echo $row['id']; ?>, this.form)"><?php echo $row['name']; ?></option>
						<?php
						}
					}
				}
				
				?>
            </select>
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <?php
	if ($user['package'] == 1)
	{
		// User is an admin.
		$query = mysql_query("SELECT * FROM `modules` WHERE `active` = '1'");
	}
	else
	{
		// User is not an admin.
		$query = mysql_query("SELECT * FROM `modules` WHERE `active` = '1' AND `package` = '0'");
	}
	$m = 0;
	while ($row = mysql_fetch_array($query))
	{
		if (isset($module_on))
		{
			$defaults = $module_on;
		}
		else if ($row_default['modules'] != '0')
		{
			$defaults = explode(',', $row_default['modules']);
		}
		
		if ($m == 0)
		{
			$style = " style=\"background-color:#e1e1e1;\"";
			$m=1;
		}
		else if ($m == 1)
		{
			$style = " style=\"background-color:#f1f1f1;\"";
			$m=0;
		}
		?>
	<tr<?php echo $style; ?>>
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	<?php echo $row['name']; ?>
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="checkbox" name="<?php echo $row['id']; ?>" onchange="noPackage(this.form)" <?php if (isset($defaults)) { if (isset($defaults[$row['id']])) { ?>checked="checked"<?php } } else if (!isset($inputPackage)) { ?>checked="checked"<?php } ?> />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
        <?php
	}
	?>
    <tr style="background-color:#f1f1f1;">
    	<td colspan="3" align="center" style="vertical-align:center;">
        	<input type="submit" name="btnSubmit" value="Create!" />
        </td>
    </tr>
</table>
</form>
<br />
<div align="center"><a href="index.php?module=usermanagement">Go Back</a></div>
<?php
}
?>