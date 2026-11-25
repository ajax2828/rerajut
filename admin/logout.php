<?php
require_once __DIR__ . '/../includes/fungsi.php';

unset($_SESSION['admin']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_nama']);
session_regenerate_id(true);
flash_set('sukses', 'Kamu sudah keluar dari halaman admin.');
redirect(BASE_URL . '/index.php');
