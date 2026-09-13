<?php

namespace App\Http\Controllers;

use App\Exports\StationsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StationTemplateController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        return Excel::download(new StationsTemplateExport, 'modelo-estacoes.xlsx');
    }
}
