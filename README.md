# 4Site WordPress Promotions Plugin
Built by 4Site Studios to solve your on-site promotional needs. Our plugin helps launch fundraising lightboxes, pushdowns, pop-ups, embedded donation experiences, and so much more.

Imagine scheduling a sitewide fundraising lightbox to appear for a week before automatically coming down and being replaced by a sitewide pushdown for two more weeks promoting signups for your latest campaign. Or tailoring that fundraising lightbox so its imagery and contents correspond to different sitewide landing pages.

That's just a taste of what the Promotions Plugin can do. All promotions can be managed with no coding experience, but you can always add your own custom HTML, CSS, and Javascript.


## Promotion Types

EN Multistep Lightbox ([see example](https://cln.sh/Dk3Kl5Nd))

> Creates a multistep lightbox that includes an embedded Engaging Networks donation page. Must be used with purpose-built EN multistep pages.

EN Lightbox

> Creates a lightbox that displays a single-step embedded Engaging Networks page. Great for newsletter signups and petitions.

Overlay

> A customizable lightbox that can appear as a modal or full site takeover. Includes options for donation buttons that pass their values to a corresponding Engaging Networks Donation page.
 
Pushdown

> Creates a banner at the top of the page that links to another page. It can be text or image.

Roll Up

> Creates a banner at the bottom of the page that links to another page. It can be text or image.

Floating Tab ([see example](https://cln.sh/kzY56ZXg))

> Add a floating tab to your website and link off to any page, or trigger a Multistep Donation Lightbox

Raw Code

> Adds custom HTML, CSS, and Javascript to the page.

## Trigger Options

> Each promotion can be triggered immediately, after X seconds, after X pixels scrolled, after X% of the page scrolled, on exit, or event via your own Javascript Trigger. ([see example](https://cln.sh/gg4M1SYY))

> Each promotion can define its suppression cookie name and time of expiration in hours, ensuring your visitors don't get overwhelmed with promotions ([see example](https://cln.sh/p5kPr6qk))

> Each promotion can be displayed on all pages or have an explicit list of URL patterns or pages defined along with a suppression list that will supersede the first. ([see example](https://cln.sh/DhTC3RqD))

> And if multiple "Lightbox" type promotions are scheduled on the same page and meet the criteria to be displayed, only the most recently created promotions will be shown to the visitor.

## Blocks & Shortcodes

This plugin provides a Gutenberg block and a shortcode to embed an Engaging Networks form. The block can be found under the "Embeds" category. ([see example](https://cln.sh/LLF4QS5Z))

The shortcode is `[en-form]`. Check out the [en-form/README.md](en-form/README.md) for more information.


# Development
## Notes

**NOTE 1:** The URL for the multistep donation lightbox script MUST be specified in the Promotions Options page in order for the multistep donation promo type to work.

**NOTE 2:** The bulk of the critical code for this plugin is located in the /public/ folder.

**NOTE 3:** ACF is required for the promos plugin to function. The promos plugin makes use of two fieldsets: "Foursite Wordpress Promotions Plugin", which contains all of the fields for the Promotion custom post type, and "Promotions Settings", which appears on the options page of that name and permits the user to specify what donation lightbox script is to be loaded.

Also of note is how the ACF fields are imported. See: /includes/acf-fields.php, a simple PHP export of the fields. This imports the ACF fields and doesn't provide the field group on the ACF fieldgroups list, which is ideal for production sites. When developing this plugin and if you need to change the fieldgroup, install ACF Extended and then select "Local" under the ACF Fieldgroups list. You can then import those field groups to the local database for editing.

## Versioning

When incrementing the version, it must be incremented in two places in /foursite-wordpress-promotion.php:
In the multiline comment under the key, "Version"
In the foursite_wordpress_promotion_VERSION define below that the multiline comment

## General Registration

Custom post-type registration and the ACF options page both occur in /admin/options.php. This is also where custom columns/interactions are registered via the appropriate hooks for the backend listing of promotions.


## Considerations when Testing

When making changes to the primary promotions loading (/public/class-foursite-wordpress-promotion-public.php) & launching (/public/js/foursite-wordpress-promotion-public.js), there's a lot to check for.


Are the promotions correctly ordered? Ordered by date DESC, but scheduled promotions should take precedence over always-on promotions.


If an overlay / modal type promotion is shown, only one should show (the first in order). All others shouldn't proc.


The five types of triggers should be verified to work: scroll length ( pixels ), scroll length ( percentage length ), exit ( moving mouse out of window ), js ( window.triggerPromotion(promo_id); ), time delay ( N seconds delay before launch ).


The floating tab promo correctly launches the lightbox modal (if configured to do so) even if another lightbox has already opened.


The six types of promotions should be verified to work: Multistep Lightbox, Raw Code, Pushdown, Floating Tab, Overlay, Signup, Roll Up. Note that the signup comes with its own trigger logic within its compiled script, so do not look for the signup promo type to be handled within the plugin's main launch script.


## ToDo's

 * css : not used; should probably be removed in a future refactor

 * js : contains foursite-wordpress-promotion-public.js which is the primary JS file that handles launching promotions

 * floating-tab : contains the CSS specific to the floating tab promo type

 * overlay : contains the full overlay source & compiled script for the overlay promo

 * partials : not used; should probably be removed in a future refactor

 * pushdown : contains the full pushdown source & compiled script for the pushdown promo

 * signup : contains the full signup source and compiled script for the signup promo

 * class-foursite-wordpress-promotion-public.php : handles loading of active promos & enqueuing the necessary script(s); this is the main workhorse of the plugin
