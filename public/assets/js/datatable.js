(() => {
  "use strict";

  const qs = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const escapeHtml = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const debounce = (fn, waitMs = 350) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), waitMs);
    };
  };

  const buildUrl = (endpoint, params) => {
    const url = new URL(endpoint, window.location.origin);
    Object.entries(params || {}).forEach(([k, v]) => {
      if (v === undefined || v === null || String(v).trim() === "") return;
      url.searchParams.set(k, String(v));
    });
    return url.toString();
  };

  const todayIso = () => {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
  };

  const getTglUji = () => {
    const el = document.getElementById("tanggal_uji");
    const val = String(el?.value || "").trim();
    return val || todayIso();
  };

  const renderEmptyRow = ({ colCount, title, subtitle }) => {
    const safeTitle = escapeHtml(title);
    const safeSubtitle = subtitle ? escapeHtml(subtitle) : "";

    return `
      <tr>
        <td colspan="${colCount}">
          <div class="table-empty">
            <div class="table-empty__title">${safeTitle}</div>
            ${safeSubtitle ? `<div class="table-empty__subtitle">${safeSubtitle}</div>` : ""}
          </div>
        </td>
      </tr>
    `;
  };

  const renderBelumRow = (row, nomor) => {
    const id = Number(row.id);
    return `
      <tr>
        <td>${nomor}</td>
        <td>${escapeHtml(row.nama)}</td>
        <td>${escapeHtml(row.no_uji)}</td>
        <td>${escapeHtml(row.nomor_kendaraan ?? "-")}</td>
        <td>${escapeHtml(row.office_tgl_uji ?? "-")}</td>
        <td>${escapeHtml(row.pos ?? "-")}</td>
        <td>
          <a href="/antrian/panggil/${id}" class="btn-call" onclick="return confirm('Yakin ingin memanggil nomor ini?')">
            <img src="https://img.icons8.com/ios-glyphs/30/phone-disconnected.png" width="20" alt="">
            Panggil
          </a>
        </td>
      </tr>
    `;
  };

  const renderStatusBadge = (status) => {
    const st = String(status ?? "").toLowerCase();
    const label = st === "selesai" ? "Selesai" : "Dipanggil";
    const cls = st === "selesai" ? "status-badge done" : "status-badge call";
    return `<span class="${cls}">${escapeHtml(label)}</span>`;
  };

  const renderSudahRow = (row, nomor) => {
    const id = Number(row.id);
    return `
      <tr>
        <td>${nomor}</td>
        <td>${escapeHtml(row.nama)}</td>
        <td>${escapeHtml(row.no_uji)}</td>
        <td>${escapeHtml(row.nomor_kendaraan ?? "-")}</td>
        <td>${escapeHtml(row.pos ?? "-")}</td>
        <td>${renderStatusBadge(row.status)}</td>
        <td>${escapeHtml(row.waktu_panggil ?? "-")}</td>
        <td class="table-actions">
          <a class="btn-mini info" href="/antrian/panggil-ulang/${id}" title="Panggil ulang"><img src="https://img.icons8.com/ios-glyphs/30/phone-disconnected.png" width="20" alt=""></a>
          <a class="btn-mini success" href="/antrian/selesai/${id}" onclick="return confirm('Yakin ingin menyelesaikan antrian ini?')" title="Selesai">✓</a>
        </td>
      </tr>
    `;
  };

  const renderPagination = ({ container, page, pageCount, onGo }) => {
    if (!container) return;
    if (!pageCount || pageCount <= 1) {
      container.innerHTML = "";
      return;
    }

    const clampedPage = Math.min(Math.max(1, page), pageCount);
    const makeBtn = (label, targetPage, disabled = false, active = false) => `
      <button
        class="pager-btn ${active ? "active" : ""}"
        type="button"
        data-page="${targetPage}"
        ${disabled ? "disabled" : ""}
      >${escapeHtml(label)}</button>
    `;

    const windowSize = 2;
    const start = Math.max(1, clampedPage - windowSize);
    const end = Math.min(pageCount, clampedPage + windowSize);

    let html = `<div class="pager">`;
    html += makeBtn("‹", clampedPage - 1, clampedPage === 1);

    if (start > 1) {
      html += makeBtn("1", 1, false, clampedPage === 1);
      if (start > 2) html += `<span class="pager-ellipsis">…</span>`;
    }

    for (let p = start; p <= end; p++) {
      html += makeBtn(String(p), p, false, p === clampedPage);
    }

    if (end < pageCount) {
      if (end < pageCount - 1) html += `<span class="pager-ellipsis">…</span>`;
      html += makeBtn(String(pageCount), pageCount, false, clampedPage === pageCount);
    }

    html += makeBtn("›", clampedPage + 1, clampedPage === pageCount);
    html += `</div>`;

    container.innerHTML = html;

    qsa("[data-page]", container).forEach((btn) => {
      btn.addEventListener("click", () => {
        const target = Number(btn.getAttribute("data-page") || "1");
        if (!Number.isFinite(target)) return;
        onGo(Math.min(Math.max(1, target), pageCount));
      });
    });
  };

  const init = (root) => {
    if (!root) return;

    // Prevent duplicate listeners when re-initializing after sync.
    if (root.__dashboardLoadData) {
      root.__dashboardLoadData(1);
      return;
    }

    const endpoint = root.getAttribute("data-endpoint");
    const kind = (root.getAttribute("data-kind") || "").toLowerCase(); // belum | sudah

    const scope = root.closest(".card") || root.parentElement || document;
    const tbody = qs("[data-dashboard-tbody]", scope);
    const pager = qs("[data-dashboard-pager]", scope);
    const searchInput = qs("[data-dashboard-search]", scope);
    const sortSelect = qs("[data-dashboard-sort]", scope);
    const perPageSelect = qs("[data-dashboard-perpage]", scope);
    const metaLabel = qs("[data-dashboard-meta]", scope);

    if (!endpoint || !tbody) return;

    const colCount = Number(root.getAttribute("data-colcount") || "1");
    let lastMeta = { total: 0, page: 1, perPage: 20, pageCount: 1 };
    const debug = new URLSearchParams(window.location.search).get("debug") === "1";

    let lastController = null;

    const setLoading = (isLoading) => {
      root.classList.toggle("is-loading", Boolean(isLoading));
    };

    const updateMetaText = () => {
      if (!metaLabel) return;
      const { total, page, perPage, pageCount } = lastMeta;
      if (!total) {
        metaLabel.textContent = "0 data";
        return;
      }
      metaLabel.textContent = `Total ${total} data · Halaman ${page}/${pageCount} · ${perPage}/hal`;
    };

    const renderTable = ({ data, meta, query }) => {
      const search = (query?.search || "").trim();
      const page = Number(meta?.page || 1);
      const perPage = Number(meta?.perPage || 20);

      if (!Array.isArray(data) || data.length === 0) {
        const title = search
          ? "Data tidak ditemukan"
          : kind === "sudah"
            ? "Belum ada data dipanggil"
            : "Belum ada data antrian";

        tbody.innerHTML = renderEmptyRow({
          colCount,
          title,
          subtitle: search ? `Kata kunci: "${search}"` : "",
        });
        return;
      }

      const startNomor = (page - 1) * perPage + 1;
      tbody.innerHTML = data
        .map((row, i) => {
          const nomor = startNomor + i;
          return kind === "sudah" ? renderSudahRow(row, nomor) : renderBelumRow(row, nomor);
        })
        .join("");
    };

    const loadData = async (page = 1) => {
      const search = searchInput ? searchInput.value : "";
      const sort = sortSelect ? sortSelect.value : "";
      const perPage = perPageSelect ? perPageSelect.value : "20";

      const url = buildUrl(endpoint, {
        type: kind,
        tgl_uji: getTglUji(),
        search,
        sort,
        perPage,
        page,
        debug: debug ? 1 : "",
      });

      setLoading(true);
      try {
        if (lastController) lastController.abort();
        lastController = new AbortController();

        const json = await window.AppFetch.fetchJson(url, { signal: lastController.signal });

        lastMeta = json.meta || lastMeta;
        updateMetaText();

        renderTable({ data: json.data || [], meta: json.meta || {}, query: json.query || {} });

        renderPagination({
          container: pager,
          page: Number(json.meta?.page || 1),
          pageCount: Number(json.meta?.pageCount || 1),
          onGo: (p) => loadData(p),
        });
      } catch (e) {
        if (e && e.name === "AbortError") return;
        tbody.innerHTML = renderEmptyRow({
          colCount,
          title: "Gagal memuat data",
          subtitle: "Coba refresh halaman.",
        });
        if (pager) pager.innerHTML = "";
        if (metaLabel) metaLabel.textContent = "";
      } finally {
        setLoading(false);
      }
    };

    Object.defineProperty(root, "__dashboardLoadData", { value: loadData });

    if (searchInput) searchInput.addEventListener("input", debounce(() => loadData(1), 450));
    if (sortSelect) sortSelect.addEventListener("change", () => loadData(1));
    if (perPageSelect) perPageSelect.addEventListener("change", () => loadData(1));

    loadData(1);
  };

  window.DashboardTable = Object.freeze({ init });
})();
