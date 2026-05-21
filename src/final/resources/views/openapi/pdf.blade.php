@php
    /**
     * Variables:
     *   $spec    array  Parsed OpenAPI document.
     *   $title   string info.title
     *   $version string info.version
     */

    $methodColors = [
        'get'     => '#2563eb',
        'post'    => '#16a34a',
        'put'     => '#ca8a04',
        'patch'   => '#ca8a04',
        'delete'  => '#dc2626',
        'options' => '#475569',
        'head'    => '#475569',
    ];

    $resolveRef = function ($node) use (&$spec) {
        if (is_array($node) && isset($node['$ref']) && is_string($node['$ref'])) {
            $parts = explode('/', ltrim($node['$ref'], '#/'));
            $cur = $spec;
            foreach ($parts as $p) {
                if (!is_array($cur) || !array_key_exists($p, $cur)) return $node;
                $cur = $cur[$p];
            }
            return $cur;
        }
        return $node;
    };

    $renderSchema = function ($schema) use (&$renderSchema, $resolveRef) {
        $schema = $resolveRef($schema);
        if (!is_array($schema)) return '';
        if (isset($schema['type']) && $schema['type'] === 'object' && isset($schema['properties'])) {
            $rows = '';
            foreach ($schema['properties'] as $name => $prop) {
                $prop = $resolveRef($prop);
                $type = $prop['type'] ?? ($prop['$ref'] ?? '—');
                if (is_array($type)) $type = implode('|', $type);
                $desc = $prop['description'] ?? '';
                $rows .= '<tr>'
                    .'<td class="k">'.e($name).'</td>'
                    .'<td class="t">'.e($type).'</td>'
                    .'<td>'.e($desc).'</td>'
                    .'</tr>';
            }
            return '<table class="props"><thead><tr><th>Pole</th><th>Typ</th><th>Popis</th></tr></thead><tbody>'.$rows.'</tbody></table>';
        }
        if (isset($schema['type']) && $schema['type'] === 'array' && isset($schema['items'])) {
            return '<div class="muted">array&lt;…&gt;</div>'.$renderSchema($schema['items']);
        }
        return '<pre class="raw">'.e(json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)).'</pre>';
    };

    $paths = $spec['paths'] ?? [];
    ksort($paths);
@endphp
<!doctype html>
<html lang="sk">
<head>
<meta charset="utf-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 60px 40px 60px 40px; }
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: #111; font-size: 10.5pt; line-height: 1.4; }
    h1 { font-size: 22pt; margin: 0 0 4pt; }
    h2 { font-size: 14pt; margin: 18pt 0 6pt; border-bottom: 1px solid #ddd; padding-bottom: 3pt; }
    h3 { font-size: 11.5pt; margin: 10pt 0 4pt; }
    .muted { color: #666; }
    .meta { color: #555; margin-bottom: 16pt; }
    .endpoint { border: 1px solid #e5e7eb; border-radius: 4pt; padding: 8pt 10pt; margin: 0 0 10pt; page-break-inside: avoid; }
    .endpoint .head { margin-bottom: 4pt; }
    .method { display: inline-block; padding: 1pt 6pt; border-radius: 3pt; color: #fff; font-weight: bold; font-size: 9pt; text-transform: uppercase; margin-right: 6pt; }
    .path { font-family: DejaVu Sans Mono, monospace; font-size: 10.5pt; }
    .summary { color: #333; margin: 2pt 0 6pt; }
    .desc { color: #444; margin: 0 0 6pt; }
    table { width: 100%; border-collapse: collapse; margin: 4pt 0; }
    th, td { border: 1px solid #e5e7eb; padding: 3pt 5pt; text-align: left; vertical-align: top; font-size: 9.5pt; }
    th { background: #f3f4f6; }
    table.props td.k { font-family: DejaVu Sans Mono, monospace; }
    table.props td.t { color: #2563eb; font-family: DejaVu Sans Mono, monospace; }
    .tag { display: inline-block; background: #eef2ff; color: #3730a3; padding: 0 5pt; border-radius: 3pt; font-size: 8.5pt; margin-left: 4pt; }
    pre.raw { background: #f9fafb; border: 1px solid #e5e7eb; padding: 4pt 6pt; font-size: 9pt; white-space: pre-wrap; word-break: break-word; }
    .tag-section { margin-top: 14pt; }
    .toc li { list-style: none; padding: 1pt 0; }
    .toc .m { display: inline-block; width: 50pt; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; }
    a { color: #1d4ed8; text-decoration: none; }
</style>
</head>
<body>

<h1>{{ $title }}</h1>
<div class="meta">
    @if($version) Verzia <b>{{ $version }}</b> · @endif
    Vygenerované {{ now()->format('Y-m-d H:i') }} · {{ count($paths) }} ciest
</div>

@if(!empty($spec['info']['description']))
    <div class="desc">{!! nl2br(e($spec['info']['description'])) !!}</div>
@endif

<h2>Obsah</h2>
<ul class="toc">
@foreach($paths as $p => $ops)
    @foreach($ops as $method => $op)
        @php $col = $methodColors[strtolower($method)] ?? '#475569'; @endphp
        <li><span class="m" style="color: {{ $col }}">{{ strtoupper($method) }}</span>
            <span class="path">{{ $p }}</span>
            @if(!empty($op['summary']))<span class="muted"> — {{ $op['summary'] }}</span>@endif
        </li>
    @endforeach
@endforeach
</ul>

<h2>Endpointy</h2>

@foreach($paths as $p => $ops)
    @foreach($ops as $method => $op)
        @php $col = $methodColors[strtolower($method)] ?? '#475569'; @endphp
        <div class="endpoint">
            <div class="head">
                <span class="method" style="background: {{ $col }}">{{ $method }}</span>
                <span class="path">{{ $p }}</span>
                @foreach(($op['tags'] ?? []) as $t)<span class="tag">{{ $t }}</span>@endforeach
            </div>
            @if(!empty($op['summary']))<div class="summary"><b>{{ $op['summary'] }}</b></div>@endif
            @if(!empty($op['description']))<div class="desc">{!! nl2br(e($op['description'])) !!}</div>@endif

            @if(!empty($op['parameters']))
                <h3>Parametre</h3>
                <table>
                    <thead><tr><th>Meno</th><th>V</th><th>Typ</th><th>Povinný</th><th>Popis</th></tr></thead>
                    <tbody>
                    @foreach($op['parameters'] as $param)
                        @php $param = $resolveRef($param); $sch = $param['schema'] ?? []; $type = $sch['type'] ?? '—'; @endphp
                        <tr>
                            <td><code>{{ $param['name'] ?? '' }}</code></td>
                            <td>{{ $param['in'] ?? '' }}</td>
                            <td>{{ $type }}@isset($sch['default']) <span class="muted">= {{ is_scalar($sch['default']) ? $sch['default'] : json_encode($sch['default']) }}</span>@endisset</td>
                            <td>{{ !empty($param['required']) ? 'áno' : '' }}</td>
                            <td>{{ $param['description'] ?? '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            @if(!empty($op['requestBody']))
                <h3>Telo požiadavky</h3>
                @php
                    $rb = $resolveRef($op['requestBody']);
                    $content = $rb['content'] ?? [];
                @endphp
                @foreach($content as $mime => $entry)
                    <div class="muted">{{ $mime }}</div>
                    {!! $renderSchema($entry['schema'] ?? []) !!}
                @endforeach
            @endif

            @if(!empty($op['responses']))
                <h3>Odpovede</h3>
                @foreach($op['responses'] as $status => $resp)
                    @php $resp = $resolveRef($resp); @endphp
                    <div><b>{{ $status }}</b> — {{ $resp['description'] ?? '' }}</div>
                    @if(!empty($resp['content']))
                        @foreach($resp['content'] as $mime => $entry)
                            <div class="muted">{{ $mime }}</div>
                            {!! $renderSchema($entry['schema'] ?? []) !!}
                        @endforeach
                    @endif
                @endforeach
            @endif
        </div>
    @endforeach
@endforeach

@if(!empty($spec['components']['schemas']))
    <h2>Schémy</h2>
    @foreach($spec['components']['schemas'] as $name => $sch)
        <div class="endpoint">
            <h3 id="schema-{{ $name }}">{{ $name }}</h3>
            {!! $renderSchema($sch) !!}
        </div>
    @endforeach
@endif

</body>
</html>
