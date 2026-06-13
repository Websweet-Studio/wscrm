<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $demo->title }} - Demo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f4ed; }
        .embed-container { width: 100%; height: 100vh; display: flex; flex-direction: column; }
        .embed-header {
            background: #141413;
            color: #faf9f5;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .embed-header h1 { font-size: 14px; font-weight: 600; font-family: Georgia, serif; }
        .embed-header a {
            color: #c96442;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #c96442;
            padding: 4px 12px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .embed-header a:hover { background: #c96442; color: #faf9f5; }
        .embed-iframe-wrapper { flex: 1; position: relative; }
        .embed-iframe-wrapper iframe { width: 100%; height: 100%; border: none; }
        .embed-badges { display: flex; gap: 6px; align-items: center; }
        .embed-badge { background: #30302e; color: #b0aea5; font-size: 10px; padding: 2px 8px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="embed-container">
        <div class="embed-header">
            <div>
                <h1>{{ $demo->title }}</h1>
                <div class="embed-badges" style="margin-top: 4px;">
                    @if($demo->demoCategory)
                        <span class="embed-badge">{{ $demo->demoCategory->name }}</span>
                    @endif
                    @foreach($demo->demoPackages as $pkg)
                        <span class="embed-badge">{{ $pkg->name }}</span>
                    @endforeach
                </div>
            </div>
            <a href="{{ $demo->url }}" target="_blank" rel="noopener noreferrer">
                Buka Website &rarr;
            </a>
        </div>
        <div class="embed-iframe-wrapper">
            <iframe src="{{ $demo->url }}" title="{{ $demo->title }}" allowfullscreen loading="lazy" sandbox="allow-scripts allow-same-origin allow-popups allow-forms"></iframe>
        </div>
    </div>
</body>
</html>