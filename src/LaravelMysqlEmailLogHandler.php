<?php
/**
 * Created by PhpStorm.
 * User: yasuk
 * Date: 2017/09/19
 * Time: 17:54
 */

namespace Kaoken\LaravelMysqlEmailLog;

use Kaoken\LaravelMysqlEmailLog\Events\BeforeWriteLogEvent;
use Mail;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class LaravelMysqlEmailLogHandler extends AbstractProcessingHandler
{
    protected $levels = [
        'DEBUG'     => Logger::DEBUG,
        'INFO'      => Logger::INFO,
        'NOTICE'    => Logger::NOTICE,
        'WARNING'   => Logger::WARNING,
        'ERROR'     => Logger::ERROR,
        'CRITICAL'  => Logger::CRITICAL,
        'ALERT'     => Logger::ALERT,
        'EMERGENCY' => Logger::EMERGENCY
    ];


    /**
     * 実装するハンドラーのログにレコードを書き込みます。
     *
     * @param  array $record
     * @return array
     */
    protected function write(array $record)
    {
        $config = app()['config']["app.mysql_log"];

        $record['pid'] = getmypid();
        $isLocal = false;
        $context_json = '[]';
        if( !isset($_SERVER['REMOTE_ADDR']) || !isset($_SERVER['REQUEST_URI']) ||
            !isset($_SERVER['REQUEST_METHOD']) ) {
            $record['ip'] = gethostbyname(gethostname());
            $record['route'] = php_sapi_name();
            $record['method'] = '----';
            $isLocal = true;
        }else{
            $record['ip'] = $_SERVER['REMOTE_ADDR'];
            $record['route'] = $_SERVER['REQUEST_URI'];
            $record['method'] = strtoupper($_SERVER['REQUEST_METHOD']);
        }

        if( count($record['context']) == 0 && !$isLocal ){
            $context_json = json_encode([$record['method']=>Request::all()]);
        }else{
            $context_json = json_encode($record['context']);
        }

        $log = new Log();

        event(new BeforeWriteLogEvent($log,$record));
        $log->user_agent  = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']:"";
        $log->create_tm = $record['datetime']->format( 'Y-m-d H:i:s.u' );
        $log->pid = $record['pid'];
        $log->ip = $record['ip'];
        $log->level_name = $record['level_name'];
        $log->route = $record['route'];
        $log->method = $record['method'];
        $log->message = $record['message'];
        $log->context = $context_json;

        $log->save();



        if( $this->levels[env('LOG_EMAIL_LEVEL')] <= $record['level']){
            $aIn=[];
            foreach($this->levels as $key => $val){
                if($record['level'] <= $val ) $aIn[] = $key;
            }

            // Acquisition of the number of send email of today
            $cnt = 0;
            if(count($aIn)>0){
                $t = Carbon::now()->format('Y-m-d');
                $cnt = Log::where('create_tm','>=', Carbon::now()->format('Y-m-d'))
                    ->where('create_tm','<', Carbon::now()->addDay()->format('Y-m-d'))
                    ->whereIn('level_name',$aIn)
                    ->count();
            }

            $clLog = $config['email_log'];
            $clLimit = $config['email_send_limit'];
            if($cnt <= env('LOG_DAY_SEND_MAIL'))
                Mail::send(new $clLog($log));
            else if( $cnt === env('LOG_DAY_SEND_MAIL')+1) {
                Mail::send(new $clLimit($log));
            }
        }
    }
}