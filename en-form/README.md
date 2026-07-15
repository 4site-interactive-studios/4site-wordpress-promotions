# Engaging Networks Embedded Form

This project was copied and modified from [4site-interactive-studios/en-multistep-embedded](https://github.com/4site-interactive-studios/en-multistep-embedded). We've removed the "multistep" from the name because it's not only for multistep forms anymore, we've added the ability to embed any Engaging Networks form.

## How to use

The Promotions Plugin has a block called "Engaging Networks Embedded Form". You can add it to any page and it will render the form as an iFrame.

The Promotions Plugin also has a shortcode called `[en-form]`. You can use it to render the form on any page or post.

## Options

You can define options via data attributes on the iFrame tag. The following options are available:

- `url` - The URL of the donation form.
- `form-color` - The theme color of the form.
- `height` - The min-height of the form.
- `border-radius` - The border radius of the form.
- `loading-color` - The color of the loading animation.
- `bounce-color` - The color of the bounce animation.
- `append-url-params` - Whether to append the current URL parameters to the iFrame URL.

### IMPORTANT: This project only works with the Engaging Networks Pages using the [engrid theme](https://github.com/4site-interactive-studios/engrid).

## Thank You page redirect

The embedded page can redirect the visitor away from the WordPress page (the top window) by posting a `redirect` message to the parent. No ENgrid changes are needed — add a code block to the Engaging Networks Thank You page with a snippet like this:

```html
<script>
  (function () {
    // Where to send the visitor as soon as they reach this Thank You page.
    // The URL may already have its own query string — that's fine.
    var redirectUrl = "https://example.org/next-page?utm_source=newsletter";

    if (window.self !== window.top) {
      // Embedded in the WordPress plugin's iframe: the parent page appends
      // the chain argument and redirects the top window.
      window.parent.postMessage({ key: "redirect", value: redirectUrl }, "*");
      return;
    }

    // Viewed directly (not embedded): redirect this window, appending the
    // chain argument the same way the parent would.
    var url = new URL(redirectUrl);
    if (!url.searchParams.has("chain")) {
      url.search += (url.search ? "&" : "?") + "chain";
    }
    window.location.href = url.toString();
  })();
</script>
```

Whether the redirect happens via the parent page or directly, Engaging Networks' `chain` URL argument is appended to the target URL (`?chain` if the URL has no query string, `&chain` otherwise, and never twice). The `chain` argument takes advantage of EN's native page chaining, which pre-populates the supporter's information on the destination page while their session is active.

Only absolute `http:`/`https:` URLs are honored — anything else (including relative or malformed URLs) is ignored by the parent.

## Development

Your js code must be on the `src/app` folder. Styling changes must be on `src/scss`.

## Install Dependencies

1. `npm install`

## Deploy

1. `npm run build` - Builds the project
2. `npm run watch` - Watch for changes and rebuilds the project

It's going to create a `dist` folder, where you can get the `donation-multistep-parent.js` file and publish it.

Currently it's published on (Shatterproof):  
https://acb0a5d73b67fccd4bbe-c2d8138f0ea10a18dd4c43ec3aa4240a.ssl.cf5.rackcdn.com/10089/donation-multistep-parent.js
