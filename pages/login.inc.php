<?php
if (isset($_GET['logout']) && !isset($_POST['btnSubmit']))
{
	// User wishes to logout.
	session_destroy();
	$session_destroyed = true;
}

if ($_SESSION['authenticated'] == true)
{
	// User is already logged in, redirect to home page.
	header('Location: index.php?page=home');
	exit;
}

if (isset($_POST['btnSubmit']))
{
	// Form has been submitted.
		
	$inputUsername = mysql_real_escape_string($_POST['txtUsername']);
	$inputPassword = sha1($_POST['txtPassword']);
	$query = mysql_query("SELECT * FROM `users` WHERE `username` = '".$inputUsername."' AND `password` = '".$inputPassword."'") or die(handle_error(mysql_error(), 'MySQL'));
		
	if (mysql_num_rows($query) == 1)
	{
		// User has provided valid login data.
		$result = mysql_fetch_array($query);
		$_SESSION['authenticated'] = true;
		$_SESSION['user_id'] = $result['id'];
		$_SESSION['load_user_details'] = true;
		
		$login_success = true;
	}
	else
	{
		// Username or Password is incorrect.
		$login_error = 1;
			
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login | Katrina Panel</title>
</head>
<body>
	<table cellspacing="0" cellpadding="0" align="center" width="424" style="border-width:1px;border-style:solid;border-color:#b6b6b6;">
    	<tr>
        	<td width="424" height="30" style="background-image:url('themes/default/images/login-header.gif');"></td>
        </tr>
        <tr>
        	<td width="424" height="100" style="background-image:url('themes/default/images/login-banner.gif');"></td>
        </tr>
        <tr>
        	<td width="424" height="252" align="center" style="vertical-align:middle;">
            	<?php
				if ($session_destroyed == true)
				{
				?>
                <div align="center" style="border-width:1px;border-style:solid;border-color:#06ff00;background-color:#a2ff98;padding:7px;margin:7px;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:12px;">You have been logged out!</div>
                <?php
				}
				else if ($login_error == 1)
				{
				?>
                <div align="center" style="border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;padding:7px;margin:7px;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:12px;">The username or password your provided is incorrect.</div>
                <?php
				}
				else if ($login_error == 2)
				{
				?>
				<div align="center" style="border-width:1px;border-style:solid;border-color:#ff0000;background-color:#ff9393;padding:7px;margin:7px;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:12px;">You have reached your max attempts at login, please wait approximately <?php echo intval($setting['attempts_exceeded_wait_time'])*60; ?></div>
				<?php
				}
				else if (isset($login_success))
				{
				?>
                <div align="center" style="border-width:1px;border-style:solid;border-color:#06ff00;background-color:#a2ff98;padding:7px;margin:7px;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:12px;">You have successfully logged in!  Loading Please Wait...
                <?php
					if (isset($_GET['page']) && $_GET['page'] != 'login')
					{
                		echo "<meta http-equiv='refresh' content='3;url=index.php?page=".$_GET['page']."'>";
					}
					else
					{
						echo "<meta http-equiv='refresh' content='3;url=index.php?page=home'>";
					}
				?>
                </div>
                <?php
				}
				?>
            	<form method="post" action="">
            	<table cellspacing="0" cellpadding="13" align="center" width="325">
                	<tr>
                    	<td align="left" style="vertical-align:middle;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:10px;font-weight:bold;">
                        	Login
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                    	<td align="left" style="vertical-align:middle;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:11px;font-weight:bold;border-top-width:1px;border-top-style:solid;border-top-color:#b6b6b6;">
                        	Username
                        </td>
                        <td align="center" style="vertical-align:middle;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:10px;font-weight:bold;border-top-width:1px;border-top-style:solid;border-top-color:#b6b6b6;">
                        	<input type="text" name="txtUsername" size="20" />
                        </td>
                    </tr>
                    <tr>
                    	<td align="left" style="vertical-align:middle;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:11px;font-weight:bold;border-top-width:1px;border-top-style:solid;border-top-color:#b6b6b6;background-color:#e7e7e7;">
                        	Password
                        </td>
                        <td align="center" style="vertical-align:middle;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:10px;font-weight:bold;border-top-width:1px;border-top-style:solid;border-top-color:#b6b6b6;background-color:#e7e7e7;">
                        	<input type="password" name="txtPassword" size="20" />
                        </td>
                    </tr>
                    <tr>
                    	<td colspan="2" align="center" style="vertical-align:middle;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:10px;font-weight:bold;border-top-width:1px;border-top-style:solid;border-top-color:#b6b6b6;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#b6b6b6;">
                        	<input type="submit" name="btnSubmit" value="Login!" style="display:block;border-width:1px;border-style:solid;border-color:#888888;background-image:url('themes/default/images/login-button-bg.gif');background-repeat:repeat-x;height:27px;" />
                        </td>
                    </tr>
                </table>
                </form>
            </td>
        </tr>
    </table>
</body>
</html>
