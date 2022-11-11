(function ($) {
  "use strict";

  // iterate over this object's configurations and take appropriate action
  console.log("client_side_triggered_config", client_side_triggered_config);

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

  window.rawCodeTriggers = [];

  for (const property in client_side_triggered_config) {
    const type = client_side_triggered_config[property].promotion_type;

    if (type == "multistep_lightbox") {
      addEventListener("open-lightbox", openLightboxEvent);
    } else if (type == "raw_code") {
      const cookie = client_side_triggered_config[property].cookie;
      const cookieExpiration =
        client_side_triggered_config[property].cookie_hours;
      const id = client_side_triggered_config[property].id;
      window.rawCodeTriggers[id] = false;
      let trigger = client_side_triggered_config[property].trigger;
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
            addRawCode(client_side_triggered_config[property]);
            if (cookieExpiration) {
              setCookie(cookie, cookieExpiration);
            } else {
              setCookie(cookie);
            }
          }, trigger);
          window.rawCodeTriggers[
            client_side_triggered_config[property].id
          ] = true;
        }
        if (triggerType === "exit") {
          document.body.addEventListener("mouseout", (e) => {
            if (e.clientY < 0 && !window.rawCodeTriggers[id]) {
              addRawCode(client_side_triggered_config[property]);
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
              scrollTriggerPx(client_side_triggered_config[property]);
            },
            true
          );
        }
        if (triggerType === "percent") {
          document.addEventListener(
            "scroll",
            function () {
              scrollTriggerPercent(client_side_triggered_config[property]);
            },
            true
          );
        }

        if (triggerType === "js") {
          window.addEventListener("open-lightbox", openLightboxEvent);
        }
      } else if (getCookie(cookie) && triggerType == "js") {
        window.addEventListener("open-lightbox", openLightboxEvent);
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

  function addRawCode(array) {
    if (array.css) {
      const newCSS = document.createElement("style");
      newCSS.setAttribute("type", "text/css");
      newCSS.textContent = array.css;
      document.head.appendChild(newCSS);
    }
    if (array.js) {
      // Remove line breaks between scripts
      const scripts = array.js
        .replace(/(\r\n|\n|\r)/gm, "")
        .replace("><", ">|<")
        .replace(">\x3C", ">|\x3C")
        .split("|");

      for (let i = 0; i < scripts.length; i++) {
        const script = stringToElement(scripts[i]);
        document.head.appendChild(script);
      }
    }
    if (array.html) {
      // Remove line breaks between elements
      const elements = array.html
        .replace(/(\r\n|\n|\r)/gm, "")
        .replace("><", ">|<")
        .replace(">\x3C", ">|\x3C")
        .split("|");

      for (let i = 0; i < elements.length; i++) {
        const element = stringToElement(elements[i]);
        document.body.appendChild(element);
      }
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
      const lightbox = client_side_triggered_config[e.detail.lightbox_id];

      if (lightbox.promotion_type != "raw_code") {
        window.DonationLightboxOptions = lightbox;
        deleteCookie(lightbox.cookie_name);
        document
          .querySelectorAll(".foursiteDonationLightbox")
          .forEach((lightbox) => {
            lightbox.remove();
          });
        window.jsLightbox = new DonationLightbox();
      } else {
        addRawCode(lightbox);
      }
    }
  }

  function stringToElement(element) {
    // Get text between tags
    let elementContent;
    if (element.includes("/>")) {
      elementContent = false;
    } else {
      elementContent = element.slice(
        element.indexOf(">") + 1,
        element.indexOf("</")
      );
    }

    let elementNameEnd;
    let hasAttributes = false;
    if (
      element.indexOf(" ") != -1 &&
      element.indexOf(" ") > element.indexOf(">")
    ) {
      elementNameEnd = element.indexOf(">");
    } else {
      elementNameEnd = element.indexOf(" ");
      hasAttributes = "true";
    }

    const elementName = element.slice(element.indexOf("<") + 1, elementNameEnd);

    const newElement = document.createElement(elementName);

    if (elementContent) {
      newElement.textContent = elementContent;
    }

    if (hasAttributes) {
      const attributes = element
        .slice(element.indexOf(" ") + 1, element.indexOf(">"))
        .split(" ");

      for (let i = 0; i < attributes.length; i++) {
        if (attributes[i].includes("=")) {
          newElement.setAttribute(
            attributes[i].split("=")[0],
            attributes[i].split("=")[1].replace(/"/g, "")
          );
        }
      }
    }

    return newElement;
  }
})(jQuery);
