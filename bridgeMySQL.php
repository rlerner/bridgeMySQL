<?php
/*
		   _    _    _
		  / \  / \  / \
		 / S \/ Q \/ L \
		/    /\   /\    \
MySQL	================= Improved
	    |    |    |     |

---------------------------------------------
- bridgeMysql Package                       -
---------------------------------------------
- Bridging the gap between MySQL and MySQLi -
---------------------------------------------
- Maintainer: Robert Lerner                 -
-             http://www.robert-lerner.com  -
---------------------------------------------
- Version: 0.0.1-alpha1 / 2015-03-25        -
---------------------------------------------

Visit the project, update your version, and report bugs: http://www.bridgemysql.com/

*/

//Configuration -- May not work with some software packages.
$GLOBALS['bridgeMysql']['config'] = [
	'client_version_lie' => 50615

	];

if (!function_exists('mysql_connect'))
	{
	function mysql_affected_rows($link=null) {
		$link = mysql_bridge_last($link);
		if ($GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->errno!=0)
			return -1;
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->affected_rows;
		}

	function mysql_client_encoding() {
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->character_set_name;
		}

	function mysql_close($link=null) {
		$link = mysql_bridge_last($link);
		//If connection was created with pconnect, then ignore attempts to close connection during this session.
		if ($GLOBALS['bridgeMysql']['connection'][$link]['persistent'])
			return true;

		if (!isset($GLOBALS['bridgeMysql']['connection'][$link]))
			{
			trigger_error('--get error for closing non-existing link',E_USER_WARNING);
			return false;
			}
		//Destroy connection and all references.
		$GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->close();
		unset($GLOBALS['bridgeMysql']['connection'][$link]);
		return true;
		}

	function mysql_connect($server=null,$username=null,$password=null,$new_link=false,$client_flags=0,$persistent=false) {
		if (!ini_get('sql.safe_mode'))
			{
			if ($server===null)
				{
				$server = ini_get('mysql.default_host');
				if ($server=='')
					$server = 'localhost:3306';
				}
			if ($username===null)
				{
				$username = ini_get('mysql.default_user');
				if ($username=='')
					$username = get_current_user();
				}
			if ($password===null)
				{
				$password = ini_get('mysql.default_password');
				}
			}
		else
			{ //Handle safe mode
			$server = 'localhost:3306';
			$username = get_current_user();
			$password = ini_get('mysql.default_password');
			$new_link = false;
			$client_flags = 0;
			}

		//Check if an identical connection already exists, and if new_link is false. If so, return that connection handle.
		if (!$new_link && is_array($GLOBALS['bridgeMysql']['connection']))
			{
			foreach ($GLOBALS['bridgeMysql']['connection'] as $k=>$v)
				{
				if (
					$GLOBALS['bridgeMysql']['connection'][$k]['server'] == $server
					&& $GLOBALS['bridgeMysql']['connection'][$k]['username'] == $username
					&& $GLOBALS['bridgeMysql']['connection'][$k]['password'] == $password
					)
				return $k;
				}
			}

		//Attempt Connection
		if (!$mysqli = new MySQLi($server,$username,$password))
			{
			$GLOBALS['bridgeMysql']['conError'] = $mysqli->connect_error;
			return false;
			}

		//Iterate Pool Connection Counter
		$thisCon = ++$GLOBALS['bridgeMysql']['connection']['count'];

		$GLOBALS['bridgeMysql']['connection'][$thisCon] = [
			'server'	=> $server
			,'username'	=> $username
			,'password'	=> $password
			,'new_link' => $new_link
			,'client_flags' => $client_flags
			,'persistent' => $persistent
			,'mysqli' => $mysqli
			];

		return $thisCon;
		}

	function mysql_create_db($database,$link=null) {
		return mysql_query('CREATE DATABASE '.$database,$link);
		}

	function mysql_data_seek($result,$row_number) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->data_seek($row_number);
		}

	function mysql_db_name($x=null,$x=null,$x=null) {
		//TODO: Unsupported Function
		//This seems to be an unused function, no results for it in GitHub, poor documentation on php.net,
		//and I've never used it. So for now, it's "passively" unsupported.
		return false;
		}

	function mysql_db_query($database,$query,$link=null) {
		mysql_select_db($database,$link);
		return mysql_query($query,$link);
		}

	function mysql_drop_db($database,$link=null) {
		return mysql_query('DROP DATABASE '.$database,$link);
		}

	function mysql_errno($link=null) {
		//TODO: Verify Connection Error Number Presentation
		$link = mysql_bridge_last($link);
		$ret = $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->errno;
		if ($ret=='')
			return 0;
		return $ret;
		}

	function mysql_error($link=null) {
		//TODO: Verify Connection Error Presentation
		if ($GLOBALS['bridgeMysql']['conError']!='')
			{
			$x = $GLOBALS['bridgeMysql']['conError'];
			$GLOBALS['bridgeMysql']['conError'] = '';
			return $x;
			}

		$link = mysql_bridge_last($link);
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->error;
		}

	function mysql_escape_string($string) {
		$escape = ['\\',chr(0),"\n","\r","'",'"',chr(32)];
		foreach($escape as $v)
			$string = str_replace($v,'\\'.$v,$string);
		return $string;
		}

	function mysql_fetch_array($result) {
		if (is_object($GLOBALS['bridgeMysql']['resultset'][$result])) //TODO: Determine impact. When running "SHOW MASTER LOGS" and then using this, we'd get a fatal on boolean.
			return $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_array();
		}

	function mysql_fetch_assoc($result) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_assoc();
		}

	function mysql_fetch_field($result,$field_offset=0) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_field_direct($field_offset);
		}

	function mysql_fetch_lengths($result) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->lengths;
		}

	function mysql_fetch_object($result,$class_name='stdClass',$params=[]) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_object($class_name,$params);
		}

	function mysql_fetch_row($result) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_row();
		}

	function mysql_field_flags($result,$x) {
		//TODO: Unsupported Function
		return 'I don\'t exist!';//'not_null'; //TODO: This is terribly wrong. But for testing.
		}

	function mysql_field_len($result,$field_offset) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_field_direct($field_offset)->max_length;
		}

	function mysql_field_name($result,$field_offset) {
		$direct = $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_field_direct($field_offset);
		return $convertType[$direct->name];
		}

	function mysql_field_seek($result,$field_offset) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->field_seek($field_offset);
		}

	function mysql_field_table($result,$field_offset) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_field_direct($field_offset)->table;
		}

	function mysql_field_type($result,$field_offset) {
		//TODO: Unsupported Function
		/*
		//TODO: Verify types.
		$convertType = [
			0 => 'decimal'
			,1 => 'tiny'
			,2 => 'short'
			,3 => 'long'
			,4 => 'float'
			,5 => 'double'
			,6 => 'null'
			,7 => 'timestamp'
			,8 => 'longlong'
			,9 => 'int24'
			,10 => 'date'
			,11 => 'time'
			,12 => 'datetime'
			,13 => 'year'
			,14 => 'newdate'
			,247 => 'enum'
			,248 => 'set'
			,249 => 'tiny_blob'
			,250 => 'medium_blob'
			,251 => 'long_blob'
			,252 => 'blob'
			,253 => 'string'
			,254 => 'string'
			,255 => 'geometry'
			];

		$direct = $GLOBALS['bridgeMysql']['resultset'][$result]->fetch_field_direct($field_offset);
		return $convertType[$direct->type];*/
		}

	function mysql_free_result($result) {
		$GLOBALS['bridgeMysql']['resultset'][$result]->free_result();
		unset($GLOBALS['bridgeMysql']['resultset'][$result]);
		return true;
		}

	function mysql_get_client_info() {
		if ($GLOBALS['bridgeMysql']['config']['client_version_lie']!='')
			{
			return $GLOBALS['bridgeMysql']['config']['client_version_lie'];
			}

		//Not going to work perfect, will require an open connection to use.
		$link = count($GLOBALS['bridgeMysql']['connection'])-1;
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->client_version;
		}

	function mysql_get_host_info($link=null) {
		$link = mysql_bridge_last($link);
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->host_info;
		}

	function mysql_get_proto_info($link=null) {
		$link = mysql_bridge_last($link);
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->protocol_version;
		}

	function mysql_get_server_info($link=null) {
		$link = mysql_bridge_last($link);
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->server_info;
		}

	function mysql_info($link=null) {
		$link = mysql_bridge_last($link);
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->info;
		}

	function mysql_insert_id($link=null) {
		$link = mysql_bridge_last($link);
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->insert_id;
		}

	function mysql_list_dbs($link=null) {
		return mysql_query('SHOW DATABASES',$link);
		}

	function mysql_list_fields($database,$table,$link=null) {
		return mysql_query("SHOW COLUMNS FROM $database.$table",$link);
		}

	function mysql_list_processes($link=null) {
		return mysql_query("SHOW PROCESSLIST",$link);
		}

	function mysql_list_tables($database,$link=null) {
		return mysql_query('SHOW TABLES FROM '.$database,$link);
		}

	function mysql_num_fields($result) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->field_count;
		}

	function mysql_num_rows($result) {
		return $GLOBALS['bridgeMysql']['resultset'][$result]->num_rows;
		}

	function mysql_pconnect($server=null,$username=null,$password=null,$client_flags=0) {
		return mysql_connect($server,$username,$password,false,$client_flags,true);
		}

	function mysql_ping($link=null) //TODO: Automatic reconnection disabled after a certain MySQL version. See PHP docs, and implement here? Or does MySQLi do that too?
		{
		$link = mysql_bridge_last($link);
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->ping();
		}

	function mysql_query($query,$link=null) {
		$link = mysql_bridge_last($link);

		//Iterate total result count so they can be tracked independantly. No correlation to connections, and putting this
		//info into the connection pool breaks the script, since count() is used to find the latest connection.
		$resultCount = ++$GLOBALS['bridgeMysql']['resultcount'];

		//Tracks the query. Not normal behavior, and can be removed. Super useful for debugging, however.
		$GLOBALS['bridgeMysql']['queryset'][$resultCount]=$query;

		if (!$GLOBALS['bridgeMysql']['resultset'][$resultCount] = $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->query($query))
			return false;
		return $resultCount;
		}

	function mysql_real_escape_string($string,$link=null) {
		$link = mysql_bridge_last($link);
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->real_escape_string($string);
		}

	function mysql_result($result,$number,$field=0) //TEST: Testing with numeric, and textual field ID.
		{
		$GLOBALS['bridgeMysql']['resultset'][$result]->data_seek($number);
		return mysql_fetch_array($GLOBALS['bridgeMysql']['resultset'][$result]);
		}

	function mysql_select_db($database,$link=null) //TODO: Return query result? Or true/false?, Or is this the same?
		{
		return mysql_query('USE '.$database,$link);
		}

	function mysql_set_charset($charset,$link=null) {
		if ($link===null)
			$link = count($GLOBALS['bridgeMysql']['connection'])-1;
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->set_charset($charset);
		}

	function mysql_stat($link=null) {
		if ($link===null)
			$link = count($GLOBALS['bridgeMysql']['connection'])-1;
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->stat;
		}

	function mysql_tablename() {
		//TODO: Unsupported Function
		die('cannot run mysql_tablename, not implemented');
		}

	function mysql_thread_id($link=null) {
		if ($link===null)
			$link = count($GLOBALS['bridgeMysql']['connection'])-1;
		return $GLOBALS['bridgeMysql']['connection'][$link]['mysqli']->thread_id;
		}

	function mysql_unbuffered_query($query,$link=null) {
		//MySQLi doesn't provide support for unbuffered queries as far as I know, so we'll wrap these into normal queries.
		return mysql_query($query,$link);
		}





	// Below are aliased functions. Do NOT copy/paste the main function here.
	// Only wrap these functions, and provide a return if needed. This is
	// to prevent inconsistent implementation and reduce supported code base.

	//Aliases mysql_create_db()
	function mysql_createdb($database,$link=null) {
		return mysql_create_db($database,$link);
		}

	//Aliases mysql_db_name()
	function mysql_dbname($result,$row,$field=null) {
		return mysql_db_name($result,$row,$field);
		}

	//Aliases mysql_drop_db()
	function mysql_dropdb($database,$link=null) {
		return mysql_drop_db($database,$link);
		}

	//Aliases mysql_field_flags()
	function mysql_fieldflags($result,$field_offset) {
		return mysql_field_flags($result,$field_offset);
		}

	//Aliases mysql_field_len()
	function mysql_fieldlen($result,$field_offset) {
		return mysql_field_len($result,$field_offset);
		}

	//Aliases mysql_field_name()
	function mysql_fieldname($result,$field_offset) {
		return mysql_field_name($result,$field_offset);
		}

	//Aliases mysql_field_table()
	function mysql_fieldtable($result,$field_offset) {
		return mysql_field_table($result,$field_offset);
		}

	//Aliases mysql_field_type()
	function mysql_fieldtype($result,$field_offset) {
		return mysql_field_type($result,$field_offset);
		}

	//Aliases mysql_free_result()
	function mysql_freeresult($result) {
		return mysql_free_result($result);
		}

	//Aliases mysql_list_dbs()
	function mysql_listdbs($link=null) {
		return mysql_list_dbs($link);
		}

	//Aliases mysql_list_fields()
	function mysql_listfields($database,$table,$link=null) {
		return mysql_list_fields($database,$table,$link);
		}

	//Aliases mysql_list_tables()
	function mysql_listtables($database,$link=null) {
		return mysql_list_tables($database,$link);
		}

	//Aliases mysql_num_fields()
	function mysql_numfields($result) {
		return mysql_num_fields($result);
		}

	//Aliases mysql_num_rows()
	function mysql_numrows($result) {
		return mysql_num_rows($result);
		}

	//Aliases mysql_select_db()
	function mysql_selectdb($database,$link=null) {
		return mysql_select_db($database,$link);
		}

	//Bridge Specific Functions
	function mysql_bridge_debug()
		{
		echo "<pre>";
		print_r($GLOBALS['bridgeMysql']);
		echo "</pre>";
		}
	//The MySQL library supports not passing a link, so we must determine the last called link.
	//If there is no connection, then the spec shows to auto-create a connection with no
	//parameters. Code Reuse, baby.
	function mysql_bridge_last($link=null)
		{
		//If the link is not specified, get the most recently created link.
		if ($link===null)
			$link = count($GLOBALS['bridgeMysql']['connection'])-1;
		//if ($link==-1)
//			$link = mysql_connect(); //TODO: Uncomment
		return $link;
		}

	}
