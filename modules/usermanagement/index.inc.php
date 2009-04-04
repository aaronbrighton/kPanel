<p>
	<span style="font-weight:bold;">User Management</span>
</p>
<form method="post" action="index.php?module=usermanagement">
<table cellspacing="1" cellpadding="3" border="0" align="center" width="800">
	<tr>
    	<td width="10" align="center" style="vertical-align:middle;"><input type="checkbox" name="checkall" onclick="select_all(this)" /></td>
		<td width="400" align="center" style="vertical-align:middle;font-weight:bold;">Name</td>
        <td width="160" align="center" style="vertical-align:middle;font-weight:bold;">Username/Server Account</td>
        <td width="80"align="center" style="vertical-align:middle;font-weight:bold;">Package</td>
        <td width="80" align="center" style="vertical-align:middle;font-weight:bold;">Owner</td>
    </tr>
<?php
if ($user['package'] == 0)
{
	// Admin is calling this list.
	$query = mysql_query("SELECT * FROM `users`");
	$numrows = mysql_num_rows($query);
	if ($numrows >= 1)
	{
		// Good just checking.
		$i=0;
		$m=1;
		while ($row = mysql_fetch_array($query))
		{
			$owner = mysql_fetch_array(mysql_query("SELECT `name` FROM `users` WHERE `id` = '".$row['owner']."'"));
			$package = mysql_fetch_array(mysql_query("SELECT `name` FROM `packages` WHERE `id` = '".$row['package']."'"));
			$owner = $owner['username'];
			$package = $package['name'];
			
			// Background color
			if ($i == 0)
			{
				$backgroundCSS = " style=\"background-color:#f1f1f1;\"";
				$i = 1;
			}
			else
			{
				// $i = 1
				$backgroundCSS = "  style=\"background-color:#e1e1e1;\"";
				$i = 0;
			}
	?>
    <tr <?php echo $backgroundCSS; ?>>
   		<td width="10" align="center" style="vertical-align:middle;"><input type="checkbox" name="check<?php echo $m; ?>" id="<?php echo $row['id']; ?>" /></td>
    	<td align="left" style="vertical-align:middle;"><label for="chkbox"><?php echo $row['name']; ?></label></td>
        <td align="center" style="vertical-align:middle;"><?php echo $row['username']; ?>/<?php echo $row['server_account']; ?></td>
        <td align="center" style="vertical-align:middle;"><?php echo $package; ?></td>
        <td align="center" style="vertical-align:middle;"><?php echo $owner; ?></td>
    </tr>
	<?php
		$m++;
		}
	}
}
else
{
	// Admin is calling this list.
	$query = mysql_query("SELECT * FROM `users` WHERE `owner` = '".$user['id']."'");
	$numrows = mysql_num_rows($query);
	if ($numrows >= 1)
	{
		// Good just checking.
		$i=0;
		$m=1;
		while ($row = mysql_fetch_array($query))
		{
			$owner = mysql_fetch_array(mysql_query("SELECT `username` FROM `users` WHERE `id` = '".$row['owner']."'"));
			if ($row['package'] == 0)
			{
				$package = '-- None --';
			}
			else
			{
				$package = mysql_fetch_array(mysql_query("SELECT `name` FROM `packages` WHERE `id` = '".$row['package']."'"));
				$package = $package['name'];
			}
			
			$owner = $owner['username'];
			
			// Background color
			if ($i == 0)
			{
				$backgroundCSS = " style=\"background-color:#f1f1f1;\"";
				$i = 1;
			}
			else
			{
				// $i = 1
				$backgroundCSS = "  style=\"background-color:#e1e1e1;\"";
				$i = 0;
			}
	?>
    <tr <?php echo $backgroundCSS; ?>>
    	<td width="10" align="center" style="vertical-align:middle;"><input type="checkbox" name="check<?php echo $m; ?>" id="<?php echo $row['id']; ?>" /></td>
    	<td align="left" style="vertical-align:middle;"><?php echo $row['name']; ?></td>
        <td align="center" style="vertical-align:middle;"><?php echo $row['username']; ?>/<?php echo $row['server_account']; ?></td>
        <td align="center" style="vertical-align:middle;"><?php echo $package; ?></td>
        <td align="center" style="vertical-align:middle;"><?php echo $owner; ?></td>
    </tr>            
	<?php
		$m++;
		}
	}
	else
	{
	?>
    <tr>
        <td colspan="5" align="center">
  	    	You are not currently managing anyone's account.
        </td>
    </tr>
    <?php
	}
}
?>
</table>
<br />
<div align="center">
	<input type="hidden" name="whattodo" value="" />
	<input type="submit" value="Create New User" onclick="submit_form('delete', 'createnew', this.form)" /> <input type="submit" value="Delete" onclick="submit_form('delete', 'removeusers', this.form)" /> <input type="submit" value="Packages" onclick="submit_form('delete', 'packages', this.form)" />
</div>
</form>
<br />
<div align="center"><a href="index.php?page=home">Go Back</a></div>