<?php

namespace App\Helpers\Dumper;

/**
 * Main facade
 */
abstract class Shuttle_Dumper
{
    /**
     * Maximum length of single insert statement
     */
    public const INSERT_THRESHOLD = 838860;

    /**
     * @var Shuttle_DBConn
     */
    public $db;

    /**
     * @var Shuttle_Dump_File
     */
    public $dump_file;

    /**
     * End of line style used in the dump
     */
    public $eol = "\r\n";

    /**
     * Specificed tables to include
     */
    public $include_tables;

    /**
     * Specified tables to exclude
     */
    public $exclude_tables = [];

    public function __construct(Shuttle_DBConn $shuttleDBConn)
    {
        $this->db = $shuttleDBConn;
    }

    /**
     * Create an export file from the tables with that prefix.
     *
     * @param  string  $export_file_location  the file to put the dump to.
     *                                        Note that whenever the file has .gz extension the dump will be comporessed with gzip
     * @param  string  $table_prefix  Allow to export only tables with particular prefix
     * @return void
     */
    abstract public function dump($export_file_location, $table_prefix = '');

    /**
     * Factory method for dumper on current hosts's configuration.
     */
    final public static function create($db_options)
    {
        $shuttleDBConnMysqli = Shuttle_DBConn::create($db_options);
        $shuttleDBConnMysqli->connect();
        if (self::has_shell_access()
            && self::is_shell_command_available('mysqldump')
            && self::is_shell_command_available('gzip')
        ) {
            $dumper = new Shuttle_Dumper_ShellCommand($shuttleDBConnMysqli);
        } else {
            $dumper = new Shuttle_Dumper_Native($shuttleDBConnMysqli);
        }

        if (isset($db_options['include_tables'])) {
            $dumper->include_tables = $db_options['include_tables'];
        }

        if (isset($db_options['exclude_tables'])) {
            $dumper->exclude_tables = $db_options['exclude_tables'];
        }

        return $dumper;
    }

    final public static function has_shell_access()
    {
        if (! is_callable('shell_exec')) {
            return false;
        }

        $disabled_functions = ini_get('disable_functions');

        return mb_stripos($disabled_functions, 'shell_exec') == false;
    }

    final public static function is_shell_command_available(string $command)
    {
        if (preg_match('~win~i', PHP_OS)) {
            /*
                            On Windows, the `where` command checks for availabilty in PATH. According
                            to the manual(`where /?`), there is quiet mode:
                            ....
                                /Q       Returns only the exit code, without displaying the list
                                         of matched files. (Quiet mode)
                            ....
            */
            $output = [];
            exec('where /Q '.$command, $output, $return_val);

            return $return_val !== 1;
        }

        $last_line = exec('which '.$command);
        $last_line = trim($last_line);

        // Whenever there is at least one line in the output,
        // it should be the path to the executable
        return $last_line !== '' && $last_line !== '0';
    }

    protected function get_tables($table_prefix)
    {
        if (! empty($this->include_tables)) {
            return $this->include_tables;
        }

        $tables = $this->db->fetch_numeric('
            SHOW TABLES LIKE "'.$this->db->escape_like($table_prefix).'%"
        ');
        $tables_list = [];
        foreach ($tables as $table) {
            $table_name = $table[0];
            if (! in_array($table_name, $this->exclude_tables)) {
                $tables_list[] = $table_name;
            }
        }

        return $tables_list;
    }
}
