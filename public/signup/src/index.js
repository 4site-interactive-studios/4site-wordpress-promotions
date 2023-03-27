//import css from "./main.css";
import scss from "./sass/main.scss";
import { crumbs } from "./crumbs";
// import { fs_signup_options } from './config';

document.addEventListener('DOMContentLoaded', function() {
  window.lightbox_triggered = (window.lightbox_triggered === undefined) ? false : window.lightbox_triggered;

  const body = document.querySelector("body");

  let logo = "";
  if (fs_signup_options.logoURL) {
    logo += `<img src="${fs_signup_options.logoURL}" />`;
  }

  let content = "";
  if (fs_signup_options.title) {
    content += `<h1>${fs_signup_options.title}</h1>`;
  }
  if (fs_signup_options.paragraph) {
    content += `<p>${fs_signup_options.paragraph}</p>`;
  }
  if (fs_signup_options.iframe) {
    content += `${fs_signup_options.iframe.replace("data-src", "src")}`;
  }
  // if (fs_signup_options.info) {
  //   content += `<p class="italic fs-signup-footer">${fs_signup_options.info}</p>`;
  // }

  const hideSignUpForm = !!parseInt(crumbs.get(fs_signup_options.cookie_name)); // Get cookie

  // This is for disable mobile view
  const viewportWidth = Math.max(
    document.documentElement.clientWidth || 0,
    window.innerWidth || 0
  );

  const isIE = !!document.documentMode;

  const setLightbox = () => {
    if (!hideSignUpForm) {
      crumbs.set(fs_signup_options.cookie_name, 0, {
        type: "day",
        value: 1,
      });
    }

    if (!isBetweenDates() || isBlacklisted() || !isWhitelisted() || hideSignUpForm || viewportWidth < 600 || isIE) {
      return;
    } else {
      if(window.lightbox_triggered) {
        return;
      } else {
        window.lightbox_triggered = true;        
      }

      body.insertAdjacentHTML(
        "afterbegin",
        `
          <div class="fs-signup-lightbox fs-signup-hidden" style="display: none;">
            <div class="fs-signup-container">
              <div class="fs-signup-lightbox-content">
                <div class="fs-signup-close-btn"></div>
                <div class="fs-signup-logo">
                  ${logo}
                </div>          
                <div class="fs-signup-container-image" style="background-image: url('${fs_signup_options.imageURL}');">
                  <img src="${fs_signup_options.imageURL}" />
                </div>
                <div class="fs-signup-container-form">
                  ${content}
                </div>
              </div>
              <div class="fs-signup-footer">
                <p>${fs_signup_options.info}</p>                    
              </div>
            </div>
          </div>`
      );
    }
    const lightbox = document.querySelector(".fs-signup-lightbox");

    const lightBoxClose = document.querySelector(".fs-signup-close-btn");
    lightBoxClose &&
      lightBoxClose.addEventListener("click", () => closeLightbox(lightbox));

    const submitBtn = document.querySelector("#fs-signup-lightbox-submit");
    submitBtn &&
      submitBtn.addEventListener("click", () => {
        crumbs.set(fs_signup_options.cookie_name, 1, {
          type: "month",
          value: 12,
        }); // Create one year cookie
      });

    setTimeout(function () {
      lightbox.style.display = "flex";
    }, fs_signup_options.trigger - 100);

    setTimeout(function () {
      lightbox.classList.remove("fs-signup-hidden");
      lightbox.classList.add("fs-signup-visible");
      body.style.overflow = "hidden";
    }, fs_signup_options.trigger);

    lightbox.addEventListener("transitionend", () => {
      if (lightbox.classList.contains("fs-signup-hidden")) {
        lightbox.style.display = "none";
      }
      if (lightbox.classList.contains("fs-signup-visible")) {
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
          if (e.data.frameHeight) {
            iframe.style.height = `${e.data.frameHeight}px`;            
          }
        } else if (e.data.hasOwnProperty("scroll") && e.data.scroll > 0) {
          const elDistanceToTop =
            window.pageYOffset + iframe.getBoundingClientRect().top;
          let scrollTo = elDistanceToTop + e.data.scroll;

          window.scrollTo({
            top: scrollTo,
            left: 0,
            behavior: "smooth",
          });
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
            crumbs.set(fs_signup_options.cookie_name, 1, {
              type: "month",
              value: 12,
            }); // Create one year cookie
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
    lightbox.classList.remove("fs-signup-visible");
    lightbox.classList.add("fs-signup-hidden");
    body.style.overflow = "auto";
    crumbs.set(fs_signup_options.cookie_name, 1, { type: "day", value: 1 });
  }

  function isWhitelisted() {
    let result = true;
    if (fs_signup_options.whitelist.length) {
      let url = window.location.pathname + window.location.search;
      // Change the default since now we need to show ONLY in whitelisted places
      result = false;
      fs_signup_options.whitelist.forEach((test) => {
        if (url.match(new RegExp(test))) result = true;
      });
    }
    return result;
  }

  function isBlacklisted() {
    let result = false;
    if (fs_signup_options.blacklist.length) {
      let url = window.location.pathname + window.location.search;
      fs_signup_options.blacklist.forEach((test) => {
        if (url.match(new RegExp(test))) result = true;
      });
    }
    return result;
  }

  function isBetweenDates() {
    let result = true;
    // Check if the there are dates defined
    if (fs_signup_options.dates.length) {
      let now = new Date();
      let start = new Date(fs_signup_options.dates[0]);
      let end = new Date(fs_signup_options.dates[1] + " 23:59:59");
      if (now < start || now > end) {
        result = false;
      }
    }
    return result;
  }

});