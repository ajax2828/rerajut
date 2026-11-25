(function () {
  var kunciPembeli = "rerajut_pembeli";
  var kunciPesanan = "rerajut_pesanan";

  var tombol = document.querySelector("[data-menu]");
  var nav = document.querySelector("[data-nav]");

  if (tombol && nav) {
    tombol.addEventListener("click", function () {
      var terbuka = nav.classList.toggle("buka");
      tombol.setAttribute("aria-expanded", terbuka ? "true" : "false");
    });
  }

  document.querySelectorAll('input[type="number"][max]').forEach(function (input) {
    input.addEventListener("input", function () {
      var maks = parseInt(input.getAttribute("max"), 10);
      var nilai = parseInt(input.value, 10);
      if (!isNaN(maks) && nilai > maks) {
        input.value = maks;
      }
      if (!isNaN(nilai) && nilai < 1) {
        input.value = 1;
      }
    });
  });

  document.querySelectorAll("form[data-konfirmasi]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      if (!window.confirm(form.getAttribute("data-konfirmasi"))) {
        event.preventDefault();
      }
    });
  });

  var flash = document.querySelector(".flash");
  if (flash) {
    window.setTimeout(function () {
      flash.style.display = "none";
    }, 6000);
  }

  function bacaPesanan() {
    try {
      var data = JSON.parse(localStorage.getItem(kunciPesanan) || "[]");
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
  }

  function simpanKodePesanan(kode) {
    if (!kode) {
      return;
    }
    var daftar = bacaPesanan().filter(function (item) {
      return item !== kode;
    });
    daftar.unshift(kode);
    localStorage.setItem(kunciPesanan, JSON.stringify(daftar.slice(0, 20)));
  }

  function tampilkanTautanPesanan() {
    var ada = bacaPesanan().length > 0;
    document.querySelectorAll(".tautan-pesanan-saya").forEach(function (tautan) {
      tautan.hidden = !ada;
    });
  }

  tampilkanTautanPesanan();

  var formCheckout = document.getElementById("form-checkout");
  if (formCheckout) {
    var namaField = ["nama", "email", "telepon", "alamat", "kota", "provinsi", "kode_pos", "catatan"];
    try {
      var tersimpan = JSON.parse(localStorage.getItem(kunciPembeli) || "{}");
      namaField.forEach(function (id) {
        var el = document.getElementById(id);
        if (el && !el.value && tersimpan[id]) {
          el.value = tersimpan[id];
        }
      });
    } catch (e) {
      localStorage.removeItem(kunciPembeli);
    }
    formCheckout.addEventListener("submit", function () {
      var data = {};
      namaField.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
          data[id] = el.value.trim();
        }
      });
      localStorage.setItem(kunciPembeli, JSON.stringify(data));
    });
  }

  var penandaPesanan = document.querySelector("[data-simpan-pesanan]");
  if (penandaPesanan) {
    simpanKodePesanan(penandaPesanan.getAttribute("data-simpan-pesanan"));
    tampilkanTautanPesanan();
  }

  var wadahPesanan = document.getElementById("daftar-pesanan-saya");
  if (wadahPesanan) {
    var api = wadahPesanan.getAttribute("data-api");
    var lacakUrl = wadahPesanan.getAttribute("data-lacak-url") || "/lacak";
    var suksesUrl = wadahPesanan.getAttribute("data-sukses-url") || "/pesanan-sukses";
    var infoMuat = document.getElementById("pesanan-saya-muat");
    var infoKosong = document.getElementById("pesanan-saya-kosong");
    var daftarKode = bacaPesanan();

    function formatRupiah(angka) {
      return "Rp " + Number(angka).toLocaleString("id-ID");
    }

    function buatElemen(tag, kelas, teks) {
      var el = document.createElement(tag);
      if (kelas) {
        el.className = kelas;
      }
      if (teks !== undefined) {
        el.textContent = teks;
      }
      return el;
    }

    function tampilkanKosong() {
      if (infoMuat) {
        infoMuat.hidden = true;
      }
      if (infoKosong) {
        infoKosong.hidden = false;
      }
    }

    function gambarPesanan(pesanan) {
      var kartu = buatElemen("article", "kartu-pesanan");
      var kepala = buatElemen("div", "kartu-pesanan-kepala");
      var kiri = buatElemen("div", "");
      kiri.appendChild(buatElemen("p", "kartu-pesanan-kode", pesanan.kode));
      kiri.appendChild(buatElemen("span", "kartu-pesanan-tanggal", pesanan.tanggal));
      kepala.appendChild(kiri);
      kepala.appendChild(buatElemen("span", "status-lencana", pesanan.status));
      kartu.appendChild(kepala);

      var daftar = buatElemen("ul", "kartu-pesanan-item");
      pesanan.items.forEach(function (item) {
        var li = document.createElement("li");
        var label = buatElemen("span", "", item.nama + " ukuran " + item.ukuran + " x " + item.jumlah);
        li.appendChild(label);
        li.appendChild(buatElemen("strong", "", formatRupiah(item.subtotal)));
        daftar.appendChild(li);
      });
      kartu.appendChild(daftar);

      var kaki = buatElemen("div", "kartu-pesanan-kaki");
      var info = buatElemen("div", "kartu-pesanan-info");
      info.appendChild(buatElemen("span", "", "Metode " + pesanan.metode));
      info.appendChild(buatElemen("strong", "", formatRupiah(pesanan.total)));
      kaki.appendChild(info);

      var aksi = buatElemen("div", "kartu-pesanan-aksi");
      var lacak = buatElemen("a", "tombol tombol-kecil", "Lacak Pesanan");
      lacak.href = lacakUrl + "?kode=" + encodeURIComponent(pesanan.kode);
      aksi.appendChild(lacak);

      if (pesanan.bisa_upload) {
        var upload = buatElemen("a", "tombol tombol-kecil tombol-garis", "Upload Bukti");
        upload.href = suksesUrl + "?kode=" + encodeURIComponent(pesanan.kode) + "#pembayaran";
        aksi.appendChild(upload);
      }

      if (pesanan.bukti) {
        aksi.appendChild(buatElemen("span", "info-bukti", "Bukti pembayaran sudah diunggah"));
      } else if (pesanan.lewat_batas) {
        aksi.appendChild(buatElemen("span", "info-lewat", "Batas upload bukti sudah lewat"));
      }

      kaki.appendChild(aksi);
      kartu.appendChild(kaki);
      return kartu;
    }

    if (!daftarKode.length) {
      tampilkanKosong();
    } else {
      Promise.all(daftarKode.map(function (kode) {
        return fetch(api + "?kode=" + encodeURIComponent(kode), { cache: "no-store" })
          .then(function (respons) {
            return respons.json();
          })
          .catch(function () {
            return { ditemukan: false };
          });
      })).then(function (hasil) {
        if (infoMuat) {
          infoMuat.hidden = true;
        }
        var valid = hasil.filter(function (pesanan) {
          return pesanan && pesanan.ditemukan;
        });
        if (!valid.length) {
          tampilkanKosong();
          return;
        }
        valid.forEach(function (pesanan) {
          wadahPesanan.appendChild(gambarPesanan(pesanan));
        });
      });
    }
  }
})();
