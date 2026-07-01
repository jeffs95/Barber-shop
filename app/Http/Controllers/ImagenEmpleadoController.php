<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImagenEmpleadoController extends Controller
{
    public function __invoke(string $filename): StreamedResponse|\Illuminate\Http\Response
    {
        $path = 'empleados/' . $filename;

        if (! Storage::disk('ftp_images')->exists($path)) {
            abort(404);
        }

        $contenido = Storage::disk('ftp_images')->get($path);
        $mime      = Storage::disk('ftp_images')->mimeType($path) ?: 'image/jpeg';

        return response($contenido, 200, [
            'Content-Type'        => $mime,
            'Cache-Control'       => 'public, max-age=86400',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
