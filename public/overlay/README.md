# Engaging Networks Overlay

This project makes it easy to add an Overlay with a custom Engaging Networks Form to your website.

## How to use

1. Add the script below to the page:

```html
<script
  defer="defer"
  src="https://{MY_EN_ASSETS_URL}/foursite-en-overlay.js"
  data-title="This is my title!"
  data-subtitle="This is my subtitle"
  data-paragraph="This is my paragraph"
  data-image="https://source.unsplash.com/random/1920x1080"
  data-donation_form="https://{MY_EN_URL}"
></script>
```

2. You can add any options as a data attribute on the `script` tag.

## Options

Every option should be set as a data attribute on the `script` tag. All options are optional, except for the `data-title`, `data-subtitle`, `data-paragraph`, and `data-image` options, which are empty by default.

- **data-cookie_name** - The name of the cookie that will be used to store the closed state. Defaults to `hideOverlay`.
- **data-cookie_expiry** - The number of days until the cookie expires. Defaults to `1`. Set to `0` to not set the cookie, and to always allow the overlay to show.
- **data-logo** - The src URL for the logo image in the top-left of the overlay.
- **data-title** - Title of the overlay.
- **data-subtitle** - Subtitle of the overlay.
- **data-paragraph** - Text of the overlay.
- **data-image** - Background Image of the overlay.
- **data-button_label** - Label of the donate button. Defaults to `Donate Now`.
- **data-amounts** - CSV of numeric amount preset options. Defaults to `35, 75, 100, 250, 500`.
- **data-other_label** - Label of Other Amount field. Defaults to `$ other`. If empty, the field will be hidden.
- **data-donation_form** - URL of the donation page.
- **data-trigger** - How the user will trigger the overlay. Defaults to `0`, which means the overlay will automatically trigger when the page loads. Check the **Trigger Options** section for more information.
- **data-start_unix** - Unix seconds timestamp of when to start showing the overlay. Defaults to `0` for no start time.
- **data-end_unix** - Unix seconds timestamp of when to stop showing the overlay. Defaults to `Infinity` for no end time.
- **data-max_width** - CSS dimension to set the overlay modal's max-width. Defaults to full screen.
- **data-max_height** - CSS dimension to set the overlay modal's max-height. Defaults to full screen.
- **data-cta_type** - Sets the call to action type (`general` or `fundraising`). Hides donation amounts if set to `general`. Defaults to `fundraising`.

### Trigger Options

- **ANY_NUMBER** (example: `2`) - The overlay will open after NUMBER seconds.
- **ANY_PIXEL** (example `400px`) - The overlay will open when the user scrolls the page for PIXEL pixels.
- **ANY_PERCENT** (example `50%`) - The overlay will open when the user scrolls the page for PERCENT of the page.
- **exit** (example `exit`) - The overlay will open when the user moves their mouse out of the page (exit intent).

### Style Variables

Some CSS variables are used throughout the overlay's styles. They are as follows:

```scss
// Backgrounds.

--bg-img-overlay-start-color
--bg-img-overlay-end-color

--bg-overlay-color // the shadow outside of the overlay modal.

// Typography.

--title-color
--title-font // font shorthand rule.

--subtitle-color
--subtitle-font

--paragraph-color
--paragraph-font

// Elements.

--divider-border

// Amount Buttons.

--amount-button-bg-color
--amount-button-color
--amount-button-border // full border shorthand declaration.
--amount-button-border-radius // full length unit, such as 50% or 3em.

--amount-button-hover-bg-color
--amount-button-hover-color
--amount-button-hover-border
--amount-button-hover-border-radius

--amount-button-selected-bg-color
--amount-button-selected-color
--amount-button-selected-border
--amount-button-selected-border-radius

// Submit Button.

--submit-button-bg-color
--submit-button-color
--submit-button-border
--submit-button-border-radius

--submit-button-hover-bg-color
--submit-button-hover-color
--submit-button-hover-border
--submit-button-hover-border-radius
```

## Development

Project's code is on the `src/app.ts` file. Styling changes must be on `src/sass` folder.

## Install Dependencies

1. `npm install`

## Deploy

1. `npm run build` - Builds the project
2. `npm run start` - Watch for changes and rebuilds the project

It's going to create a `dist` folder, where you can get the `foursite-en-overlay.js` file and publish it.
