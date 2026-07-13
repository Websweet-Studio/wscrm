# Checklist
- [x] Endpoint `POST /api/agent/blog` mengembalikan 201 dengan data blog post saat token valid
- [x] Endpoint mengembalikan 401 saat tanpa token
- [x] Endpoint mengembalikan 401 saat token salah
- [x] Endpoint mengembalikan 422 saat validasi gagal (missing title/content/category)
- [x] Blog post tersimpan di database dengan benar
- [x] Token dikonfigurasi via `.env` bukan hardcode
