export class Pushdown {
  constructor() {
    const scriptTag = document.querySelector("script[src*='pushdown.js']");
    this.mode = scriptTag.getAttribute("data-pushdown-mode") || "text";
    this.content = scriptTag.getAttribute("data-pushdown-content") || "";
    this.link = scriptTag.getAttribute("data-pushdown-link") || "";
    this.image = scriptTag.getAttribute("data-pushdown-image") || "";
    this.gif = scriptTag.getAttribute("data-pushdown-gif") || "";
    this.bg_color = scriptTag.getAttribute("data-pushdown-bg-color");
    this.fg_color = scriptTag.getAttribute("data-pushdown-fg-color");
    this.paragraph = scriptTag.getAttribute("data-pushdown-paragraph") || "";
    this.button_label = scriptTag.getAttribute("data-pushdown-button-label") || "";
    if (this.link) {
      this.init();
      return;
    }
  }
  init() {
    this.createContainer();
  }
  createContainer() {
    const container = document.createElement("div");
    container.setAttribute("id", "pushdown");
    container.classList.add("pushdown-mode-" + this.mode);
    container.innerHTML = this.getContent();
    const body = document.querySelector("body");
    body.insertBefore(container, body.firstChild);
  }
  lightOrDark(hex_color) {
    hex_color = +("0x" + hex_color.slice(1).replace( 
      hex_color.length < 5 && /./g, '$&$&'
    ));

    let r = hex_color >> 16;
    let g = hex_color >> 8 & 255;
    let b = hex_color & 255;

    let hsp = Math.sqrt(
      0.299 * (r * r) +
      0.587 * (g * g) +
      0.114 * (b * b)
    );

    if (hsp > 127.5) {
      return 'light';
    } else {
      return 'dark';
    }
  }
  hexToHSL(hex_color) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex_color);
  
    if (!result) {
      throw new Error("Could not parse Hex Color");
    }
  
    const r_hex = parseInt(result[1], 16);
    const g_hex = parseInt(result[2], 16);
    const b_hex = parseInt(result[3], 16);
  
    const r = r_hex / 255;
    const g = g_hex / 255;
    const b = b_hex / 255;
  
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
  
    let h = (max + min) / 2;
    let s = h;
    let l = h;
  
    if (max === min) {
      // Achromatic
      return { h: 0, s: 0, l };
    }
  
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    switch (max) {
      case r:
        h = (g - b) / d + (g < b ? 6 : 0);
        break;
      case g:
        h = (b - r) / d + 2;
        break;
      case b:
        h = (r - g) / d + 4;
        break;
    }
    h /= 6;
  
    s = s * 100;
    s = Math.round(s);
    l = l * 100;
    l = Math.round(l);
    h = Math.round(360 * h);
  
    return { h, s, l };
  }
  getContent() {
    let content = "";
    let style = "";

    let style_css = '';
    if(this.fg_color) {
      style_css += `color:${this.fg_color};`;
    }
    if(this.bg_color) {
      style_css += `background-color:${this.bg_color};`;
    }

    if (this.mode === "text") {
      content = `<a class="pushdown-link" href="${this.link}" style="${style_css}">
      <h2 class="pushdown-title" style="${style_css}">${this.content}</h2> 
      <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.266 0.000686225L10.2241 7.95951L2.18366 16L0.995117 14.8115L7.84706 7.95951L1.07746 1.18991L2.266 0V0.000686225Z" fill="currentColor"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.2709 0.000686225L19.229 7.95951L11.1885 16L10 14.8115L16.8519 7.95951L10.0823 1.18991L11.2709 0V0.000686225Z" fill="currentColor"/>
      </svg>
      </a>`;

      style = `
        #pushdown.pushdown-mode-text svg {
          color: ${this.fg_color};
          background-color: ${this.bg_color};
        }
        #pushdown.pushdown-mode-text .pushdown-link:hover svg {
          background-color: ${this.fg_color};
          color: ${this.bg_color};
        }
      `;
    } else if (this.mode === "full") {
      let button_style_css = "";
      let button_hover_style_css = "";
      const dark_fg_color = (this.lightOrDark(this.fg_color) === 'dark');
      if(this.fg_color) {
        button_style_css += `background-color:${this.fg_color}!important;`;
        button_style_css += `border-color:${this.fg_color}!important;`;
        button_hover_style_css += `color:${this.fg_color}!important;`;

        if(dark_fg_color) {
          button_style_css += `color:white!important;`;
        }
      }
      if(this.bg_color) {
        button_hover_style_css += `background-color:${this.bg_color}!important;`;
        if(!dark_fg_color) {
          button_style_css += `color:${this.bg_color}!important;`;
        }
      }

      content = `
      <div class="pushdown-wrapper" style="background-image: url('${this.image}');">
        <div class="pushdown-close" onclick="window.scrollBy({ left: 0, top: document.getElementById('pushdown').getBoundingClientRect().height, behavior: 'smooth' });">
          <div class="pushdown-close-inner">
            <!-- close SVG -->
            <svg class="close-icon" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g id="icons">
                <path id="Union" d="M31.9995 1.88235L30.1172 0L16 14.1174L1.88278 3.09577e-05L0.000453111 1.88238L14.1177 15.9998L0 30.1176L1.88233 32L16 17.8821L30.1177 32L32 30.1176L17.8823 15.9998L31.9995 1.88235Z" fill="white"/>
              </g>
            </svg>
          </div>
        </div>

        <img class="pushdown-mobile-image" src="${this.image}" />
        <div class="pushdown-overlay" style="${style_css}">
          <h2 class="pushdown-title">${this.content}</h2>
          <div class="pushdown-paragraph">
            ${this.paragraph}
          </div>
          <a class="pushdown-overlay-button" href="${this.link}" target="_blank">
            ${this.button_label}
          </a>
        </div>
      </div>
      `;

      style = `
        .pushdown-overlay-button {
          ${button_style_css}
        }
        .pushdown-overlay-button:hover {
          ${button_hover_style_css}
        }
      `;
    } else {
      content = `
      <a class="pushdown-link" href="${this.link}" style="background-image: url('${this.image}');${style_css}">
        <div class="pushdown-content" style="${style_css}">
          <h2 class="pushdown-title" style="${style_css}">${this.content}</h2>
      `;

      if (this.gif) {
        content += `<img src="${this.gif}" alt="${this.content}">`;
      }

      if(this.bg_color) {
        let hsl = this.hexToHSL(this.bg_color);
        let hsl_code = `${hsl.h}, ${hsl.s}%, ${hsl.l}%`;
        style = `
          #pushdown.pushdown-mode-image .pushdown-content::after {
            background: linear-gradient(
              to right,
              hsl(${hsl_code}) 0%,
              hsla(${hsl_code}, 0.987) 8.1%,
              hsla(${hsl_code}, 0.951) 15.5%,
              hsla(${hsl_code}, 0.896) 22.5%,
              hsla(${hsl_code}, 0.825) 29%,
              hsla(${hsl_code}, 0.741) 35.3%,
              hsla(${hsl_code}, 0.648) 41.2%,
              hsla(${hsl_code}, 0.55) 47.1%,
              hsla(${hsl_code}, 0.45) 52.9%,
              hsla(${hsl_code}, 0.352) 58.8%,
              hsla(${hsl_code}, 0.259) 64.7%,
              hsla(${hsl_code}, 0.175) 71%,
              hsla(${hsl_code}, 0.104) 77.5%,
              hsla(${hsl_code}, 0.049) 84.5%,
              hsla(${hsl_code}, 0.013) 91.9%,
              hsla(${hsl_code}, 0) 100%
            );
          }
        `;  
      }

      content += "</div> </a>";
    }

    style = `<style>${style}</style>`;
    return content + style;
  }
}