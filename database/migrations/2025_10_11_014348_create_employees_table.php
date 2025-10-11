<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nik')->unique()->comment('Nomor Induk Karyawan');
            $table->string('position')->comment('Jabatan');
            $table->string('department')->comment('Departemen');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('hire_date')->comment('Tanggal Bergabung');
            $table->decimal('salary', 12, 2)->nullable()->comment('Gaji Pokok');
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
