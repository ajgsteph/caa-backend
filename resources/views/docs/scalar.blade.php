@php
    $config = json_encode([
        'theme' => 'purple',
        'layout' => 'modern',
        'hideDownloadButton' => false,
        'darkMode' => true,
        'metaData' => [
            'title' => 'CAA — API Reference',
            'description' => 'Documentation interactive de l\'API CAA',
        ],
        'authentication' => [
            'preferredSecurityScheme' => 'default',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAA — API Reference</title>
    <link rel="icon" href="data:,">
    <style>body { margin: 0; }</style>
</head>
<body>
    <script
        id="api-reference"
        type="application/json"
        data-url="{{ url('docs.openapi') }}"
        data-configuration="{{ $config }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
</body>
</html>
