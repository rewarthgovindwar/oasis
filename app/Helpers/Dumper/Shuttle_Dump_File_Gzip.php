<?php

namespace App\Helpers\Dumper;

/**
 * Gzip implementation. Uses gz* functions.
 */
class Shuttle_Dump_File_Gzip extends Shuttle_Dump_File
{
    public function open()
    {
        return gzopen($this->file_location, 'wb9');
    }

    public function write($string)
    {
        return gzwrite($this->fh, $string);
    }

    public function end(): bool
    {
        return gzclose($this->fh);
    }
}
