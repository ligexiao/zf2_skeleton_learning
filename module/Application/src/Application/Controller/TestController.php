<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Common\DBOperFactory;
use Common\ReturnInfo;
use Zend\View\Model\ViewModel;

class TestController extends BaseController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function stringAction()
    {
        echo "hello zendframework2";exit;
    }

    public function wechatAction()
    {
        $params['timestamp'] = $this->getParam('timestamp');
        $params['nonce'] = $this->getParam('nonce');
        $params['signature'] = $this->getParam('signature');
        $params['echostr'] = $this->getParam('echostr');

        $token = $this->getConfig('wechat_token');
        $tmpArr = array($params['timestamp'],$params['nonce'],$token);//将时间戳，token，随机数放到数组里，方便排序

        sort($tmpArr,SORT_STRING);//字典序排序数组

        $tmpStr = sha1(implode($tmpArr));//拼接字符串并用sha1算法加密

        if($tmpStr == $params['signature']){

            echo $params['echostr'];//验证成功，输出echostr

            exit;

        }else{

            echo 'error';

            exit;

        }

    }

    public function jsonAction()
    {
        // query: wechat_id
        $data = array(
            array(
                'act_id'=>111,
                'act_begin_time'=>'2017-01-13 08:20:30',
                'act_end_time'=>'2017-01-16 08:20:30',
                'act_theme'=>'古美路篮球',
                'act_desc'=>'古美路篮球活动啦啦啦啦啦',
                'act_status'=>'进行中',
                'act_create_user'=>'alex',
                'act_user_count'=>2,
                'act_user_limit'=>5,
                'act_user_info'=>array(
                    array('wechat_id'=>'123213ierer',
                        'nick_name'=>'jeff',
                        ),
                    array('wechat_id'=>'54234asdf333kk',
                        'nick_name'=>'alex',
                    ),
                ),
            ),
            array(
                'act_id'=>222,
                'act_begin_time'=>'2017-01-01 12:10:00',
                'act_end_time'=>'2017-01-02 12:10:00',
                'act_theme'=>'大保健',
                'act_desc'=>'大保健我请客快来',
                'act_status'=>'已过期',
                'act_user_count'=>2,
                'act_user_limit'=> -1,
                'act_create_user'=>'jeff',
                'act_user_info'=>array(
                    array('wechat_id'=>'123213ierer',
                        'nick_name'=>'jeff',
                    ),
                    array('wechat_id'=>'54234asdf333kk',
                        'nick_name'=>'alex',
                    ),
                ),
            ),
        );

        $data_insert = array(
            'act_begin_time'=>'2017-01-01 12:10:00',
            'act_end_time'=>'2017-01-02 12:10:00',
            'act_theme'=>'古美路篮球',
            'act_desc'=>'古美路篮球活动啦啦啦啦啦',
            'act_user_limit'=>5,
            'create_user'=>'123213ierer',
        );

        $db = DBOperFactory::getDb();
        $ret = $db->query("SELECT * FROM country limit 5");
        $this->ret->set(new ReturnInfo(0, 'ok',$ret));
    }
}
