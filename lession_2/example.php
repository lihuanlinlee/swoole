<?php
/**
 * 案例，初始化段位
 * @Author dalin<771635214@qq.com>
 * @date 2017/10/12
 */
if ( PHP_SAPI != 'cli' )
{
    exit("Not allow!");
}
//用于调试
if(!defined('PATH_ROOT'))
{
    define('PATH_CRON_LOG', 'crond/log/');
    define("PATH_ROOT", realpath(dirname(__DIR__)).'/');
    define('CRON_TIME', time());//CRON开始时间
    require PATH_ROOT.'vendor/autoload.php';
}

$start_mtime = microtime(true);

//设置父进程名称
cli_set_process_title("init_level_swoole");

$logger = \lib\Log::init(PATH_CRON_LOG.basename(__FILE__));

lib\util::testMemory();

//初始化数据库
$db = \lib\DB::init(false);

//初始化缓存类
$cache = \lib\Cache::init();

//查找总的更新数据量(这里有一点要注意的，由于使用多进程要实例化多个类，故这里不能使用单例模式)
$count = $db->count("game_user");

//每次操作10000条数据
$pre = 10000;

//最大执行次数
$num = ceil($count/$pre);

for($i=0;$i<$num;++$i)
{
    //查询开始值
    $from = $i * $pre;
    $process = new swoole_process(function (swoole_process $worker) use ($from,$pre)
    {
        //设置子进程名称
        $worker->name("init_level_swoole_child_".$worker->pid);
        
        //初始化数据库
        $db = \lib\DB::init(false);
        
        //初始化缓存类
        $cache = \lib\Cache::init();
        
        //初始化所有用户段位
      //$data = $db->select("game_user",["user_id"],["LIMIT"=>[$from,$pre],"ORDER"=>["user_id"=>"ASC"]]);
        $data = $db->query("select user_id from game_user where score != 0 order by user_id asc limit {$from},{$pre} ");
        
        while($res = $data->fetch())
        {
            //更新数据
            $db->update("game_user",["level"=>"bronze_1","score"=>0],["user_id"=> $res['user_id']]);
            $cache->delete('user_info_'.$res['user_id']);
        }
        
        //结束进程
        $worker->exit(0);
    }, false);
    
    $process->start();
}


    //更新排行榜缓存
    $cache->delete('rank_group');
    
    //获取特定用户的排名信息,暂时最大分数大概为1000
    for($i=0;$i<1000;$i++)
    {
        $cache->delete('rank_'.$i);
    }

    for($i=0;$i<$num;++$i)
    {
        //等待最后一个子进程执行完毕主进程处于阻塞状态
        swoole_process::wait();
    }
    
$logger->error('success', ['info'=>'success']);    
//echo lib\util::testMemory('完成执行');









