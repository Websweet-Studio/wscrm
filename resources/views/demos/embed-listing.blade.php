<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Demo Website</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #f5f4ed;
            color: #141413;
            line-height: 1.6;
        }
        .container { max-width: 960px; margin: 0 auto; padding: 20px 16px; }

        /* Filters */
        .filters {
            background: #faf9f5;
            border: 1px solid #f0eee6;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filters input[type="text"],
        .filters select {
            background: #ffffff;
            border: 1px solid #e8e6dc;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
            color: #141413;
            outline: none;
            flex: 1;
            min-width: 120px;
        }
        .filters input[type="text"] { flex: 2; }
        .filters .clear-btn {
            background: none;
            border: none;
            color: #c96442;
            font-size: 12px;
            cursor: pointer;
            padding: 4px 8px;
        }

        /* Info */
        .info { font-size: 12px; color: #5e5d59; margin-bottom: 16px; }

        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #f0eee6;
            border-radius: 14px;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .card-img {
            position: relative;
            aspect-ratio: 16/9;
            background: #e8e6dc;
            overflow: hidden;
        }
        .card-img img { width: 100%; height: 100%; object-fit: cover; }
        .card-img .placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            color: #b0aea5;
            font-size: 32px;
        }
        .card-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #c96442;
            color: #faf9f5;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        .card-body { padding: 14px; }
        .card-body h3 {
            font-size: 15px;
            font-weight: 600;
            font-family: Georgia, serif;
            color: #141413;
            margin-bottom: 6px;
        }
        .card-body p {
            font-size: 12px;
            color: #5e5d59;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .packages { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 10px; }
        .packages span {
            border: 1px solid #e8e6dc;
            color: #4d4c48;
            padding: 1px 7px;
            border-radius: 5px;
            font-size: 10px;
        }
        .card-actions { display: flex; gap: 8px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary { background: #c96442; color: #faf9f5; flex: 1; }
        .btn-primary:hover { background: #b5573a; }
        .btn-outline { background: #faf9f5; color: #4d4c48; border: 1px solid #e8e6dc; }
        .btn-outline:hover { background: #e8e6dc; }

        /* Empty */
        .empty {
            text-align: center;
            padding: 48px 20px;
            color: #5e5d59;
        }
        .empty-icon { font-size: 48px; color: #b0aea5; margin-bottom: 12px; }

        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-top: 16px;
            flex-wrap: wrap;
        }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 8px;
            border-radius: 8px;
            font-size: 13px;
            text-decoration: none;
            border: 1px solid #e8e6dc;
            color: #4d4c48;
            background: #ffffff;
            transition: all 0.2s;
        }
        .pagination a:hover { background: #f0eee6; }
        .pagination .active {
            background: #c96442;
            color: #faf9f5;
            border-color: #c96442;
        }
        .pagination .disabled {
            opacity: 0.4;
            pointer-events: none;
        }

        /* Preview iframe - fullscreen like YouTube */
        .preview-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: #000;
        }
        .preview-overlay.active { display: flex; flex-direction: column; }
        .preview-modal {
            background: #141413;
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
            flex: 1;
        }
        .preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 16px;
            background: #1a1a1a;
            color: #faf9f5;
            flex-shrink: 0;
            gap: 8px;
        }
        .preview-header h3 { font-size: 14px; font-family: Georgia, serif; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
        .preview-close {
            background: none;
            border: 1px solid #444;
            color: #faf9f5;
            font-size: 18px;
            cursor: pointer;
            padding: 4px 12px;
            border-radius: 6px;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .preview-close:hover { background: #333; }
        .preview-header a {
            color: #c96442;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #c96442;
            padding: 4px 12px;
            border-radius: 8px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .preview-header a:hover { background: #c96442; color: #faf9f5; }
        .preview-iframe { flex: 1; border: none; width: 100%; background: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Filters -->
        <form method="GET" action="/demo-web/embed" class="filters">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari demo website...">
            @if(count($categories) > 0)
                <select name="category">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat['slug'] }}" {{ ($filters['category'] ?? '') == $cat['slug'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                    @endforeach
                </select>
            @endif
            @if(count($packages) > 0)
                <select name="package">
                    <option value="">Semua Paket</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg['slug'] }}" {{ ($filters['package'] ?? '') == $pkg['slug'] ? 'selected' : '' }}>{{ $pkg['name'] }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary" style="font-size:12px;padding:8px 14px;">Filter</button>
            @if($filters['search'] || $filters['category'] || $filters['package'])
                <a href="/demo-web/embed" class="clear-btn">Reset</a>
            @endif
        </form>

        @if($demos->total() > 0)
            <div class="info">
                Menampilkan {{ $demos->firstItem() }}-{{ $demos->lastItem() }} dari {{ $demos->total() }} demo
            </div>
        @endif

        @if($demos->count() > 0)
            <div class="grid">
                @foreach($demos as $demo)
                    <div class="card">
                        <div class="card-img">
                            @if($demo['featured_image'])
                                <img src="{{ $demo['featured_image'] }}" alt="{{ $demo['title'] }}" loading="lazy">
                            @else
                                <div class="placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                                </div>
                            @endif
                            @if($demo['category'])
                                <span class="card-badge">{{ $demo['category'] }}</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <h3>{{ $demo['title'] }}</h3>
                            @if($demo['description'])
                                <p>{{ $demo['description'] }}</p>
                            @endif
                            @if(count($demo['packages']) > 0)
                                <div class="packages">
                                    @foreach($demo['packages'] as $pkg)
                                        <span>{{ $pkg['name'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="card-actions">
                                <a href="{{ $demo['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                                    Lihat Demo
                                </a>
                                <button type="button" class="btn btn-outline" onclick="openPreview({{ $demo['id'] }}, '{{ addslashes($demo['title']) }}', '{{ $demo['url'] }}')">Preview</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty">
                <div class="empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                </div>
                <h3 style="color:#141413;font-family:Georgia,serif;margin-bottom:8px;">Tidak ada demo ditemukan</h3>
                <p>{{ ($filters['search'] || $filters['category'] || $filters['package']) ? 'Coba sesuaikan filter pencarian Anda.' : 'Belum ada demo website yang tersedia saat ini.' }}</p>
                @if($filters['search'] || $filters['category'] || $filters['package'])
                    <a href="/demo-web/embed" class="btn btn-outline" style="margin-top:12px;">Reset Filter</a>
                @endif
            </div>
        @endif

        <!-- Pagination -->
        @if($demos->lastPage() > 1)
            <div class="pagination">
                @if($demos->previousPageUrl())
                    <a href="{{ $demos->previousPageUrl() }}">&laquo; Sebelumnya</a>
                @else
                    <span class="disabled">&laquo; Sebelumnya</span>
                @endif

                @for($i = 1; $i <= $demos->lastPage(); $i++)
                    @if($demos->lastPage() <= 7 || $i === 1 || $i === $demos->lastPage() || abs($i - $demos->currentPage()) <= 1)
                        @if($i === $demos->currentPage())
                            <span class="active">{{ $i }}</span>
                        @else
                            <a href="{{ $demos->url($i) }}">{{ $i }}</a>
                        @endif
                    @elseif(abs($i - $demos->currentPage()) === 2)
                        <span style="padding:0 4px;color:#87867f;">...</span>
                    @endif
                @endfor

                @if($demos->nextPageUrl())
                    <a href="{{ $demos->nextPageUrl() }}">Selanjutnya &raquo;</a>
                @else
                    <span class="disabled">Selanjutnya &raquo;</span>
                @endif
            </div>
        @endif
    </div>

    <!-- Preview Modal - Fullscreen like YouTube -->
    <div id="previewOverlay" class="preview-overlay">
        <div class="preview-modal">
            <div class="preview-header">
                <h3 id="previewTitle">Preview</h3>
                <div style="display:flex;gap:8px;align-items:center;flex-shrink:0;">
                    <a id="previewLink" href="#" target="_blank" rel="noopener noreferrer">Buka Website &rarr;</a>
                    <button class="preview-close" onclick="closePreview()">&#10005; Tutup</button>
                </div>
            </div>
            <iframe id="previewIframe" class="preview-iframe" src="" sandbox="allow-scripts allow-same-origin allow-popups allow-forms" loading="lazy"></iframe>
        </div>
    </div>

    <script>
        const overlay = document.getElementById('previewOverlay');

        function openPreview(id, title, url) {
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewLink').href = url;
            document.getElementById('previewIframe').src = url;
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            // Request fullscreen like YouTube
            if (overlay.requestFullscreen) {
                overlay.requestFullscreen();
            } else if (overlay.webkitRequestFullscreen) {
                overlay.webkitRequestFullscreen();
            } else if (overlay.msRequestFullscreen) {
                overlay.msRequestFullscreen();
            }
        }

        function closePreview(e) {
            if (e && e.target !== e.currentTarget) return;
            // Exit fullscreen if active
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else if (document.webkitFullscreenElement) {
                document.webkitExitFullscreen();
            }
            overlay.classList.remove('active');
            document.getElementById('previewIframe').src = '';
            document.body.style.overflow = '';
        }

        // Also exit fullscreen on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
                overlay.classList.remove('active');
                document.getElementById('previewIframe').src = '';
                document.body.style.overflow = '';
            }
        });

        // When fullscreen exits (e.g. user presses Esc natively), close the preview too
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                overlay.classList.remove('active');
                document.getElementById('previewIframe').src = '';
                document.body.style.overflow = '';
            }
        });
        document.addEventListener('webkitfullscreenchange', function() {
            if (!document.webkitFullscreenElement) {
                overlay.classList.remove('active');
                document.getElementById('previewIframe').src = '';
                document.body.style.overflow = '';
            }
        });
    </script>
</body>
</html>