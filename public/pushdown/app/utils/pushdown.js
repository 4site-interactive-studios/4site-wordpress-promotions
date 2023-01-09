export class Pushdown {
  constructor() {
    console.log("Pushdown: constructor");
    const scriptTag = document.querySelector("script[src*='pushdown.js']");
    this.mode = scriptTag.getAttribute("data-pushdown-mode") || "text";
    this.content = scriptTag.getAttribute("data-pushdown-content") || "";
    this.link = scriptTag.getAttribute("data-pushdown-link") || "";
    this.image = scriptTag.getAttribute("data-pushdown-image") || "";
    if (this.link) {
      this.init();
      return;
    }
    console.log("Pushdown: No Link");
  }
  init() {
    console.log("Pushdown: init");
    this.createContainer();
  }
  createContainer() {
    console.log("Pushdown: createContainer");
    const container = document.createElement("div");
    container.setAttribute("id", "pushdown");
    container.classList.add("pushdown-mode-" + this.mode);
    container.innerHTML = this.getContent();
    const body = document.querySelector("body");
    body.insertBefore(container, body.firstChild);
  }
  getContent() {
    console.log("Pushdown: getContent");
    let content = "";
    if (this.mode === "text") {
      content = `<a class="pushdown-link" href="${this.link}">
      <h2 class="pushdown-title">${this.content}</h2> 
      <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.266 0.000686225L10.2241 7.95951L2.18366 16L0.995117 14.8115L7.84706 7.95951L1.07746 1.18991L2.266 0V0.000686225Z" fill="currentColor"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.2709 0.000686225L19.229 7.95951L11.1885 16L10 14.8115L16.8519 7.95951L10.0823 1.18991L11.2709 0V0.000686225Z" fill="currentColor"/>
      </svg>
      </a>`;
    } else {
      content = `<a class="pushdown-link" href="${this.link}" style="background-image: url('${this.image}')">
      <div class="pushdown-content">
      <h2 class="pushdown-title">${this.content}</h2>
      <img src="https://aaf1a18515da0e792f78-c27fdabe952dfc357fe25ebf5c8897ee.ssl.cf5.rackcdn.com/1839/pushdown-highlight.gif?v=1636659064000" alt="${this.content}">
      </div>
      </a>`;
    }
    return content;
  }
}
