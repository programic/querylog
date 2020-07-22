<?php

namespace Programic\Querylog;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class QuerylogServiceProvider extends ServiceProvider
{
    public $queryCount = 0;
    public $totalQueryTime = 0;
    public $countsPerQuery = [];
    public $sqlLogFile = "/logs/sql.log";

    public function boot()
    {
        // publish config
        $this->publishes([
            __DIR__.'/../config/querylog.php' => config_path('querylog.php'),
        ], 'config');

        $config = config('querylog');

        $GLOBALS['generate_sql_time'] = 0;
        $GLOBALS['rememberable_count_get'] = 0;
        $GLOBALS['rememberable_count_cached'] = 0;
        $GLOBALS['rememberable_time_cached'] = 0;
        $GLOBALS['rememberable_count_db'] = 0;
        $GLOBALS['rememberable_time_db'] = 0;
        $GLOBALS['log_query'] = true;
        $GLOBALS['log_query_name'] = "";


        if ($config['enabled'] === true) {
            DB::listen(function ($query) use ($config) {
                $line = "";
                if ($this->queryCount === 0) {
                    $line .= "\n\n\n";
                }
                $this->queryCount++;
                $this->totalQueryTime += $query->time;
                $sqlQuery = $query->sql;
                $queryHash = md5($query->sql) . md5(json_encode($query->bindings));


                if (isset($this->countsPerQuery[$queryHash])) {
                    $this->countsPerQuery[$queryHash]++;
                } else {
                    $this->countsPerQuery[$queryHash] = 1;
                }

                $callCount = $this->countsPerQuery[$queryHash];

                $sql = $sqlQuery;

                $line .= "[" . date("Y-m-d H:i:s") . "]" . $GLOBALS['log_query_name'] . " SQL query #" . $this->queryCount . " (Execution time: \033[31m" . $query->time . "ms\033[0m, Call count: \033[31m" . $callCount . "x\033[0m, Total time: \033[31m" . $this->totalQueryTime . "ms\033[0m)\n";
                $line .= "     Bindings:  " . json_encode($query->bindings) . "\n";
                $line .= "     QUERY:     " . $sql . "\n";

                file_put_contents($config['log_location'], $line, $config['log_file_type']);
            });
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/querylog.php',
            'querylog'
        );
    }

}
