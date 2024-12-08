<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateStoredProcedure extends Command
{
    protected $signature = 'db:create-sp';
    protected $description = 'Crea el procedimiento almacenado migrate_data en la base de datos';

    public function handle()
    {
        $sql = "
        DELIMITER $$

        CREATE PROCEDURE migrate_data()
        BEGIN
            INSERT INTO persona (nombre, paterno, materno)
            SELECT DISTINCT nombre, paterno, materno
            FROM temporal;

            INSERT INTO telefono (persona_id, telefono)
            SELECT p.id, t.telefono
            FROM temporal t
            INNER JOIN persona p
            ON t.nombre = p.nombre AND t.paterno = p.paterno AND t.materno = p.materno;

            INSERT INTO direccion (persona_id, calle, numero_exterior, numero_interior, colonia, cp)
            SELECT p.id, t.calle, t.numero_exterior, t.numero_interior, t.colonia, t.cp
            FROM temporal t
            INNER JOIN persona p
            ON t.nombre = p.nombre AND t.paterno = p.paterno AND t.materno = p.materno;
        END$$

        DELIMITER ;
        ";

        DB::unprepared($sql);
        $this->info('Procedimiento almacenado migrate_data creado exitosamente.');
    }
}
