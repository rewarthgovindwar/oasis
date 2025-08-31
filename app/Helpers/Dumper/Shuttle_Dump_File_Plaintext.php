<?php

namespace App\Helpers\Dumper;

/**
 * Plain text implementation. Uses standard file functions in PHP.
 */
class Shuttle_Dump_File_Plaintext extends Shuttle_Dump_File
{
    public function open()
    {
        return fopen($this->file_location, 'w');
    }

    public function write($string)
    {
        return fwrite($this->fh, $string);
    }

    public function end(): bool
    {
        return fclose($this->fh);
    }
}
