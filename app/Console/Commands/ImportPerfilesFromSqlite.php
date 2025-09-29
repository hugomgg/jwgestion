<?php

namespace App\Console\Commands;

use App\Models\Perfil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPerfilesFromSqlite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-perfiles-from-sqlite {--truncate : Truncate table before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import perfiles data from SQLite database to MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting import of perfiles from SQLite to MySQL...');

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

            $sqliteData = DB::connection('sqlite_temp')->select('SELECT COUNT(*) as count FROM perfiles');
            $this->info('SQLite connection OK. Found ' . $sqliteData[0]->count . ' records.');

            // Obtener datos de SQLite
            $this->info('Fetching data from SQLite...');
            $perfiles = DB::connection('sqlite_temp')->select('SELECT * FROM perfiles');

            if (empty($perfiles)) {
                $this->warn('No data found in SQLite perfiles table.');
                return;
            }

            // Truncar tabla si se solicita
            if ($this->option('truncate')) {
                $this->warn('Truncating perfiles table...');
                Perfil::truncate();
            }

            $imported = 0;
            $skipped = 0;

            $this->info('Importing ' . count($perfiles) . ' records...');
            $this->newLine();

            // Progress bar
            $bar = $this->output->createProgressBar(count($perfiles));
            $bar->start();

            foreach ($perfiles as $perfil) {
                try {
                    // Convertir stdClass a array
                    $data = (array) $perfil;

                    // Usar updateOrCreate para evitar duplicados por nombre
                    Perfil::updateOrCreate(
                        ['nombre' => $data['nombre']],
                        $data
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $this->error("Error importing record ID {$perfil->id}: " . $e->getMessage());
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
