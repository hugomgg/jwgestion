<?php

namespace App\Console\Commands;

use App\Models\Cancion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCancionesFromSqlite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-canciones-from-sqlite {--truncate : Truncate table before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import canciones data from SQLite database to MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting import of canciones from SQLite to MySQL...');

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

            $sqliteData = DB::connection('sqlite_temp')->select('SELECT COUNT(*) as count FROM canciones');
            $this->info('SQLite connection OK. Found ' . $sqliteData[0]->count . ' records.');

            // Obtener datos de SQLite
            $this->info('Fetching data from SQLite...');
            $canciones = DB::connection('sqlite_temp')->select('SELECT * FROM canciones');

            if (empty($canciones)) {
                $this->warn('No data found in SQLite canciones table.');
                return;
            }

            // Truncar tabla si se solicita
            if ($this->option('truncate')) {
                $this->warn('Truncating canciones table...');
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Cancion::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            $imported = 0;
            $skipped = 0;

            $this->info('Importing ' . count($canciones) . ' records...');
            $this->newLine();

            // Progress bar
            $bar = $this->output->createProgressBar(count($canciones));
            $bar->start();

            foreach ($canciones as $cancion) {
                try {
                    // Convertir stdClass a array
                    $data = (array) $cancion;

                    // Limpiar campos que no existen en MySQL o que necesitan ajuste
                    unset($data['creador'], $data['modificador']);

                    // Usar updateOrCreate para evitar duplicados por numero
                    Cancion::updateOrCreate(
                        ['numero' => $data['numero']],
                        array_merge($data, [
                            'creador' => 1,
                            'modificador' => 1,
                        ])
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $this->error("Error importing record ID {$cancion->id}: " . $e->getMessage());
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
