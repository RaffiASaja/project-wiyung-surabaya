(() => {
  "use strict";

  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const todayIso = () => {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
  };

  document.addEventListener("DOMContentLoaded", () => {
    const inputTanggal = document.getElementById("tanggal_uji");
    if (inputTanggal && !String(inputTanggal.value || "").trim()) {
      inputTanggal.value = todayIso();
    }

    qsa("[data-dashboard-table]").forEach((root) => window.DashboardTable.init(root));

    // Jika operator mengganti tanggal filter, refresh tabel (tanpa sync kantor).
    if (inputTanggal) {
      inputTanggal.addEventListener("change", () => {
        qsa("[data-dashboard-table]").forEach((root) => window.DashboardTable.init(root));
      });
    }
  });

  // Optional: tombol manual "Tampilkan Data" (sync kantor berdasarkan tanggal)
  const btnAmbil = document.getElementById("btnAmbilData");
  const inputTanggal = document.getElementById("tanggal_uji");
  let autoSyncTimer = null;
  let lastSyncTanggal = null;

  const refreshTables = () => {
    const tables = Array.from(document.querySelectorAll("[data-dashboard-table]"));
    if (tables.length) {
      tables.forEach((t) => window.DashboardTable.init(t));
      return true;
    }
    return false;
  };

  const stopAutoSync = () => {
    if (autoSyncTimer) {
      clearInterval(autoSyncTimer);
      autoSyncTimer = null;
    }
  };

  const startAutoSync = () => {
    if (autoSyncTimer) return;
    autoSyncTimer = setInterval(async () => {
      if (!lastSyncTanggal) return;
      try {
        const json = await window.OfficeApi.syncByTanggal(lastSyncTanggal, { debug: false });
        if (json && json.status) {
          refreshTables();
        }
      } catch (e) {
        // silent: auto sync should not disrupt operator
      }
    }, 5 * 60 * 1000);
  };

  if (inputTanggal) {
    inputTanggal.addEventListener("change", () => {
      // ganti tanggal => stop auto sync sampai sync manual berhasil untuk tanggal baru
      lastSyncTanggal = null;
      stopAutoSync();
    });
  }

  if (btnAmbil && inputTanggal) {
    btnAmbil.addEventListener("click", async () => {
      const tanggal = String(inputTanggal.value || "").trim();
      if (!tanggal) {
        alert("Pilih tanggal dulu!");
        return;
      }

      const debug = new URLSearchParams(window.location.search).get("debug") === "1";

      try {
        const json = await window.OfficeApi.syncByTanggal(tanggal, { debug });

        if (!json.status) {
          const msg = json?.sync?.error || json?.message || "Gagal ambil data";
          alert(msg);
          // eslint-disable-next-line no-console
          console.error("[ambil-data-kantor] failed:", json);
          return;
        }

        alert("Berhasil");

        lastSyncTanggal = tanggal;
        startAutoSync();

        if (!refreshTables()) {
          location.reload();
        }
      } catch (err) {
        alert("Gagal ambil data");
        // eslint-disable-next-line no-console
        console.error(err);
      }
    });
  }
})();
