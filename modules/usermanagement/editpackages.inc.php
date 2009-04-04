<?php
if ($_POST['whattodo'] == 'edit')
{
	if (isset($_POST['btnSubmit']))
	{
		// Edit form has been submitted.
		
		// Collect Vars //
		$inputName = $_POST['txtName'];
		$inputStorage = $_POST['txtStorage'];
		$inputDatabases = $_POST['txtDatabases'];
		
		foreach ($_POST as $key => $value)
		{		
			if (is_int($key) && $key != 'txtDatabases' && $key != 'txtStorage' && $key != 'txtName' && $key != 'btnSubmit')
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
		
		// Run Checks //
		$query = mysql_query("SELECT * FROM `packages` WHERE `name` = '".mysql_real_escape_string($inputName)."'");
	
		if (mysql_num_rows($query) == 0)
		{
			// Package with this name already exists.
			$errorString = $errorString . '<br />This package doesnt exist!';
		}
		
		if ($inputName == 'none')
		{
			$errorString = $errorString . '<br />You cannot use \'none\' as a package name.';
		}
		
		if(eregi('#^[A-Z0-9 ]+$#i', $inputName))
		{
			// The name contains an invalid character.
			$errorString = $errorString . '<br />The Name entered has characters that are not alpha-numerical.';
		}
		
		if(eregi('[^0-9_]', $inputStorage))
		{
			// The storage field, was submitted with character others then 0-9.
			$errorString = $errorString . '<br />That is not a valid storage amount.';
		}
		
		if(eregi('[^0-9_]', $inputDatabases))
		{
			// The storage field, was submitted with character others then 0-9.
			$errorString = $errorString . '<br />That is not a valid amount of databases.';
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
	
		// Make the changes if no errors occured, in the above checks. //
		
		if (!isset($errorString))
		{
			if (isset($module_on))
			{
				foreach ($module_on as $key => $value)
				{
					$modules = $modules . $key . ',';
				}
			}
			
			// No errors occured, lets go ahead and add it.
			$query = mysql_query("UPDATE `packages` SET `name` = ".mysql_real_escape_string($inputName)." AND `modules` = ".mysql_real_escape_string($modules)." AND `space` = '".$inputStorage."' AND `databases` = '".$inputDatabases."'");
			
			if ($query)
			{
				$success = true;
			}
			else
			{
				$errorString = "An error occured while inserting the new package into the database, '".mysql_error()."'";
			}
			
		}	
	}
	
	if ($success == true)
	{
		?>
        <p>
            <span style="font-weight:bold;">Create a Package</span>
        </p>
        <div align="center" style="padding:7px;border-width:1px;border-style:solid;border-color:#06ff00;background-color:#a2ff98;">
            Package has been successfully modified!  Redirecting...
            <meta http-equiv="refresh" content="3;URL=index.php?module=usermanagement&page=packages"> 
        </div>
        <br />
        <?php
	}
	else
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
?>
<p>
	<span style="font-weight:bold;">Edit Package</span>
</p>
	<?php
	$i=0;
    foreach ($_POST as $key => $value)
    {
        if (is_int($key))
        {
			// Look for the check boxes.
			if ($value == 'on')
			{
				// Check box selected.
				$package = $key;
            	$selected = true;
				$i++;
        	}
		}
    }
	
	if ($selected != true)
	{
		// No package was selected.
		$errorString = $errorString. "<br />You must select a package to edit.";
		$bad = true;
	}
	else if ($i > 1)
	{
		$errorString = $errorString . "<br />You can only edit one package at a time.";
		$bad = true;
	}
	if ($user['package'] != 1 && !isset($errorString))
	{
		// User is not an admin
		if (mysql_num_rows(mysql_query("SELECT * FROM `packages` WHERE `id` = '".$package."' AND `owner` = '".$user['id']."'")) == 0)
		{
			// Package is one this user does not own.
			$errorString = $errorString . "<br />You do not have permission to edit this package.";
			$bad = true;
		}
	}
    ?>
	<?php
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
	
	if ($bad != true)
	{
		$query = mysql_query("SELECT * FROM `packages` WHERE `id` = '".$package."'");
		$row = mysql_fetch_array($query);
    ?>
<form method="post" action="" onsubmit="return fixUpForSubmit(this);">
<table cellspacing="1" cellpadding="5" align="center" width="400">
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Name
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtName" value="<?php if (isset($errorString)) { echo $inputName; } else { echo $row['name']; } ?>" size="30" maxlength="63" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Storage Space
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtStorage" maxlength="63" value="<?php if (isset($errorString)) { echo $inputStorage; } else { echo $row['space']; } ?>" size="5" maxlength="63" style="text-align:right;" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#f1f1f1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	# of Databases
            <br />(max 9999)
        </td>
        <td align="right" style="vertical-align:middle;">
        	<input type="text" name="txtDatabases" value="<?php if (isset($errorString)) { echo $inputDatabases; } else { echo $row['databases']; } ?>" size="5" maxlength="4" style="text-align:right;" />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <tr style="background-color:#e1e1e1;">
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	Modules
        </td>
        <td align="center" style="vertical-align:middle;">
        	<input type="checkbox" name="checkall" onclick="select_all(this)"<?php if ($_POST['checkall'] == 'on') { ?> checked="checked"<?php } ?> />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
    <?php
	if ($user['package'] == 1)
	{
		// User is an admin.
		$query_package = mysql_query("SELECT * FROM `packages` WHERE `id` = '".$package."'");

	}
	else
	{
		// User is not an admin.
		$query_package = mysql_query("SELECT * FROM `packages` WHERE `id` = '".$package."' AND `owner` = '".$user['id']."'");
	}
	$row_package = mysql_fetch_array($query);
	$modules = explode(',', $row['modules']);
	foreach ($modules as $key => $value)
	{
		// Flip $modules inside out.
		$modules_flipped[$value] = true;
	}
	
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
	
	$i=1;
	$m = 0;
	while ($row = mysql_fetch_array($query))
	{
		if ($row_default['modules'] != '0')
		{
			$defaults = explode(',', $row_default['modules']);
		}
		
		if ($m == 0)
		{
			$style = " style=\"background-color:#f1f1f1;\"";
			$m=1;
		}
		else if ($m == 1)
		{
			$style = " style=\"background-color:#e1e1e1;\"";
			$m=0;
		}
		?>
	<tr<?php echo $style; ?>>
    	<td align="left" style="vertical-align:middle;font-weight:bold;">
        	<?php echo $row['name']; ?>
        </td>
        <td align="center" style="vertical-align:middle;">
        	<input type="checkbox" name="check<?php echo $i; ?>" id="<?php echo $row['id']; ?>"<?php if ($module_on[$row['id']] == 'on') { ?> checked="checked"<?php } else if ($modules_flipped[$row['id']] == true) { ?> checked="checked"<?php } ?> />
        </td>
        <td width="21" height="21" align="left" style="vertical-align:middle;">
        	<a href=""><img src="modules/usermanagement/images/help.png" width="16" height="16" border="0" alt="Help" title="More about this..."></a>
        </td>
    </tr>
        <?php
		$i++;
	}
	?>
    <tr style="background-color:#f1f1f1;">
    	<td colspan="3" align="center" style="vertical-align:middle;">
        	<input type="submit" name="btnSubmit" value="Create">
        </td>
    </tr>
</table>
</form>
<br />

<?php
	} // Else Success
	}
	?>
    <div align="center"><a href="index.php?module=usermanagement&page=packages">Go Back</a></div>
	<br>
    <?php
}
else
{
	// This page was reached incorrectly... Redirect back to package view.
	echo "<META http-equiv='refresh' content='0;URL=index.php?module=usermanagement&page=packages'>";
	exit;
}
?>