@extends('layouts.app')

@section('content')
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Kotak masuk</p>
        <h1>Pesan Pelanggan</h1>
      </div>
    </div>

    <div class="admin-panel">
      <h2>Daftar Pesan ({{ $pesanList->count() }})</h2>
      @if ($pesanList->isEmpty())
      <p>Belum ada pesan masuk.</p>
      @else
      <div class="daftar-pesan-admin">
        @foreach ($pesanList as $pesan)
        <article class="pesan-admin">
          <div class="pesan-kepala">
            <div>
              <strong>{{ $pesan->subjek }}</strong>
              <p>{{ $pesan->nama }} / <a href="mailto:{{ $pesan->email }}">{{ $pesan->email }}</a> / {{ tanggal_wib($pesan->dibuat_pada, false) }}</p>
            </div>
            <form action="{{ route('admin.messages.destroy', $pesan->id) }}" method="post" data-konfirmasi="Hapus pesan dari {{ $pesan->nama }}?">
              @csrf
              <button type="submit" class="tautan-hapus">Hapus</button>
            </form>
          </div>
          <p class="pesan-isi">{!! nl2br(e($pesan->pesan)) !!}</p>
        </article>
        @endforeach
      </div>
      @endif
    </div>

    <div class="admin-panel">
      <h2>Daftar Langganan Email ({{ $langgananList->count() }})</h2>
      @if ($langgananList->isEmpty())
      <p>Belum ada pelanggan yang mendaftar.</p>
      @else
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead><tr><th>Email</th><th>Tanggal daftar</th><th>Aksi</th></tr></thead>
          <tbody>
            @foreach ($langgananList as $langganan)
            <tr>
              <td>{{ $langganan->email }}</td>
              <td>{{ tanggal_wib($langganan->dibuat_pada, false) }}</td>
              <td>
                <form action="{{ route('admin.subscribers.destroy', $langganan->id) }}" method="post" data-konfirmasi="Hapus email {{ $langganan->email }} dari daftar langganan?">
                  @csrf
                  <button type="submit" class="tautan-hapus">Hapus</button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
</section>
@endsection
