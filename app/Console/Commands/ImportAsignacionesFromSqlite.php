<?php

namespace App\Console\Commands;

use App\Models\Asignacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportAsignacionesFromSqlite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-asignaciones-from-sqlite {--truncate : Truncate table before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import asignaciones data from SQLite database to MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting import of asignaciones from SQLite to MySQL...');

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

            $sqliteData = DB::connection('sqlite_temp')->select('SELECT COUNT(*) as count FROM asignaciones');
            $this->info('SQLite connection OK. Found ' . $sqliteData[0]->count . ' records.');

            // Obtener datos de SQLite
            $this->info('Fetching data from SQLite...');
            $asignaciones = DB::connection('sqlite_temp')->select('SELECT * FROM asignaciones');

            if (empty($asignaciones)) {
                $this->warn('No data found in SQLite asignaciones table.');
                return;
            }

            // Truncar tabla si se solicita
            if ($this->option('truncate')) {
                $this->warn('Truncating asignaciones table...');
                Asignacion::truncate();
            }

            $imported = 0;
            $skipped = 0;

            $this->info('Importing ' . count($asignaciones) . ' records...');
            $this->newLine();

            // Progress bar
            $bar = $this->output->createProgressBar(count($asignaciones));
            $bar->start();

            foreach ($asignaciones as $asignacion) {
                try {
                    // Convertir stdClass a array
                    $data = (array) $asignacion;

                    // Limpiar campos que no existen en MySQL o que necesitan ajuste
                    unset($data['creador'], $data['modificador'], $data['creado_por_timestamp'], $data['modificado_por_timestamp']);

                    // Usar updateOrCreate para evitar duplicados
                    Asignacion::updateOrCreate(
                        ['abreviacion' => $data['abreviacion'] ?? null],
                        array_merge($data, [
                            'creador_id' => 1,
                            'modificador_id' => 1,
                        ])
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $this->error("Error importing record ID {$asignacion->id}: " . $e->getMessage());
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
