<?php
/**
 * Occurs before log writing
 */
namespace Kaoken\LaravelMysqlEmailLog\Events;

use Illuminate\Queue\SerializesModels;

class BeforeWriteLogEvent
{
    use SerializesModels;
    /**
     * Log model
     * @var model
     */
    public $log;
    /**
     * Log recode
     * @var array
     */
    public $recode;

    /**
     * Create a new event instance.
     *
     * @param object $log    Log model
     * @param array  $recode Log recode
     */
    public function __construct(&$log, array $recode)
    {
        $this->log = &$log;
        $this->recode = $recode;
    }
}