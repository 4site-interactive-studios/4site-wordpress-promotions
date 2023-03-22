window.addEventListener("DOMContentLoaded", () => {
  "use strict";

  /**
   * All of the code for your public-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  if (window.client_side_triggered_config == undefined) {
    return;
  }

  window.rawCodeTriggers = [];

  for (const lightbox_id in client_side_triggered_config) {
    const type = client_side_triggered_config[lightbox_id].promotion_type;

    if (type == "multistep_lightbox") {
      addEventListener("trigger-promotion", triggerPromotionEvent);
    } else if (type == "raw_code" || type == "pushdown") {
      const cookie = client_side_triggered_config[lightbox_id].cookie;
      const cookieExpiration =
        client_side_triggered_config[lightbox_id].cookie_hours;
      const id = client_side_triggered_config[lightbox_id].id;
      window.rawCodeTriggers[id] = false;
      let trigger = client_side_triggered_config[lightbox_id].trigger;
      const triggerType = getTriggerType(trigger);

      if (!getCookie(cookie)) {
        if (triggerType === false) {
          trigger = 2000;
        }
        if (triggerType === "seconds") {
          trigger = Number(trigger) * 1000;
        }

        if (triggerType === "seconds" || triggerType === false) {
          window.setTimeout(() => {
            addRawCode(client_side_triggered_config[lightbox_id]);
            if (cookieExpiration) {
              setCookie(cookie, cookieExpiration);
            } else {
              setCookie(cookie);
            }
          }, trigger);
          window.rawCodeTriggers[
            client_side_triggered_config[lightbox_id].id
          ] = true;
        }
        if (triggerType === "exit") {
          document.body.addEventListener("mouseout", (e) => {
            if (e.clientY < 0 && !window.rawCodeTriggers[id]) {
              addRawCode(client_side_triggered_config[lightbox_id]);
              if (cookieExpiration) {
                setCookie(cookie, cookieExpiration);
              } else {
                setCookie(cookie);
              }
              window.rawCodeTriggers[id] = true;
            }
          });
        }
        if (triggerType === "pixels") {
          document.addEventListener(
            "scroll",
            function () {
              scrollTriggerPx(client_side_triggered_config[lightbox_id]);
            },
            true
          );
        }
        if (triggerType === "percent") {
          document.addEventListener(
            "scroll",
            function () {
              scrollTriggerPercent(client_side_triggered_config[lightbox_id]);
            },
            true
          );
        }

        if (triggerType === "js") {
          window.addEventListener("trigger-promotion", triggerPromotionEvent);
        }
      } else if (getCookie(cookie) && triggerType == "js") {
        window.addEventListener("trigger-promotion", triggerPromotionEvent);
      }
    } else if (type == "floating_tab") {
      const cookie = client_side_triggered_config[lightbox_id].cookie;
      const cookieExpiration =
        client_side_triggered_config[lightbox_id].cookie_hours;
      const id = client_side_triggered_config[lightbox_id].id;
      window.rawCodeTriggers[id] = false;
      const triggerType = client_side_triggered_config[lightbox_id].trigger;

      if (triggerType == "js") {
        window.addEventListener("trigger-promotion", triggerPromotionEvent);
      } else if (triggerType == 0) {
        addRawCode(client_side_triggered_config[lightbox_id]);
        if (cookieExpiration) {
          setCookie(cookie, cookieExpiration);
        } else {
          setCookie(cookie);
        }

        window.rawCodeTriggers[
          client_side_triggered_config[lightbox_id].id
        ] = true;
      }
    }
  }

  function setCookie(cookie, hours = 24, path = "/") {
    const expires = new Date(Date.now() + hours * 36e5).toUTCString();
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
    setCookie(cookie, "", -1, path);
  }
  ``;

  function getTriggerType(trigger) {
    /**
     * Any integer (e.g., 5) -> Number of seconds to wait before triggering the lightbox
     * Any pixel (e.g.: 100px) -> Number of pixels to scroll before trigger the lightbox
     * Any percentage (e.g., 30%) -> Percentage of the height of the page to scroll before triggering the lightbox
     * The word exit -> Triggers the lightbox when the mouse leaves the DOM area (exit intent).
     * With 0 as default, the lightbox will trigger as soon as the page finishes loading.
     */
    console.log("Trigger Value: ", trigger);

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

  function addRawCode(promotionConfig) {
    removeOldPromotions();
    const promotionClass = "promotion-" + promotionConfig.id;

    if (promotionConfig.promotion_type == "pushdown") {
      const pushdownMode = promotionConfig.pushdown_type;
      const pushdownImage = promotionConfig.image;
      const pushdownGif = promotionConfig.gif != "" ? promotionConfig.gif : "";
      const pushdownText = promotionConfig.pushdown_title;
      const pushdownLink = promotionConfig.url;
      const pushdownSrc = promotionConfig.src;

      const pushdownScript = document.createElement("script");
      pushdownScript.src = pushdownSrc;
      pushdownScript.id = "foursite-wordpress-pushdown-promotion-js";
      pushdownScript.setAttribute("crossorigin", "anonymous");
      pushdownScript.setAttribute("data-pushdown-mode", pushdownMode);
      pushdownScript.setAttribute("data-pushdown-link", pushdownLink);
      pushdownScript.setAttribute("data-pushdown-image", pushdownImage);
      if (pushdownGif != "") {
        pushdownScript.setAttribute("data-pushdown-gif", pushdownGif);
      }
      pushdownScript.setAttribute("data-pushdown-content", pushdownText);
      pushdownScript.classList.add(promotionClass);

      document.body.appendChild(pushdownScript);
      return;
    } else if (
      promotionConfig.promotion_type == "floating_tab" &&
      promotionConfig.html
    ) {
      const htmlContainer = document.createElement("div");
      htmlContainer.innerHTML = promotionConfig.html;
      const floatingTabElement = htmlContainer.children[0];
      floatingTabElement.classList.add("floating-tab-html");
      floatingTabElement.classList.add("floating-tab-element");
      floatingTabElement.classList.add("promotion-element");
      floatingTabElement.classList.add(promotionClass);
      document.body.appendChild(floatingTabElement);

      if (promotionConfig.css) {
        const newCSS = document.createElement("style");
        newCSS.setAttribute("type", "text/css");
        newCSS.classList.add("floating-tab-css");
        newCSS.classList.add("floating-tab-element");
        newCSS.classList.add("promotion-element");
        newCSS.classList.add(promotionClass);
        newCSS.textContent = promotionConfig.css;
        document.body.appendChild(newCSS);
      }

      return;
    }

    if (promotionConfig.html) {
      const htmlContainer = document.createElement("div");
      htmlContainer.classList.add("promotion-html");
      htmlContainer.classList.add("promotion-element");
      htmlContainer.classList.add(promotionClass);
      htmlContainer.innerHTML = promotionConfig.html;
      document.body.appendChild(htmlContainer);
    }

    if (promotionConfig.css) {
      const newCSS = document.createElement("style");
      newCSS.setAttribute("type", "text/css");
      newCSS.classList.add("promotion-css");
      newCSS.classList.add("promotion-element");
      newCSS.classList.add(promotionClass);
      newCSS.textContent = promotionConfig.css;
      document.body.appendChild(newCSS);
    }

    if (promotionConfig.js) {
      // Remove line breaks between scripts
      const jsContainer = document.createElement("div");
      jsContainer.classList.add("promotion-js");
      jsContainer.classList.add("promotion-element");
      jsContainer.classList.add(promotionClass);
      jsContainer.innerHTML = promotionConfig.js;

      document.body.appendChild(jsContainer);
      Array.from(document.querySelector(".promotion-js").children).forEach(
        (child) => {
          if (child.innerHTML != "") {
            eval(child.innerHTML);
          } else if (child.tagName == "SCRIPT" && child.src != "") {
            const newScript = document.createElement("script");
            newScript.setAttribute("src", child.src);
            newScript.classList.add("promotion-js");
            newScript.classList.add("promotion-element");
            newScript.classList.add(promotionClass);

            child.remove();
            document.body.appendChild(newScript);
          } else if (
            child.tagName == "LINK" &&
            child.getAttribute("href") != ""
          ) {
            const newLink = document.createElement("link");
            newLink.setAttribute("href", child.getAttribute("href"));

            if (child.getAttribute("rel")) {
              newLink.setAttribute("rel", child.getAttribute("rel"));
            }

            newLink.classList.add("promotion-js");
            newLink.classList.add("promotion-element");
            newLink.classList.add(promotionClass);

            child.remove();
            document.body.appendChild(newLink);
          }
        }
      );
    }
  }

  function scrollTriggerPx(promotionConfig) {
    const triggerValue = Number(promotionConfig.trigger.replace("px", ""));
    if (
      window.scrollY >= triggerValue &&
      !window.rawCodeTriggers[promotionConfig.id]
    ) {
      addRawCode(promotionConfig);
      if (promotionConfig.cookie_hours) {
        setCookie(promotionConfig.cookie, promotionConfig.cookie_hours);
      } else {
        setCookie(cookie);
      }
      window.rawCodeTriggers[promotionConfig.id] = true;
    }
  }
  function scrollTriggerPercent(promotionConfig) {
    const triggerValue = Number(promotionConfig.trigger.replace("%", ""));
    const clientHeight = document.documentElement.clientHeight;
    const scrollHeight = document.documentElement.scrollHeight - clientHeight;
    const target = (triggerValue / 100) * scrollHeight;
    if (
      window.scrollY >= target &&
      !window.rawCodeTriggers[promotionConfig.id]
    ) {
      addRawCode(promotionConfig);
      if (promotionConfig.cookie_hours) {
        setCookie(promotionConfig.cookie, promotionConfig.cookie_hours);
      } else {
        setCookie(cookie);
      }
      window.rawCodeTriggers[promotionConfig.id] = true;
    }
  }

  function triggerPromotionEvent(e) {
    if (e.detail.promotion_id) {
      const promotion = client_side_triggered_config[e.detail.promotion_id];

      if (!promotion) {
        console.log("Invalid promotion");
        return;
      }

      if (promotion.trigger != "js") {
        console.log("Promotion not set to JS trigger");
        return;
      }

      if (promotion.promotion_type == "multistep_lightbox") {
        promotion.trigger = 0;
        window.DonationLightboxOptions = promotion;
        deleteCookie(promotion.cookie_name);
        removeOldPromotions();
        new DonationLightbox();
      } else if (promotion.promotion_type == "floating_tab") {
        const promotionClass = "promotion-" + promotion.id;
        addRawCode(promotion);
        new DonationLightbox();
      } else {
        addRawCode(promotion);
      }
    }
  }

  function removeOldPromotions() {
    document
      .querySelectorAll(
        ".foursiteDonationLightbox,.promotion-element,.pushdown-mode-image,.pushdown-mode-text"
      )
      .forEach((promotion) => {
        promotion.remove();
      });
  }

  window.triggerPromotion = function (id) {
    window.dispatchEvent(
      new CustomEvent("trigger-promotion", { detail: { promotion_id: id } })
    );
  };
});
