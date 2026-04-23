//import css from "./main.css";
import scss from "./sass/main.scss";
import { crumbs } from "./crumbs";

window.launchENLightbox = function(options) {
  let two_col_layout = options.layout != 'one-col';

  let logo = "";
  if(two_col_layout && options.logoURL) {
    logo += `<div class="fs-signup-logo"><img src="${options.logoURL}" /></div>`;
  }


  let content = "";
  if(two_col_layout && options.title) {
    content += `<h1>${options.title}</h1>`;
  }
  if(two_col_layout && options.paragraph) {
    content += `<p>${options.paragraph}</p>`;
  }

  content += `<iframe width='100%' scrolling='no' class='en-iframe ' src='${options.url}' frameborder='0' allowfullscreen='' style='display:none' allow='autoplay; encrypted-media; payment;'></iframe>`;

  let footer = "";
  if(two_col_layout && options.footer) {
    footer += `
      <div class="fs-signup-footer">
      <p>${options.footer}</p>
      </div>
    `;
  }

  let image = "";
  if(two_col_layout && options.imageURL) {
    image += `
      <div class="fs-signup-container-image" style="background-image: url('${options.imageURL}');">
        <img src="${options.imageURL}" />
      </div>    
    `;
  }

  let layout_class = "";
  if(options.layout) {
    layout_class += options.layout;
  }

  const body = document.querySelector("body");
  body.insertAdjacentHTML("afterbegin",
    `
      <div class="fs-signup-lightbox fs-signup-hidden ${layout_class}" style="display: flex;">
        <div class="fs-signup-container">
          <div class="fs-signup-lightbox-content">
            <div class="fs-signup-close-btn"></div>
            ${logo}
            ${image}
            <div class="fs-signup-container-form">
              ${content}
            </div>
          </div>
          ${footer}
        </div>
      </div>
    `
  );


  const lightbox = document.querySelector(".fs-signup-lightbox");

  const lightBoxClose = document.querySelector(".fs-signup-close-btn");
  lightBoxClose && lightBoxClose.addEventListener("click", () => closeLightbox(lightbox));

  setTimeout(function () {
    lightbox.classList.remove("fs-signup-hidden");
    lightbox.classList.add("fs-signup-visible");
    body.style.overflow = "hidden";
  }, 0);

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

  window.addEventListener('message', (e) => {
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
      } else if (e.data.hasOwnProperty("key") && e.data.key == "status" && e.data.hasOwnProperty("value") && e.data.value == "submit") {
        document.querySelector('.fs-signup-container').scrollTop = 0;
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
          crumbs.set(options.cookie_name, 1, {
            type: "hour",
            value: options.cookie_hours,
          });
        }
      }

      if (e.data.hasOwnProperty("close") && e.data.close) {
        closeLightbox(lightbox);
      }
    }
  });

  function closeLightbox(lightbox) {
    lightbox.classList.remove("fs-signup-visible");
    lightbox.classList.add("fs-signup-hidden");
    body.style.overflow = "auto";
  }
}