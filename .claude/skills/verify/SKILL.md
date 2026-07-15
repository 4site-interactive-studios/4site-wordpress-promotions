---
name: verify
description: How to build and runtime-verify this plugin's en-form embed (parent iframe script) without a WordPress install.
---

# Verifying the en-form embed

## Build

```bash
cd en-form && npm install
NODE_OPTIONS=--openssl-legacy-provider npm run build
```

The `--openssl-legacy-provider` flag is required on Node 17+ (webpack 5.48 uses md4 hashing → `ERR_OSSL_EVP_UNSUPPORTED` without it). `en-form/dist/` is committed — always rebuild and commit it with source changes. Bump the plugin version in `foursite-wordpress-promotion.php` (header + `define`) so WordPress cache-busts the enqueued script.

## Runtime harness (no WordPress needed)

The parent script only needs an element matching `.promo-form-iframe` with `data-src` etc. — the same markup the shortcode emits. Create in a scratch dir:

- `parent.html` — `<iframe class="promo-form-iframe" data-src="http://localhost:8642/child.html" data-form_color="#f26722" data-height="300px" data-append_url_params="true">` + `<script src="en-form-parent.js"></script>` (copy from `en-form/dist/`).
- `child.html` — buttons that `window.parent.postMessage({key, value}, "*")` the messages under test, plus `{frameHeight: N}`.
- `target.html` — prints `location.href` (for redirect tests).

Serve with `python3 -m http.server 8642` from that dir, open `parent.html` in the Browser pane, click buttons inside the iframe, observe navigation/height.

Gotchas:
- Load `parent.html?debug=true` to get the script's `isDebug()` console logging (visible via read_console_messages).
- Browser pane screenshots are 2x — halve image coordinates before clicking.
- `read_page` does not traverse into the iframe; click by screenshot coordinates.

## Message contract handled by the parent

`{frameHeight}` resize; `{key: "status"|"error"|"class"|"donationinfo"|"redirect", value}` — see `en-form/src/app/utils/en-form-parent.js` `receiveMessage()`.
