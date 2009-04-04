<?php
// RAM Usage
$ramUsage = shell_exec("/usr/bin/sudo free -b -m");
preg_match("/Mem:(\s+)(\d+)(\s+)(\d+)/", $ramUsage, $matches);
$ramTotal = $matches[2];
$ramUsage = $matches[4];

// Processor Average
$processorAverage = shell_exec("/usr/bin/sudo uptime");
if (preg_match("/\s(.*)\sup\s(.*),\s\s(.*),\s\sload\saverage:\s(.*)/", $processorAverage, $matches))
{
	$uptime = $matches[2];
	$processorAverage = $matches[4];
}
else
{
	preg_match("/\s(.*)\sup\s(.*),\s\s(.*),\s\s(.*),\s\sload\saverage:\s(.*)/", $processorAverage, $matches);
	$uptime = $matches[2];
	$processorAverage = $matches[5];
}
// MySQL Status
$mysqlStatus = shell_exec("/usr/bin/sudo telnet localhost 3306");
if (preg_match("/Connected\sto\s(.*)/", $mysqlStatus))
{
	$mysqlStatus = true;
}
else
{
	$mysqlStatus = false;
}

// Telnet Status
$ftpStatus = shell_exec("/usr/bin/sudo telnet localhost 21");
if (preg_match("/Connected\sto\s(.*)/", $ftpStatus))
{
	$ftpStatus = true;
}
else
{
	$ftpStatus = false;
}
?>
<p>
	<span style="font-weight:bold;text-decoration:underline;">Server Status</span>
</p>
<p>
	<table cellspacing="1" cellpadding="5" border="0" align="center" width="600">
    	<tr style="background-color:#f1f1f1;">
        	<td width="65%" align="left" style="vertical-align:middle;font-weight:bold;">Service</td>
            <td width="25%" align="center" style="vertical-align:middle;font-weight:bold;">Load</td>
            <td width="10%" align="center" style="vertical-align:middle;font-weight:bold;">Status</td>
        </tr>
        <tr style="background-color:#e1e1e1;">
        	<td style="vertical-align:middle;font-weight:bold;">Server Uptime</td>
            <td align="center" style="vertical-align:middle;font-weight:bold;"><?php echo $uptime; ?></td>
            <td align="center" style="vertical-align:middle;font-weight:bold;"><img src="themes/<?php echo $setting['theme']; ?>/images/check.png" alt="Ok" title="Ok"></td>
        </tr>
        <tr style="background-color:#f1f1f1;">
        	<td style="vertical-align:middle;font-weight:bold;">Server Load Averages</td>
            <td align="center" style="vertical-align:middle;font-weight:bold;"><?php echo $processorAverage; ?></td>
            <td align="center" style="vertical-align:middle;font-weight:bold;"><img src="themes/<?php echo $setting['theme']; ?>/images/check.png" alt="Ok" title="Ok"></td>
        </tr>
        <tr style="background-color:#e1e1e1;">
        	<td style="vertical-align:middle;font-weight:bold;">RAM Usage</td>
            <td  align="center"style="vertical-align:middle;font-weight:bold;"><?php echo $ramUsage ?>/<?php echo $ramTotal ?>MB</td>
            <td align="center" style="vertical-align:middle;font-weight:bold;"><img src="themes/<?php echo $setting['theme']; ?>/images/check.png" alt="Ok" title="Ok"></td>
        </tr>
        <tr style="background-color:#f1f1f1;">
        	<td style="vertical-align:middle;font-weight:bold;">Apache (HTTP)</td>
            <td  align="center"style="vertical-align:middle;font-weight:bold;"> -- </td>
            <td align="center" style="vertical-align:middle;font-weight:bold;"><img src="themes/<?php echo $setting['theme']; ?>/images/check.png" alt="Ok" title="Ok"></td>
        </tr>
        <tr style="background-color:#e1e1e1;">
        	<td style="vertical-align:middle;font-weight:bold;">MySQL (Database)</td>
            <td  align="center"style="vertical-align:middle;font-weight:bold;"> -- </td>
            <td align="center" style="vertical-align:middle;font-weight:bold;">
            	<?php 
				if ($mysqlStatus == true)
				{
				?>
				<img src="themes/<?php echo $setting['theme']; ?>/images/check.png" alt="Ok" title="Ok">
                <?php
				}
				else
				{
				?>
                <img src="themes/<?php echo $setting['theme']; ?>/images/close.png" alt="Bad" title="Bad">
                <?php
				}
				?>
            </td>
        </tr>
        <tr style="background-color:#f1f1f1;">
        	<td style="vertical-align:middle;font-weight:bold;">ProFTPd (FTP)</td>
            <td  align="center"style="vertical-align:middle;font-weight:bold;"> -- </td>
            <td align="center" style="vertical-align:middle;font-weight:bold;">
           		<?php 
				if ($ftpStatus == true)
				{
				?>
				<img src="themes/<?php echo $setting['theme']; ?>/images/check.png" alt="Ok" title="Ok">
                <?php
				}
				else
				{
				?>
                <img src="themes/<?php echo $setting['theme']; ?>/images/close.png" alt="Bad" title="Bad">
                <?php
				}
				?>
            </td>
        </tr>
    </table>
</p>
<br />
<div align="center"><a href="index.php?page=home">Go Back</a></div>