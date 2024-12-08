<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    public function getPersonas(Request $request)
    {
        $offset = $request->query('offset', 0); // Inicio de los datos
        $limit = $request->query('limit', 100); // Número de datos por página

        try {
            $result = DB::select('CALL get_personas_with_details(?, ?)', [$offset, $limit]);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
