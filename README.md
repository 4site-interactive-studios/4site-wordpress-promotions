# 4site-wordpress-promotions

## Show / Hide Triggers

The following triggers can be used to show or hide a lightbox. Note that for conflicting conditions between showing & hiding, the hiding will take precedent. Also note that if the user has already seen the lightbox, a cookie it set so that they do not see it a second time.

Show On (URL Pattern)

> Enter a comma-delimited list of slugs. If any of those slugs are in the base URL of the viewed page, it will trigger the lightbox to show itself.
> EX: ghana,ethiopia
> On visits to https://www.amnestyusa.org/countries/Ethiopia/, or to https://www.amnestyusa.org/reports/annual-report-ghana-2013/ , the lightbox ought to show up because the URL to the page contains the string, "ethiopia". Note that this does not extend to the query string (the stuff that may optionally be present after a question mark). For example, https://www.amnestyusa.org/?test=ghana would not trigger the lightbox.

Hide On (URL Pattern)

> This exhibits identical behavior to Show On (URL Pattern), but instead of triggering the lightbox to show itself, it will instead cause it to NOT show.

Show On (Individual Pages)

> Select as few or as many pages you want for the lightbox to show itself on. The URL does not matter -- it checks the ID of the currently-viewed page against its list, to determine if it should show the lightbox or not.

Hide On (Individual Pages)

> This exhibits identical behavior to Show On (Individual Pages), but instead of triggering the lightbox to show itself, it will instead cause it to NOT show.

## Promotion Types

Multistep Lightbox

> Creates a multistep lightbox that includes a donation page.

Raw Code

> Adds custom HTML, CSS, and Javascript to the page.

Pushdown

> Creates a banner at the top of the page that links to another page. Can be text or image.

Signup Lightbox

> Creates a lightbox that displays a sign up page for campaigns or newsletters.

## Blocks & Shortcodes

This plugin provides a Gutenberg block and a shortcode to embed an Engaging Networks form. The block can be found under the "Embeds" category.

The shortcode is `[en-form]`. Check out the [en-form/README.md](en-form/README.md) for more information.
