import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";
import { escapeHTML } from "@wordpress/escape-html";
import {
  TextControl,
  ColorIndicator,
  ToggleControl,
} from "@wordpress/components";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./style.scss";

registerBlockType("promotions/en-multistep", {
  title: __("Engaging Networks Multistep Form", "promotions"),
  icon: "embed-generic",
  category: "embed",
  attributes: {
    url: {
      type: "string",
      default: "",
    },
    formColor: {
      type: "string",
      default: "#f26722",
    },
    height: {
      type: "string",
      default: "500px",
    },
    borderRadius: {
      type: "string",
      default: "5px",
    },
    loadingColor: {
      type: "string",
      default: "#E5E6E8",
    },
    bounceColor: {
      type: "string",
      default: "#16233f",
    },
    appendUrlParams: {
      type: "boolean",
      default: false,
    },
  },
  supports: {
    align: true, // Enables alignment options
    alignWide: true, // Enables full-width alignment
  },

  edit: ({ attributes, setAttributes }) => {
    const {
      url,
      formColor,
      height,
      borderRadius,
      loadingColor,
      bounceColor,
      appendUrlParams,
    } = attributes;

    const blockProps = useBlockProps();
    // Add a class to the block wrapper
    blockProps.className = blockProps.className + " promotions-en-multistep";

    return (
      <div {...blockProps}>
        <TextControl
          label={__("EN Page URL", "promotions")}
          value={url}
          onChange={(value) => setAttributes({ url: value })}
        />
        <div className="color-control">
          <TextControl
            label={__("Form Color", "promotions")}
            value={formColor}
            onChange={(value) => setAttributes({ formColor: value })}
          />
          <ColorIndicator
            className="promotions-color-picker"
            colorValue={formColor}
          />
        </div>
        <TextControl
          label={__("Height", "promotions")}
          value={height}
          onChange={(value) => setAttributes({ height: value })}
        />
        <TextControl
          label={__("Border Radius", "promotions")}
          value={borderRadius}
          onChange={(value) => setAttributes({ borderRadius: value })}
        />
        <div className="color-control">
          <TextControl
            label={__("Loading Color", "promotions")}
            value={loadingColor}
            onChange={(value) => setAttributes({ loadingColor: value })}
          />
          <ColorIndicator
            className="promotions-color-picker"
            colorValue={loadingColor}
          />
        </div>
        <div className="color-control">
          <TextControl
            label={__("Bounce Color", "promotions")}
            value={bounceColor}
            onChange={(value) => setAttributes({ bounceColor: value })}
          />
          <ColorIndicator
            className="promotions-color-picker"
            colorValue={bounceColor}
          />
        </div>
        <ToggleControl
          label={__("Append URL Params", "promotions")}
          checked={appendUrlParams}
          onChange={(value) => setAttributes({ appendUrlParams: value })}
        />
      </div>
    );
  },

  save: ({ attributes }) => {
    const {
      url,
      formColor,
      height,
      borderRadius,
      loadingColor,
      bounceColor,
      appendUrlParams,
    } = attributes;

    const shortcode = `[en-multistep
      url="${escapeHTML(url)}"
      form-color="${escapeHTML(formColor)}"
      height="${escapeHTML(height)}"
      border-radius="${escapeHTML(borderRadius)}"
      loading-color="${escapeHTML(loadingColor)}"
      bounce-color="${escapeHTML(bounceColor)}"
      append-url-params="${appendUrlParams}"
    ]`;

    return <div>{shortcode}</div>;
  }, // The save function is optional for this block as we're using the shortcode.
});
