# Plan: Tambahkan Relasi Task ke Order/Service

## Summary

Menambahkan kolom `order_id` (foreign key nullable ke `orders`) pada tabel `tasks`, sehingga setiap tugas bisa dikaitkan ke layanan/order tertentu. Ini memungkinkan filter tugas berdasarkan layanan (misalnya "buat artikel untuk web X"), dan menampilkan relasi tersebut di halaman admin tasks.

## Current State

### Yang sudah ada
- **Tabel `tasks`**: `id`, `title`, `description`, `status`, `priority`, `due_date`, `assigned_user_id`, `assigned_department`, `created_by_user_id`, `task_category_id`, `qc_results`
- **Tabel `orders`**: `id`, `customer_id`, `domain_name`, `service_type`, `status`, dll. Scope `services()` = status active/suspended/expired/terminated.
- **Task model** (`app/Models/Task.php`): belum ada relasi ke Order.
- **TaskController** (`app/Http/Controllers/Admin/TaskController.php`): `index()` melempar `tasks`, `departments`, `categories`, `users`, `userDepartments`, `filters`, `editingTask` ke Vue.
- **Tasks Vue page** (`resources/js/pages/Admin/Tasks/Index.vue`): ada filter status, kategori, user, dept. Tidak ada filter order.
- **Halaman Services**: `GET /admin/services?status=active` menampilkan Order dengan scope `services()`.

### Yang perlu diubah
1. Database: tambah kolom `order_id` di `tasks`
2. Model: tambah relasi `order()` dan `hasMany('tasks')` di Order
3. Controller: terima `order_id` di store/update, eager-load `order.customer`, kirim data services ke Vue
4. Vue: tambah filter order, kolom order di tabel, select order di create/edit modal

---

## Proposed Changes

### Step 1: Migration — Tambah order_id ke tasks

**File baru:** `database/migrations/YYYY_MM_DD_HHMMSS_add_order_id_to_tasks_table.php`

```php
Schema::table('tasks', function (Blueprint $table) {
    $table->foreignId('order_id')->nullable()->after('task_category_id')
        ->constrained('orders')->nullOnDelete();
});
```

- `nullable()` karena tidak semua tugas harus terkait layanan
- `nullOnDelete()` agar task tetap ada jika order dihapus

### Step 2: Model — Task.php

**File:** `app/Models/Task.php`

Tambah:
- Kolom `'order_id'` ke `$fillable`
- Cast `'order_id' => 'integer'` (opsional, tipe int sudah otomatis)
- Relasi:
```php
public function order(): BelongsTo
{
    return $this->belongsTo(Order::class);
}
```

Import: `use App\Models\Order;`

### Step 3: Model — Order.php

**File:** `app/Models/Order.php`

Tambah relasi:
```php
public function tasks(): HasMany
{
    return $this->hasMany(Task::class);
}
```

### Step 4: Controller — TaskController.php

**File:** `app\Http\Controllers\Admin\TaskController.php`

#### `index()`:
- Tambahkan `'order_id'` ke eager load: `->with(['assignedUser.employee', 'creator', 'category', 'order.customer'])`
- Filter: `->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->order_id))`
- Ambil data services: `$services = Order::services()->with('customer')->orderBy('domain_name')->get(['id', 'domain_name', 'service_type', 'customer_id']);`
- Tambahkan ke return Inertia: `'services' => $services`
- Tambahkan `'order_id'` ke `filters` array

#### `store()`:
- Tambah validasi: `'order_id' => 'nullable|exists:orders,id'`
- (Sudah otomatis via `$fillable`)

#### `update()`:
- Tambah validasi: `'order_id' => 'nullable|exists:orders,id'`
- (Sudah otomatis via `$fillable`)

### Step 5: Vue — Index.vue

**File:** `resources/js/pages/Admin/Tasks/Index.vue`

#### Interface:
- Tambahkan field ke interface `Task`: `order_id?: number | null; order?: { id: number; domain_name?: string; service_type?: string; customer?: { name: string } } | null;`
- Tambahkan interface `Order` (service): `id`, `domain_name`, `service_type`, `customer?`

#### Props:
- Tambahkan `services: Order[]` ke interface `Props`

#### Filters (baris 466-499):
- Tambahkan select dropdown filter **Order** sebelum tombol "Cari":
```html
<div>
  <select id="orderFilter" v-model="orderFilter" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
    <option value="">Semua Layanan</option>
    <option v-for="svc in services" :key="svc.id" :value="svc.id">
      {{ svc.domain_name || 'Order #' + svc.id }} ({{ svc.customer?.name || '-' }})
    </option>
  </select>
</div>
```
- Ubah grid dari `grid-cols-5` menjadi `grid-cols-6` (atau buat baris baru)

#### State vars:
- `const orderFilter = ref(props.filters?.order_id || '');`

#### handleSearch():
- Tambah: `order_id: orderFilter.value || undefined,`

#### Tabel — Kolom baru (setelah Assigned To, sebelum Aksi):
```html
<TableHead>Order</TableHead>
```
```html
<TableCell>
  <span v-if="task.order" class="text-xs">
    {{ task.order.domain_name || 'Order #' + task.order.id }}
    <span class="text-muted-foreground">({{ task.order.customer?.name || '-' }})</span>
  </span>
  <span v-else class="text-muted-foreground text-xs">-</span>
</TableCell>
```

#### Create Modal (createForm):
- Tambah `order_id: '' as number | '' | null` ke `useForm`
- Tambah field select Order setelah select Kategori:
```html
<div>
  <Label for="order_id" class="mb-2 block">Layanan (Opsional)</Label>
  <select id="order_id" v-model="createForm.order_id" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
    <option value="">— Tanpa Layanan —</option>
    <option v-for="svc in services" :key="svc.id" :value="svc.id">
      {{ svc.domain_name || 'Order #' + svc.id }} ({{ svc.customer?.name || '-' }})
    </option>
  </select>
</div>
```

#### submitCreate():
- Tambah `order_id: createForm.order_id || undefined` ke payload

#### Edit Modal (editForm):
- Tambah `order_id: '' as number | '' | null` ke `useForm`
- Tambah field select Order setelah select Kategori (mirip dengan create modal)

#### openEditModal():
- Tambah: `editForm.order_id = task.order_id || '';`

#### submitEdit():
- Tambah `order_id: editForm.order_id || undefined` ke payload

---

## Assumptions & Decisions

1. **Scope layanan**: Menggunakan `Order::services()` (status active, suspended, expired, terminated) — sesuai dengan URL yang diberikan user (`/admin/services?status=active`).
2. **Order nullable**: Task tidak wajib punya relasi ke order — banyak task internal (admin, maintenance) tidak terkait layanan spesifik.
3. **Nama filter/label "Layanan"**: Menggunakan istilah "Layanan" di UI (bukan "Order") karena konteksnya services, konsisten dengan sidebar menu "Services".
4. **Tidak perlu migration terpisah untuk index**: Index pada `order_id` otomatis dibuat oleh `foreignId()` di Laravel.
5. **Tampilan di tabel**: Kolom baru diletakkan setelah "Assigned To" dan sebelum "Aksi" — agar tidak mengganggu layout yang sudah ada.

## Verification

1. **Migration**: `php artisan migrate` — pastikan kolom `order_id` muncul di tabel `tasks`
2. **Model**: Cek relasi `$task->order` dan `$order->tasks` berfungsi
3. **Create task**: Buka `/admin/tasks`, klik "Buat Tugas", pilih Layanan, simpan — task baru memiliki `order_id`
4. **Edit task**: Edit task, ubah layanan, simpan
5. **Filter**: Pilih layanan dari dropdown filter, klik Cari — hanya task untuk layanan itu yang muncul
6. **Tabel**: Kolom "Order" menampilkan domain_name atau "Order #ID" dengan nama customer
7. **Null case**: Buat task tanpa layanan, pastikan muncul dengan "-" di kolom order dan filter "Semua Layanan" tetap menampilkannya
