<?php
function handle_error($error, $type)
{
	$string = '\n'.date('g:i:sA F. j, Y').' --- '.$type.' --- '.$error;
	
	$fp = fopen('../logs/katrina_error_log.log', 'a');
	fwrite($fp, $string, strlen($string)+1);
	fclose($fp);
}
?>