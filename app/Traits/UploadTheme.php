<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait UploadTheme
{
    public function recurse_copy(string $src, string $dst): void
    {
        try {
            // return $src;
            $dir = opendir($src);
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file !== '.') && ($file !== '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->recurse_copy($src.'/'.$file, $dst.'/'.$file);
                    } else {
                        copy($src.'/'.$file, $dst.'/'.$file);
                    }
                }
            }

            closedir($dir);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
        }

    }

    public function delete_directory(string $dirname): ?bool
    {
        try {
            if (is_dir($dirname)) {
                $dir_handle = opendir($dirname);
            } else {
                return false;
            }

            if (! $dir_handle) {
                return false;
            }

            while ($file = readdir($dir_handle)) {
                if ($file !== '.' && $file !== '..') {
                    if (! is_dir($dirname.'/'.$file)) {
                        unlink($dirname.'/'.$file);
                    } else {
                        $this->delete_directory($dirname.'/'.$file);
                    }
                }
            }

            closedir($dir_handle);
            rmdir($dirname);

            return true;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
        }

        return null;
    }
}
