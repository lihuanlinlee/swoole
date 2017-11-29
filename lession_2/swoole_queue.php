<?php
/**
 * 进程间通信-消息队列(有点混乱，还是没有完全弄懂)
 */

//创建子进程
$worker_num =3;//创建的进程数
for($i=0;$i<$worker_num ; $i++)
{
    $process = new swoole_process('callback_function', false,false);
    
    //设置父进程名称(由于父进程只有一个，故只会为同一个父进程重复设置进程名称)
    $process->name("parent: worker".$i);
    
    //启用消息队列作为进程间通信(useQueue要在start的之前调用)
    $process->useQueue();
    
    //执行fork系统调用，启动子进程(返回进程ID)
    $pid = $process->start();
    
    $process->push("父进程插入队列[$pid]\n");
    
    $result = $process->pop();
     
    //这里主进程，接受到的子进程的数据
    echo "接受子进程的数据 $result\n";
}



//子进程创建成功后要执行的函数(回调函数和主进程是异步调用的,子进程的所有操作都要放在这里，这里实际上就是代表子进程)
function callback_function(swoole_process $process) 
{
    //设置子进程名称
    $process->name("child: worker");
    
    $recv = $process->pop();

    $process->push("子进程插入队列".$process->pid);
    
    echo "读取父进程队列: $recv\n";

    sleep(2);//注意这里有个sleep
    
    $process->exit(0);
}


