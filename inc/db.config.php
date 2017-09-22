<?php

    if (!defined('_XISO_')) exit;

    $g5     = array();

    define('XISO_MYSQLI_USE', true);
    define('XISO_MYSQL_DB', 'lgeprm');
    // SQL 에러를 표시할 것인지 지정
	// 에러를 표시하려면 TRUE 로 변경
	define('XISO_DISPLAY_SQL_ERROR', FALSE);

    $connect_db = sql_connect("localhost", "xiso", "weather010!") or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db(XISO_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');

    // mysql connect resource $g5 배열에 저장 - 명랑폐인님 제안
    $g5['connect_db'] = $connect_db;

    sql_set_charset('utf8', $connect_db);

	/**
	 * Return the sequence value incremented by 1
	 * Auto_increment column only used in the sequence table
	 * @return int
	 */
	function _getNextSequence()
	{
		$query = "insert into `sequence` (seq) values ('0')";
		sql_query($query);
		$sequence = sql_insert_id();

		if($sequence % 10000 == 0)
		{
			$query = sprintf("delete from `sequence` where seq < %d", $sequence);
			sql_query($query);
		}

		return $sequence;
	}

	/**
	 * Alias of DB::getNextSequence()
	 *
	 * @see DB::getNextSequence()
	 * @return int
	 */
	function getNextSequence()
	{
		$seq = _getNextSequence();
		setUserSequence($seq);
		return $seq;
	}

	/**
	 * Set Sequence number to session
	 *
	 * @param int $seq sequence number
	 * @return void
	 */
	function setUserSequence($seq)
	{
		$arr_seq = array();
		if(isset($_SESSION['seq']))
		{
			$arr_seq = $_SESSION['seq'];
		}
		$arr_seq[] = $seq;
		$_SESSION['seq'] = $arr_seq;
	}

	/**
	 * Check Sequence number grant
	 *
	 * @param int $seq sequence number
	 * @return boolean
	 */
	function checkUserSequence($seq)
	{
		if(!isset($_SESSION['seq']))
		{
			return false;
		}
		if(!in_array($seq, $_SESSION['seq']))
		{
			return false;
		}

		return true;
	}

	/*************************************************************************
	**
	**  SQL 관련 함수 모음
	**
	*************************************************************************/

	// DB 연결
	function sql_connect($host, $user, $pass, $db=XISO_MYSQL_DB)
	{
	    global $g5;

	    if(function_exists('mysqli_connect') && XISO_MYSQLI_USE) {
	        $link = mysqli_connect($host, $user, $pass, $db);

	        // 연결 오류 발생 시 스크립트 종료
	        if (mysqli_connect_errno()) {
	            die('Connect Error: '.mysqli_connect_error());
	        }
	    } else {
	        $link = mysql_connect($host, $user, $pass);
	    }

	    return $link;
	}


	// DB 선택
	function sql_select_db($db, $connect)
	{
	    global $g5;

	    if(function_exists('mysqli_select_db') && XISO_MYSQLI_USE)
	        return @mysqli_select_db($connect, $db);
	    else
	        return @mysql_select_db($db, $connect);
	}


	function sql_set_charset($charset, $link=null)
	{
	    global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];

	    if(function_exists('mysqli_set_charset') && XISO_MYSQLI_USE)
	        mysqli_set_charset($link, $charset);
	    else
	        mysql_query(" set names {$charset} ", $link);
	}

	//sql_Query를 실행하여 list를 배열로만들어주는 함수.
    //페이징까지 한번에 처리 예정.
    //by xiso
    function sql_query_array($sql, $error=XISO_DISPLAY_SQL_ERROR, $link=null){
        $result = sql_query($sql);

        $output = new Object();
        $output->data = array();
        while($row = sql_fetch_object($result)){
            $output->data[] = $row;
        }

        return $output;
    }

	// mysqli_query 와 mysqli_error 를 한꺼번에 처리
	// mysql connect resource 지정 - 명랑폐인님 제안
	function sql_query($sql, $error=XISO_DISPLAY_SQL_ERROR, $link=null)
	{
	    global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];

	    // Blind SQL Injection 취약점 해결
	    $sql = trim($sql);
	    // union의 사용을 허락하지 않습니다.
	    //$sql = preg_replace("#^select.*from.*union.*#i", "select 1", $sql);
	    $sql = preg_replace("#^select.*from.*[\s\(]+union[\s\)]+.*#i ", "select 1", $sql);
	    // `information_schema` DB로의 접근을 허락하지 않습니다.
	    $sql = preg_replace("#^select.*from.*where.*`?information_schema`?.*#i", "select 1", $sql);

	    if(function_exists('mysqli_query') && XISO_MYSQLI_USE) {
	        if ($error) {
	            $result = @mysqli_query($link, $sql) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
	        } else {
	            $result = @mysqli_query($link, $sql);
	        }
	    } else {
	        if ($error) {
	            $result = @mysql_query($sql, $link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
	        } else {
	            $result = @mysql_query($sql, $link);
	        }
	    }

	    return $result;
	}

	function sql_begin($link=null){
		global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];
	    
        @mysqli_begin_transaction($link);
	}

	function sql_commit($link=null){
		global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];

	    @mysqli_commit($link);
    }

    function sql_rollback($link=null){
		global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];

		@mysqli_rollback($link);
    }


	// 쿼리를 실행한 후 결과값에서 한행을 얻는다.
	function sql_fetch($sql, $error=XISO_DISPLAY_SQL_ERROR, $link=null)
	{
	    global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];

	    $result = sql_query($sql, $error, $link);
	    //$row = @sql_fetch_array($result) or die("<p>$sql<p>" . mysqli_errno() . " : " .  mysqli_error() . "<p>error file : $_SERVER['SCRIPT_NAME']");
	    $row = sql_fetch_array($result);
	    return $row;
	}


	// 결과값에서 한행 연관배열(이름으로)로 얻는다.
	function sql_fetch_array($result)
	{
	    if(function_exists('mysqli_fetch_assoc') && XISO_MYSQLI_USE)
	        $row = @mysqli_fetch_assoc($result);
	    else
	        $row = @mysql_fetch_assoc($result);

	    return $row;
	}

	// added by overcode
	function sql_fetch_object($result)
	{
		if(function_exists('mysqli_fetch_object') && XISO_MYSQLI_USE)
	        $row = @mysqli_fetch_object($result);
	    else
	        $row = @mysql_fetch_object($result);

	    return $row;
	}


	// $result에 대한 메모리(memory)에 있는 내용을 모두 제거한다.
	// sql_free_result()는 결과로부터 얻은 질의 값이 커서 많은 메모리를 사용할 염려가 있을 때 사용된다.
	// 단, 결과 값은 스크립트(script) 실행부가 종료되면서 메모리에서 자동적으로 지워진다.
	function sql_free_result($result)
	{
	    if(function_exists('mysqli_free_result') && XISO_MYSQLI_USE)
	        return mysqli_free_result($result);
	    else
	        return mysql_free_result($result);
	}


	function sql_password($value)
	{
	    // mysql 4.0x 이하 버전에서는 password() 함수의 결과가 16bytes
	    // mysql 4.1x 이상 버전에서는 password() 함수의 결과가 41bytes
	    $row = sql_fetch(" select password('$value') as pass ");

	    return $row['pass'];
	}


	function sql_insert_id($link=null)
	{
	    global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];

	    if(function_exists('mysqli_insert_id') && XISO_MYSQLI_USE)
	        return mysqli_insert_id($link);
	    else
	        return mysql_insert_id($link);
	}


	function sql_num_rows($result)
	{
	    if(function_exists('mysqli_num_rows') && XISO_MYSQLI_USE)
	        return mysqli_num_rows($result);
	    else
	        return mysql_num_rows($result);
	}


	function sql_field_names($table, $link=null)
	{
	    global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];

	    $columns = array();

	    $sql = " select * from `$table` limit 1 ";
	    $result = sql_query($sql, $link);

	    if(function_exists('mysqli_fetch_field') && XISO_MYSQLI_USE) {
	        while($field = mysqli_fetch_field($result)) {
	            $columns[] = $field->name;
	        }
	    } else {
	        $i = 0;
	        $cnt = mysql_num_fields($result);
	        while($i < $cnt) {
	            $field = mysql_fetch_field($result, $i);
	            $columns[] = $field->name;
	            $i++;
	        }
	    }

	    return $columns;
	}


	function sql_error_info($link=null)
	{
	    global $g5;

	    if(!$link)
	        $link = $g5['connect_db'];

	    if(function_exists('mysqli_error') && XISO_MYSQLI_USE) {
	        return mysqli_errno($link) . ' : ' . mysqli_error($link);
	    } else {
	        return mysql_errno($link) . ' : ' . mysql_error($link);
	    }
	}
?>