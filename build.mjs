// Minifies the plugin's hand-written public/admin assets with esbuild.
//
// Each source file is minified to a sibling `*.min.js` / `*.min.css`, which is
// what PHP enqueues in production (see the `foursite_wordpress_promotion_MIN`
// constant). The unminified sources stay readable and are loaded when
// SCRIPT_DEBUG is on. The committed `.min` files let the plugin run on a fresh
// clone without a build step.
//
// The overlay/pushdown/signup sub-projects have their own webpack builds and are
// intentionally not handled here.
//
//   npm run build     one-off build
//   npm run watch     rebuild on change
import * as esbuild from "esbuild";

const targets = [
  {
    in: "public/js/foursite-wordpress-promotion-public.js",
    out: "public/js/foursite-wordpress-promotion-public.min.js",
  },
  {
    in: "public/css/foursite-wordpress-promotion-public.css",
    out: "public/css/foursite-wordpress-promotion-public.min.css",
  },
  {
    in: "public/floating-tab/fs-floating-tab.css",
    out: "public/floating-tab/fs-floating-tab.min.css",
  },
  {
    in: "admin/css/foursite-wordpress-promotion-admin.css",
    out: "admin/css/foursite-wordpress-promotion-admin.min.css",
  },
  {
    in: "admin/js/foursite-wordpress-promotion-admin.js",
    out: "admin/js/foursite-wordpress-promotion-admin.min.js",
  },
];

const watch = process.argv.includes("--watch");

function optionsFor(target) {
  const isJs = target.in.endsWith(".js");
  return {
    entryPoints: [target.in],
    outfile: target.out,
    minify: true,
    legalComments: "none",
    logLevel: "info",
    // These run unbundled on public-facing sites; keep broad browser support.
    ...(isJs ? { target: ["es2017"] } : {}),
  };
}

if (watch) {
  const contexts = await Promise.all(
    targets.map((t) => esbuild.context(optionsFor(t)))
  );
  await Promise.all(contexts.map((ctx) => ctx.watch()));
  console.log("esbuild: watching for changes... (ctrl-c to stop)");
} else {
  await Promise.all(targets.map((t) => esbuild.build(optionsFor(t))));
  console.log("esbuild: build complete");
}
