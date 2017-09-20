<?php

namespace Kaoken\LaravelMysqlEmailLog\Model;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    public $timestamps = false;
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $dates = ['create_tm'];
    protected $jsonDecodeData=null;


    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $config = app()['config']["app.mysql_log"];
        $this->connection = $config['connection'];
        parent::__construct($attributes);
    }

    /**
     * Decode JSON of character string state of 'context' and obtain by array
     * @return array|null
     */
    public function &getJsonDecodeData()
    {
        if($this->jsonDecodeData===null)
            $this->jsonDecodeData = json_decode($this->context);
        return $this->jsonDecodeData;
    }
}
