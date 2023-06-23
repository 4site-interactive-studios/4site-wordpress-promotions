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
