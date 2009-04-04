<?php
require('settings.php'); // Include settings.

if ($setting['live'] != true)
{
	// System is not live.
	echo "Software is not live, as per configuration in settings.php";
	exit;
}

require_once('includes/functions/handle_error.inc.php'); // Include error handling function.
require_once('includes/functions/check_referrer.inc.php'); // Inlclude referrer checking function.

if ($db['type'] == 'mysql')
{
	// Database being used is MySQL, lets make the connection.
	mysql_connect($db['host'], $db['user'], $db['pass']) or die(handle_error(mysql_error(), 'MySQL')); // Connect to server.
	mysql_select_db($db['name']) or die(handle_error(mysql_error(), 'MySQL')); // Select the database, to work with.
	
	$query = mysql_query("SELECT * FROM `settings`") or die(handle_error(mysql_error(), 'MySQL')); // Query the database for settings.
	while ($row = mysql_fetch_array($query))
	{
	
		$setting[$row['name']] = $row['value']; // Load settings from database into $setting variable.
	}
}
else
{
	handle_error('Un-supported database type, as defined in settings.php', 'Config');
	echo 'Please see logs/katrina_error_log.log, for more details';
	exit;
}

require_once('includes/core/session_handler.inc.php');

session_start(); // Start the session.

if (!isset($_SESSION['initiated']))
{
	session_regenerate_id();
	$_SESSION['initiated'] = true;
}

if (isset($_SESSION['HTTP_USER_AGENT']))
{
	if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT']))
	{
		// User has failed validation checks, present them with the login.
		session_destroy();
	}
}
else
{
	$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
}

if ($_SESSION['authenticated'] == true)
{
	$query = mysql_query("SELECT * FROM `users` WHERE `id` = '".mysql_real_escape_string($_SESSION['user_id'])."'") or die(handle_error(mysql_error(), 'MySQL')); // Query the database for user information.
	$user = mysql_fetch_array($query); // Load the $user variable with User Data.
	$userAllowtmp = explode(',', $user['allow']);
	foreach ($userAllowtmp as $key => $value)
	{
		$userAllow[$value] = true;
	}
	
	$userDenytmp = explode(',', $user['deny']);
	foreach ($userDenytmp as $key => $value)
	{
		$userDeny[$value] = true;
	}
	
	
				
	$query = mysql_query("SELECT * FROM `packages` WHERE `id` = '".$user['package']."'");
	$package = mysql_fetch_array($query);
	//require_once('includes/core/grab_stats.inc.php'); -- deprecated.
	// User is authenticated, load a page.
	if (isset($_GET['page']) || isset($_GET['module']))
	{
		$clean_vars = array('..', '/', '\\', '~', '.inc.php', '.php');
		
		if (isset($_GET['module']))
		{
			$query = mysql_query("SELECT * FROM `modules` WHERE `location` = '".mysql_real_escape_string(str_replace($clean_vars, '', $_GET['module']))."'") or die(handle_error(mysql_error(), 'MySQL'));
			if (mysql_num_rows($query) == 1)
			{
				// The module exists, within the database, check to see if hte user has permission to use the module.
				$module = mysql_fetch_array($query);
				
				if ($userAllow[$module['id']] == true)
				{
					// User does have permisson to use this module...Lets load it up.
					$fp = fopen('modules/'.str_replace($clean_vars, '', $_GET['module']).'/header.inc.php', 'r');
					$header_data = fread($fp, filesize('modules/'.str_replace($clean_vars, '', $_GET['module']).'/header.inc.php')+1);
					fclose($fp);
					
					require_once('themes/'.$setting['theme'].'/header.inc.php');
					if (isset($_GET['page']))
					{
						require_once('modules/'.str_replace($clean_vars, '', $_GET['module']).'/'.str_replace($clean_vars, '', $_GET['page']).'.inc.php');
					}
					else
					{
						require_once('modules/'.str_replace($clean_vars, '', $_GET['module']).'/index.inc.php');
					}
					require_once('themes/'.$setting['theme'].'/footer.inc.php');
				}
				else
				{
					// User does not have permisson to use this module.
					header('Location: index.php?page=home');
					exit;
				}
			}
			else
			{
				// Module being called does not exist in the database.
				echo "See logs/katrina_error_log.log for more information.";
				handle_error('Module '.str_replace($clean_vars, '', $_GET['module']).' does not exist in the database.', 'Module Initialization');
				exit;
			}
		}
		else
		{
			require_once('themes/'.$setting['theme'].'/header.inc.php');
			require_once('pages/'.str_replace($clean_vars, '', $_GET['page']).'.inc.php');
			require_once('themes/'.$setting['theme'].'/footer.inc.php');
		}
	}
	else
	{
		require_once('pages/login.inc.php');
	}
}
else
{
	// User has not been authenticated, display a login page.
	require_once('pages/login.inc.php');
}
?>