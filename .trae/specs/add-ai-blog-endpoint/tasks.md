# Tasks
- [x] Task 1: Buat middleware `AgentTokenAuth` untuk validasi Bearer token dari config
  - [x] Baca Bearer token dari `Authorization` header
  - [x] Bandingkan dengan `config('services.agent.token')`
  - [x] Return 401 jika tidak cocok atau tidak ada
- [x] Task 2: Buat controller `AgentBlogController` dengan method `store`
  - [x] Validasi input: title (required), content (required), blog_category_id (required|exists), type (required|in:article,announcement,news), status (nullable|in:draft,published), excerpt (nullable), meta_data (nullable|array)
  - [x] Generate slug dari title
  - [x] Set user_id dari admin user pertama
  - [x] Return response JSON 201 dengan data blog post
- [x] Task 3: Register route dan config
  - [x] Tambah route `POST /api/agent/blog` di `routes/web.php` dengan middleware `agent.auth`
  - [x] Tambah key `agent` di `config/services.php` dengan token dari env
  - [x] Daftarkan middleware alias `agent.auth` di `bootstrap/app.php`

# Task Dependencies
- No dependencies — semua task bisa jalan parallel
