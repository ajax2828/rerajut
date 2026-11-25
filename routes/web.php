<?php

use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

// Storefront
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');

Route::get('/produk', [ProductController::class, 'index'])->name('produk.index');
Route::get('/produk/{slug}', [ProductController::class, 'show'])->name('produk.show');

Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang.index');
Route::post('/keranjang', [CartController::class, 'handle'])->name('keranjang.handle');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::get('/pesanan-sukses', [OrderController::class, 'success'])->name('pesanan.sukses');
Route::post('/upload-bukti', [OrderController::class, 'uploadProof'])->name('pesanan.upload');
Route::get('/lacak', [OrderController::class, 'track'])->name('lacak');
Route::get('/pesanan-saya', [OrderController::class, 'myOrders'])->name('pesanan.saya');
Route::get('/api/pesanan', [OrderController::class, 'apiStatus'])->name('api.pesanan');

Route::get('/kontak', [ContactController::class, 'show'])->name('kontak.show');
Route::post('/kontak', [ContactController::class, 'store'])->name('kontak.store');
Route::post('/langganan', [ContactController::class, 'subscribe'])->name('langganan.store');

// SEO
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Admin Auth
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes
Route::prefix('admin')->middleware('admin.auth')->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::post('/pesanan/{id}/status', [AdminOrderController::class, 'updateStatus'])
        ->whereNumber('id')
        ->name('admin.orders.status');
    Route::get('/pesanan/{kode}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/pesanan/{kode}/tolak-bukti', [AdminOrderController::class, 'rejectProof'])->name('admin.orders.reject_proof');

    Route::get('/produk', [AdminProductController::class, 'index'])->name('admin.products.index');
    Route::get('/produk/tambah', [AdminProductController::class, 'create'])->name('admin.products.create');
    Route::post('/produk', [AdminProductController::class, 'store'])->name('admin.products.store');
    Route::get('/produk/{id}/ubah', [AdminProductController::class, 'edit'])
        ->whereNumber('id')
        ->name('admin.products.edit');
    Route::post('/produk/{id}', [AdminProductController::class, 'update'])
        ->whereNumber('id')
        ->name('admin.products.update');
    Route::post('/produk/{id}/hapus', [AdminProductController::class, 'destroy'])
        ->whereNumber('id')
        ->name('admin.products.destroy');

    Route::get('/kategori', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/kategori', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::post('/kategori/{id}', [AdminCategoryController::class, 'update'])
        ->whereNumber('id')
        ->name('admin.categories.update');
    Route::post('/kategori/{id}/hapus', [AdminCategoryController::class, 'destroy'])
        ->whereNumber('id')
        ->name('admin.categories.destroy');

    Route::get('/pesan', [AdminMessageController::class, 'index'])->name('admin.messages.index');
    Route::post('/pesan/{id}/hapus', [AdminMessageController::class, 'destroyMessage'])
        ->whereNumber('id')
        ->name('admin.messages.destroy');
    Route::post('/langganan/{id}/hapus', [AdminMessageController::class, 'destroySubscriber'])
        ->whereNumber('id')
        ->name('admin.subscribers.destroy');

    Route::get('/akun', [AdminAccountController::class, 'show'])->name('admin.account.show');
    Route::post('/akun/profil', [AdminAccountController::class, 'updateProfile'])->name('admin.account.profile');
    Route::post('/akun/sandi', [AdminAccountController::class, 'updatePassword'])->name('admin.account.password');
});
