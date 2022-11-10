(function ($) {
  "use strict";

  // iterate over this object's configurations and take appropriate action  
  console.log('js_triggered_lb_config', js_triggered_lb_config);

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

/*
  $.post(
    "/wp-content/plugins/4site-wordpress-promotions/public/raw-code-data.php",
    function (data) {
      let jsonData;
      try {
        jsonData = JSON.parse(data);
      } catch {
        return false;
      }

      window.rawCodeTriggers = [];
      window.lightboxArr = jsonData;

      // if (jsonData["css"]) {
      //   document.querySelector(
      //     "head"
      //   ).innerHTML += `<style type='text/css'>${jsonData.css}</style>`;
      // }

      // if (jsonData.javascript) {
      //   document.querySelector("head").innerHTML += jsonData.javascript;
      // }

      // if (jsonData.html) {
      //   document.querySelector("body").innerHTML += jsonData.html;
      // }

      for (let i = 0; i < jsonData.length; i++) {
        const currentArr = jsonData[i];

        if (currentArr.type == "raw_code") {
          const cookie = currentArr.cookie;
          const cookieExpiration = currentArr.cookie_hours;
          const id = currentArr.id;
          window.rawCodeTriggers[id] = false;
          let trigger = currentArr.trigger;
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
                addRawCode(currentArr);
                if (cookieExpiration) {
                  setCookie(cookie, cookieExpiration);
                } else {
                  setCookie(cookie);
                }
              }, trigger);
              window.rawCodeTriggers[currentArr.id] = true;
            }
            if (triggerType === "exit") {
              document.body.addEventListener("mouseout", (e) => {
                if (e.clientY < 0 && !window.rawCodeTriggers[id]) {
                  addRawCode(currentArr);
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
                  scrollTriggerPx(currentArr);
                },
                true
              );
            }
            if (triggerType === "percent") {
              document.addEventListener(
                "scroll",
                function () {
                  scrollTriggerPercent(currentArr);
                },
                true
              );
            }

            if (triggerType === "js") {
              window.addEventListener("open-lightbox", openLightboxEvent);
            }
          } else if (triggerType == "js") {
            window.addEventListener("open-lightbox", openLightboxEvent);
          }
        } else {
          window.addEventListener("open-lightbox", openLightboxEvent);
        }
      }
    }
  );
*/
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

  function addRawCode(array) {
    if (array.css) {
      document.querySelector(
        "head"
      ).innerHTML += `<style type='text/css'>${array.css}</style>`;
    }
    if (array.javascript) {
      document.querySelector("head").innerHTML += array.javascript;
    }
    if (array.html) {
      document.querySelector("body").innerHTML += array.html;
    }
  }

  function scrollTriggerPx(arr) {
    const triggerValue = Number(arr.trigger.replace("px", ""));
    if (window.scrollY >= triggerValue && !window.rawCodeTriggers[arr.id]) {
      addRawCode(arr);
      if (arr.cookie_hours) {
        setCookie(arr.cookie, arr.cookie_hours);
      } else {
        setCookie(cookie);
      }
      window.rawCodeTriggers[arr.id] = true;
    }
  }
  function scrollTriggerPercent(arr) {
    const triggerValue = Number(arr.trigger.replace("%", ""));
    const clientHeight = document.documentElement.clientHeight;
    const scrollHeight = document.documentElement.scrollHeight - clientHeight;
    const target = (triggerValue / 100) * scrollHeight;
    if (window.scrollY >= target && !window.rawCodeTriggers[arr.id]) {
      addRawCode(arr);
      if (arr.cookie_hours) {
        setCookie(arr.cookie, arr.cookie_hours);
      } else {
        setCookie(cookie);
      }
      window.rawCodeTriggers[arr.id] = true;
    }
  }

  function openLightboxEvent(e) {
    if (e.detail.lightbox_id) {
      const lightbox = window.lightboxArr.find(
        (item) => item.id == e.detail.lightbox_id
      );

      if (lightbox.type != "raw_code") {
        const lightboxScript = document.createElement("script");
        lightboxScript.id = lightbox.script_name;

        // Remove lightbox script if it exists already
        if (document.querySelector(`[id=${lightboxScript.id}]`)) {
          document.querySelector(`[id=${lightboxScript.id}]`).remove();
        }
        lightboxScript.textContent = lightbox.script_code;

        const parentScript = document.querySelector(
          "#foursite-wordpress-promotion-js"
        );
        console.log("Adding ", lightboxScript);
        parentScript.parentElement.insertBefore(lightboxScript, parentScript);
      } else {
        addRawCode(lightbox);
      }
    }
  }
})(jQuery);
