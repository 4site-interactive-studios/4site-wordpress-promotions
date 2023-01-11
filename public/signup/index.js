//import css from "./main.css";
import scss from "./sass/main.scss";
import { crumbs } from "./crumbs";
// import { options } from './config';

const body = document.querySelector("body");

let image = "";
if (options.imageURL) {
  image += `<img src="${options.imageURL}" />`;
}
let logo = "";
if (options.logoURL) {
  logo += `<img src="${options.logoURL}" />`;
}

let content = "";
if (options.title) {
  content += `<h1>${options.title}</h1>`;
}
if (options.paragraph) {
  content += `<p>${options.paragraph}</p>`;
}
if (options.iframe) {
  content += `${options.iframe.replace("data-src", "src")}`;
}
if (options.info) {
  content += `<p class="italic">${options.info}</p>`;
}

const hideSignUpForm = !!parseInt(crumbs.get(options.cookie_name)); // Get cookie

// This is for disable mobile view
const viewportWidth = Math.max(
  document.documentElement.clientWidth || 0,
  window.innerWidth || 0
);

const isIE = !!document.documentMode;

const setLightbox = () => {
  if (!hideSignUpForm) {
    crumbs.set(options.cookie_name, 0, {
      type: "day",
      value: 1,
    });
  }

  if (
    !isBetweenDates() ||
    isBlacklisted() ||
    !isWhitelisted() ||
    hideSignUpForm ||
    viewportWidth < 600 ||
    isIE
  ) {
    return;
  } else {
    body.insertAdjacentHTML(
      "afterbegin",
      `
  <div class="lightbox hidden" style="display: none;">
    <div class="lightbox-content">
      <div class="close-btn"></div>
      <div class="logo">
        ${logo}
      </div>
      <div class="container-image">
        ${image}
      </div>
      <div class="container-form">
        ${content}
      </div>
    </div>
  </div>`
    );
  }
  const lightbox = document.querySelector(".lightbox");

  const lightBoxClose = document.querySelector(".close-btn");
  lightBoxClose &&
    lightBoxClose.addEventListener("click", () => closeLightbox(lightbox));

  const submitBtn = document.querySelector("#lightbox-submit");
  submitBtn &&
    submitBtn.addEventListener("click", () => {
      crumbs.set(options.cookie_name, 1, {
        type: "month",
        value: 12,
      }); // Create one year cookie
    });

  setTimeout(function () {
    lightbox.style.display = "flex";
  }, options.trigger - 100);

  setTimeout(function () {
    lightbox.classList.remove("hidden");
    lightbox.classList.add("visible");
    body.style.overflow = "hidden";
  }, options.trigger);

  lightbox.addEventListener("transitionend", () => {
    if (lightbox.classList.contains("hidden")) {
      lightbox.style.display = "none";
    }
    if (lightbox.classList.contains("visible")) {
      lightbox.style.display = "flex";
    }
  });

  lightbox.addEventListener("click", (e) => {
    if (e.target == lightbox) {
      closeLightbox(lightbox);
    }
  });

  function getFrameByEvent(event) {
    return [].slice
      .call(document.getElementsByTagName("iframe"))
      .filter(function (iframe) {
        return iframe.contentWindow === event.source;
      })[0];
  }

  window.onmessage = (e) => {
    var iframe = getFrameByEvent(e);
    if (iframe) {
      if (e.data.hasOwnProperty("frameHeight")) {
        iframe.style.display = "block";
        iframe.style.height = `${e.data.frameHeight}px`;
      } else if (e.data.hasOwnProperty("scroll") && e.data.scroll > 0) {
        const elDistanceToTop =
          window.pageYOffset + iframe.getBoundingClientRect().top;
        let scrollTo = elDistanceToTop + e.data.scroll;

        window.scrollTo({
          top: scrollTo,
          left: 0,
          behavior: "smooth",
        });
        console.log("Scrolling Window To", scrollTo);
      }

      if (
        e.data.hasOwnProperty("pageNumber") &&
        e.data.hasOwnProperty("pageCount")
      ) {
        if (
          e.data.pageNumber &&
          e.data.pageCount &&
          e.data.pageNumber == e.data.pageCount
        ) {
          crumbs.set(options.cookie_name, 1, { type: "month", value: 12 }); // Create one year cookie
        }
      }

      if (e.data.hasOwnProperty("close") && e.data.close) {
        closeLightbox(lightbox);
      }
    }
  };
};
setLightbox();

function closeLightbox(lightbox) {
  lightbox.classList.remove("visible");
  lightbox.classList.add("hidden");
  body.style.overflow = "auto";
  crumbs.set(options.cookie_name, 1, { type: "day", value: 1 });
}

function isWhitelisted() {
  let result = true;
  if (options.whitelist.length) {
    let url = window.location.pathname + window.location.search;
    // Change the default since now we need to show ONLY in whitelisted places
    result = false;
    options.whitelist.forEach((test) => {
      if (url.match(new RegExp(test))) result = true;
    });
  }
  return result;
}

function isBlacklisted() {
  let result = false;
  if (options.blacklist.length) {
    let url = window.location.pathname + window.location.search;
    options.blacklist.forEach((test) => {
      if (url.match(new RegExp(test))) result = true;
    });
  }
  return result;
}

function isBetweenDates() {
  let result = true;
  // Check if the there are dates defined
  if (options.dates.length) {
    let now = new Date();
    let start = new Date(options.dates[0]);
    let end = new Date(options.dates[1] + " 23:59:59");
    if (now < start || now > end) {
      result = false;
    }
  }
  return result;
}
