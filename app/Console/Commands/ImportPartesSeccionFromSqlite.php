<?php

namespace App\Console\Commands;

use App\Models\ParteSeccion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPartesSeccionFromSqlite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-partes-seccion-from-sqlite {--truncate : Truncate table before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import partes_seccion data from SQLite database to MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting import of partes_seccion from SQLite to MySQL...');

        try {
            // Verificar conexión a SQLite usando ruta directa
            $sqlitePath = database_path('database.sqlite');
            $this->info('Checking SQLite connection...');

            if (!file_exists($sqlitePath)) {
                $this->error("SQLite database file not found at: {$sqlitePath}");
                return 1;
            }

            // Configurar conexión temporal a SQLite
            config(['database.connections.sqlite_temp' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]]);

            $sqliteData = DB::connection('sqlite_temp')->select('SELECT COUNT(*) as count FROM partes_seccion');
            $this->info('SQLite connection OK. Found ' . $sqliteData[0]->count . ' records.');

            // Obtener datos de SQLite
            $this->info('Fetching data from SQLite...');
            $partes = DB::connection('sqlite_temp')->select('SELECT * FROM partes_seccion');

            if (empty($partes)) {
                $this->warn('No data found in SQLite partes_seccion table.');
                return;
            }

            // Truncar tabla si se solicita
            if ($this->option('truncate')) {
                $this->warn('Truncating partes_seccion table...');
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                ParteSeccion::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            // Crear mapa de IDs para asignaciones (SQLite ID -> MySQL ID)
            // NOTA: Los IDs son los mismos en ambas bases de datos
            $asignacionIdMap = [
                1 => 1,   // PD
                2 => 2,   // DT
                3 => 3,   // CB
                4 => 4,   // EA
                5 => 5,   // AY
                6 => 6,   // LE
                15 => 15, // LB
                16 => 16, // DI
                21 => 21, // NV
                22 => 22, // CE
                23 => 23, // OR
                24 => 24, // DS
            ];

            // Crear mapa de IDs para secciones (SQLite ID -> MySQL ID)
            $seccionIdMap = [
                1 => 1,
                2 => 2,
                3 => 3,
                7 => 4, // Sección faltante
            ];

            $imported = 0;
            $skipped = 0;

            $this->info('Importing ' . count($partes) . ' records...');
            $this->newLine();

            // Progress bar
            $bar = $this->output->createProgressBar(count($partes));
            $bar->start();

            foreach ($partes as $parte) {
                try {
                    // Convertir stdClass a array
                    $data = (array) $parte;

                    // Mapear IDs de asignacion y seccion
                    if (isset($data['asignacion_id']) && isset($asignacionIdMap[$data['asignacion_id']])) {
                        $data['asignacion_id'] = $asignacionIdMap[$data['asignacion_id']];
                    }

                    if (isset($data['seccion_id']) && isset($seccionIdMap[$data['seccion_id']])) {
                        $data['seccion_id'] = $seccionIdMap[$data['seccion_id']];
                    }

                    // Usar updateOrCreate para evitar duplicados por abreviacion
                    ParteSeccion::updateOrCreate(
                        ['abreviacion' => $data['abreviacion']],
                        $data
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $this->error("Error importing record ID {$parte->id}: " . $e->getMessage());
                    $skipped++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("Import completed!");
            $this->info("Imported: {$imported} records");
            if ($skipped > 0) {
                $this->warn("Skipped: {$skipped} records");
            }

        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
