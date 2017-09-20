<?php

namespace Kaoken\LaravelConfirmation\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Kaoken\LaravelMysqlEmailLog\Model\Log;

class LogMailToAdmin extends Mailable
{
    use SerializesModels;

    /**
     *
     * @var Log
     */
    protected $log;

    /**
     * Create a new message instance.
     *
     * @param Log $log Log model
     */
    public function __construct($log)
    {
        $this->log = $log;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        $config = app()['config']["app.mysql_log"];

        return $this->text('vendor.mysql_email_log.log')
            ->subject(env('APP_NAME').' - Log ['.$this->log->level_name.']')
            ->to($config['to'], 'Admin')
            ->with(['log'=>$this->log, 'config'=>$config]);
    }
}
