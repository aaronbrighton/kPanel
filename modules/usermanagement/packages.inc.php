<p>
	<span style="font-weight:bold;">Manage Packages</span>
</p>
<form method="post" action="index.php?module=usermanagement">
<table cellspacing="1" cellpadding="5" align="center" width="500">
	<tr>
    	<td width="10" align="center" style="vertical-align:middle;"><input type="checkbox" name="checkall" onclick="select_all(this)" /></td>
    	<td align="center" style="vertical-align:middle;font-weight:bold;">Name</td>
        <td align="center" style="vertical-align:middle;font-weight:bold;">Space</td>
        <td align="center" style="vertical-align:middle;font-weight:bold;">Databases</td>
    </tr>
    <?php
	if ($user['package'] == 1)
	{
		// This is an admin
		$query = mysql_query("SELECT * FROM `packages`");
	}
	else
	{
		// This is not an admin.
		$query = mysql_query("SELECT * FROM `packages` WHERE `id` > '2'");

		$i = 0;
		$m = 0;
		while ($row = mysql_fetch_array($query))
		{
			if ($row['owner'] == $user['id'] || $row['owner'] == 0)
			{
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
        <td align="center" style="vertical-align:middle;"><?php echo $row['space']; ?></td>
        <td align="center" style="vertical-align:middle;"><?php echo $row['databases']; ?></td>
	</tr>
				<?php
				$m++;
            }
		} 
	}
        
	?>
</table>
<br />
<div align="center">
	<input type="hidden" name="whattodo" value="">
    <input type="button" name="button" value="Create Package" onclick="submit_form('delete', 'createpackage', this.form)">
	<input type="button" name="button" value="Delete" onclick="submit_form('delete', 'removepackages', this.form)">
    <input type="button" name="button" value="Edit" onclick="submit_form('edit', 'editpackages', this.form)">
    
</div>
</form>
<br />
<div align="center"><a href="index.php?module=usermanagement">Go Back</a></div>