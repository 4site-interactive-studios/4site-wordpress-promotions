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
  hexToHSL(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  
    if (!result) {
      throw new Error("Could not parse Hex Color");
    }
  
    const rHex = parseInt(result[1], 16);
    const gHex = parseInt(result[2], 16);
    const bHex = parseInt(result[3], 16);
  
    const r = rHex / 255;
    const g = gHex / 255;
    const b = bHex / 255;
  
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