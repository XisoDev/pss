<?php

if (!defined('_XISO_')) exit;
function writeLog($log,$file){
    $log_txt = $log;
    $log_dir = "./files/logs/";
    FileHandler::makeDir($log_dir);
    $log_file = fopen($log_dir. $file . time() . ".txt", "a");
    fwrite($log_file, $log_txt."\r\n");
    fclose($log_file);
}

function setReturn($error,$message,$return_url = false){
    $_SESSION['_XISO_ERROR_'] = $error;
    $_SESSION['_XISO_MESSAGE_'] = $message;

    if(!$return_url){
        if($error < 0){
            $return_url = $_REQUEST['error_return_url'];
        }else{
            $return_url = $_REQUEST['success_return_url'];
        }

        if(!$return_url) $return_url = $_SERVER['HTTP_REFERER'];
    }

    header('Location: ' . $return_url);
    return true;
}

function getController($class_name){
    include_once "./modules/{$class_name}/controller.php";
    $class_name = $class_name."Controller";
    $object = new $class_name();
    $object->init();
    return $object;
}
function getView($class_name){
    include_once "./modules/{$class_name}/view.php";
    $class_name = $class_name."View";
    $object = new $class_name();
    $object->init();
    return $object;
}
function getModel($class_name){
    include_once "./modules/{$class_name}/model.php";
    $class_name = $class_name."Model";
    $object = new $class_name();
    $object->init();
    return $object;
}

function insertQuery($table_name, $args){
	$output = insertQueryString($table_name, $args);
    if(!$output->toBool()) return $output;

    $output->result = sql_query($output->sql);
    if(!$output->result){
        global $g5;
        $link = $g5['connect_db'];

        $output->error = -1;
        $output->message = mysqli_errno($link) . " : " .  mysqli_error($link);
    }
	return $output;
}

function insertQueryString($table_name, $args){
	if(!$table_name || !$args) return new Object(-1, "invalid_args");

	$array = array();

	foreach($args as $key => $val){
	    $array[$key] = addslashes($val);
    }

	// build query...
	$sql  = "INSERT INTO `{$table_name}`";

	// implode keys of $array...
	$sql .= " (`".implode("`, `", array_keys($array))."`)";

	// implode values of $array...
	$sql .= " VALUES ('".implode("', '", $array)."') ";

	$output = new Object();
	$output->sql = $sql;
	return $output;
}

function updateQueryString($table_name, $args){
    if(!$table_name || !$args) return new Object(-1, "invalid_args");

    $sql = "update `{$table_name}` set ";

    $set_text = array();
    foreach($args as $key => $val){
        $set_text[] = "`{$key}` = '". addslashes($val) ."'";
    }
    $sql .= implode(",", $set_text);

    return $sql;
}

/**
 * Get a time gap between server's timezone and XE's timezone
 *
 * @return int
 */
function zgap()
{
	$time_zone = '+0900';
	if($time_zone < 0)
	{
		$to = -1;
	}
	else
	{
		$to = 1;
	}

	$t_hour = substr($time_zone, 1, 2) * $to;
	$t_min = substr($time_zone, 3, 2) * $to;

	$server_time_zone = date("O");
	if($server_time_zone < 0)
	{
		$so = -1;
	}
	else
	{
		$so = 1;
	}

	$c_hour = substr($server_time_zone, 1, 2) * $so;
	$c_min = substr($server_time_zone, 3, 2) * $so;

	$g_min = $t_min - $c_min;
	$g_hour = $t_hour - $c_hour;

	$gap = $g_min * 60 + $g_hour * 60 * 60;
	return $gap;
}

/**
 * YYYYMMDDHHIISS format changed to unix time value
 *
 * @param string $str Time value in format of YYYYMMDDHHIISS
 * @return int
 */
function ztime($str)
{
	if(!$str)
	{
		return;
	}

	$hour = (int) substr($str, 8, 2);
	$min = (int) substr($str, 10, 2);
	$sec = (int) substr($str, 12, 2);
	$year = (int) substr($str, 0, 4);
	$month = (int) substr($str, 4, 2);
	$day = (int) substr($str, 6, 2);
	if(strlen($str) <= 8)
	{
		$gap = 0;
	}
	else
	{
		$gap = zgap();
	}

	return mktime($hour, $min, $sec, $month ? $month : 1, $day ? $day : 1, $year) + $gap;
}

/**
 * If the recent post within a day, output format of YmdHis is "min/hours ago from now". If not within a day, it return format string.
 *
 * @param string $date Time value in format of YYYYMMDDHHIISS
 * @param string $format If gap is within a day, returns this format.
 * @return string
 */
function getTimeGap($date, $format = 'Y.m.d')
{
	$gap = $_SERVER['REQUEST_TIME'] + zgap() - ztime($date);

	if($gap < 60)
	{
		$buff = sprintf("%d minute ago", (int) ($gap / 60) + 1);
	}
	elseif($gap < 60 * 60)
	{
		$buff = sprintf("%d minutes ago", (int) ($gap / 60) + 1);
	}
	elseif($gap < 60 * 60 * 2)
	{
		$buff = sprintf("%d hour ago", (int) ($gap / 60 / 60) + 1);
	}
	elseif($gap < 60 * 60 * 24)
	{
		$buff = sprintf("%d hours ago", (int) ($gap / 60 / 60) + 1);
	}
	else
	{
		$buff = zdate($date, $format);
	}

	return $buff;
}



/**
 * Name of the month return
 *
 * @param int $month Month
 * @param boot $short If set, returns short string
 * @return string
 */
function getMonthName($month, $short = TRUE)
{
	$short_month = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	$long_month = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	return !$short ? $long_month[$month] : $short_month[$month];
}

/**
 * Change the time format YYYYMMDDHHIISS to the user defined format
 *
 * @param string|int $str YYYYMMDDHHIISS format time values
 * @param string $format Time format of php date() function
 * @param bool $conversion Means whether to convert automatically according to the language
 * @return string
 */
function zdate($str, $format = 'M d, Y H:i:s')
{
	// return null if no target time is specified
	if(!$str)
	{
		return;
	}

	return date($format, strtotime( $str ) );
//
//	// If year value is less than 1970, handle it separately.
//	if((int) substr($str, 0, 4) < 1970)
//	{
//		$hour = (int) substr($str, 8, 2);
//		$min = (int) substr($str, 10, 2);
//		$sec = (int) substr($str, 12, 2);
//		$year = (int) substr($str, 0, 4);
//		$month = (int) substr($str, 4, 2);
//		$day = (int) substr($str, 6, 2);
//
//		$trans = array(
//			'Y' => $year,
//			'y' => sprintf('%02d', $year % 100),
//			'm' => sprintf('%02d', $month),
//			'n' => $month,
//			'd' => sprintf('%02d', $day),
//			'j' => $day,
//			'G' => $hour,
//			'H' => sprintf('%02d', $hour),
//			'g' => $hour % 12,
//			'h' => sprintf('%02d', $hour % 12),
//			'i' => sprintf('%02d', $min),
//			's' => sprintf('%02d', $sec),
//			'M' => getMonthName($month),
//			'F' => getMonthName($month, FALSE)
//		);
//
//		$string = strtr($format, $trans);
//	}
//	else
//	{
//		// if year value is greater than 1970, get unixtime by using ztime() for date() function's argument.
//		$string = date($format, ztime($str));
//	}
//	return $string;
}
?>