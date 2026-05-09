(() => {
  "use strict";

  /**
   * Small fetch helper:
   * - Always sends Accept: application/json
   * - Tries to parse JSON even on non-2xx (so UI can show server message)
   * - Throws Error with `status` and `payload`
   */
  const fetchJson = async (url, { method = "GET", headers = {}, body, signal } = {}) => {
    const res = await fetch(url, {
      method,
      headers: { Accept: "application/json", ...headers },
      body,
      signal,
    });

    let payload;
    try {
      payload = await res.json();
    } catch {
      payload = null;
    }

    if (!res.ok) {
      const message =
        (payload && (payload.message || payload.error)) ||
        `HTTP ${res.status} ${res.statusText}`.trim();
      const err = new Error(message);
      err.status = res.status;
      err.payload = payload;
      throw err;
    }

    return payload;
  };

  window.AppFetch = Object.freeze({ fetchJson });
})();

