(() => {
  "use strict";

  const buildUrl = (endpoint, params) => {
    const url = new URL(endpoint, window.location.origin);
    Object.entries(params || {}).forEach(([key, value]) => {
      if (value === undefined || value === null || String(value).trim() === "") return;
      url.searchParams.set(key, String(value));
    });
    return url.toString();
  };

  const syncByTanggal = async (tanggal, { debug = false, signal } = {}) => {
    const url = buildUrl("/antrian/ambil-data-kantor", {
      tgl_uji: tanggal,
      debug: debug ? 1 : "",
    });
    return window.AppFetch.fetchJson(url, { signal });
  };

  window.OfficeApi = Object.freeze({ syncByTanggal });
})();

