<?php
function check_referrer($file, $port)
{
	if ($_SERVER['HTTP_REFERER'] == 'http://'.$_SERVER['SERVER_NAME'].':'.$port.'/'.$file)
	{
		return true;
	}
	else
	{
		return false;
	}
}
?>