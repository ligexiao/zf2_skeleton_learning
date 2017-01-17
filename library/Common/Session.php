<?php

namespace Common;

class Session
{
	const SESSION_EXPIRES = 86400;// session过去时间

	/**
	 * 更新session登录信息
	 * @param string $open_id
	 * @param string $session_key
	 * @return boolean
	 */
	public static function updateSession($open_id = '', $session_key = '')
	{
		$table = 't_session';
		if(!empty($open_id) && !empty($session_key)) {
			$db = DBOperFactory::getDb();
			$sql = "select Fid,UNIX_TIMESTAMP(Flast_login_time) as lastlogin from {$table} where ";
			$where1 = $db->quoteInto('open_id=?', $open_id);
			$where2 = $db->quoteInto('Fsession_key=?', $session_key);
			$sql = $sql . $where1 . ' and ' . $where2;
			Log::info('query sql:' . $sql);
			
			$row = $db->fetchRow($sql);
			if(!empty($row)) {
				$lastlogin = $row['lastlogin'];
				$sess_id = $row['Fid'];
                $unixnow = time();
                $now = date('YmdHis', $unixnow);
				($unixnow . ',' . $lastlogin . ',' . ($unixnow - $lastlogin) . ',' . self::SESSION_EXPIRES);

				if(($unixnow - $lastlogin) < self::SESSION_EXPIRES) // SESSION 保留一天，过期需要从新登录。
				{
					$update_data_arr = array(
							'Flast_login_time' 	=> 	$now,
					);
					$where = $db->quoteInto('Fid=?', $sess_id);
					$row_affected = $db->update($table, $update_data_arr, $where);
                    Log::info('update last_time row affected no oa:' . $row_affected);

					return true;
				}
			}
		}
        return false;
	}

	/**
	 生成session key
	 * @return string
	 */
	public static function genSessionKey()
	{
		$str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		srand(time());
		$result_str = '';
		for($i = 0; $i < 20; $i++)
		{
            $index = rand(0, strlen($str) - 1);
            $result_str .= $str[$index];
		}
		return $result_str;
	}
	
	/**
	 * 设置的session信息
	 * @param string $openid
     * @param string $wx_session_key: 微信session key
	 * @return string
	 */
	public static function set_session($openid=null, $wx_session_key=null, $trd_session_key=null)
	{
        if(!( (!empty($openid)&&!empty($wx_session_key)) || !empty($trd_session_key))){
            Log::info("set_session: 参数错误");
            return array();
        }
        $db = DBOperFactory::getDb();
        $table = 't_session';
        $new_sessionkey = self::genSessionKey();// 生成新的session key
        $unixnow = time();
        $now = date('YmdHis', $unixnow);

        // 初始值
        $session_arr = array(
            'openid' => $openid,
            'session_key' => $wx_session_key,
            'trd_session_key' => $trd_session_key,
        );
        if(empty($trd_session_key)){// 第一次
            $insert_data_arr = array(
                'Ftrd_session_key' 	=>	$new_sessionkey,
                'Fopenid' 	=>	$openid,
                'Fsession_key' 	=>	$wx_session_key,
                'Fadd_time'	=>	$now,
                'Flast_login_time'	=>	$now
            );
            $row_affected = $db->insert($table, $insert_data_arr);
            Log::info('insert sucess, row affected:' . $row_affected);
            $session_arr['trd_session_key'] = $new_sessionkey;
        }else{// 更新session
            $sql = $db->quoteInto('select Fid,UNIX_TIMESTAMP(Flast_login_time) as lastlogin,Fopenid,Fsession_key from ' . $table . ' where Ftrd_session_key=?', $trd_session_key);
            Log::info('query session sql: ' . $sql);
            $row = $db->fetchRow($sql);
            if(!empty($row)) {
                $session_arr['openid'] = $row['Fopenid'];
                $session_arr['session_key'] = $row['Fsession_key'];

                $sess_id = $row['Fid'];
                $lastlogin = $row['lastlogin'];

                Log::info($unixnow . ',' . $lastlogin . ',' . ($unixnow - $lastlogin) . ',' . self::SESSION_EXPIRES);

                if(($unixnow - $lastlogin) < self::SESSION_EXPIRES) {
                    $update_data_arr = array(
                        'Fsession_key' => $wx_session_key,
                        'Flast_time' 	=> 	$now
                    );
                    $where = $db->quoteInto('Fid=?', $sess_id);
                    $row_affected = $db->update($table, $update_data_arr, $where);
                    Log::info('update last_time row affected:' . $row_affected);
                } else {// session过期
                    $update_data_arr = array(
                        'Fsession_key' => $wx_session_key,
                        'Ftrd_session_key' 	=>	$new_sessionkey,
                        'Flast_login_time' 	=> 	$now
                    );

                    $where = $db->quoteInto('Fid=?', $sess_id);
                    $row_affected = $db->update($table, $update_data_arr, $where);
                    $session_arr['trd_session_key'] = $new_sessionkey;
                    Log::info('update all row affected:' . $row_affected);
                }
            }else{
                Log::err("set_session: trd_session_key({$trd_session_key})数据非法");
                return array();
            }
        }

		return $session_arr;
	}
	
	public static function encodeURL($url)
	{
		return str_replace('.', '%2E', urlencode($url));
	}
}
