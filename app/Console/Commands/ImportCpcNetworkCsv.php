<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportCpcNetworkCsv extends Command
{
    protected $signature = 'cpc:import-network
        {nodes : Path to Relationships Table_14 November_nodes.csv}
        {relationships : Path to Relationships Table_14 November.csv}';

    protected $description = 'Import CPC Leadership network nodes and weighted relationships from CSV files';

    public function handle()
    {
        $nodesPath = $this->resolvePath($this->argument('nodes'));
        $relationshipsPath = $this->resolvePath($this->argument('relationships'));

        if (!SchemaHasTable::check('leaders') || !SchemaHasTable::check('leader_relationships')) {
            $this->error('Required tables are missing. Run php artisan migrate first.');
            return 1;
        }

        $this->info('Importing leader nodes...');
        $nodeCount = $this->importNodes($nodesPath);

        $this->info('Importing relationship scores...');
        $edgeCount = $this->importRelationships($relationshipsPath);

        $this->info("Finished. Nodes processed: {$nodeCount}; relationships imported: {$edgeCount}.");
        return 0;
    }

    private function resolvePath($path)
    {
        $fullPath = base_path($path);

        if (!is_file($fullPath)) {
            throw new RuntimeException("CSV file not found: {$fullPath}");
        }

        return $fullPath;
    }

    private function openCsv($path)
    {
        $handle = fopen($path, 'r');

        if (!$handle) {
            throw new RuntimeException("Unable to open CSV: {$path}");
        }

        $headers = fgetcsv($handle, 25 * 1024 * 1024, ',', '"', '');

        if (!$headers) {
            fclose($handle);
            throw new RuntimeException("CSV has no header row: {$path}");
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $headers = array_map(function ($header) {
            return trim($header);
        }, $headers);

        return [$handle, $headers];
    }

    private function rows($handle, array $headers)
    {
        while (($values = fgetcsv($handle, 25 * 1024 * 1024, ',', '"', '')) !== false) {
            if ($values === [null] || $values === []) {
                continue;
            }

            // Pad short rows and ignore any unexpected trailing values.
            $values = array_slice(array_pad($values, count($headers), null), 0, count($headers));
            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = isset($values[$index]) ? trim((string) $values[$index]) : null;
            }

            yield $row;
        }
    }

    private function importNodes($path)
    {
        list($handle, $headers) = $this->openCsv($path);
        $processed = 0;

        $map = [
            'english_name' => 'english_name',
            'chinese_name' => 'chinese_name',
            'gender' => 'gender',
            'age' => 'age',
            'birth_province' => 'birth_province',
            'ethnicity' => 'ethnicity',
            'current_position' => 'current_position',
            'concurrent_position_1' => 'concurrent_position_1',
            'rank' => 'rank',
            'province_of_employment' => 'province_of_employment',
            'status' => 'status',
        ];

        DB::beginTransaction();

        try {
            foreach ($this->rows($handle, $headers) as $row) {
                $leaderId = $row['leader_id'] ?? null;

                if (!$leaderId) {
                    continue;
                }

                $attributes = [];

                foreach ($map as $csvColumn => $dbColumn) {
                    $value = $row[$csvColumn] ?? null;

                    // Do not overwrite existing leader data with empty CSV cells.
                    if ($value === null || $value === '') {
                        continue;
                    }

                    if ($dbColumn === 'age') {
                        $value = is_numeric($value) ? (int) $value : null;
                        if ($value === null) {
                            continue;
                        }
                    }

                    $attributes[$dbColumn] = $value;
                }

                $exists = DB::table('leaders')->where('leader_id', $leaderId)->exists();

                if ($exists) {
                    if (!empty($attributes)) {
                        DB::table('leaders')->where('leader_id', $leaderId)->update($attributes);
                    }
                } else {
                    $attributes['leader_id'] = $leaderId;
                    DB::table('leaders')->insert($attributes);
                }

                $processed++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);
        return $processed;
    }

    private function importRelationships($path)
    {
        list($handle, $headers) = $this->openCsv($path);
        $columns = [
            'leader_id_1',
            'leader_id_2',
            'relationship_score',
            'evidence_categories',
            'relationship_types',
            'total_evidence_records',
            'career_evidence_records',
            'education_evidence_records',
            'evidence_details',
            'leader_1_name',
            'leader_1_chinese_name',
            'leader_1_position',
            'leader_1_rank',
            'leader_2_name',
            'leader_2_chinese_name',
            'leader_2_position',
            'leader_2_rank',
        ];

        $count = 0;
        $batch = [];

        DB::beginTransaction();

        try {
            // Delete is transactional in MySQL; avoid TRUNCATE here because it
            // causes an implicit commit.
            DB::table('leader_relationships')->delete();

            foreach ($this->rows($handle, $headers) as $row) {
                $id1 = $row['leader_id_1'] ?? null;
                $id2 = $row['leader_id_2'] ?? null;

                if (!$id1 || !$id2 || $id1 === $id2) {
                    continue;
                }

                $record = [
                    'leader_id_1' => $id1,
                    'leader_id_2' => $id2,
                    'relationship_score' => (int) ($row['relationship_score'] ?? 0),
                    'evidence_categories' => (int) ($row['evidence_categories'] ?? 0),
                    'relationship_types' => $row['relationship_types'] ?? null,
                    'total_evidence_records' => (int) ($row['total_evidence_records'] ?? 0),
                    'career_evidence_records' => (int) ($row['career_evidence_records'] ?? 0),
                    'education_evidence_records' => (int) ($row['education_evidence_records'] ?? 0),
                    'evidence_details' => $row['evidence_details'] ?? null,
                    'leader_1_name' => $row['leader_1_name'] ?? null,
                    'leader_1_chinese_name' => $row['leader_1_chinese_name'] ?? null,
                    'leader_1_position' => $row['leader_1_position'] ?? null,
                    'leader_1_rank' => $row['leader_1_rank'] ?? null,
                    'leader_2_name' => $row['leader_2_name'] ?? null,
                    'leader_2_chinese_name' => $row['leader_2_chinese_name'] ?? null,
                    'leader_2_position' => $row['leader_2_position'] ?? null,
                    'leader_2_rank' => $row['leader_2_rank'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $batch[] = $record;

                if (count($batch) >= 250) {
                    DB::table('leader_relationships')->insert($batch);
                    $count += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('leader_relationships')->insert($batch);
                $count += count($batch);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);
        return $count;
    }
}

/**
 * Small helper avoids coupling this command to a particular migration state.
 */
class SchemaHasTable
{
    public static function check($table)
    {
        return \Illuminate\Support\Facades\Schema::hasTable($table);
    }
}
