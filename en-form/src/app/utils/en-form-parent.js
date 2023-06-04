import "./confetti";
export class ENFormParent {
  constructor() {
    this.iframes = document.querySelectorAll(".promo-form-iframe");
    if (!this.iframes.length) {
      if (this.isDebug())
        console.log("ENFormParent: constructor: iframe not found");
      return;
    }
    console.log("ENFormParent: constructor");
    window.dataLayer = window.dataLayer || [];
    this.defaultOptions = {
      name: "4Site Engaging Networks iFrame",
      form_color: "#f26722",
      src: "",
      height: "",
      border_radius: "0",
      loading_color: "#E5E6E8",
      bounce_color: "#16233f",
      append_url_params: "false",
    };
    this.options = {};
    this.container = {};
    this.containerID = {};
    this.donationinfo = {};
    this.iframes.forEach((iframe, key) => {
      iframe.dataset.key = key;
      this.options[key] = { ...this.defaultOptions };
      this.donationinfo[key] = {};
    });
    this.init();
  }
  loadOptions() {
    this.iframes.forEach((iframe, key) => {
      // Get Data Attributes
      let data = iframe.dataset;
      // Set Options
      if ("name" in data) this.options[key].name = data.name;
      if ("form_color" in data) this.options[key].form_color = data.form_color;
      if ("src" in data) this.options[key].src = data.src;
      if ("height" in data) this.options[key].height = data.height;
      if ("border_radius" in data)
        this.options[key].border_radius = data.border_radius;
      if ("loading_color" in data)
        this.options[key].loading_color = data.loading_color;
      if ("bounce_color" in data)
        this.options[key].bounce_color = data.bounce_color;
      if ("append_url_params" in data)
        this.options[key].append_url_params = data.append_url_params;
      if (this.isDebug())
        console.log("ENFormParent: loadOptions: options: ", this.options[key]);
    });
  }
  init() {
    console.log("ENFormParent: init");
    window.addEventListener("message", this.receiveMessage.bind(this), false);
    this.loadOptions();
    this.build();
  }
  build() {
    if (this.isDebug()) console.log("ENFormParent: build");
    this.iframes.forEach((iframe, key) => {
      const src = new URL(this.options[key].src);
      this.containerID[key] =
        "foursite-" + Math.random().toString(36).substring(7);
      src.searchParams.append("color", this.options[key].form_color);
      if (this.options[key].height) {
        src.searchParams.append("height", this.options[key].height);
      }
      if (this.options[key].append_url_params.toLowerCase() === "true") {
        const urlParams = new URLSearchParams(window.location.search);
        for (const [key, value] of urlParams) {
          src.searchParams.append(key, value);
        }
      }
      const container = document.createElement("div");
      container.classList.add("foursiteENFormParent-container");
      container.id = this.containerID[key];
      const height = this.options[key].height ?? "400px";
      const markup = `
        <div class="dm-content" style="border-radius: ${this.options[key].border_radius}">
            <div class="dm-loading" style="background-color: ${this.options[key].loading_color}">
              <div class="spinner">
                <div class="double-bounce1" style="background-color: ${this.options[key].bounce_color}"></div>
                <div class="double-bounce2" style="background-color: ${this.options[key].bounce_color}"></div>
              </div>
            </div>
            <iframe style='height: ${height}; min-height: ${height};' allow='payment' loading='lazy' width='100%' scrolling='no' class='promo-form-iframe' src='${src}' data-key='${key}' frameborder='0' allowfullscreen></iframe>
        </div>
            `;
      container.innerHTML = markup;

      this.container[key] = container;
      iframe.parentNode.insertBefore(this.container[key], iframe);
      iframe.remove();
      const newIframe = this.container[key].querySelector("iframe");
      newIframe.addEventListener("load", () => {
        const action = "Viewed";
        const category = "Engaging Networks iFrame";
        const label = this.options[key].name;
        this.sendGAEvent(category, action, label);
        this.status("loaded", key);
      });
    });
  }

  getFrameByEvent(event) {
    return [].slice
      .call(document.getElementsByTagName("iframe"))
      .filter(function (iframe) {
        return iframe.contentWindow === event.source;
      })[0];
  }

  // Receive a message from the child iframe
  receiveMessage(event) {
    // console.log("ENFormParent: receiveMessage: event: ", event.data);
    const message = event.data;
    const iframe = this.getFrameByEvent(event);
    const key = iframe.dataset.key ?? 0;

    if (message && "frameHeight" in message) {
      iframe.style.height = message.frameHeight + "px";
      if ("scroll" in message && !this.isInViewport(iframe)) {
        // Scroll to the top of the iframe smoothly
        iframe.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
      return;
    }

    switch (message.key) {
      case "status":
        this.status(message.value, key);
        break;
      case "error":
        this.error(message.value, key);
        break;
      case "class":
        this.container[key].classList.add(message.value);
        break;
      case "donationinfo":
        this.donationinfo[key] = JSON.parse(message.value);
        console.log(
          "ENFormParent: receiveMessage: donationinfo: ",
          this.donationinfo[key]
        );
        break;
    }
  }
  status(status, key) {
    switch (status) {
      case "loading":
        this.container[key]
          .querySelector(".dm-loading")
          .classList.remove("is-loaded");
        break;
      case "loaded":
        this.container[key]
          .querySelector(".dm-loading")
          .classList.add("is-loaded");
        break;
      case "submitted":
        this.donationinfo[key].frequency =
          this.donationinfo[key].frequency == "no"
            ? ""
            : this.donationinfo[key].frequency;
        let iFrameUrl = new URL(
          this.container[key].querySelector("iframe").src
        );
        for (const key in this.donationinfo[key]) {
          iFrameUrl.searchParams.append(key, this.donationinfo[key]);
        }
        this.container[key].querySelector("iframe").src = iFrameUrl
          .toString()
          .replace("/donate/1", "/donate/2");
        break;
      case "celebrate":
        const motion = window.matchMedia("(prefers-reduced-motion: reduce)");
        if (!motion.matches) {
          this.startConfetti();
        }
        break;
    }
  }
  error(error, key) {
    this.shake(key);
    // console.error(error);
    const container = this.container[key].querySelector(".dm-content");
    const errorMessage = document.createElement("div");
    errorMessage.classList.add("error-message");
    errorMessage.style.borderRadius = this.options.border_radius;
    errorMessage.innerHTML = `<p>${error}</p><a class="close" href="#">Close</a>`;
    errorMessage.querySelector(".close").addEventListener("click", (e) => {
      e.preventDefault();
      errorMessage.classList.remove("dm-is-visible");
      // One second after close animation ends, remove the error message
      setTimeout(() => {
        errorMessage.remove();
      }, 1000);
    });
    container.appendChild(errorMessage);
    // 300ms after error message is added, show the error message
    setTimeout(() => {
      errorMessage.classList.add("dm-is-visible");
      // Five seconds after error message is shown, remove the error message
      setTimeout(() => {
        errorMessage.querySelector(".close").click();
      }, 5000);
    }, 300);
  }
  startConfetti() {
    const duration = 3 * 1000;
    const animationEnd = Date.now() + duration;
    const defaults = {
      startVelocity: 30,
      spread: 360,
      ticks: 60,
      zIndex: 100000,
      useWorker: false,
    };

    const randomInRange = (min, max) => {
      return Math.random() * (max - min) + min;
    };

    const interval = setInterval(function () {
      const timeLeft = animationEnd - Date.now();

      if (timeLeft <= 0) {
        return clearInterval(interval);
      }

      const particleCount = 50 * (timeLeft / duration);
      // since particles fall down, start a bit higher than random
      confetti(
        Object.assign({}, defaults, {
          particleCount,
          origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
        })
      );
      confetti(
        Object.assign({}, defaults, {
          particleCount,
          origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
        })
      );
    }, 250);
  }
  shake(key) {
    this.container[key].classList.add("shake");
    // Remove class after 1 second
    setTimeout(() => {
      this.container[key].classList.remove("shake");
    }, 1000);
  }
  sendGAEvent(category, action, label) {
    if ("sendEvent" in window) {
      window.sendEvent(category, action, label, null);
    } else {
      window.dataLayer.push({
        event: "event",
        eventCategory: category,
        eventAction: action,
        eventLabel: label,
      });
    }
  }
  isInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      // rect.bottom <=
      //   (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }
  isDebug() {
    const regex = new RegExp("[\\?&]debug=([^&#]*)");
    const results = regex.exec(location.search);
    return results === null
      ? ""
      : decodeURIComponent(results[1].replace(/\+/g, " "));
  }
}
