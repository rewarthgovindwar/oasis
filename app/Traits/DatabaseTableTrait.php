<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait DatabaseTableTrait
{
    public function tableWithRecordId(): array
    {
        $tables = $this->getAllTables();
        $withRecords = [];
        $db = 'Tables_in_'.env('DB_DATABASE');
        foreach ($tables as $table) {
            $table_name = config('database.default') === 'mysql' ? $table->{$db} : $table->tablename;
            if ((Schema::hasColumn($table_name, 'record_id'))) {
                $withRecords[] = $table_name;
            }

            if ((Schema::hasColumn($table_name, 'student_record_id'))) {
                $withRecords[] = $table_name;
            }
        }

        return $withRecords;
    }

    public function tableWithRecordIdActiveStatus(): array
    {
        $tables = $this->getAllTables();
        $db = 'Tables_in_'.env('DB_DATABASE');
        $recordWithActiveStatus = [];
        foreach ($tables as $table) {
            $table_name = config('database.default') === 'mysql' ? $table->{$db} : $table->tablename;
            if ((Schema::hasColumns($table_name, ['record_id', 'active_status']))) {
                $recordWithActiveStatus[] = $table_name;
            }

            if ((Schema::hasColumns($table_name, ['student_record_id', 'active_status']))) {
                $recordWithActiveStatus[] = $table_name;
            }
        }

        return $recordWithActiveStatus;
    }

    private function getAllTables()
    {
        return Schema::getAllTables();
    }
}
