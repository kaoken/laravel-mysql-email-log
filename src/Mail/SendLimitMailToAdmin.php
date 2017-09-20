<?php
/**
 * Mail send limit exceeded
 */
namespace Kaoken\LaravelMysqlEmailLog\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendLimitMailToAdmin extends Mailable
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

        return $this->view('vendor.mysql_email_log.over_limit')
            ->subject(env('APP_NAME').' - Log mail send limit exceeded. ['.$this->log->level_name.']')
            ->to($config['to'], 'Admin')
            ->with(['log'=>$this->log, 'config'=>$config]);
    }
}
