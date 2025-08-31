<?php

namespace App\Helpers\Dumper;

class Shuttle_Dumper_Native extends Shuttle_Dumper
{
    public function dump($export_file_location, $table_prefix = ''): void
    {
        $eol = $this->eol;
        $this->dump_file = Shuttle_Dump_File::create($export_file_location);
        $this->dump_file->write('-- Generation time: '.date('r').$eol);
        $this->dump_file->write('-- Host: '.$this->db->host.$eol);
        $this->dump_file->write('-- DB name: '.$this->db->name.$eol);
        $this->dump_file->write(sprintf('/*!40030 SET NAMES UTF8 */;%s%s', $eol, $eol));

        $tables = $this->get_tables($table_prefix);
        foreach ($tables as $table) {
            $this->dump_table($table);
        }

        unset($this->dump_file);
    }

    public function get_create_table_sql(string $table): string
    {
        $create_table_sql = $this->db->fetch('SHOW CREATE TABLE `'.$table.'`');

        return $create_table_sql[0]['Create Table'].';';
    }

    protected function dump_table(string $table)
    {
        $eol = $this->eol;
        $this->dump_file->write(sprintf('DROP TABLE IF EXISTS `%s`;%s', $table, $eol));
        $create_table_sql = $this->get_create_table_sql($table);
        $this->dump_file->write($create_table_sql.$eol.$eol);
        $data = $this->db->query(sprintf('SELECT * FROM `%s`', $table));
        $shuttleInsertStatement = new Shuttle_Insert_Statement($table);
        while ($row = $this->db->fetch_row($data)) {
            $row_values = [];
            foreach ($row as $value) {
                $row_values[] = $this->db->escape($value);
            }

            $shuttleInsertStatement->add_row($row_values);
            if ($shuttleInsertStatement->get_length() > self::INSERT_THRESHOLD) {
                // The insert got too big: write the SQL and create
                // new insert statement
                $this->dump_file->write($shuttleInsertStatement->get_sql().$eol);
                $shuttleInsertStatement->reset();
            }
        }

        $sql = $shuttleInsertStatement->get_sql();
        if ($sql) {
            $this->dump_file->write($shuttleInsertStatement->get_sql().$eol);
        }

        $this->dump_file->write($eol.$eol);
    }
}
