<?php
// Determine users disk space usage.
$systemQuota = shell_exec("/usr/bin/sudo repquota -u /");
preg_match("/".$user['server_account']."(.*)(--|\+-)(\s+)(\d+)(\s+)(\d+)/", $systemQuota, $matches);
$storageUsed = $matches['4'] / 1024;
$storageTotal = $matches['6'] / 1024;
$storagePercent = ($storageUsed/$storageTotal)*100;

// Calculate disk space usage including, accounts owned by this user (Account Creator only)
if ($user['package'] == 2)
{
	$query = mysql_query("SELECT * FROM `users` WHERE `owner` = '".$user['id']."'");
	if (mysql_num_rows($query) > 0)
	{
		// This user owns one or more other users.
		while ($row = mysql_fetch_array($query))
		{
			preg_match("/".$row['server_account']."(.*)(--|\+-)(\s+)(\d+)(\s+)(\d+)/", $systemQuota, $matches);
			$actualStorageUsed  = $actualStorageUsed + $matches['6'];
			$actualStorageTotal = $actualStorageTotal + $matches['6'];
		}
		
	}
	$actualStorageUsed = ($actualStorageUsed + ($storageUsed*1024)) / 1024;
	$actualStorageTotal = $storageTotal;
	$actualStoragePercent = ($actualStorageUsed/$actualStorageTotal)*100;
}

// Databases //
$query_user_package = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '".$user['package']."'"));
if ($query_user_package['account_creator'] == 1)
{
	// User is an account creator
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
	
	$databasePercent = $total_db_count/$self_databases;
}
else
{
	// Just a normal user.
	$databasePercent = $user['num_databases']/$user['max_databases'];
}
	


// Database Percentage.


// Apache Version
$apacheVersion = shell_exec("/usr/bin/sudo /usr/local/apache2/bin/httpd -v");
preg_match("/Server\sversion:\sApache\/(.*)/", $apacheVersion, $matches);
$apacheVersion = $matches[1];

// MySQL Version
$mysqlVersion = shell_exec("/usr/bin/mysql -V");
preg_match("/Ver\s(.*)\sDistrib\s(.*),/", $mysqlVersion, $matches);
$mysqlVersion = $matches[2];
?>
<br />
                <br />
            	<table cellspacing="0" cellpadding="0" align="center" width="1014">
                	<tr>
                    	<td width="25%" align="center" style="vertical-align:top;">
                        	<!-- Stats [Start] -->
                            <table cellspacing="0" cellpadding="0" align="center" style="border-width:1px;border-style:solid;border-color:#b6b6b6;">
                            	<tr>
                                	<td width="226" height="30">
                                    	<img src="themes/<?php echo $setting['theme']; ?>/images/statistics-header.gif" alt="Statistics" title="Statistics" />
                                    </td>
                                </tr>
                                <tr>
                                	<td align="center" width="226" height="32" style="vertical-align:middle;background-color::#ededed;">
                                    	<a href="http://<?php echo $setting['server_ip']; ?>/~<?php echo $user['server_account']; ?>/" target="_blank" style="font-weight:bold;">Live Site</a>
                                    </td>
                                </tr>
                                <?php if ($user['package'] == 2) { ?>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#FFFFFF;">
                                    	<!-- Storage [Start] -->
                                        <table cellspacing="0" cellpadding="0" align="center">
                                        	<tr>
                                            	<td width="88" align="center" style="vertical-align:middle;" class="stat-box">
                                                	Total Storage<br />
                                                    <span style="font-weight:bold;"><?php echo intval($actualStorageUsed); ?>/<?php echo $actualStorageTotal; ?>MB</span>
                                                </td>
                                                <td align="center" style="vertical-align:middle;padding:5px;">
                                                	<table cellspacing="0" cellpadding="0" align="center" width="128" style="border-width:1px;border-style:solid;border-color:#7d7d7d;">
                                                    	<tr>
                                                        	<td width="<?php echo $actualStoragePercent; ?>%" height="20" style="background-image:url('themes/<?php echo $setting['theme']; ?>/images/<?php if ($actualStoragePercent > 100) { ?>loading-bar-bg-red.gif<?php } else { ?>loading-bar-bg.gif<?php } ?> ');background-color:#07a5ff;"></td>
                                                            <td width="<?php echo 100-$actualStoragePercent; ?>%" height="20" style="background-color:#FFFFFF;"></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Storage [End] -->
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#ededed;">
                                    	<!-- Storage [Start] -->
                                        <table cellspacing="0" cellpadding="0" align="center">
                                        	<tr>
                                            	<td width="88" align="center" style="vertical-align:middle;" class="stat-box">
                                                	Storage<br />
                                                    <span style="font-weight:bold;"><?php echo intval($storageUsed); ?>/<?php echo $storageTotal; ?>MB</span>
                                                </td>
                                                <td align="center" style="vertical-align:middle;padding:5px;">
                                                	<table cellspacing="0" cellpadding="0" align="center" width="128" style="border-width:1px;border-style:solid;border-color:#7d7d7d;">
                                                    	<tr>
                                                        	<td width="<?php echo $storagePercent; ?>%" height="20" style="background-image:url('themes/<?php echo $setting['theme']; ?>/images/<?php if ($storagePercent > 100) { ?>loading-bar-bg-red.gif<?php } else { ?>loading-bar-bg.gif<?php } ?> ');background-color:#07a5ff;"></td>
                                                            <td width="<?php echo 100-$storagePercent; ?>%" height="20" style="background-color:#FFFFFF;"></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Storage [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#FFFFFF;">
                                    	<!-- Databases [Start] -->
                                        <table cellspacing="0" cellpadding="0" align="center">
                                        	<tr>
                                            	<td width="88" align="center" style="vertical-align:middle;" class="stat-box">
                                                	Databases<br />
                                                    <span style="font-weight:bold;"><?php if ($query_user_package['account_creator'] == 1) { echo $total_db_count; ?>/<?php echo $self_databases; } else { echo $user['num_databases']; ?>/<?php echo $user['max_databases']; } ?></span>
                                                </td>
                                                <td align="center" style="vertical-align:middle;padding:5px;">
                                                	<table cellspacing="0" cellpadding="0" align="center" width="128" style="border-width:1px;border-style:solid;border-color:#7d7d7d;">
                                                    	<tr>
                                                        	<td width="<?php echo $databasePercent; ?>%" height="20" style="background-image:url('themes/<?php echo $setting['theme']; ?>/images/loading-bar-bg.gif');background-color:#07a5ff;"></td>
                                                            <td width="<?php echo 100-$databasePercent; ?>%" height="20" style="background-color:#FFFFFF;"></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Databases [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#ededed;">
                                    	<!-- User Account [Start] -->
                                        <table cellspacing="0" cellpadding="5" align="center" width="226">
                                        	<tr>
                                            	<td width="50%" align="left" style="vertical-align:middle;" class="stat-box">
                                                	<span style="font-weight:bold;">Server Account</span>
                                                </td>
                                                <td width="50%" align="left" style="vertical-align:middle;">
                                                	<?php echo $user['server_account']; ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- User Account [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#FFFFFF;">
                                    	<!-- Package [Start] -->
                                        <table cellspacing="0" cellpadding="5" align="center" width="226">
                                        	<tr>
                                            	<td width="50%" align="left" style="vertical-align:middle;" class="stat-box">
                                                	<span style="font-weight:bold;">Package</span>
                                                </td>
                                                <td width="50%" align="left" style="vertical-align:middle;">
                                                	<?php echo $package['name']; ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Package [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#ededed;">
                                    	<!-- Panel Version [Start] -->
                                        <table cellspacing="0" cellpadding="5" align="center" width="226">
                                        	<tr>
                                            	<td width="50%" align="left" style="vertical-align:middle;" class="stat-box">
                                                	<span style="font-weight:bold;">Panel Version</span>
                                                </td>
                                                <td width="50%" align="left" style="vertical-align:middle;">
                                                	<?php echo $setting['version']; ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Panel Version [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#FFFFFF;">
                                    	<!-- Apache Version [Start] -->
                                        <table cellspacing="0" cellpadding="5" align="center" width="226">
                                        	<tr>
                                            	<td width="50%" align="left" style="vertical-align:middle;" class="stat-box">
                                                	<span style="font-weight:bold;">Apache Version</span>
                                                </td>
                                                <td width="50%" align="left" style="vertical-align:middle;">
                                                	<?php echo $apacheVersion; ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Apache Version [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#ededed;">
                                    	<!-- PHP Version [Start] -->
                                        <table cellspacing="0" cellpadding="5" align="center" width="226">
                                        	<tr>
                                            	<td width="50%" align="left" style="vertical-align:middle;" class="stat-box">
                                                	<span style="font-weight:bold;">PHP Version</span>
                                                </td>
                                                <td width="50%" align="left" style="vertical-align:middle;">
                                                	<?php echo phpversion(); ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- PHP Version [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#FFFFFF;">
                                    	<!-- MySQL Version [Start] -->
                                        <table cellspacing="0" cellpadding="5" align="center" width="226">
                                        	<tr>
                                            	<td width="50%" align="left" style="vertical-align:middle;" class="stat-box">
                                                	<span style="font-weight:bold;">MySQL Version</span>
                                                </td>
                                                <td width="50%" align="left" style="vertical-align:middle;">
                                                	<?php echo $mysqlVersion; ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- MySQL Version [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#ededed;">
                                    	<!-- Operating System [Start] -->
                                        <table cellspacing="0" cellpadding="5" align="center" width="226">
                                        	<tr>
                                            	<td width="50%" align="left" style="vertical-align:middle;" class="stat-box">
                                                	<span style="font-weight:bold;">Operating System</span>
                                                </td>
                                                <td width="50%" align="left" style="vertical-align:middle;">
                                                	Linux [Debian]
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Operating System [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#FFFFFF;">
                                    	<!-- Operating System [Start] -->
                                        <table cellspacing="0" cellpadding="5" align="center" width="226">
                                        	<tr>
                                            	<td width="50%" align="left" style="vertical-align:middle;" class="stat-box">
                                                	<span style="font-weight:bold;">Server Status</span>
                                                </td>
                                                <td width="50%" align="left" style="vertical-align:middle;">
                                                	<a href="index.php?page=status" style="font-weight:bold;">Click Here</a>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Operating System [End] -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" width="226" height="32" style="vertical-align:middle;background-color:#ededed;">
                                    	<!-- Operating System [Start] -->
                                        <a href="index.php?logout" style="font-weight:bold;">Sign Out</a>
                                        <!-- Operating System [End] -->
                                    </td>
                                </tr>
                            </table>
                            <!-- Stats [End] -->
                        </td>
                        <td width="75%" align="center" style="vertical-align:top;">
                        	<!-- Modules [Start] -->
                            <table cellspacing="0" cellpadding="0" align="center" width="725" style="border-width:1px;border-style:solid;border-color:#b6b6b6;">
                            	<tr>
                                	<td width="725" height="30">
                                    	<img src="themes/<?php echo $setting['theme']; ?>/images/modules-header.gif" alt="Modules" title="Modules" />
                                    </td>
                                </tr>
                                <tr>
                                	<td width="725">
                                    	<table cellspacing="0" cellpadding="10" align="center" width="725">
                                        	<tr>
                                            	<td width="20%" align="center" style="vertical-align:top;">
                                                    <a href="index.php?module=filemanager"><img src="themes/<?php echo $setting['theme']; ?>/images/filemanager.gif" border="0" alt="File Manager" title="File Manager" /></a><br /><br />
                                                    <a href="index.php?module=filemanager" style="font-weight:bold;">File Manager</a>
                                                </td>
                                                <td width="20%" align="center" style="vertical-align:top;">
                                                    <a href="index.php?module=backups"><img src="themes/<?php echo $setting['theme']; ?>/images/backups.gif" border="0" alt="Backups" title="Backups" /></a><br /><br />
                                                    <a href="index.php?module=backups" style="font-weight:bold;">Backups</a>
                                                </td>
                                                <td width="20%" align="center" style="vertical-align:top;">
                                                	<a href="index.php?module=passwordprotect"><img src="themes/<?php echo $setting['theme']; ?>/images/passwordprotect.gif" border="0" alt="Password Protected Directories" title="Password Protected Directories" /></a><br /><br />
                                                    <a href="index.php?module=passwordprotect" style="font-weight:bold;">Password Protected Directories</a>
                                                </td>
                                                <td width="20%" align="center" style="vertical-align:top;">
                                                	<a href="index.php?module=cronjobs"><img src="themes/<?php echo $setting['theme']; ?>/images/cronjobs.gif" border="0" alt="Cron Jobs" title="Cron Jobs" /></a><br /><br />
                                                    <a href="index.php?module=cronjobs" style="font-weight:bold;">Cron Jobs</a>
                                                </td>
                                                <td width="20%" align="center" style="vertical-align:top;">
                                                	<a href="index.php?module=errorpages"><img src="themes/<?php echo $setting['theme']; ?>/images/errorpages.gif" border="0" alt="Error Pages" title="Error Pages" /></a><br /><br />
                                                    <a href="index.php?module=errorpages" style="font-weight:bold;">Error Pages</a>
                                                </td>
                                            </tr>
                                            <tr>
                                            	<td width="20%" align="center" style="vertical-align:top;">
                                                    <a href="index.php?module=databasemanager"><img src="themes/<?php echo $setting['theme']; ?>/images/mysql.gif" border="0" alt="Database Management" title="Database Management" /></a><br /><br />
                                                    <a href="index.php?module=databasemanager" style="font-weight:bold;">Database Management</a>
                                                </td>
                                                <td width="20%" align="center" style="vertical-align:top;">
                                                    <a href="http://<?php echo $setting['server_ip'] ?>/~phpmyadmin"><img src="themes/<?php echo $setting['theme']; ?>/images/phpmyadmin.gif" border="0" alt="phpMyAdmin" title="phpMyAdmin" /></a><br /><br />
                                                    <a href="http://<?php echo $setting['server_ip'] ?>/~phpmyadmin" target="_blank" style="font-weight:bold;">phpMyAdmin</a>
                                                </td>
                                                <td width="20%" align="center" style="vertical-align:top;">
                                                	<a href="index.php?module=usermanagement"><img src="themes/<?php echo $setting['theme']; ?>/images/user_add.png" border="0" alt="Manage Users" title="Manage Users" /></a><br /><br />
                                                    <a href="index.php?module=usermanagement" style="font-weight:bold;">User Management</a>
                                                </td>
                                                <td width="20%" align="center" style="vertical-align:top;">
                                                	
                                                </td>
                                                <td width="20%" align="center" style="vertical-align:top;">
                                                	
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- Modules [End] -->
                        </td>
                    </tr>
                </table>