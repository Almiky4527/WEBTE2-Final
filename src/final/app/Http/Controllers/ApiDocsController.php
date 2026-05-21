<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\Yaml\Yaml;

class ApiDocsController extends Controller
{
    private function specPath(): string
    {
        return storage_path('openapi/openapi.yaml');
    }

    /**
     * Serve the raw OpenAPI YAML document.
     */
    public function yaml()
    {
        $path = $this->specPath();
        abort_unless(is_file($path), 404, 'OpenAPI spec not found');

        return response()->file($path, [
            'Content-Type' => 'application/yaml; charset=UTF-8',
        ]);
    }

    /**
     * Render the current OpenAPI spec to a PDF.
     *
     * The PDF is generated dynamically from the on-disk YAML so it always
     * reflects the current contract; header carries the title and the footer
     * shows page numbering as `n / total`.
     */
    public function pdf()
    {
        $path = $this->specPath();
        abort_unless(is_file($path), 404, 'OpenAPI spec not found');

        $spec = Yaml::parseFile($path);
        $title = data_get($spec, 'info.title', 'API documentation');
        $version = data_get($spec, 'info.version', '');

        $pdf = Pdf::loadView('openapi.pdf', [
            'spec'    => $spec,
            'title'   => $title,
            'version' => $version,
        ])->setPaper('a4');
        $pdf->render();

        $dom = $pdf->getDomPDF();
        $canvas = $dom->getCanvas();
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $header = $title.($version ? ' — v'.$version : '');

        $canvas->page_text(40, 24, $header, null, 9, [0.35, 0.35, 0.35]);
        $canvas->page_text(
            $w - 130, $h - 28,
            'Strana {PAGE_NUM} / {PAGE_COUNT}',
            null, 9, [0.35, 0.35, 0.35]
        );

        $filename = 'openapi-'.preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($title)).'.pdf';

        return $pdf->stream($filename);
    }
}
