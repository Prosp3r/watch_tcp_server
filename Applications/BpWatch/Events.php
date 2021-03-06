<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
require_once __DIR__ . '/../Statistics/Clients/StatisticClient.php';
require_once __DIR__ . '/Channel/src/Client.php';
//use \Workerman\Lib\Timer;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    
    /**
    * @author wzb<wangzhibin_x@foxmail.com>
    * @date Dec 22, 2016 3:46:25 PM
    * worker start
    * 
    */
	public static function onWorkerStart($businessworker){
		
		// Channel客户端连接到Channel服务端
		//Channel\Client::connect('127.0.0.1', 2206);
		// 订阅broadcast事件，并注册事件回调
// 		Channel\Client::on('custom_server', function($event_data){
			
// 		});
	}
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        //echo $client_id."\n";
        // 向当前client_id发送数据
        //Gateway::sendToClient($client_id, "Hello $client_id");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login");
         if($_SERVER['GATEWAY_PORT'] == 10003){
            Gateway::joinGroup($client_id,'watch_g1');
         }
    }

   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */

   public static function onMessage($client_id, $message) {

        $showtime=date("Y-m-d H:i:s");
        echo $message." ".$showtime."---\n";
        //test timer
        // $time_interval=1.5;
        // Timer::add($time_interval,function(){
        //     echo "task run".date("Y-m-d H:i:s")."\n";
        // });

        //$handle=new HandleData();
        if($_SERVER['GATEWAY_PORT'] == 10003){
            //$handle->handle_watch_data($client_id,$message);
            HandleData::handle_watch_data($client_id, $message);

        }else if($_SERVER['GATEWAY_PORT'] == 10002){
			HandleData::handle_server_data($client_id, $message);
			Gateway::joinGroup($client_id,'app_g1');
            //$handle->handle_server_data($client_id,$message);
        }else if($_SERVER['GATEWAY_PORT'] == 9999){//for debug
        	//statistics
        	// 统计开始
        	StatisticClient::tick("bp_watch", 'debug_data');
        	// 统计的产生，接口调用是否成功、错误码、错误日志
        	$success = true; $code = 99999; $msg = '';
        	// 上报结果
        	StatisticClient::report('bp_watch', 'debug_data', $success, $code, $msg);
        	//end statistics
            if($message != 'huayingtek'){
                Gateway::closeClient($client_id);
            }
            Gateway::joinGroup($client_id,'debug1');
        }

   }

   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送
       //GateWay::sendToAll("$client_id logout");
   }


}
