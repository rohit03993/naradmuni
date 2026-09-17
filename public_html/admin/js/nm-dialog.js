(function (w, d) {
  "use strict";
  if (w.nmDialog) {
    return;
  }

  var nativeConfirm = w.confirm.bind(w);
  var queue = [];
  var open = false;
  var passConfirm = false;
  var root = null;
  var iconEl;
  var titleEl;
  var msgEl;
  var okBtn;
  var cancelBtn;
  var resolver = null;
  var currentMode = "alert";

  var ICONS = {
    success:
      '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1.2 14.2-3.5-3.5 1.4-1.4 2.1 2.1 4.6-4.6 1.4 1.4-6 6z"/></svg>',
    error:
      '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 14h-2v-2h2v2zm0-4h-2V6h2v6z"/></svg>',
    danger:
      '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2 1 21h22L12 2zm1 16h-2v-2h2v2zm0-4h-2V9h2v5z"/></svg>'
  };

  function kindFrom(msg, isConfirm) {
    var s = String(msg || "").toLowerCase();
    if (isConfirm || /delete|remove|reset|cannot be undone/.test(s)) {
      return "danger";
    }
    if (/sorry|error|fail|missing|not found|not correct|required|already in use/.test(s)) {
      return "error";
    }
    return "success";
  }

  function titleFrom(msg, kind, isConfirm) {
    var s = String(msg || "").toLowerCase();
    if (isConfirm) {
      return /delete/.test(s) ? "Delete?" : "Please confirm";
    }
    if (/scheduled/.test(s)) {
      return "Scheduled";
    }
    if (/published/.test(s)) {
      return "Published";
    }
    if (kind === "error") {
      return "Couldn’t complete";
    }
    return "Done";
  }

  function ensure() {
    if (root) {
      return;
    }
    root = d.createElement("div");
    root.className = "nm-dlg-root";
    root.innerHTML =
      '<div class="nm-dlg-backdrop" data-nm-dlg="cancel"></div>' +
      '<div class="nm-dlg" role="dialog" aria-modal="true" aria-labelledby="nm-dlg-title">' +
      '<div class="nm-dlg-icon"></div>' +
      '<h3 class="nm-dlg-title" id="nm-dlg-title"></h3>' +
      '<p class="nm-dlg-msg"></p>' +
      '<div class="nm-dlg-actions">' +
      '<button type="button" class="nm-dlg-btn nm-dlg-cancel" data-nm-dlg="cancel">Cancel</button>' +
      '<button type="button" class="nm-dlg-btn nm-dlg-ok" data-nm-dlg="ok">OK</button>' +
      "</div></div>";
    d.body.appendChild(root);
    iconEl = root.querySelector(".nm-dlg-icon");
    titleEl = root.querySelector(".nm-dlg-title");
    msgEl = root.querySelector(".nm-dlg-msg");
    okBtn = root.querySelector(".nm-dlg-ok");
    cancelBtn = root.querySelector(".nm-dlg-cancel");
    root.addEventListener("click", function (e) {
      var act = e.target.getAttribute("data-nm-dlg");
      if (act === "ok") {
        finish(true);
      } else if (act === "cancel") {
        finish(currentMode === "alert");
      }
    });
  }

  function finish(result) {
    if (!open) {
      return;
    }
    open = false;
    if (root) {
      root.classList.remove("is-open");
    }
    var r = resolver;
    resolver = null;
    if (r) {
      r(result);
    }
    setTimeout(flush, 160);
  }

  function flush() {
    if (open || !queue.length) {
      return;
    }
    if (!d.body) {
      if (d.readyState === "loading") {
        d.addEventListener("DOMContentLoaded", flush, { once: true });
      }
      return;
    }
    ensure();
    var item = queue.shift();
    var opts = item.opts || {};
    var msg = opts.message == null ? "" : String(opts.message);
    var isConfirm = opts.mode === "confirm";
    var kind = opts.type || kindFrom(msg, isConfirm);
    currentMode = isConfirm ? "confirm" : "alert";
    open = true;
    resolver = item.resolve;
    root.setAttribute("data-kind", kind);
    iconEl.innerHTML = ICONS[kind] || ICONS.success;
    titleEl.textContent = opts.title || titleFrom(msg, kind, isConfirm);
    msgEl.textContent = msg;
    cancelBtn.hidden = !isConfirm;
    okBtn.textContent = opts.okText || (isConfirm ? "Delete" : "OK");
    okBtn.classList.toggle("is-danger", kind === "danger" || kind === "error");
    root.classList.add("is-open");
    okBtn.focus();
  }

  function show(opts) {
    return new Promise(function (resolve) {
      queue.push({ opts: opts || {}, resolve: resolve });
      flush();
    });
  }

  function nmAlert(msg, opts) {
    opts = opts || {};
    return show({
      mode: "alert",
      message: msg,
      type: opts.type,
      title: opts.title,
      okText: opts.okText || "OK"
    });
  }

  function nmConfirm(msg, opts) {
    opts = opts || {};
    return show({
      mode: "confirm",
      message: msg,
      type: opts.type || "danger",
      title: opts.title,
      okText: opts.okText || "Delete"
    });
  }

  function nmToast(msg, type) {
    if (!d.body) {
      return;
    }
    var el = d.createElement("div");
    el.className = "nm-toast is-" + (type || "success");
    el.textContent = msg == null ? "" : String(msg);
    d.body.appendChild(el);
    requestAnimationFrame(function () {
      el.classList.add("is-show");
    });
    setTimeout(function () {
      el.classList.remove("is-show");
      setTimeout(function () {
        if (el.parentNode) {
          el.parentNode.removeChild(el);
        }
      }, 250);
    }, 2800);
  }

  function readyNotice(payload) {
    var p = payload || w.__nmNotice || {};
    var msg = p.message || "";
    var href = p.href || "";
    return nmAlert(msg, { type: p.type }).then(function () {
      if (href) {
        w.location.href = href;
      }
    });
  }

  d.addEventListener("keydown", function (e) {
    if (!open || e.key !== "Escape") {
      return;
    }
    e.preventDefault();
    finish(currentMode === "alert");
  });

  d.addEventListener(
    "click",
    function (e) {
      var el = e.target.closest("[data-nm-confirm], a.delete, button.delete");
      if (!el || el.__nmOk) {
        return;
      }
      var msg = el.getAttribute("data-nm-confirm");
      if (!msg && el.classList.contains("delete")) {
        msg = "Are you sure you want to remove this data?";
      }
      if (!msg) {
        return;
      }
      e.preventDefault();
      e.stopImmediatePropagation();
      nmConfirm(msg, {
        okText: /reset/i.test(msg) ? "Reset" : "Delete"
      }).then(function (ok) {
        if (!ok) {
          return;
        }
        el.__nmOk = true;
        passConfirm = true;
        var href = el.getAttribute("href");
        if (el.tagName === "A" && href && href !== "#" && href.indexOf("javascript:") !== 0) {
          w.location.href = el.href;
          return;
        }
        el.click();
        passConfirm = false;
        el.__nmOk = false;
      });
    },
    true
  );

  w.nmDialog = {
    alert: nmAlert,
    confirm: nmConfirm,
    toast: nmToast,
    ready: readyNotice
  };
  w.nmAlert = nmAlert;
  w.nmConfirm = nmConfirm;
  w.nmToast = nmToast;
  w.nmReadyNotice = readyNotice;
  w.alert = function (msg) {
    nmAlert(msg);
  };
  w.confirm = function (msg) {
    if (passConfirm) {
      return true;
    }
    return nativeConfirm(msg);
  };

  if (w.__nmNotice && w.__nmNotice.message) {
    readyNotice(w.__nmNotice);
  }
})(window, document);
