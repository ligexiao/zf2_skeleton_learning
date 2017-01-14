<?php
namespace Application\Model;

use Application\Includes\Error;
use Common\DBOperFactory;
use Common\Log;

class Activity extends AbstractModel
{
    public $errCode= 0;
    public $errMsg = 'ok';

    public function __construct()
    {
        $this->db = DBOperFactory::getDb();
    }

    /***
     * 获取用户活动列表
     */
    public function user_act_list($params)
    {
        $where_sql = " ua.Fenable='Y' AND a.Fenable='Y' AND u.Fenable='Y'";
        if(!empty($params['user_id'])){
            $where_sql .= " AND u.Fid='{$params['user_id']}'";
        }
        if(!empty($params['act_id'])){
            $where_sql .= " AND a.Fid='{$params['act_id']}'";
        }
        if ($params['page'] > 0 && $params['size'] > 0) {
            $offset = $params['page'] * $params['size'] - $params['size'];
            $where_sql .= " LIMIT {$offset}, {$params['size']}";
        }

        $sql = "SELECT
                ua.Fid,
                ua.Fopt_type,
                u.Fid AS user_id,
                u.Fwechat_id,
                u.Fnick_name,
                a.Fid AS act_id,
                Fbegin_time,
                Fend_time,
                Ftheme,
                Fcontent,
                Flimit_min,
                Flimit_max,
                Fstatus,
                Fcreate_user
            FROM r_user_act ua
            INNER JOIN t_user u on ua.Fuser_id=u.Fid
            INNER JOIN t_activity a on ua.Fact_id=a.Fid
            WHERE {$where_sql}";
        $res =  $this->db->query($sql);
        //return $res;
        $ret = array();
        if(is_array($res) && count($res)>0){
            foreach($res as $item){
                $ret[$item['act_id']] = array(
                    'act_id' => $item['act_id'],
                    'act_begin_time' => $item['Fbegin_time'],
                    'act_end_time' => $item['Fend_time'],
                    'act_theme' => $item['Ftheme'],
                    'act_content' => $item['Fcontent'],
                    'act_status' => $item['Fstatus'],
                    'act_limit_min' => $item['Flimit_min'],
                    'act_limit_max' => $item['Flimit_max'],
                    'act_create_user' => $item['Fcreate_user'],
                    'act_user_info' => isset($ret[$item['act_id']]['act_user_info'])?$ret[$item['act_id']]['act_user_info']:array(),
                );

                $ret[$item['act_id']]['act_user_info'][] = array(
                    'user_id' => $item['user_id'],
                    'wechat_id' => $item['Fwechat_id'],
                    'nick_name' => $item['Fnick_name'],
                );
            }
        }
        return $ret;
    }

    public function update_activity($params){
        if(empty($params['act_id'])){
            $this->errCode = -1;
            $this->errMsg = "act_id参数不能为空";
            return ;
        }
        $upd_info = array();
        if(!empty($params['act_begin_time'])){
            $upd_info['Fbegin_time'] = $params['act_begin_time'];
        }
        if(!empty($params['act_end_time'])){
            $upd_info['Fend_time'] = $params['act_end_time'];
        }
        if(!empty($params['act_theme'])){
            $upd_info['Ftheme'] = $params['act_theme'];
        }
        if(!empty($params['act_content'])){
            $upd_info['Fcontent'] = $params['act_content'];
        }
        if(!empty($params['act_limit_min'])){
            $upd_info['Flimit_min'] = $params['act_limit_min'];
        }
        if(!empty($params['act_limit_max'])){
            $upd_info['Flimit_max'] = $params['act_limit_max'];
        }
        if(empty($upd_info)){
            Log::err("update_activity: params invalid!");
            $this->errCode = -2;
            $this->errMsg = "参数非法";
            return ;
        }

        $affect_rows = $this->db->update('t_activity', $upd_info, "Fid='{$params['act_id']}'");
        if($affect_rows <0){
            Log::err("update t_activity failed");
            Error::trigger(Error::ERR_LOGIC_COMMON, "更新活动失败");
            return ;
        }

        return;
    }

    public function add_activity($params){
        $info_act = array();
        if(empty($params['act_begin_time'])){
            $this->errCode = -1;
            $this->errMsg = "act_begin_time参数不能为空";
            return ;
        }
        $info_act['Fbegin_time'] = $params['act_begin_time'];

        if(empty($params['act_end_time'])){
            $this->errCode = -2;
            $this->errMsg = "act_end_time参数不能为空";
            return ;
        }
        $info_act['Fend_time'] = $params['act_end_time'];

        if(empty($params['act_theme'])){
            $this->errCode = -3;
            $this->errMsg = "act_theme参数不能为空";
            return ;
        }
        $info_act['Ftheme'] = $params['act_theme'];

        if(empty($params['wechat_id'])){
            $this->errCode = -4;
            $this->errMsg = "wechat_id参数不能为空";
            return ;
        }
        $info_user['Fwechat_id'] = $params['wechat_id'];

        if(empty($params['nick_name'])){
            $this->errCode = -5;
            $this->errMsg = "nick_name参数不能为空";
            return ;
        }
        $info_user['Fnick_name'] = $params['nick_name'];
        $info_act['Fcreate_user'] = $params['nick_name'];

        if(!empty($params['act_content'])){
            $info_act['Fcontent'] = $params['act_content'];
        }
        if(!empty($params['act_limit_min'])){
            $info_act['Flimit_min'] = $params['act_limit_min'];
        }
        if(!empty($params['act_limit_max'])){
            $info_act['Flimit_max'] = $params['act_limit_max'];
        }
        $add_time = date("Y-m-d H:i:s");
        $info_act['Fadd_time'] = $add_time;
        $info_act['Fadd_time'] = $add_time;

        $insert_act_id = $this->db->insert('t_activity', $info_act);
        if($insert_act_id <=0){
            Log::err("insert t_activity failed");
            Error::trigger(Error::ERR_LOGIC_COMMON, "新增活动失败");
        }
        $insert_user_id = $this->db->insert('t_user', $info_user);
        if($insert_user_id <=0){
            Log::err("insert t_user_act failed");
            Error::trigger(Error::ERR_LOGIC_COMMON, "新增活动失败");
        }

        $info_rel = array(
            'Fuser_id' => $insert_user_id,
            'Fact_id' =>$insert_act_id,
            'Fopt_type' => 1,
            'Fadd_time' =>$add_time
        );
        $insert_rel_id = $this->db->insert('r_user_act', $info_rel);
        if($insert_rel_id <=0){
            Log::err("insert r_user_act failed");
            Error::trigger(Error::ERR_LOGIC_COMMON, "新增活动失败");
        }

        return;
    }

    public function update_delete($params){
        if(empty($params['act_id'])){
            $this->errCode = -1;
            $this->errMsg = "act_id参数不能为空";
            return ;
        }

        $affect_rows = $this->db->update('t_activity', array('Fenable'=>'N'), "Fid='{$params['act_id']}'");
        if($affect_rows <0){
            Log::err("disable t_activity failed");
            Error::trigger(Error::ERR_LOGIC_COMMON, "删除活动失败");
            return ;
        }

        $affect_rows = $this->db->update('t_activity', array('Fenable'=>'N'), "Fid='{$params['act_id']}'");
        if($affect_rows <0){
            Log::err("disable t_activity failed");
            Error::trigger(Error::ERR_LOGIC_COMMON, "删除活动失败");
            return ;
        }

        $affect_rows_r = $this->db->update('r_user_act', array('Fenable'=>'N'), "Fact_id='{$params['act_id']}'");
        if($affect_rows_r <0){
            Log::err("disable r_user_act failed");
            Error::trigger(Error::ERR_LOGIC_COMMON, "删除活动失败");
            return ;
        }

        return;
    }

    public function join_activity($params){
        if(empty($params['act_id'])){
            $this->errCode = -1;
            $this->errMsg = "act_id参数不能为空";
            return ;
        }
        if(empty($params['user_id'])){
            $this->errCode = -2;
            $this->errMsg = "user_id参数不能为空";
            return ;
        }
        if(empty($params['opt_type'])){
            $this->errCode = -3;
            $this->errMsg = "opt_type参数不能为空";
            return ;
        }

        $uinfo = array(
            'Fact_id' => $params['act_id'],
            'Fuser_id' => $params['user_id'],
            'Fopt_type' => $params['opt_type'],
        );
        $affect_rows_r = $this->db->insert('r_user_act',$uinfo);
        if($affect_rows_r <=0){
            Log::err("insert r_user_act failed");
            Error::trigger(Error::ERR_LOGIC_COMMON, "加入活动失败");
            return ;
        }

        return;
    }
}
