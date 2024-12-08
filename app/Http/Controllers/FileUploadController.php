<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function uploadExcel(Request $request)
    {
        // Validar que el archivo sea un Excel
        $request->validate([
            'file' => 'required|mimes:xlsx|max:2048',
        ]);

        // Asegurar que el directorio de destino exista
        $uploadDirectory = storage_path('app/uploads');
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }

        // Mover el archivo a una ubicación accesible
        $uploadedFile = $request->file('file');
        $fileName = $uploadedFile->getClientOriginalName();
        $filePath = $uploadDirectory . '/' . $fileName;
        $uploadedFile->move($uploadDirectory, $fileName);

        // Convertir el archivo Excel a CSV
        $csvPath = $uploadDirectory . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '.csv';
        $this->convertExcelToCsv($filePath, $csvPath);

        // Escapar la ruta para MySQL
        $escapedCsvPath = addslashes($csvPath);

        // Procesar el archivo usando LOAD DATA LOCAL INFILE
        try {
            DB::connection()->getPdo()->exec("
                LOAD DATA LOCAL INFILE '{$escapedCsvPath}'
                INTO TABLE temporal
                FIELDS TERMINATED BY ',' 
                LINES TERMINATED BY '\\n'
                IGNORE 1 ROWS
                (nombre, paterno, materno, telefono, calle, numero_exterior, numero_interior, colonia, cp)
            ");

            // Llamar al procedimiento almacenado para migrar los datos
            DB::statement('CALL migrate_data()');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Archivo cargado y datos migrados exitosamente'], 200);
    }

    private function convertExcelToCsv($excelPath, $csvPath)
    {
        // Usar PhpSpreadsheet para convertir a CSV
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Csv');
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setSheetIndex(0); // Primera hoja
        $writer->save($csvPath);
    }
    public function getPaginatedData(Request $request)
{
    $request->validate([
        'page' => 'required|integer|min:1',
        'page_size' => 'required|integer|min:1',
    ]);

    $page = $request->input('page');
    $pageSize = $request->input('page_size');
    $startIndex = ($page - 1) * $pageSize;

    $result = DB::select('CALL paginate_personas(?, ?)', [$startIndex, $pageSize]);

    return response()->json($result, 200);
}
public function getPersonaDetails($id)
{
    $result = DB::select('CALL get_persona_details(?)', [$id]);

    if (empty($result)) {
        return response()->json(['error' => 'Persona no encontrada'], 404);
    }

    return response()->json($result, 200);
}

}
