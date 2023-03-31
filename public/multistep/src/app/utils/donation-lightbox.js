import "./confetti";
export class DonationLightbox {
  constructor(opts) { 
    window.dataLayer = window.dataLayer || [];
    this.defaultOptions = {
      name: "4Site Multi-Step Splash",
      image: "",
      video: "",
      logo: "",
      title: "",
      paragraph: "",
      mobile_enabled: false,
      mobile_title: "",
      mobile_paragraph: "",
      footer: "",
      bg_color: "#254d68",
      txt_color: "#FFFFFF",
      form_color: "#2375c9",
      url: null,
      cookie_hours: 24,
      cookie_name: "HideDonationLightbox"
    };
    this.donationinfo = {};
    if(opts) {
      this.options = Object.assign(this.defaultOptions, opts);
    } else {
      this.options = { ...this.defaultOptions };      
    }
    this.animationCount = 0;

    this.init();
  }
  setOptions(options) {
    this.options = Object.assign(this.options, options);
  }
  loadOptions(element = null) {
    if (typeof window.DonationLightboxOptions !== "undefined") {
      this.setOptions(
        Object.assign(this.defaultOptions, window.DonationLightboxOptions)
      );
    } else {
      this.setOptions(this.defaultOptions);
    }
    if (!element) {
      return;
    }
    // Get Data Attributes
    let data = element.dataset;

    // Set Options
    if ("name" in data) {
      this.options.name = data.name;
    }
    if ("image" in data) {
      this.options.image = data.image;
    }
    if ("video" in data) {
      this.options.video = data.video;
    }
    if ("autoplay" in data) {
      this.options.autoplay = data.autoplay;
    } else {
      this.options.autoplay = false;
    }
    if ("divider" in data) {
      this.options.divider = data.divider;
    }

    if ("logo" in data) {
      this.options.logo = data.logo;
    }
    if ("title" in data) {
      this.options.title = data.title;
    }
    if ("paragraph" in data) {
      this.options.paragraph = data.paragraph;
    }
    if ("mobile_enabled" in data) {
      this.options.mobile_enabled = data.mobile_enabled;
    }
    if ("mobile_title" in data) {
      this.options.mobile_title = data.mobile_title;
    }
    if ("mobile_paragraph" in data) {
      this.options.mobile_paragraph = data.mobile_paragraph;
    }
    if ("footer" in data) {
      this.options.footer = data.footer;
    }
    if ("bg_color" in data) {
      this.options.bg_color = data.bg_color;
    }
    if ("txt_color" in data) {
      this.options.txt_color = data.txt_color;
    }
    if ("form_color" in data) {
      this.options.form_color = data.form_color;
    }
  }
  init() {
    document.querySelectorAll("[data-donation-lightbox]").forEach((e) => {
      e.addEventListener(
        "click",
        (event) => {
          // Get clicked element
          let element = event.target;
          this.build(event);
        },
        false
      );
    });
    window.addEventListener("message", this.receiveMessage.bind(this), false);
    if (
      typeof window.DonationLightboxOptions !== "undefined" &&
      window.DonationLightboxOptions.hasOwnProperty("url") &&
      !this.getCookie()
    ) {
      this.build(window.DonationLightboxOptions.url);
    }
  }
  build(event) {
    let href = null;
    if (typeof event === "object") {
      // Get clicked element
      let element = event.target.closest("a");
      this.loadOptions(element);
      href = new URL(element.href);
    } else {
      href = new URL(event);
      this.loadOptions();
    }
    // Do not build if mobile is disabled and on mobile
    if (!this.options.mobile_enabled && this.isMobile()) {
      return;
    }
    if (typeof event.preventDefault === "function") {
      event.preventDefault();
    }
    // Delete overlay if exists
    if (this.overlay) {
      this.overlay.parentNode.removeChild(this.overlay);
    }
    this.overlayID = "foursite-" + Math.random().toString(36).substring(7);
    href.searchParams.append("color", this.options.form_color);
    const markup = `
      <div class="foursiteDonationLightbox-mobile-container">
        <h1 class="foursiteDonationLightbox-mobile-title">${
          this.options.mobile_title
        }</h1>
        <p class="foursiteDonationLightbox-mobile-paragraph">${
          this.options.mobile_paragraph
        }</p>
      </div>
      <div class="foursiteDonationLightbox-container">
        ${
          this.options.logo
            ? `<img class="dl-mobile-logo" src="${this.options.logo}" alt="${this.options.title}">`
            : ""
        }
        <div class="dl-content">
          <div class="left" style="background-color: ${
            this.options.bg_color
          }; color: ${this.options.txt_color}">
            ${
              this.options.logo
                ? `<img class="dl-logo" src="${this.options.logo}" alt="${this.options.title}">`
                : ""
            }
            <a href="#" class="dl-close-viewmore" style="color: ${
              this.options.bg_color
            };">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 16 16">
                <path fill="currentColor" d="M7.214.786c.434-.434 1.138-.434 1.572 0 .433.434.433 1.137 0 1.571L4.57 6.572h10.172c.694 0 1.257.563 1.257 1.257s-.563 1.257-1.257 1.257H4.229l4.557 4.557c.433.434.433 1.137 0 1.571-.434.434-1.138.434-1.572 0L0 8 7.214.786z"></path>
              </svg>
            </a>
            <div class="dl-container">
              ${this.loadHero()}
              ${
                this.options.divider
                  ? `<img class="dl-divider" src="${this.options.divider}" alt="Divider">`
                  : ""
              }
              <div class="dl-container-inner" style="background-color: ${
                this.options.bg_color
              }; color: ${this.options.txt_color}">
                <h1 class="dl-title" style="color: ${this.options.txt_color}">${
      this.options.title
    }</h1>
                <p class="dl-paragraph" style="color: ${
                  this.options.txt_color
                }">${this.options.paragraph}</p>
                <a class="dl-viewmore" href="#"style="color: ${
                  this.options.txt_color
                }; border-color: ${this.options.txt_color}">View More</a>
              </div>
              <div class="dl-celebration">
                <div class="frame frame1">
                    <h3>and the animals</h3>
                    <h2>THANK YOU!</h2>
                </div>
                <div class="frame frame2">
                  <div id="bunnyAnimation"></div>
                </div>
                <div class="frame frame3">
                  <h2 class="name">Fernando,</h2>
                  <h2 class="phrase">you are a hero <br>to animals.</h2>
                </div>
              </div>
            </div>
          </div>
          <div class="right">
            <a href="#" class="dl-button-close"></a>
            <div class="dl-loading" style="background-color: ${
              this.options.form_color
            }">
              <div class="spinner">
                <div class="double-bounce1"></div>
                <div class="double-bounce2"></div>
              </div>
            </div>
            <iframe allow='payment' loading='lazy' id='dl-iframe' width='100%' scrolling='no' class='dl-iframe' src='${href}' frameborder='0' allowfullscreen></iframe>
          </div>
        </div>
        <div class="dl-footer">
          <p>${this.options.footer}</p>                    
        </div>
      </div>
    `;

    const additionalStylesElement = document.head.appendChild(
      document.createElement("style")
    );

    additionalStylesElement.innerHTML = `
      p.dl-paragraph::after {
        position: absolute;
        bottom: 0;
        right: 0;

        background: rgb(205, 228, 252);
        background: linear-gradient(360deg, ${this.options.bg_color}, rgba(205, 228, 252, 0));
        content: "";
        height: 60px;
        transition: 0.3s transform ease-in-out;
        width: 100%;
      }

      .dl-container-inner::-webkit-scrollbar-thumb {
        background: ${this.options.form_color};
        border-radius: 10px;
      }

      .dl-container.playing .btn-pause:hover {
        color: ${this.options.form_color}
      }
    `;
    let overlay = document.createElement("div");
    overlay.id = this.overlayID;
    overlay.classList.add("is-hidden");
    overlay.classList.add("foursiteDonationLightbox");
    overlay.innerHTML = markup;
    const closeButton = overlay.querySelector(".dl-button-close");
    closeButton.addEventListener("click", this.close.bind(this));
    overlay.addEventListener("click", (e) => {
      if (e.target.id == this.overlayID) {
        this.close(e);
      }
    });

    const closeViewMore = overlay.querySelector(".dl-close-viewmore");
    closeViewMore.addEventListener("click", (e) => {
      e.preventDefault();
      overlay.querySelector(".left").classList.remove("view-more");
    });

    const viewmore = overlay.querySelector(".dl-viewmore");
    viewmore.addEventListener("click", (e) => {
      e.preventDefault();
      overlay.querySelector(".left").classList.add("view-more");
    });

    const videoElement = overlay.querySelector("video");
    if (videoElement) {
      const playButton = overlay.querySelector(".btn-play");
      const pauseButton = overlay.querySelector(".btn-pause");

      if (playButton) {
        playButton.addEventListener("click", () => {
          videoElement.play();
        });
      }

      if (pauseButton) {
        pauseButton.addEventListener("click", () => {
          videoElement.pause();
        });
      }

      videoElement.addEventListener("play", (event) => {
        overlay.querySelector(".dl-container").classList.add("playing");
        overlay.querySelector(".dl-container").classList.remove("paused");
      });

      videoElement.addEventListener("pause", (event) => {
        overlay.querySelector(".dl-container").classList.remove("playing");
        overlay.querySelector(".dl-container").classList.add("paused");
      });

      videoElement.addEventListener("ended", (event) => {
        overlay.querySelector(".dl-container").classList.remove("playing");
        overlay.querySelector(".dl-container").classList.remove("paused");
        videoElement.load();
      });
    }

    document.addEventListener("keyup", (e) => {
      if (e.key === "Escape") {
        closeButton.click();
      }
    });
    // If there's no mobile title & paragraph, hide the mobile container
    if (
      this.options.mobile_title == "" &&
      this.options.mobile_paragraph == ""
    ) {
      overlay.querySelector(
        ".foursiteDonationLightbox-mobile-container"
      ).style.display = "none";
    }
    this.overlay = overlay;
    document.body.appendChild(overlay);
    this.open();
  }
  open() {
    const action = window.petaGA_GenericAction_Viewed ?? "Viewed";
    const category = window.petaGA_SplashCategory ?? "Splash Page";
    const label = window.petaGA_SplashLabel ?? this.options.name;
    this.sendGAEvent(category, action, label);
    this.overlay.classList.remove("is-hidden");
    document.body.classList.add("has-DonationLightbox");
  }

  close(e) {
    const action = window.petaGA_GenericAction_Closed ?? "Closed";
    const category = window.petaGA_SplashCategory ?? "Splash Page";
    const label = window.petaGA_SplashLabel ?? this.options.name;
    const videoElement = this.overlay.querySelector("video");
    this.sendGAEvent(category, action, label);
    e.preventDefault();
    this.overlay.classList.add("is-hidden");
    document.body.classList.remove("has-DonationLightbox");
    if (videoElement) {
      videoElement.pause();
    }
    if (this.options.url) {
      this.setCookie(this.options.cookie_hours);
    }
  }
  // Receive a message from the child iframe
  receiveMessage(event) {

    const message = event.data;

    switch (message.key) {
      case "status":
        this.status(message.value, event);
        break;
      case "error":
        this.error(message.value, event);
        break;
      case "class":
        document
          .querySelector(".foursiteDonationLightbox")
          .classList.add(message.value);
        break;
      case "donationinfo":
        this.donationinfo = JSON.parse(message.value);
        break;
      case "firstname":
        const firstname = message.value;
        const nameHeading = document.querySelector(".dl-celebration h2.name");
        if (nameHeading) {
          nameHeading.innerHTML = firstname + ",";
          if (firstname.length > 12) {
            nameHeading.classList.add("big-name");
          }
        }
        break;
    }
  }
  status(status, event) {
    switch (status) {
      case "loading":
        document.querySelector(".dl-loading").classList.remove("is-loaded");
        break;
      case "loaded":
        document.querySelector(".dl-loading").classList.add("is-loaded");
        break;
      case "submitted":
        this.donationinfo.frequency =
          this.donationinfo.frequency == "no"
            ? ""
            : this.donationinfo.frequency;
        let iFrameUrl = new URL(document.getElementById("dl-iframe").src);
        for (const key in this.donationinfo) {
          iFrameUrl.searchParams.append(key, this.donationinfo[key]);
        }
        document.getElementById("dl-iframe").src = iFrameUrl
          .toString()
          .replace("/donate/1", "/donate/2");
        break;
      case "close":
        this.close(event);
        break;
      case "celebrate":
        const motion = window.matchMedia("(prefers-reduced-motion: reduce)");
        if (motion.matches) {
          this.celebrate(false);
        } else {
          this.celebrate(true);
        }
        break;
      case "footer":
        const action = window.petaGA_GenericAction_Clicked ?? "Clicked";
        const category = window.petaGA_SplashCategory ?? "Splash Page";
        const label = window.petaGA_SplashLabel ?? this.options.name;
        this.sendGAEvent(category, action, label);
        const footer = document.querySelector(".dl-footer");
        if (footer) {
          footer.classList.add("open");
        }
        break;
    }
  }
  error(error, event) {
    this.shake();
    const container = document.querySelector(
      ".foursiteDonationLightbox .right"
    );
    const errorMessage = document.createElement("div");
    errorMessage.classList.add("error-message");
    errorMessage.innerHTML = `<p>${error}</p><a class="close" href="#">Close</a>`;
    errorMessage.querySelector(".close").addEventListener("click", (e) => {
      e.preventDefault();
      errorMessage.classList.remove("dl-is-visible");
      // One second after close animation ends, remove the error message
      setTimeout(() => {
        errorMessage.remove();
      }, 1000);
    });
    container.appendChild(errorMessage);
    // 300ms after error message is added, show the error message
    setTimeout(() => {
      errorMessage.classList.add("dl-is-visible");
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
  celebrate(animate = true) {
    const leftContainer = document.querySelector(
      `#${this.overlayID} .dl-content .left`
    );
    const newLogo =
      'data:image/svg+xml;utf8,<svg width="146" height="146" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M73 146c40.317 0 73-32.683 73-73S113.317 0 73 0 0 32.683 0 73s32.683 73 73 73z" fill="%23fff"/><path d="M36.942 53.147H25.828L14.49 95.107h9.391l4.553-16.321-.502 2.15c6.335.55 20.226.661 22.235-14.668 2.003-14.83-13.225-13.121-13.225-13.121zm1.056 17.533c-3.168 2.978-7.725 1.93-7.725 1.93l1.278-4.686 1.722-6.232c.667-.055 5.224-.551 6.503.498 1.39 1.103.39 6.451-1.78 8.492l.002-.002zM78.513 56.345a6.223 6.223 0 0 0-3.169-2.537c-8.057-2.595-14.671 3.529-19.284 9.428a37.298 37.298 0 0 0-7.558 21.394c.222 4.577 1.334 9.704 6.058 11.524 7.724 1.93 13.394-4.577 17.56-9.98.444-.771 1.222-1.433 1.222-2.316-.333-.165-.612-.44-1.005-.44a29.047 29.047 0 0 1-2.89 3.693c-2.666 2.592-6.057 4.632-9.948 3.75-4.279-1.93-4.446-6.893-4.056-11.193.193-1.652.566-3.28 1.112-4.852l2.889-.828c6.39-2.095 13.944-2.536 18.228-8.932 1.445-2.427 2.612-6.065.835-8.713l.006.002zM64.286 70.46c-8.725 2.812-6.558 1.654-6.558 1.654s6.668-19.74 13.448-17.425c6.892 2.316 1.885 12.963-6.892 15.77h.002zM83.46 53.312l27.899-.165-2.445 9.042-9.114.166-9.282 32.752H80.29L89.24 62.3h-8.392l2.613-8.988zM119.471 53.256l-20.34 41.796h10.504l3.224-6.892h9.448v6.837l9.226-.055V53.147l-12.06.11h-.002zm-2.834 26.631 5.558-11.965.055-.165v12.13h-5.613z" fill="%23FEBA4B"/></svg>';
    const logo = leftContainer.querySelector(".dl-logo");
    if (!animate) {
      leftContainer.classList.add("celebrating");
      if (logo) {
        logo.src = newLogo;
        logo.style.maxWidth = "98px";
        logo.style.transform = "translateX(-50%)";
        logo.style.left = "50%";
        logo.style.top = "20px";
      }
      const frame1 = leftContainer.querySelector(".frame1");
      frame1.style.bottom = "360px";
      const celebratingDiv = document.querySelector(".dl-celebration");
      celebratingDiv.style.backgroundImage = `url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="46" height="38" fill="none" viewBox="0 0 46 38"><path fill="%23C92533" d="M33.707 0C29.268 0 25.174 2.166 23 5.664 20.826 2.166 16.732 0 12.293 0 5.504 0 0 5.693 0 11.83 0 27.245 23 38 23 38s23-10.755 23-26.17C46 5.693 40.496 0 33.707 0z"/></svg>')`;
      celebratingDiv.style.backgroundSize = "80%";
      celebratingDiv.style.backgroundPosition = "center 215px";
      celebratingDiv.style.backgroundRepeat = "no-repeat";

      return;
    }
    if (this.isMobile()) {
      this.startConfetti();
      return;
    }

    // Left Animation
    leftContainer.classList.add("celebrating");
    if (logo) {
      logo.src = newLogo;
    }
    this.loadScript(
      "https://cdnjs.cloudflare.com/ajax/libs/gsap/3.2.0/gsap.min.js",
      () => {
        const tl = gsap.timeline({
          onComplete: this.startBunny(),
        });
        if (logo) {
          tl.to(".dl-logo", {
            duration: 1,
            x: "50%",
            right: "50%",
            top: "155px",
            maxWidth: "145px",
            ease: "power1.inOut",
          });
        }
        tl.to(
          ".frame1",
          {
            bottom: "150px",
            duration: 1,
            ease: "power1.inOut",
          },
          ">-1"
        );
        if (logo) {
          tl.to(".dl-logo", {
            duration: 1,
            delay: 1,
            top: "20px",
            maxWidth: "98px",
            ease: "power1.inOut",
          });
        }
        tl.to(
          ".frame1",
          {
            bottom: "360px",
            duration: 1,
            ease: "power1.inOut",
          },
          ">-1"
        );
      }
    );
  }
  startBunny() {
    this.loadScript(
      "https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.14/lottie.min.js",
      () => {
        const tl2 = gsap.timeline();
        tl2.to(".frame2", {
          opacity: "1",
          duration: 1,
          ease: "power1.inOut",
        });
        tl2.add(() => {
          const anim = bodymovin.loadAnimation({
            container: document.querySelector("#bunnyAnimation"),
            renderer: "svg",
            loop: false,
            autoplay: true,
            path: "https://000665513.codepen.website/data.json",
          });
          anim.addEventListener("complete", () => {
            if (this.animationCount > 3) {
              anim.goToAndPlay(130, true);
              this.animationCount++;
            } else {
              this.startConfetti();
            }
          });
        }, "+=0.5");
        // Make the text grow
        tl2.fromTo(".frame3", 1, { scale: 0 }, { scale: 1 }, "+=6");
      }
    );
  }

  shake() {
    const element = document.querySelector(".dl-content");
    if (element) {
      element.classList.add("shake");
      // Remove class after 1 second
      setTimeout(() => {
        element.classList.remove("shake");
      }, 1000);
    }
  }
  setCookie(hours = 24, path = "/") {
    const expires = new Date(Date.now() + hours * 36e5).toUTCString();
    document.cookie =
      this.options.cookie_name +
      "=" +
      encodeURIComponent(true) +
      "; expires=" +
      expires +
      "; path=" +
      path;
  }

  getCookie() {
    return document.cookie.split("; ").reduce((r, v) => {
      const parts = v.split("=");
      return parts[0] === this.options.cookie_name
        ? decodeURIComponent(parts[1])
        : r;
    }, "");
  }

  deleteCookie(path = "/") {
    setCookie(this.options.cookie_name, "", -1, path);
  }
  loadScript(url, callback) {
    const script = document.createElement("script");
    script.src = url;
    document.body.appendChild(script);

    script.onload = () => {
      if (callback) callback();
    };
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
  isMobile() {
    // Check the viewport width to see if the user is using a mobile device
    const viewportWidth = Math.max(
      document.documentElement.clientWidth || 0,
      window.innerWidth || 0
    );
    return viewportWidth <= 799;
  }
  loadHero() {
    if (!this.options.video) {
      return `<img class="dl-hero" src="${this.options.image}" alt="${this.options.title}" />`;
    }
    const autoplay = this.options.autoplay || false;
    let markup = autoplay
      ? `<video autoplay muted loop playsinline`
      : `<video playsinline`;
    markup += ` poster="${this.options.image}">`;
    markup += `<source src="${this.options.video}" type="video/mp4">`;
    markup += `</video>`;
    return `<div class="dl-hero">
    ${markup}
    ${
      !autoplay
        ? `<div class="btn-play">
              <svg class="play-svg" xmlns="http://www.w3.org/2000/svg" width="26" height="31" viewBox="0 0 55.127 61.182"><g id="Group_38215" data-name="Group 38215" transform="translate(30 35)" fill="currentColor"><g id="play-button-arrowhead_1_" data-name="play-button-arrowhead (1)" transform="translate(-30 -35)"><path id="Path_18" data-name="Path 18" d="M18.095,1.349C12.579-1.815,8.107.777,8.107,7.134v46.91c0,6.363,4.472,8.952,9.988,5.791l41-23.514c5.518-3.165,5.518-8.293,0-11.457Z" transform="translate(-8.107 0)"/></g></g></svg>
            </div>

            <div class="btn-pause">
              <svg class="pause-svg" xmlns="http://www.w3.org/2000/svg" width="31" height="31" viewBox="0 0 31 31"><path d="M10 31h-6v-31h6v31zm15-31h-6v31h6v-31z" fill="currentColor" /></svg>
            </div>`
        : ""
    }
    </div>`;
  }
}
