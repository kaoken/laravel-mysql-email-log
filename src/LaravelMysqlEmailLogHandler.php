<?php
namespace Kaoken\LaravelMysqlEmailLog;


use Carbon\Carbon;
use Mail;
use Kaoken\LaravelMysqlEmailLog\Events\BeforeWriteLogEvent;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger as Monolog;
use Throwable;

class LaravelMysqlEmailLogHandler extends AbstractProcessingHandler
{
    protected $levels = [
        'DEBUG'     => Monolog::DEBUG,
        'INFO'      => Monolog::INFO,
        'NOTICE'    => Monolog::NOTICE,
        'WARNING'   => Monolog::WARNING,
        'ERROR'     => Monolog::ERROR,
        'CRITICAL'  => Monolog::CRITICAL,
        'ALERT'     => Monolog::ALERT,
        'EMERGENCY' => Monolog::EMERGENCY
    ];


    /**
     * Writes a record to the log of the handler to be implemented.
     *
     * @param  array $record
     */
    protected function write(array $record)
    {
        try{
            $this->mysqlEmailLogWrite($record);
        }
        catch (Throwable $e){
            tap($this->createEmergencyLogger(), function ($logger) use ($e) {
                $logger->emergency('Unable to create configured logger. Using emergency logger.', [
                    'exception' => $e,
                ]);
            });
        }
    }


    /**
     * Create an emergency log handler to avoid white screens of death.
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createEmergencyLogger()
    {
        return new \Illuminate\Log\Logger(
            new Monolog(
                'laravel',
                [new \Monolog\Handler\StreamHandler(
                    storage_path().'/logs/laravel.log',
                    Monolog::DEBUG
                )]
            ),
            app()["events"]
        );
    }

    private function mysqlEmailLogWrite(array $record){
        $config = app()['config']["logging.channels.mysql_log"];

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
            $context_json = json_encode([$record['method']=>request()->all()]);
        }else{
            $context_json = json_encode($record['context']);
        }

        $clLog = $config['model'];
        $log = new $clLog();

        event(new BeforeWriteLogEvent($log,$record));
        $log->user_agent  = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']:"";
        $log->create_tm = $record['datetime']->format( 'Y-m-d H:i:s.u' );
        $log->pid = $record['pid'];
        $log->ip = $record['ip'];
        $log->level = $record['level'];
        $log->level_name = $record['level_name'];
        $log->route = $record['route'];
        $log->method = $record['method'];
        $log->message = $record['message'];
        $log->context = $context_json;

        $log->save();

        //
        if( $config['email'] !== true ) return;

        // Preparation for sending mail.
        $lv = 'ERROR';
        if( !isset($config['email_send_level']) ) $lv = strtoupper($config['email_send_level']);
        if( !array_key_exists($lv,$this->levels))$lv = 'ERROR';

        if( $this->levels[$lv] <= $record['level']){
            // Acquisition of the number of send email of today
            $t = Carbon::now()->format('Y-m-d');
            $cnt = ($clLog)::where('create_tm','>=', Carbon::now()->format('Y-m-d'))
                ->where('create_tm','<', Carbon::now()->addDay()->format('Y-m-d'))
                ->where('level','>=',$record['level'])
                ->count();

            $clMailLog = $config['email_log'];
            $clMailLimit = $config['email_send_limit'];

            $limit = 64;
            if( isset($config['max_email_send_count']) && is_int($config['max_email_send_count'])) $limit = $config['max_email_send_count'];

            if($cnt <= $limit)
                Mail::send(new $clMailLog($log));
            else if( $cnt === $limit+1) {
                Mail::send(new $clMailLimit($log));
            }
        }
    }
}
