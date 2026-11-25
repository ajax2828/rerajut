<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kategori')) {
            Schema::create('kategori', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nama', 80);
                $table->string('slug', 80)->unique();
            });
        }

        if (!Schema::hasTable('produk')) {
            Schema::create('produk', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('kategori_id');
                $table->string('nama', 150);
                $table->string('slug', 170)->unique();
                $table->text('deskripsi');
                $table->integer('harga');
                $table->integer('harga_asli');
                $table->string('ukuran', 20);
                $table->string('kondisi', 40);
                $table->integer('stok')->default(1);
                $table->string('gambar', 120);
                $table->boolean('unggulan')->default(false);
                $table->dateTime('dibuat_pada')->useCurrent();

                $table->index(['unggulan', 'stok'], 'idx_produk_unggulan_stok');
                $table->index('stok', 'idx_produk_stok');
                $table->foreign('kategori_id', 'fk_produk_kategori')
                    ->references('id')
                    ->on('kategori');
            });
        }

        if (!Schema::hasTable('pesanan')) {
            Schema::create('pesanan', function (Blueprint $table) {
                $table->increments('id');
                $table->string('kode', 30)->unique();
                $table->string('nama', 120);
                $table->string('email', 150);
                $table->string('telepon', 30);
                $table->text('alamat');
                $table->string('kota', 80);
                $table->string('provinsi', 80);
                $table->string('kode_pos', 10);
                $table->text('catatan')->nullable();
                $table->string('metode_bayar', 40);
                $table->integer('subtotal');
                $table->integer('ongkir');
                $table->integer('total');
                $table->string('status', 40)->default('Menunggu Pembayaran');
                $table->string('bukti_bayar', 160)->nullable();
                $table->dateTime('batas_bayar')->nullable();
                $table->dateTime('dibayar_pada')->nullable();
                $table->dateTime('dibuat_pada')->useCurrent();

                $table->index(['status', 'batas_bayar'], 'idx_pesanan_status_batas');
            });
        }

        if (!Schema::hasTable('pesanan_item')) {
            Schema::create('pesanan_item', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pesanan_id');
                $table->unsignedInteger('produk_id');
                $table->string('nama_produk', 150);
                $table->string('ukuran', 20);
                $table->integer('harga');
                $table->integer('jumlah');
                $table->integer('subtotal');

                $table->index('produk_id', 'idx_item_produk');
                $table->foreign('pesanan_id', 'fk_item_pesanan')
                    ->references('id')
                    ->on('pesanan')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('pesan_kontak')) {
            Schema::create('pesan_kontak', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nama', 120);
                $table->string('email', 150);
                $table->string('subjek', 150);
                $table->text('pesan');
                $table->dateTime('dibuat_pada')->useCurrent();
            });
        }

        if (!Schema::hasTable('langganan')) {
            Schema::create('langganan', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email', 150)->unique();
                $table->dateTime('dibuat_pada')->useCurrent();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username', 60)->unique();
                $table->string('password', 255);
                $table->string('nama', 120);
                $table->dateTime('dibuat_pada')->useCurrent();
            });
        }

        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('pesanan_item');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('produk');
        Schema::dropIfExists('kategori');
        Schema::dropIfExists('pesan_kontak');
        Schema::dropIfExists('langganan');
        Schema::dropIfExists('users');
    }
};
