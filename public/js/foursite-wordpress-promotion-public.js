window.addEventListener("DOMContentLoaded", () => {
  "use strict";

  if (client_side_triggered_config == undefined) {
    return;
  }

  window.triggerPromotion = function (id) {
    window.dispatchEvent(
      new CustomEvent("trigger-promotion", { detail: { promotion_id: id } })
    );
  };

  document.addEventListener("multistep-lightbox", (e) => {
    if (e.detail.action == "closed" && e.detail.context !== "object") {
      document.getElementById(e.detail.id).remove();
    }
  });

  // lightboxes are limited to 1/page, and can be launched from a few different scripts that set this variable
  window.lightbox_triggered =
    window.lightbox_triggered === undefined ? false : window.lightbox_triggered;

  // we don't want to show more than one floating tab at a time
  let floating_tab_triggered = false;

  let scroll_px_triggered = [];
  let scroll_per_triggered = [];
  let exit_triggered = [];
  let js_triggered = [];
  let time_triggered = [];
  let hide_floating_tab = false;

  window.rawCodeTriggers = [];

  // Kick off ad-blocker detection once per page load; resolves to true/false.
  // Used by ab_test promos to pick between the main and ad-blocker variants.
  window.fsAdBlockerDetection = window.fsAdBlockerDetection || new Promise((resolve) => {
    // Create an element that mimics an ad and inject it into the page
    document.body.insertAdjacentHTML(
      "beforeend",
      '<ins data-adBlockTest class="adsbygoogle ad-zone ad-space ad-unit textads banner-ads banner_ads" style="display: block !important; width:1px !important; height: 1px !important; visibility: hidden !important;"></ins>'
    );
    const ad = document.querySelector("[data-adBlockTest]");

    // Check to see if the visitor is running an Ad Blocker
    let blocked = false;
    if (ad) {
      const width = ad.offsetWidth;
      if (width == 0) {
        blocked = true;
      }
    }

    resolve(blocked);
  });

  for (let i = 0; i < client_side_triggered_config.length; i++) {
    // skip if there is already a cookie set for this promo
    const cookie = client_side_triggered_config[i].cookie_name;
    if (getCookie(cookie)) {
      continue;
    }

    if (client_side_triggered_config[i].display == 'scheduled' && !scheduledForToday(client_side_triggered_config[i])) {
      continue;
    }

    // Make replacements in eligible promotions if particular strings (eg, HOST_PAGE_URL) are present
    client_side_triggered_config[i] = makePromoReplacements(client_side_triggered_config[i]);

    // add our promo to the appropriate array
    let trigger = client_side_triggered_config[i].trigger;    
    let trigger_type = getTriggerType(trigger);
    if (!trigger_type) {
      trigger_type = "seconds";
      trigger = 2000;
    } else if (trigger_type === "seconds") {
      trigger = Number(trigger) * 1000;
    }
    client_side_triggered_config[i].trigger_type = trigger_type;

    switch (trigger_type) {
      case "exit":
        exit_triggered.push(client_side_triggered_config[i]);
        break;
      case "pixels":
        client_side_triggered_config[i].trigger = Number(
          client_side_triggered_config[i].trigger.replace("px", "")
        );
        scroll_px_triggered.push(client_side_triggered_config[i]);
        break;
      case "percent":
        client_side_triggered_config[i].trigger = Number(
          client_side_triggered_config[i].trigger.replace("%", "")
        );
        scroll_per_triggered.push(client_side_triggered_config[i]);
        break;
      case "js":
        js_triggered.push(client_side_triggered_config[i]);
        break;
      case "seconds":
        time_triggered.push({
          seconds: trigger,
          promo: client_side_triggered_config[i],
        });
        break;
      default:
        break;
    }
  }

  function format_date(d) {
    let month = d.getMonth() + 1;
    let day = d.getDay();
    let hours = d.getHours();
    let minutes = d.getMinutes();
    let seconds = d.getSeconds();

    if (month < 10) month = '0' + month;
    if (day < 10) day = '0' + day;
    if (hours < 10) hours = '0' + hours;
    if (minutes < 10) minutes = '0' + minutes;
    if (seconds < 10) seconds = '0' + seconds;

    return d.getFullYear() + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
  }
  function scheduledForToday(promotion) {
    if (promotion.display == 'scheduled' && promotion.start && promotion.end) {
      const start = Date.parse(promotion.start);
      const end = Date.parse(promotion.end);
      const today = Date.now();

      if (start <= today && end >= today) {
        return true;
      }
    }
    return false;
  }

  function isEveryAction(promotion) {
    return promotion.page_host == 'ea';
    //return (promotion.url.indexOf('/a/') >= 0);
  }

  function addMultistepLightbox(promotion) {
    if(isEveryAction(promotion)) {
      if (window.EADonationLightboxObj) {
        delete window.EADonationLightboxObj;
      }

      insertCss(promotion.id, promotion.custom_css);

      if (promotion.custom_js) {
        insertJs(promotion.id, promotion.custom_js);
      }

      window.EADonationLightboxObj = new EADonationLightbox(promotion);
    } else {
      if (window.donationLightboxObj) {
        delete window.donationLightboxObj;
      }

      // remove previously set logo fix CSS
      const existing_logo_fix_css = document.querySelectorAll(
        ".multistep-logo-fix"
      );
      for (let i = 0; i < existing_logo_fix_css.length; i++) {
        existing_logo_fix_css[i].remove();
      }

      // add new logo fix CSS (the external multistep script doesn't seem to respect the values we pass in)
      if (promotion.hasOwnProperty("logo_fix_css")) {
        const new_css = document.createElement("style");
        new_css.setAttribute("type", "text/css");
        new_css.classList.add("multistep-logo-fix");
        new_css.textContent = promotion.logo_fix_css;
        document.body.appendChild(new_css);
      }

      insertCss(promotion.id, promotion.custom_css);

      if (promotion.custom_js) {
        insertJs(promotion.id, promotion.custom_js);
      }

      window.DonationLightboxOptions = promotion;
      window.DonationLightboxOptions.trigger = 0;

      clearEventsForFloatingTab();
      window.donationLightboxObj = new DonationLightbox();
    }
  }

  function addSignupLightbox(promotion) {
    if(promotion.css) insertCss(promotion.id, promotion.css);
    if(window.launchENLightbox) window.launchENLightbox(promotion);
    else console.error('EN Lightbox script not loaded');
  }

  function runAbTest(promotion) {
    if (!Array.isArray(promotion.variants) || promotion.variants.length === 0) {
      return;
    }
    const variant = promotion.variants[Math.floor(Math.random() * promotion.variants.length)];
    window.fsAdBlockerDetection.then((blocked) => {
      const selected = (blocked && variant.ad_blocker_promotion)
        ? variant.ad_blocker_promotion
        : variant.promotion;
      if (selected) {
        window.dispatchEvent(
          new CustomEvent("ab_test_result", {
            detail: { test_id: promotion.id, selected_id: selected.id }
          })
        );
        launchPromotion(selected);
      }
    }).catch((error) => {
      console.error("Error during ad blocker detection for AB test promotion:", promotion, "Error:", error);
    });
  }

  function launchPromotion(promotion) {
    switch (promotion.promotion_type) {
      case "cta_lightbox":
        if (window.lightbox_triggered) {
          return;
        } else {
          window.lightbox_triggered = true;
        }
        addCtaLightbox(promotion);
        break;
      case "email_capture_lightbox":
        if (window.lightbox_triggered) {
          return;
        } else {
          window.lightbox_triggered = true;
        }
        addEmailCaptureLightbox(promotion);
        break;
      case "rollup":
        if (promotion.hide_under && window.innerWidth <= promotion.hide_under) {
        } else {
          addRollup(promotion);
        }
        break;
      case "multistep_lightbox":
        if (window.lightbox_triggered) {
          return;
        } else {
          window.lightbox_triggered = true;
          addMultistepLightbox(promotion);
        }
        break;
      case "signup_lightbox":
        if (window.lightbox_triggered) {
          return;
        } else {
          window.lightbox_triggered = true;
          addSignupLightbox(promotion);
        }
        break;
      case "raw_code":
        if (promotion.is_lightbox) {
          if (window.lightbox_triggered) {
            return;
          } else {
            window.lightbox_triggered = true;
          }
        }
        addRawCode(promotion);
        break;
      case "pushdown":
        addPushdown(promotion);
        break;
      case "floating_signup":
        if (!window.lightbox_triggered) {
          window.lightbox_triggered = true;
          addFloatingEmailSignup(promotion);
          watchFloatingEmailSignup(promotion);
        }
        break;
      case "floating_tab":
        if (floating_tab_triggered) {
          return;
        } else {
          floating_tab_triggered = true;
        }        
        addFloatingTab(promotion);
        watchFloatingTab(promotion);
        break;
      case "overlay":
        if (!window.lightbox_triggered) {
          addOverlay(promotion);
          window.lightbox_triggered = true;
        } else {
          return;
        }
        break;
      case "video":
        if (!window.lightbox_triggered) {
          addVideoLightbox(promotion);
          window.lightbox_triggered = true;
        } else {
          return;
        }

        break;
      case "redirect":
        if (promotion.url) {
          window.location.assign(promotion.url);
        }
        break;
      case "ab_test":
        runAbTest(promotion);
        break;
    }

    if (promotion.cookie_hours) {
      setCookie(promotion.cookie_name, promotion.cookie_hours);
    }

    // notify the page for any third-party listeners that want to react
    window.dispatchEvent(
      new CustomEvent("fs_promo_launch", { detail: { promo: promotion } })
    );
  }

  if (scroll_px_triggered.length) {
    document.addEventListener("scroll", scrollPxTrigger);
  }
  if (scroll_per_triggered.length) {
    document.addEventListener("scroll", scrollPercentTrigger);
  }
  if (exit_triggered.length) {
    document.body.addEventListener("mouseleave", exitTrigger);
  }
  if (js_triggered.length) {
    addEventListener("trigger-promotion", jsTrigger);
  }
  if (time_triggered.length) {
    let zero_counter = 0;
    for (let i = 0; i < time_triggered.length; i++) {
      if (time_triggered[i].seconds == 0 && zero_counter == 0) {
        launchPromotion(time_triggered[i].promo);
      } else {
        window.setTimeout(() => {
          launchPromotion(time_triggered[i].promo);
        }, time_triggered[i].seconds + zero_counter * 0.5);
      }
      if (time_triggered[i].seconds == 0) {
        zero_counter++;
      }
    }
  }

  function exitTrigger() {
    for (let i = exit_triggered.length - 1; i >= 0; i--) {
      launchPromotion(exit_triggered[i]);
      exit_triggered.splice(i, 1);
      break;
    }
    if (exit_triggered.length == 0) {
      document.body.removeEventListener("mouseleave", exitTrigger);
    }
  }

  function clearEventsForFloatingTab() {
    document.querySelectorAll("[data-donation-lightbox]").forEach((e) => {
      let new_tab = e.cloneNode(true);
      e.parentNode.replaceChild(new_tab, e);
    });
  }

  function scrollPxTrigger() {
    for (let i = scroll_px_triggered.length - 1; i >= 0; i--) {
      if (window.scrollY >= scroll_px_triggered[i].trigger) {
        launchPromotion(scroll_px_triggered[i]);
        scroll_px_triggered.splice(i, 1);
      }
    }
    if (scroll_px_triggered.length == 0) {
      document.removeEventListener("scroll", scrollPxTrigger);
    }
  }

  function scrollPercentTrigger() {
    const client_height = document.documentElement.clientHeight;
    const scroll_height = document.documentElement.scrollHeight - client_height;

    for (let i = scroll_per_triggered.length - 1; i >= 0; i--) {
      const target = (scroll_per_triggered[i].trigger / 100) * scroll_height;
      if (window.scrollY >= target) {
        launchPromotion(scroll_per_triggered[i]);
        scroll_per_triggered.splice(i, 1);
      }
    }
    if (scroll_per_triggered.length == 0) {
      document.removeEventListener("scroll", scrollPercentTrigger);
    }
  }

  function jsTrigger(e) {
    if (e.detail.promotion_id) {
      const promotion_id = Number(e.detail.promotion_id);
      for (let i = js_triggered.length - 1; i >= 0; i--) {
        if (js_triggered[i].id == promotion_id) {
          deleteCookie(js_triggered[i].cookie_name);
          launchPromotion(js_triggered[i]);
          js_triggered.splice(i, 1);
          break;
        }
      }
      if (scroll_per_triggered.length == 0) {
        document.removeEventListener("trigger-promotion", jsTrigger);
      }
    }
  }

  function addCtaLightbox(promotion) {
    const overlay = document.createElement("div");
    overlay.classList.add("fs-cta-modal-container");
    overlay.setAttribute("promotion-id", promotion.id);

    const modal = document.createElement("div");
    modal.classList.add("fs-cta-modal");

    const modal_close_button = document.createElement("div");
    modal_close_button.classList.add("fs-cta-modal-close-button");
    if (promotion.image.bg_color) {
      modal_close_button.style.color = promotion.image.bg_color;
      modal_close_button.style.borderColor = promotion.image.bg_color;
    }
    modal.appendChild(modal_close_button);

    if (promotion.image.url && promotion.image.position === "left") {
      modal.classList.add("fs-cta-modal-image-left");
    } else if (promotion.image.url && promotion.image.position === "right") {
      modal.classList.add("fs-cta-modal-image-right");
    } else {
      modal.classList.add("fs-cta-modal-no-image");
    }

    if (promotion.bg_color) {
      modal.style.backgroundColor = promotion.bg_color;
    }
    if (promotion.fg_color) {
      modal.style.color = promotion.fg_color;
    }

    const modal_text_column = document.createElement("div");
    modal_text_column.classList.add("fs-cta-modal-text-column");

    if (promotion.header) {
      const modal_header = document.createElement("div");
      modal_header.classList.add("fs-cta-modal-header");
      modal_header.innerHTML = promotion.header;
      modal_text_column.appendChild(modal_header);
    }

    if (promotion.body) {
      const modal_content = document.createElement("div");
      modal_content.classList.add("fs-cta-modal-content");
      modal_content.innerHTML = promotion.body;
      modal_text_column.appendChild(modal_content);
    }

    if (promotion.cta_1.label && promotion.cta_1.link) {
      const modal_cta_button_1 = document.createElement("a");
      modal_cta_button_1.classList.add("fs-cta-modal-button");
      modal_cta_button_1.href = promotion.cta_1.link;
      modal_cta_button_1.target = '_blank';
      modal_cta_button_1.innerHTML = promotion.cta_1.label;
      if (promotion.cta_1.bg_color) {
        modal_cta_button_1.style.backgroundColor = promotion.cta_1.bg_color;
      }
      if (promotion.cta_1.fg_color) {
        modal_cta_button_1.style.color = promotion.cta_1.fg_color;
      }
      modal_text_column.appendChild(modal_cta_button_1);
    }

    if (promotion.cta_2.label && promotion.cta_2.link) {
      const modal_cta_button_2 = document.createElement("a");
      modal_cta_button_2.classList.add("fs-cta-modal-button");
      modal_cta_button_2.href = promotion.cta_2.link;
      modal_cta_button_2.target = '_blank';
      modal_cta_button_2.innerHTML = promotion.cta_2.label;
      if (promotion.cta_2.bg_color) {
        modal_cta_button_2.style.backgroundColor = promotion.cta_2.bg_color;
      }
      if (promotion.cta_2.fg_color) {
        modal_cta_button_2.style.color = promotion.cta_2.fg_color;
      }
      modal_text_column.appendChild(modal_cta_button_2);
    }

    modal.appendChild(modal_text_column);

    if (promotion.image.url) {
      const modal_image_column = document.createElement("div");
      modal_image_column.classList.add("fs-cta-modal-image-column");

      const modal_image = document.createElement("img");
      modal_image.src = promotion.image.url;
      modal_image.alt = promotion.image.alt;
      modal_image.classList.add("fs-cta-modal-image");

      const modal_image_container = document.createElement("div");
      modal_image_container.classList.add("fs-cta-modal-image-container");
      modal_image_container.style.backgroundImage = `url(${promotion.image.url})`;
      modal_image_container.appendChild(modal_image);

      modal_image_column.appendChild(modal_image_container);
      modal.appendChild(modal_image_column);
    }

    const css = `
      .fs-cta-modal-container {
        z-index: 9999;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        -webkit-animation: fadeIn 0.5s;
        animation: fadeIn 0.5s;
        justify-content: center;
        align-items: center;
        background: rgba(0,0,0,0.45);
        overflow-y: scroll;
      }
      .fs-cta-modal {
        margin: 10vh auto;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        position: relative;
        width: 95%;
        max-width: 880px;
        height: fit-content;
      }
      .fs-cta-modal.fs-cta-modal-image-left {
        flex-direction: row-reverse;
      }
      .fs-cta-modal-header {
        font-size: 32px;
        font-weight: 600;
      }
      .fs-cta-modal-content {
        font-size: 20px;
        line-height: 1.5;
        font-weight: 400;
      }
      .fs-cta-modal-text-column {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
        gap: 20px;
        width: 50%;
        padding: 30px 30px;
      }
      .fs-cta-modal.fs-cta-modal-no-image .fs-cta-modal-text-column {
        width: 100%;
      }
      .fs-cta-modal-image-column {
        width: 50%;
      }
      .fs-cta-modal-image-container {
        overflow: hidden;
        background-size: cover;
        height: 100%;
        width: 100%;
        background-position: center;
        background-repeat: no-repeat;
      }
      .fs-cta-modal-image {
        max-width: unset;
        object-fit: cover;
        display: none;
      }
      .fs-cta-modal-button {
        display: block;
        padding: 10px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        width: 100%;
        opacity: 1;
        transition: opacity 0.7s;
      }
      .fs-cta-modal-button:hover {
        text-decoration: none;
        opacity: 0.8;
      }

      .fs-cta-modal-close-button {
        position: absolute;
        box-sizing: content-box;
        right: 10px;
        top: 10px;
        width: 25px;
        height: 25px;
        z-index: 1000;
        padding: 5px;
        border: 3px solid #f6f7f8;
        cursor: pointer;
        opacity: 0.5;
        transition: 0.3s opacity ease-in-out;
      }
      .fs-cta-modal-close-button:hover {
        opacity: 1;
      }
      .fs-cta-modal-close-button:hover::before {
        transform: rotate(45deg) scale(1.5);
      }
      .fs-cta-modal-close-button:hover::after {
        transform: rotate(-45deg) scale(1.5);
      }
      .fs-cta-modal-close-button::before,
      .fs-cta-modal-close-button::after {
        transition: 0.3s transform ease-in-out, 0.3s background-color ease-in-out;
        position: absolute;
        content: " ";
        height: 19px;
        width: 2px;
        background-color: #f6f7f8;
        left: 17px;
        top: 8px;
      }
      .fs-cta-modal-close-button::before {
        transform: rotate(45deg);
      }
      .fs-cta-modal-close-button::after {
        transform: rotate(-45deg);
      }

      .fs-cta-modal-noscroll {
        overflow: hidden;
      }

      @media (max-width: 700px) {
        .fs-cta-modal-container {
          align-items: flex-start;
        }
        .fs-cta-modal,
        .fs-cta-modal.fs-cta-modal-image-left,
        .fs-cta-modal.fs-cta-modal-image-right {
          flex-direction: column-reverse;
        }
        .fs-cta-modal-text-column,
        .fs-cta-modal-image-column {
          width: 100%;
        }
        .fs-cta-modal-container {
          background-image: unset;
        }
        .fs-cta-modal-image {
          object-fit: contain;
          max-width: 100%;
          display: block;
        }
        .fs-cta-modal-header {
          font-size: 24px;
        }
        .fs-cta-modal-content {
          font-size: 18px;
        }
        .fs-cta-modal-button {
          font-size: 18px;
        }
      }
      ${promotion.css}
    `;
    insertCss(promotion.id, css);

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Prevent scrolling of the page while the CTA modal is open
    document.body.classList.add('fs-cta-modal-noscroll');

    // Handle closure of the CTA modal
    function detectEscape(e) {
      if (e.key === "Escape") {
        closeCtaModal();
      }
    }
    function clickOutsideModal(e) {
      closeCtaModal();
    }
    function clickWithinModal(e) {
      e.stopPropagation();
    }
    function closeCtaModal() {
      document.body.removeEventListener('keyup', detectEscape);
      document.querySelector('.fs-cta-modal-close-button').removeEventListener('click', closeCtaModal);
      document.querySelector('.fs-cta-modal-container').removeEventListener('click', clickOutsideModal);
      document.querySelector('.fs-cta-modal').removeEventListener('click', clickWithinModal);
      document.querySelector('.fs-cta-modal-container').style.display = 'none';
      document.body.classList.remove('fs-cta-modal-noscroll');
    }
    document.body.addEventListener('keyup', detectEscape);
    document.querySelector('.fs-cta-modal-close-button').addEventListener('click', closeCtaModal);
    document.querySelector('.fs-cta-modal').addEventListener('click', clickWithinModal);
    document.querySelector('.fs-cta-modal-container').addEventListener('click', clickOutsideModal);
  }

  function addEmailCaptureLightbox(promotion) {
    const overlay = document.createElement("div");
    overlay.classList.add("fs-ecl-modal-container");
    overlay.setAttribute("promotion-id", promotion.id);

    const modal = document.createElement("div");
    modal.classList.add("fs-ecl-modal");

    const modal_close_button = document.createElement("div");
    modal_close_button.classList.add("fs-ecl-modal-close-button");
    if (promotion.image.bg_color) {
      modal_close_button.style.color = promotion.image.bg_color;
      modal_close_button.style.borderColor = promotion.image.bg_color;
    }
    modal.appendChild(modal_close_button);

    if (promotion.image.url && promotion.image.position === "left") {
      modal.classList.add("fs-ecl-modal-image-left");
    } else if (promotion.image.url && promotion.image.position === "right") {
      modal.classList.add("fs-ecl-modal-image-right");
    } else {
      modal.classList.add("fs-ecl-modal-no-image");
    }

    if (promotion.bg_color) {
      modal.style.backgroundColor = promotion.bg_color;
    }
    if (promotion.fg_color) {
      modal.style.color = promotion.fg_color;
    }

    const modal_text_column = document.createElement("div");
    modal_text_column.classList.add("fs-ecl-modal-text-column");

    if (promotion.header) {
      const modal_header = document.createElement("div");
      modal_header.classList.add("fs-ecl-modal-header");
      modal_header.classList.add("fs-ecl-pre-submission-show");
      modal_header.innerHTML = promotion.header;
      modal_text_column.appendChild(modal_header);
    }

    if (promotion.body) {
      const modal_content = document.createElement("div");
      modal_content.classList.add("fs-ecl-modal-content");
      modal_content.classList.add("fs-ecl-pre-submission-show");
      modal_content.innerHTML = promotion.body;
      modal_text_column.appendChild(modal_content);
    }

    // Email capture form (pre-submission)
    const modal_form = document.createElement("form");
    modal_form.classList.add("fs-ecl-modal-form");
    modal_form.classList.add("fs-ecl-pre-submission-show");

    const modal_email = document.createElement("input");
    modal_email.classList.add("fs-ecl-modal-email");
    modal_email.setAttribute("type", "email");
    modal_email.setAttribute("name", "email");
    modal_email.setAttribute("placeholder", promotion.email_placeholder || "Email Address");
    modal_email.required = true;

    const modal_submit = document.createElement("button");
    modal_submit.classList.add("fs-ecl-modal-submit");
    modal_submit.setAttribute("type", "submit");
    modal_submit.innerHTML = promotion.submit.label || "Submit";
    if (promotion.submit.bg_color) {
      modal_submit.style.backgroundColor = promotion.submit.bg_color;
    }
    if (promotion.submit.fg_color) {
      modal_submit.style.color = promotion.submit.fg_color;
    }

    const modal_error = document.createElement("div");
    modal_error.classList.add("fs-ecl-modal-error");
    modal_error.innerHTML = "The email address is invalid, please try again.";

    modal_form.appendChild(modal_email);
    modal_form.appendChild(modal_submit);
    modal_form.appendChild(modal_error);
    modal_text_column.appendChild(modal_form);

    // Success message (post-submission)
    const modal_success = document.createElement("div");
    modal_success.classList.add("fs-ecl-modal-success");
    modal_success.classList.add("fs-ecl-post-submission-show");

    if (promotion.success.header) {
      const success_header = document.createElement("div");
      success_header.classList.add("fs-ecl-modal-header");
      success_header.innerHTML = promotion.success.header;
      modal_success.appendChild(success_header);
    }
    if (promotion.success.body) {
      const success_body = document.createElement("div");
      success_body.classList.add("fs-ecl-modal-content");
      success_body.innerHTML = promotion.success.body;
      modal_success.appendChild(success_body);
    }
    if (promotion.success.button.title && promotion.success.button.url) {
      const success_button = document.createElement("a");
      success_button.classList.add("fs-ecl-modal-success-button");
      success_button.href = promotion.success.button.url;
      success_button.innerHTML = promotion.success.button.title;
      if (promotion.success.button.target) {
        success_button.target = promotion.success.button.target;
      }
      if (promotion.submit.bg_color) {
        success_button.style.backgroundColor = promotion.submit.bg_color;
      }
      if (promotion.submit.fg_color) {
        success_button.style.color = promotion.submit.fg_color;
      }
      modal_success.appendChild(success_button);
    }
    modal_text_column.appendChild(modal_success);

    modal.appendChild(modal_text_column);

    if (promotion.image.url) {
      const modal_image_column = document.createElement("div");
      modal_image_column.classList.add("fs-ecl-modal-image-column");

      const modal_image = document.createElement("img");
      modal_image.src = promotion.image.url;
      modal_image.alt = promotion.image.alt;
      modal_image.classList.add("fs-ecl-modal-image");

      const modal_image_container = document.createElement("div");
      modal_image_container.classList.add("fs-ecl-modal-image-container");
      modal_image_container.style.backgroundImage = `url(${promotion.image.url})`;
      modal_image_container.appendChild(modal_image);

      modal_image_column.appendChild(modal_image_container);
      modal.appendChild(modal_image_column);
    }

    const css = `
      .fs-ecl-modal-container {
        z-index: 9999;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        -webkit-animation: fadeIn 0.5s;
        animation: fadeIn 0.5s;
        justify-content: center;
        align-items: center;
        background: rgba(0,0,0,0.45);
        overflow-y: scroll;
      }
      .fs-ecl-modal {
        margin: 10vh auto;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        position: relative;
        width: 95%;
        max-width: 880px;
        height: fit-content;
      }
      .fs-ecl-modal.fs-ecl-modal-image-left {
        flex-direction: row-reverse;
      }
      .fs-ecl-modal-header {
        font-size: 32px;
        font-weight: 600;
      }
      .fs-ecl-modal-content {
        font-size: 20px;
        line-height: 1.5;
        font-weight: 400;
      }
      .fs-ecl-modal-text-column {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
        gap: 20px;
        width: 50%;
        padding: 30px 30px;
        box-sizing: border-box;
      }
      .fs-ecl-modal.fs-ecl-modal-no-image .fs-ecl-modal-text-column {
        width: 100%;
      }
      .fs-ecl-modal-image-column {
        width: 50%;
      }
      .fs-ecl-modal-image-container {
        overflow: hidden;
        background-size: cover;
        height: 100%;
        width: 100%;
        background-position: center;
        background-repeat: no-repeat;
      }
      .fs-ecl-modal-image {
        max-width: unset;
        object-fit: cover;
        display: none;
      }
      .fs-ecl-modal-form {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        width: 100%;
      }
      .fs-ecl-modal-email {
        width: 100%;
        box-sizing: border-box;
        padding: 10px;
        font-size: 18px;
        border: 1px solid #ccc;
      }
      .fs-ecl-modal-submit {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 10px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        border: none;
        font-size: 18px;
        opacity: 1;
        transition: opacity 0.7s;
      }
      .fs-ecl-modal-submit:hover {
        opacity: 0.8;
      }
      .fs-ecl-modal-success-button {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 10px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        opacity: 1;
        transition: opacity 0.7s;
        margin-top: auto;
      }
      .fs-ecl-modal-success-button:hover {
        text-decoration: none;
        opacity: 0.8;
      }
      .fs-ecl-modal-error {
        display: none;
        color: #F04245;
        font-size: 16px;
      }
      .fs-ecl-modal-form.has-error .fs-ecl-modal-error {
        display: block;
      }
      .fs-ecl-post-submission-show {
        display: none;
      }
      .fs-ecl-modal.submitted .fs-ecl-pre-submission-show {
        display: none;
      }
      .fs-ecl-modal.submitted .fs-ecl-post-submission-show {
        display: block;
      }
      .fs-ecl-modal.submitted .fs-ecl-modal-success {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
        width: 100%;
        flex: 1 1 auto;
      }

      .fs-ecl-modal-close-button {
        position: absolute;
        box-sizing: content-box;
        right: 10px;
        top: 10px;
        width: 25px;
        height: 25px;
        z-index: 1000;
        padding: 5px;
        border: 3px solid #f6f7f8;
        cursor: pointer;
        opacity: 0.5;
        transition: 0.3s opacity ease-in-out;
      }
      .fs-ecl-modal-close-button:hover {
        opacity: 1;
      }
      .fs-ecl-modal-close-button:hover::before {
        transform: rotate(45deg) scale(1.5);
      }
      .fs-ecl-modal-close-button:hover::after {
        transform: rotate(-45deg) scale(1.5);
      }
      .fs-ecl-modal-close-button::before,
      .fs-ecl-modal-close-button::after {
        transition: 0.3s transform ease-in-out, 0.3s background-color ease-in-out;
        position: absolute;
        content: " ";
        height: 19px;
        width: 2px;
        background-color: #f6f7f8;
        left: 17px;
        top: 8px;
      }
      .fs-ecl-modal-close-button::before {
        transform: rotate(45deg);
      }
      .fs-ecl-modal-close-button::after {
        transform: rotate(-45deg);
      }

      .fs-ecl-modal-noscroll {
        overflow: hidden;
      }

      @media (max-width: 700px) {
        .fs-ecl-modal-container {
          align-items: flex-start;
        }
        .fs-ecl-modal,
        .fs-ecl-modal.fs-ecl-modal-image-left,
        .fs-ecl-modal.fs-ecl-modal-image-right {
          flex-direction: column-reverse;
        }
        .fs-ecl-modal-text-column,
        .fs-ecl-modal-image-column {
          width: 100%;
        }
        .fs-ecl-modal-container {
          background-image: unset;
        }
        .fs-ecl-modal-image {
          object-fit: contain;
          max-width: 100%;
          display: block;
        }
        .fs-ecl-modal-header {
          font-size: 24px;
        }
        .fs-ecl-modal-content {
          font-size: 18px;
        }
        .fs-ecl-modal-submit {
          font-size: 18px;
        }
      }
      ${promotion.css}
    `;
    insertCss(promotion.id, css);

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Prevent scrolling of the page while the modal is open
    document.body.classList.add('fs-ecl-modal-noscroll');

    // Handle form submission -> POST to the WP proxy, which submits to Engaging Networks
    modal_form.addEventListener("submit", function (e) {
      e.preventDefault();
      const is_valid = validateEmail(modal_email);
      if (!is_valid) {
        modal_form.classList.add("has-error");
        return;
      }
      modal_form.classList.remove("has-error");
      submitEmailCaptureToEn(promotion, modal_email.value, modal, modal_form);
    });

    // Handle closure of the modal
    function detectEscape(e) {
      if (e.key === "Escape") {
        closeEclModal();
      }
    }
    function clickOutsideModal(e) {
      closeEclModal();
    }
    function clickWithinModal(e) {
      e.stopPropagation();
    }
    function closeEclModal() {
      document.body.removeEventListener('keyup', detectEscape);
      document.querySelector('.fs-ecl-modal-close-button').removeEventListener('click', closeEclModal);
      document.querySelector('.fs-ecl-modal-container').removeEventListener('click', clickOutsideModal);
      document.querySelector('.fs-ecl-modal').removeEventListener('click', clickWithinModal);
      document.querySelector('.fs-ecl-modal-container').style.display = 'none';
      document.body.classList.remove('fs-ecl-modal-noscroll');
    }
    document.body.addEventListener('keyup', detectEscape);
    document.querySelector('.fs-ecl-modal-close-button').addEventListener('click', closeEclModal);
    document.querySelector('.fs-ecl-modal').addEventListener('click', clickWithinModal);
    document.querySelector('.fs-ecl-modal-container').addEventListener('click', clickOutsideModal);
  }

  async function submitEmailCaptureToEn(promotion, email, modal, form) {
    function showSuccess() {
      // Lock the text column to its current (pre-submission) height so swapping the form for the
      // success message doesn't change the lightbox height. If the success content is taller, it
      // scrolls within the locked height rather than growing the lightbox.
      const text_column = modal.querySelector(".fs-ecl-modal-text-column");
      if (text_column) {
        text_column.style.height = text_column.offsetHeight + "px";
        text_column.style.overflowY = "auto";
      }
      modal.classList.add("submitted");
      if (parseInt(promotion.cookie_hours) > 0) {
        setCookie(promotion.cookie_name, promotion.cookie_hours);
      }
    }

    // If the EN proxy isn't configured, just show the success state.
    if (!promotion.submit_url) {
      showSuccess();
      return;
    }

    form.classList.add("processing");
    try {
      const response = await fetch(promotion.submit_url, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        method: 'POST',
        body: JSON.stringify({ email: email }),
      });
      const r = await response.json();
      if (r.success) {
        showSuccess();
      } else {
        form.classList.add("has-error");
        console.error('Error submitting email capture to Engaging Networks', r.error);
      }
    } catch (error) {
      form.classList.add("has-error");
      console.error('Error submitting email capture to Engaging Networks', error);
    }
    form.classList.remove("processing");
  }

  function addRawCode(promotion) {
    if (promotion.html) {
      const html_container = document.createElement("div");
      html_container.classList.add("promotion-html");
      html_container.classList.add("promotion-element");
      html_container.classList.add("promotion-" + promotion.id);
      html_container.setAttribute("promotion-id", promotion.id);
      html_container.innerHTML = promotion.html;
      document.body.appendChild(html_container);
    }

    if (promotion.css) {
      insertCss(promotion.id, promotion.css);
    }

    if (promotion.js) {
      insertJs(promotion.id, promotion.js);
    }
  }

  function insertHtml(promotion_id, promotion_html) {
    const html_container = document.createElement("div");
    html_container.classList.add("promotion-html");
    html_container.classList.add("promotion-element");
    html_container.classList.add("promotion-" + promotion_id);
    html_container.innerHTML = promotion_html;
    document.body.appendChild(html_container);
  }

  function addRollup(promotion) {
    insertHtml(
      promotion.id,
      `
        <div id="fs-rollup-container" promotion-id="${promotion.id}">
          <a href="${promotion.link}" target="${promotion.target}"><div id="fs-rollup-inner">${promotion.html}</div></a>
        </div> 
      `
    );
    insertCss(
      promotion.id,
      `
        @media screen and (min-width: 1200px) { 
          body {
            margin-bottom: 190px;
          }
        }
        #fs-rollup-container {
          width: 100%;
          height: 200px;
          background: #ffffff;
          padding: 0px;
          position: fixed;
          bottom: 0px;
          right: 0px;
          z-index: 500;
          box-shadow: 0 0 15px #333333;
        }
        #fs-rollup-inner {
          max-width: 1170px;
          height: 200px;
          margin: 0 auto;
          background: #ffffff url('${promotion.image}') top left no-repeat;
          background-size: auto 100% ;
        }
        ${promotion.css}
      `
    );
    promotion.js += `
        <script>
          jQuery(document).ready(function() {
            jQuery(document).scroll(lb_scroll_watcher);
          });

          let slide_timeout = null;
          function lb_scroll_watcher() {
            if(slide_timeout) {
              clearTimeout(slide_timeout);
              slide_timeout = null;
            }

            var y = jQuery(this).scrollTop();
            if (y > 50) {
              slide_timeout = setTimeout(lb_slide_up, 250);
            } 
            if (y < 50) {
              slide_timeout = setTimeout(lb_slide_down, 250);
            }
          }

          function lb_slide_up() {
            if (jQuery(window).width() > 1200) { 

              jQuery("#fs-rollup-container").stop().animate({
                height: '200px'
              }, 700, 'swing');

              jQuery("#fs-rollup-inner").stop().animate({
                marginTop: '-0px'
              }, 300, 'swing');

              jQuery("#fs-rollup-inner").css('height', '200px');
              jQuery("body").css('margin-bottom', '200px');
              jQuery("#fs-rollup-inner").css('display', 'block');

            } else {

              jQuery("#fs-rollup-container").css('margin-top', '0px');
              jQuery("#fs-rollup-container").css('height', 'auto');
              jQuery("#fs-rollup-inner").css('height', 'auto');
              jQuery("body").css('margin-bottom', '0px');

            }
          }

          function lb_slide_down() {

            jQuery("#fs-rollup-container").stop().animate({
              height: '0px'
            }, 500, 'swing');

            jQuery("#fs-rollup-inner").stop().animate({
              marginTop: 0
            }, 300, 'swing');

            jQuery("body").css('margin-bottom', '0px');
          }

          window.lb_click_close = function(event) {
            lb_slide_down();
            jQuery(document).off('scroll', lb_scroll_watcher);

            if(${promotion.cookie_hours}) {
              setCookie('${promotion.cookie_name}', ${promotion.cookie_hours});
            } else if(${promotion.close_cookie_hours}) {
              setCookie('${promotion.cookie_name}', ${promotion.close_cookie_hours});
            }
          }
        </script>
      `;
    if (promotion.hide_under) {
      promotion.js += `
          <script>
            window.addEventListener('resize', function() {
              if(window.innerWidth <= ${promotion.hide_under}) {
                const rollup_container = document.getElementById('fs-rollup-container');
                if(rollup_container) {
                  rollup_container.style.display = 'none';
                }                  
              }
            });
          </script>
        `;
    }
    if (promotion.close_if_oustide_click) {
      promotion.js += `
          <script>
            jQuery(window).click(lb_click_close);
          </script>
        `;
      if (!promotion.close_if_inside_click) {
        promotion.js += `
            <script>
              jQuery('#fs-rollup-container').click((event) => { event.stopPropagaion(); });
            </script>
          `;
      }
    }

    if (promotion.close_if_inside_click) {
      promotion.js += `
          <script>
            jQuery('#fs-rollup-container').click(lb_click_close);
          </script>
        `;
    }

    insertJs(promotion.id, `${promotion.js}`);
  }

  function addPushdown(promotion) {
    const pushdownScript = document.createElement("script");
    pushdownScript.src = promotion.src;
    pushdownScript.id = "foursite-wordpress-pushdown-promotion-js";
    pushdownScript.setAttribute("crossorigin", "anonymous");
    pushdownScript.setAttribute("data-pushdown-mode", promotion.pushdown_type);
    pushdownScript.setAttribute("data-pushdown-link", promotion.url);
    pushdownScript.setAttribute("data-pushdown-image", promotion.image);
    pushdownScript.setAttribute("data-pushdown-fg-color", promotion.fg_color);
    pushdownScript.setAttribute("data-pushdown-bg-color", promotion.bg_color);
    pushdownScript.setAttribute("data-promotion-id", promotion.id);
    pushdownScript.setAttribute(
      "data-pushdown-paragraph",
      promotion.pushdown_paragraph
    );
    pushdownScript.setAttribute(
      "data-pushdown-button-label",
      promotion.pushdown_button
    );
    if (promotion.gif != "") {
      pushdownScript.setAttribute("data-pushdown-gif", promotion.gif);
    }
    pushdownScript.setAttribute(
      "data-pushdown-content",
      promotion.pushdown_title
    );
    pushdownScript.classList.add("promotion-" + promotion.id);

    if (promotion.custom_css) {
      insertCss(promotion.id, promotion.custom_css);
    }

    document.body.appendChild(pushdownScript);
  }

  function hideFloatingTab() {
    const floating_tab = document.querySelector("#fs-donation-tab");
    if (floating_tab) {
      floating_tab.classList.add("floating-tab-hide");
    }
  }

  function showFloatingTab() {
    if(hide_floating_tab) return;
    const floating_tab = document.querySelector("#fs-donation-tab");
    if (floating_tab) {
      floating_tab.classList.remove("floating-tab-hide");
    }
  }
  function watchFloatingTab(promotion) {
    const trigger = promotion.trigger;
    const trigger_type = promotion.trigger_type;
    switch (trigger_type) {
      case "pixels":
        document.addEventListener("scroll", () => {
          watchFloatingTabPx(trigger);
        });
        break;
      case "percent":
        document.addEventListener("scroll", () => {
          watchFloatingTabPercent(trigger);
        });
        break;
      default:
        window.setTimeout(() => {
          showFloatingTab();
        }, trigger);
        break;
    }
  }

  function watchFloatingTabPx(trigger) {
    if (window.scrollY >= trigger) {
      showFloatingTab();
    } else {
      hideFloatingTab();
    }
  }
  function watchFloatingTabPercent(trigger) {
    const client_height = document.documentElement.clientHeight;
    const scroll_height = document.documentElement.scrollHeight - client_height;
    const target = (trigger / 100) * scroll_height;
    if (window.scrollY >= target) {
      showFloatingTab();
    } else {
      hideFloatingTab();
    }
  }

  function addFloatingTab(promotion) {
    const html_container = document.createElement("div");
    html_container.innerHTML = promotion.html;
    const floating_tab_element = html_container.children[0];
    floating_tab_element.classList.add("floating-tab-html");
    floating_tab_element.classList.add("floating-tab-element");
    floating_tab_element.classList.add("promotion-element");
    floating_tab_element.classList.add("promotion-" + promotion.id);
    floating_tab_element.setAttribute("promotion-id", promotion.id);
    floating_tab_element.setAttribute("data-id", promotion.id);
    document.body.appendChild(floating_tab_element);

    if (promotion.css) {
      const new_css = document.createElement("style");
      new_css.setAttribute("type", "text/css");
      new_css.classList.add("floating-tab-css");
      new_css.classList.add("floating-tab-element");
      new_css.classList.add("promotion-element");
      new_css.classList.add("promotion-" + promotion.id);
      new_css.textContent = promotion.css;
      document.body.appendChild(new_css);
    }

    if (promotion.js) {
      insertJs(promotion.id, promotion.js);
    }

    if (promotion.open_lightbox) {
      if (window.DonationLightboxOptions) {
        delete window.DonationLightboxOptions;
      }
      if (window.donationLightboxObj) {
        delete window.donationLightboxObj;
      }
      clearEventsForFloatingTab();
      window.donationLightboxObj = new DonationLightbox();
    }
    if(hide_floating_tab) {
      hideFloatingTab();
    }
  }


  function validateEmail(email_input) {
    let is_valid = false;
    if(typeof email_input.checkValidity === 'function') {
      email_input.required = true;
      is_valid = email_input.checkValidity();
      email_input.required = false;
    }
    if(is_valid) is_valid = /\S+@\S+\.\S+/.test(email_input.value);
    return is_valid;
  }
  
  function suppressFloatingEmailSignup(promotion, close_modal) {
    if(close_modal) {
      const floating_signup = document.querySelector('.fes-container');
      floating_signup.style.display = "none";
      hide_floating_tab = false;
      showFloatingTab();
      stopWatchingFloatingEmailSignup();
    }

    if(parseInt(promotion.cookie_hours) > 0) {
      setCookie(promotion.cookie_name, promotion.cookie_hours);
    } else if(parseInt(promotion.close_cookie_hours) > 0) {
      setCookie(promotion.cookie_name, promotion.close_cookie_hours);
    }
  }

  async function floatingEmailSignupSubmit(form, token, promotion) {
    const email_input = form.querySelector('.fes-container__inner__form__email');
    const is_valid = validateEmail(email_input);
    if(!is_valid) {
      form.classList.add('has-error');
    } else {
      form.classList.remove('has-error');
      form.classList.add('processing');
      if(promotion.submit_url) {
        try {
          const response = await fetch(promotion.submit_url, {
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            method: 'POST',
            body: JSON.stringify({ email: email_input.value, recaptcha: token }),        
          });
        
          const r = await response.json();
          if(r.success) {
            const floating_signup = form.closest('.fes-container');
            floating_signup.classList.add("submitted");
            suppressFloatingEmailSignup(promotion, false);  
          } else {
            console.error('Error submitting floating signup form', r.error);
          }
        } catch (error) {
          console.error('Error submitting floating signup form', error);
        }
      } else {
        floating_signup.classList.add("submitted");
      }
      form.classList.remove('processing');
    }
  }

  function addFloatingEmailSignup(promotion) {
    const floating_signup = document.createElement("div");
    floating_signup.classList.add("fes-container");
    floating_signup.setAttribute("promotion-id", promotion.id);

    const floating_signup_inner = document.createElement("div");
    floating_signup_inner.classList.add("fes-container__inner");

    const floating_signup_close = document.createElement("div");
    floating_signup_close.classList.add("fes-container__inner__close");
    floating_signup_close.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.49991 8.70706L13.3638 14.571L14.778 13.1568L8.91412 7.29285L14.3638 1.84315L12.9496 0.428939L7.49991 5.87863L2.05011 0.428833L0.635893 1.84305L6.08569 7.29285L0.22168 13.1569L1.63589 14.5711L7.49991 8.70706Z" fill="${promotion.fg_color}"/>
      </svg>
    `;
    floating_signup_close.addEventListener("click", (e) => {
      suppressFloatingEmailSignup(promotion, true);
    });

    const floating_signup_text = document.createElement("div");
    floating_signup_text.classList.add("fes-container__inner__text");

    const floating_signup_title = document.createElement("div");
    floating_signup_title.classList.add("fes-container__inner__text__title");
    floating_signup_title.innerHTML = `
      <span class='fes-pre-submission-show'>${promotion.title}</span>
      <span class='fes-post-submission-show'>${promotion.post_submission_title}</span>
    `;

    const floating_signup_content = document.createElement("div");
    floating_signup_content.classList.add("fes-container__inner__text__content");
    floating_signup_content.innerHTML = `
      <span class='fes-pre-submission-show'>${promotion.paragraph}</span>
      <span class='fes-post-submission-show'>${promotion.post_submission_paragraph}</span>
    `;

    const floating_signup_form = document.createElement("form");
    floating_signup_form.classList.add("fes-container__inner__form");    
    floating_signup_form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const form = e.target.closest('.fes-container__inner__form');
      if(promotion.recaptcha) {
        grecaptcha.ready(() => {
          grecaptcha.execute(promotion.recaptcha, {action: 'submit'}).then(function(token) {       
            floatingEmailSignupSubmit(form, token, promotion);
          });
        });  
      } else {
        floatingEmailSignupSubmit(form, null, promotion);
      }
    });

    const floating_signup_email = document.createElement("input");
    floating_signup_email.classList.add("fes-container__inner__form__email");
    floating_signup_email.classList.add("fes-pre-submission-show");
    floating_signup_email.setAttribute("type", "email");
    floating_signup_email.setAttribute("name", "email");
    floating_signup_email.setAttribute("placeholder", "Email Address");

    const floating_signup_submit = document.createElement("button");
    floating_signup_submit.classList.add("fes-container__inner__form__submit");
    floating_signup_submit.classList.add("fes-pre-submission-show");
    floating_signup_submit.setAttribute("type", "submit");
    floating_signup_submit.innerHTML = "Submit";

    const floating_signup_post_submit_button = document.createElement("a");
    floating_signup_post_submit_button.classList.add("fes-container__inner__form__post-submit-button");
    floating_signup_post_submit_button.classList.add("fes-post-submission-show");
    floating_signup_post_submit_button.textContent = promotion.post_submission_button.title;
    floating_signup_post_submit_button.href = promotion.post_submission_button.url;
    floating_signup_post_submit_button.target = promotion.post_submission_button.target;

    const floating_signup_error = document.createElement("div");
    floating_signup_error.classList.add("fes-container__inner__form__error");
    floating_signup_error.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="7" fill="#F04245"/><text x="5" y="11" fill="white">!</text></svg>
      The email address is invalid, please try again.
    `;

    const css = `
      .fes-container__inner {
        background: ${promotion.bg_color};
        color: ${promotion.fg_color};
      }
      .fes-container__inner__form__email {
        border: 2px solid ${promotion.bg_color};
      }
      .fes-container__inner__form__submit,
      .fes-container__inner__form__post-submit-button {
        background: ${promotion.button_bg_color};
        color: ${promotion.button_fg_color};
      }
      ${promotion.custom_css}
    `;

    floating_signup_form.appendChild(floating_signup_email);
    floating_signup_form.appendChild(floating_signup_submit);
    floating_signup_form.appendChild(floating_signup_post_submit_button);
    floating_signup_form.appendChild(floating_signup_error);

    floating_signup_text.appendChild(floating_signup_title);
    floating_signup_text.appendChild(floating_signup_content);

    floating_signup_inner.appendChild(floating_signup_text);
    floating_signup_inner.appendChild(floating_signup_form);
    floating_signup_inner.appendChild(floating_signup_close);

    floating_signup.appendChild(floating_signup_inner);
    document.body.appendChild(floating_signup);

    insertCss(promotion.id, css);

    hide_floating_tab = true;
    hideFloatingTab();
  }



  function stopWatchingFloatingEmailSignup() {
    document.removeEventListener("scroll", watchFloatingEmailSignupForFooterVisibility);
  }
  function hideFloatingEmailSignup() {
    const floating_signup = document.querySelector('.fes-container');
    if (floating_signup) {
      floating_signup.style.display = "none";
    }
  }
  function showFloatingEmailSignup() {
    const floating_signup = document.querySelector('.fes-container');
    if (floating_signup) {
      floating_signup.style.display = null;
    }
  }
  function watchFloatingEmailSignupForFooterVisibility() {
    const footer = document.querySelector('footer');
    const floating_signup = document.querySelector('.fes-container');
    const rect = footer.getBoundingClientRect();
    if(rect.top <= (window.innerHeight || document.documentElement.clientHeight)) {
      hide_floating_tab = false;
      hideFloatingEmailSignup();
      showFloatingTab();
    } else if(floating_signup.style.display === "none") { 
      hide_floating_tab = true;
      hideFloatingTab();
      showFloatingEmailSignup();      
    }
  }
  function watchFloatingEmailSignup() {
    const footer = document.querySelector('footer');
    if(footer) {
      document.addEventListener("scroll", watchFloatingEmailSignupForFooterVisibility);
    }
  }

  function addVideoLightbox(promotion) {
    const is_autoplay = promotion.options.includes('autoplay');
    const is_muted = promotion.options.includes('muted');

    const video_lightbox = document.createElement("div");
    video_lightbox.classList.add("fs-video-modal-container");
    video_lightbox.setAttribute("promotion-id", promotion.id);

    const video_modal = document.createElement("div");
    video_modal.classList.add("fs-video-modal");

    const video_modal_close_button = document.createElement("div");
    video_modal_close_button.classList.add("fs-video-modal-close-button");
    video_modal_close_button.innerHTML = `
      <svg width="43" height="43" viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0.707031 0.707092L41.707 41.7071" stroke="white" stroke-width="2"/>
      <path d="M41.707 0.707092L0.707031 41.7071" stroke="white" stroke-width="2"/>
      </svg>
    `;
    video_modal.appendChild(video_modal_close_button);

    const video_content = document.createElement("div");
    video_content.classList.add("fs-video-modal-content");

    let video_type = 'mp4';
    let video_html = '';
    let video_id = '';
    let poster_attribute = '';
    if (promotion.video_url.includes('youtube.com') || promotion.video_url.includes('youtu.be')) {
      if (promotion.video_url.includes('youtube.com')) {
        const url = new URL(promotion.video_url);
        video_id = url.searchParams.get('v');
      } else if (promotion.video_url.includes('youtu.be')) {
        video_id = promotion.video_url.split('youtu.be/')[1].split('?')[0];
      }
      if (promotion.thumbnail) {
        poster_attribute = `background-image:url(${promotion.thumbnail}); background-size:cover; background-position:center;`;
      }
      video_html = `<div id="fs-youtube-video-container" style="${poster_attribute}"></div>`;
      video_type = 'youtube';
    } else if (promotion.video_url.endsWith('.mp4')) {
      if (promotion.thumbnail) {
        poster_attribute = `poster="${promotion.thumbnail}"`;
      }
      video_html = `
        <video width="100%" height="100%" controls ${poster_attribute} ${is_autoplay ? 'autoplay' : ''} ${is_muted ? 'muted' : ''}>
          <source src="${promotion.video_url}" type="video/mp4">Your browser does not support the video tag.
        </video>`;
    }

    video_content.innerHTML = video_html;
    video_modal.appendChild(video_content);

    if(promotion.button.url) {
      // decode HTML entities in button title
      const temp = document.createElement('div');
      temp.innerHTML = promotion.button.title;
      const decoded_title = temp.innerHTML;

      const video_modal_cta_button = document.createElement("a");
      video_modal_cta_button.classList.add("fs-video-modal-cta-button");
      video_modal_cta_button.href = promotion.button.url;
      video_modal_cta_button.target = promotion.button.target;
      video_modal_cta_button.innerHTML = decoded_title;
      video_modal.appendChild(video_modal_cta_button);
    }


    video_lightbox.appendChild(video_modal);


    let css = '';
    if(promotion.background_color) {
      css += `
        .fs-video-modal-container[promotion-id="${promotion.id}"].fs-video-modal-container {
          background: ${promotion.background_color};
        }
      `;
    }
    if(promotion.foreground_color) {
      css += `
        .fs-video-modal-container[promotion-id="${promotion.id}"] .fs-video-modal-close-button svg path {
          stroke: ${promotion.foreground_color};
        }
      `;
    }
    if(promotion.button_background_color) {
      css += `
        .fs-video-modal-container[promotion-id="${promotion.id}"] .fs-video-modal-cta-button {
          background: ${promotion.button_background_color};
        }
      `;
    }
    if(promotion.button_foreground_color) {
      css += `
        .fs-video-modal-container[promotion-id="${promotion.id}"] .fs-video-modal-cta-button {
          color: ${promotion.button_foreground_color};
        }
      `;
    }
    css += promotion.css;
    if(css) {
      insertCss(promotion.id, css);
    }


    document.body.appendChild(video_lightbox);

    if(video_type === 'youtube') {
      const tag = document.createElement('script');
      tag.src = "https://www.youtube.com/iframe_api";
      document.body.appendChild(tag);

      window.onYouTubeIframeAPIReady = function() {
        window.video_lightbox_yt_player = new YT.Player('fs-youtube-video-container', {
          height: '390',
          width: '640',
          videoId: video_id,
          playerVars: { autoplay: is_autoplay ? 1 : 0, mute: is_muted ? 1 : 0 },
          events: {
            'onReady': function(event) {              
              if(is_autoplay) {
                event.target.playVideo();
              }
            }
          }
        });
      };
    }

    function openVideoModal() {
      const video_lightbox_container = document.querySelector('.fs-video-modal-container');
      video_lightbox_container.classList.add('fs-video-modal-open');
      video_lightbox_container.querySelector('.fs-video-modal-close-button').addEventListener('click', closeVideoModal);
      video_lightbox_container.querySelector('.fs-video-modal-content').addEventListener('click', clickWithinModal);
      video_lightbox_container.addEventListener('click', clickOutsideModal);
      document.body.addEventListener('keyup', detectEscape);

      setTimeout(() => {
          video_lightbox_container.classList.add('fs-video-modal-show');
      }, 100);      
    }
    function closeVideoModal() {
      const video_lightbox_container = document.querySelector('.fs-video-modal-container');
      video_lightbox_container.removeEventListener('click', clickOutsideModal);
      video_lightbox_container.querySelector('.fs-video-modal-close-button').removeEventListener('click', closeVideoModal);
      video_lightbox_container.querySelector('.fs-video-modal-content').removeEventListener('click', clickWithinModal);
      document.body.removeEventListener('keyup', detectEscape);

      video_lightbox_container.classList.remove('fs-video-modal-show');
      setTimeout(() => {
        video_lightbox_container.classList.remove('fs-video-modal-open');
        video_lightbox_container.remove();
      }, 600);
    }
    function clickOutsideModal(e) {
      closeVideoModal();
    }
    function clickWithinModal(e) {
      e.stopPropagation();
    }
    function detectEscape(e) {      
      if (e.key === "Escape") {
        closeVideoModal();
      }
    }

    openVideoModal();
  }

  function addOverlay(promotion) {
    let s = document.createElement("script");
    s.setAttribute("type", "text/javascript");
    s.setAttribute("src", promotion.js_url);
    s.setAttribute("data-title", promotion.title);
    s.setAttribute("data-subtitle", promotion.subtitle);
    s.setAttribute("data-logo", promotion.logo);
    s.setAttribute("data-donation_form", promotion.donation_form);
    s.setAttribute("data-paragraph", promotion.paragraph);
    s.setAttribute("data-other_label", promotion.other_label);
    s.setAttribute("data-button_label", promotion.button_label);
    s.setAttribute("data-cookie_name", promotion.cookie_name);
    s.setAttribute("data-cookie_expiry", promotion.cookie_expiry);
    s.setAttribute("data-max_width", promotion.max_width);
    s.setAttribute("data-max_height", promotion.max_height);
    s.setAttribute("data-cta_type", promotion.cta_type);
    s.setAttribute("data-image", promotion.image);
    s.setAttribute("data-amounts", promotion.amounts);
    s.setAttribute("data-promotion_id", promotion.id);
    document.body.appendChild(s);

    insertCss(promotion.id, promotion.custom_css);
  }

  function insertCss(promotion_id, css) {
    const new_css = document.createElement("style");
    new_css.setAttribute("type", "text/css");
    new_css.classList.add("promotion-css");
    new_css.classList.add("promotion-element");
    new_css.classList.add("promotion-" + promotion_id);
    new_css.textContent = css;
    document.body.appendChild(new_css);
  }

  function insertJs(promotion_id, js) {
    const js_container_id = "promotion-" + promotion_id;
    const js_container = document.createElement("div");
    js_container.classList.add("promotion-js");
    js_container.classList.add("promotion-element");
    js_container.setAttribute("id", js_container_id);
    js_container.innerHTML = js;
    document.body.appendChild(js_container);

    Array.from(document.getElementById(js_container_id).children).forEach(
      (child) => {
        if (child.innerHTML != "") {
          eval(child.innerHTML);
        } else if (child.tagName == "SCRIPT" && child.src != "") {
          const new_script = document.createElement("script");
          [...child.attributes].forEach((attr) => {
            new_script.setAttribute(attr.nodeName, attr.nodeValue);
          });
          new_script.classList.add("promotion-js");
          new_script.classList.add("promotion-element");
          new_script.classList.add("promotion-" + promotion_id);

          child.remove();
          document.getElementsByTagName("head")[0].appendChild(new_script);
        } else if (child.tagName == "LINK") {
          const new_link = document.createElement("link");
          [...child.attributes].forEach((attr) => {
            new_link.setAttribute(attr.nodeName, attr.nodeValue);
          });
          new_link.classList.add("promotion-js");
          new_link.classList.add("promotion-element");
          new_link.classList.add("promotion-" + promotion_id);

          child.remove();
          document.body.appendChild(new_link);
        }
      }
    );
  }

  function setCookie(cookie, hours = 24, path = "/") {
    const expires = new Date(Date.now() + parseInt(hours) * 36e5).toUTCString();
    document.cookie =
      cookie +
      "=" +
      encodeURIComponent(true) +
      "; expires=" +
      expires +
      "; path=" +
      path;
  }

  function getCookie(cookie) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${cookie}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
  }

  function deleteCookie(cookie, path = "/") {
    setCookie(cookie, -1, path);
  }

  function getTriggerType(trigger) {
    /**
     * Any integer (e.g., 5) -> Number of seconds to wait before triggering the lightbox
     * Any pixel (e.g.: 100px) -> Number of pixels to scroll before trigger the lightbox
     * Any percentage (e.g., 30%) -> Percentage of the height of the page to scroll before triggering the lightbox
     * The word exit -> Triggers the lightbox when the mouse leaves the DOM area (exit intent).
     * With 0 as default, the lightbox will trigger as soon as the page finishes loading.
     */

    if (!isNaN(trigger)) {
      return "seconds";
    } else if (trigger.includes("px")) {
      return "pixels";
    } else if (trigger.includes("%")) {
      return "percent";
    } else if (trigger.includes("exit")) {
      return "exit";
    } else if (trigger.includes("js")) {
      return "js";
    } else {
      return false;
    }
  }

  function makePromoReplacements(promotion) {
    if(typeof promotion.url == "string" && promotion.url) {
      if(promotion.url.includes("HOST_PAGE_URL")) {
        promotion.url = promotion.url.replace("HOST_PAGE_URL", encodeURIComponent(window.location.href));
      }

      if(promotion.append_parent_query_string && window.location.search) {
        const query_string = window.location.search.startsWith('?') ? window.location.search.slice(1) : window.location.search;
        promotion.url += (promotion.url.includes('?')) ? '&' : '?';
        promotion.url += query_string;
      }
    }
    return promotion;
  }
});
