<?php

/**
 * Adds the Documentation page under the Promotions admin menu.
 */
class Foursite_Wordpress_Promotion_Documentation {
	public function __construct() {
		// Priority 11 keeps Documentation below the other Promotions submenu items.
		add_action('admin_menu', [$this, 'admin_menu'], 11);
	}

	function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=wordpress_promotion',
			__('Documentation', 'four-site-wordpress-promotions'),
			__('Documentation', 'four-site-wordpress-promotions'),
			'edit_pages',
			'fswp-documentation',
			[$this, 'render_documentation_page']
		);
	}

	function render_documentation_page() {
		if (!current_user_can('edit_pages')) {
			return;
		}

		$settings_url = esc_url(admin_url('edit.php?post_type=wordpress_promotion&page=acf-options-settings'));
		$list_url = esc_url(admin_url('edit.php?post_type=wordpress_promotion'));
		$new_url = esc_url(admin_url('post-new.php?post_type=wordpress_promotion'));
		?>
		<style>
			.fswp-docs { max-width: 860px; }
			.fswp-docs h2 { margin-top: 2em; padding-bottom: .4em; border-bottom: 1px solid #c3c4c7; }
			.fswp-docs ol, .fswp-docs ul { margin-left: 1.5em; }
			.fswp-docs ul { list-style: disc; }
			.fswp-docs li { margin-bottom: .6em; line-height: 1.5; }
			.fswp-docs table { margin-top: 1em; }
			.fswp-docs .fswp-docs-toc li { margin-bottom: .2em; }
		</style>
		<div class="wrap fswp-docs">
			<h1>Promotions Documentation</h1>
			<p>How to set up, schedule, and take down a promotion. These steps apply to every promotion type; the fields on the Content &amp; Styles tab change depending on the type you choose.</p>

			<ul class="fswp-docs-toc">
				<li><a href="#fswp-docs-before">Before you start</a></li>
				<li><a href="#fswp-docs-setup">1. Set up a promotion</a></li>
				<li><a href="#fswp-docs-schedule">2. Schedule a promotion</a></li>
				<li><a href="#fswp-docs-takedown">3. Take down a promotion</a></li>
				<li><a href="#fswp-docs-notes">Good to know</a></li>
			</ul>

			<h2 id="fswp-docs-before">Before you start</h2>
			<p>Decide which type of promotion you need:</p>
			<table class="widefat striped">
				<thead>
					<tr><th>Type</th><th>What it does</th></tr>
				</thead>
				<tbody>
					<tr><td>EN Multistep Lightbox</td><td>A lightbox with an embedded Engaging Networks multistep donation page.</td></tr>
					<tr><td>EN Lightbox</td><td>A lightbox with a single-step embedded Engaging Networks page, such as a signup or petition.</td></tr>
					<tr><td>CTA Lightbox</td><td>A call-to-action lightbox with an image, text, and up to two buttons.</td></tr>
					<tr><td>Email Capture Lightbox</td><td>A lightbox with an email signup form that submits to Engaging Networks.</td></tr>
					<tr><td>Overlay</td><td>A modal or full-screen takeover, with optional donation amount buttons.</td></tr>
					<tr><td>Pushdown</td><td>A text or image banner at the top of the page that links to another page.</td></tr>
					<tr><td>Roll Up</td><td>An image banner at the bottom of the page that links to another page.</td></tr>
					<tr><td>Floating Email Signup</td><td>A small floating email signup form that submits to a Gravity Form.</td></tr>
					<tr><td>Floating Tab</td><td>A tab fixed to the edge of the screen that links to a page or opens a multistep lightbox.</td></tr>
					<tr><td>Raw Code</td><td>Your own HTML, CSS, and JavaScript added to the page.</td></tr>
					<tr><td>Video Lightbox</td><td>A lightbox that plays a YouTube or mp4 video.</td></tr>
					<tr><td>Redirect</td><td>Sends the visitor to another URL.</td></tr>
					<tr><td>A/B Test</td><td>Splits visitors between two or more existing promotions.</td></tr>
				</tbody>
			</table>
			<p>Some types need a one-time setup under <a href="<?php echo $settings_url; ?>">Promotions &gt; Settings</a>:</p>
			<ul>
				<li><strong>EN Multistep Lightbox:</strong> the Promotion Lightbox Script URL must be filled in.</li>
				<li><strong>Floating Email Signup:</strong> the reCAPTCHA v3 site key and secret key must be filled in, and reCAPTCHA must be disabled on the Gravity Form itself.</li>
				<li><strong>Email Capture Lightbox:</strong> the Engaging Networks Proxy Endpoint URL must be filled in. Without it the form shows its success message but no email is captured.</li>
			</ul>

			<h2 id="fswp-docs-setup">1. Set up a promotion</h2>
			<ol>
				<li>Go to <a href="<?php echo $new_url; ?>">Promotions &gt; Add New Promotion</a> and give it a title. The title is only shown in the admin, so name it something you will recognize later, such as the campaign and date.</li>
				<li>Under <strong>Promotion Visibility &amp; Type</strong>, leave the visibility on <strong>Turned Off</strong> for now and choose the promotion type from the dropdown.</li>
				<li>On the <strong>Content &amp; Styles</strong> tab, fill in the content for the type you chose: text, images, links, colors, and the Engaging Networks page URL where one is needed.</li>
				<li>On the <strong>Display Settings</strong> tab, choose the <strong>Trigger</strong>, which controls when the promotion appears:
					<ul>
						<li><strong>Immediately:</strong> as soon as the page loads.</li>
						<li><strong>Seconds Later:</strong> after the number of seconds you enter.</li>
						<li><strong>On Pixel Scroll</strong> or <strong>On Percentage Scroll:</strong> once the visitor has scrolled that far down the page.</li>
						<li><strong>On Exit:</strong> when the visitor moves their mouse out of the browser window.</li>
						<li><strong>Javascript Trigger:</strong> only when custom code on the site opens it.</li>
					</ul>
				</li>
				<li>Set the <strong>Suppression Cookie Hours</strong>. This is how long a visitor who has seen the promotion goes without seeing it again. For example, 24 shows it at most once a day.</li>
				<li>Choose where the promotion appears. With all four fields empty it is eligible on every page.
					<ul>
						<li><strong>Show On (URL Pattern)</strong> and <strong>Show On (Individual Pages)</strong> limit it to matching URLs or the pages you pick.</li>
						<li><strong>Hide On (URL Pattern)</strong> and <strong>Hide On (Individual Pages)</strong> exclude URLs or pages. Hide always wins over Show.</li>
						<li><strong>404 Pages</strong> controls whether it runs on "page not found" pages. It does not by default.</li>
					</ul>
				</li>
				<li>Click <strong>Publish</strong>. The promotion is saved but not yet visible to visitors, because its visibility is still Turned Off.</li>
				<li>When you are ready to test or go live, change the visibility to <strong>Turned On</strong> (to show it right away, with no end date) or <strong>Scheduled</strong> (see the next section) and click <strong>Update</strong>.</li>
				<li>Check the promotion on the front end of the site in a private or incognito window.</li>
			</ol>

			<h2 id="fswp-docs-schedule">2. Schedule a promotion</h2>
			<ol>
				<li>Edit the promotion and set the visibility to <strong>Scheduled</strong>.</li>
				<li>Fill in both the <strong>Start Date</strong> and the <strong>End Date</strong>. Both are required; a scheduled promotion with either date missing will not show.</li>
				<li>Make sure the promotion is published, then click <strong>Update</strong>.</li>
				<li>Go to <a href="<?php echo $list_url; ?>">All Promotions</a> and check the <strong>Status</strong>, <strong>Start</strong>, and <strong>End</strong> columns. An upcoming promotion reads <strong>Scheduled - Upcoming</strong>, and changes to <strong>Scheduled - Active</strong> once it starts.</li>
			</ol>
			<p><strong>About the times:</strong> the start and end are checked against each visitor's own clock. A promotion set to end at 11:59 pm ends at 11:59 pm local time for each visitor, wherever they are. The Status column on the All Promotions screen uses US Eastern time.</p>
			<p>To run one promotion and then replace it with another, schedule both ahead of time with back-to-back dates. Nothing needs to be done by hand at the changeover.</p>

			<h2 id="fswp-docs-takedown">3. Take down a promotion</h2>
			<ol>
				<li><strong>Scheduled promotions come down on their own.</strong> Once the End Date passes, the promotion stops showing and its status reads <strong>Scheduled - Expired</strong>. No action is needed.</li>
				<li><strong>To take a promotion down early,</strong> or to take down one that is Turned On, edit it, set the visibility to <strong>Turned Off</strong>, and click <strong>Update</strong>. Its status reads <strong>Off</strong>.</li>
				<li>Switching a promotion to Draft or moving it to the Trash also stops it. Its status reads <strong>Off - Not Published</strong>. Prefer Turned Off if you may want to reuse the promotion later.</li>
				<li>Confirm in a private or incognito window that the promotion no longer appears. If the site uses page caching, clear the cache first.</li>
			</ol>

			<h2 id="fswp-docs-notes">Good to know</h2>
			<ul>
				<li><strong>Only one lightbox shows per page.</strong> If several lightbox promotions qualify for the same page, scheduled promotions take priority over ones that are Turned On, and after that the most recently created one wins.</li>
				<li><strong>You will only see a promotion once while testing.</strong> After your first view the suppression cookie hides it from you. Use a new private or incognito window each time you test.</li>
				<li><strong>A/B Test promotions control their variants.</strong> A promotion added to an A/B Test follows the test's visibility and display settings, regardless of its own.</li>
				<li><strong>Promotions can be copied between sites.</strong> On the All Promotions screen, tick the promotions you want, choose <strong>Export</strong> from the Bulk actions dropdown, and click Apply. Then upload that file under Promotions &gt; Import Promos on the other site.</li>
			</ul>
		</div>
		<?php
	}
}

new Foursite_Wordpress_Promotion_Documentation();
