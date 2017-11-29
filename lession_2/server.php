<?php
/*
 * Lession 2:swoole多进程
 * @Author dalin
 * @Date 2017/11/29
 * @Desc 为什么要使用多进程？
 *   假如你有一个计划任务，每天凌晨定时执行，计划任务的工作包括增删改查多个操作且每个操作的业务都是独立的，如果都放在同一个进程
 *   里面，万一某一步出现致命性错误，那么将会导致整个进程失败,对业务造成很大的影响。有没有可能将这些操作都独立成一个进程，然后单独运行?
 *   可以，你可以把这些进程都单独分配不同的时间节点执行。对，这样就可以把进程造成的影响分离开。但是，有没有想过，通常情况下，这些进程都是
 *   存在很大的关联性，互相之间要进行通信或者是引用一些复用的部分，而且执行的时间并不一定允许分割开。所以这个时候，使用多进程去处理这些业务就
 *   变得尤其重要了.
 * 
 *   Linux进程间通信
 *                      --管道(PIPE)机制[我的理解是管道是内存中用于交换数据的内存块的称谓]
 *                      --传统IPC((IPC, interprocess communication 进程间通信))
 *                                         * 消息队列
 *                                         * 信号量
 *                                         * 共享内存
 */


/*
 * 利用管道实现进程间通信
 * 在子进程内调用write，父进程必须调用read接收此数据，否则父进程会一直阻塞直到有write
 * 在父进程内调用write，子进程必须调用read接收此数据，否则子进程会一直阻塞直到有write
 */

//设置父进程名称用这个函数即可
cli_set_process_title("Parent: worker");

//创建子进程
$worker_num =3;//创建的进程数
for($i=0;$i<$worker_num ; $i++)
{
    $process = new swoole_process('callback_function', false);
    
    //设置父进程名称,实际上父进程也是有"多个"的,这里的多个并不是说会生成多个父进程，实际上那是子进程，可以理解成子进程是嵌套在父进程里面，在一个循环体内就是只有一个父进程
    //process->name("parent: worker".$i);
    
    //执行fork系统调用，启动子进程(返回进程ID)
    $pid = $process->start();
    
    //子进程句柄向自己管道里写内容(外部)
    $process->write("你好儿子[$pid]，我是你爸！\n");
    
    //父进程读取子进程写入的数据(读取函数会一直阻塞监听直到有数据写入)
    echo $process->read(); 
    
    //等待一个子进程执行完毕主进程处于阻塞状态（不是所有子进程，只是当前子进程）
    $rel = swoole_process::wait();
}



//子进程创建成功后要执行的函数(回调函数和主进程是异步调用的,子进程的所有操作都要放在这里，这里实际上就是代表子进程)
function callback_function(swoole_process $process) 
{
    //设置子进程名称
    $process->name("child: worker");
    
    //子进程句柄向自己管道里写内容（内部）
    $process->write("你好爸，我是你儿子{$process->pid},我的管道ID是{$process->pipe}\n");
    
     //子进程读取父进程写入的数据(读取函数会一直阻塞监听直到有数据写入)
    echo $process->read();
    
    sleep(5);
    
    //结束进程
    $process->exit(0);
}







