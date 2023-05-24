window.addEventListener("DOMContentLoaded", () => {
  "use strict";

  if (client_side_triggered_config == undefined) {
    return;
  }

  document.addEventListener('multistep-lightbox', e => {
    if(e.detail.action == 'closed' && e.detail.context !== 'object') {
      document.getElementById(e.detail.id).remove();
    }
  });
 
  // lightboxes are limited to 1/page, and can be launched from a few different scripts that set this variable
  window.lightbox_triggered = (window.lightbox_triggered === undefined) ? false : window.lightbox_triggered;

  let scroll_px_triggered = [];
  let scroll_per_triggered = [];
  let exit_triggered = [];  
  let js_triggered = [];
  let time_triggered = [];
 
  window.rawCodeTriggers = [];

  for (let i = 0; i < client_side_triggered_config.length; i++) {
    // skip if there is already a cookie set for this promo
    const cookie = client_side_triggered_config[i].cookie_name;
    if(getCookie(cookie)) {
      continue;
    }

    // add our promo to the appropriate array
    let trigger = client_side_triggered_config[i].trigger;
    const trigger_type = getTriggerType(trigger);
    if (!trigger_type) {
      trigger_type = 'seconds';
      trigger = 2000;
    } else if (trigger_type === "seconds") {
      trigger = Number(trigger) * 1000;
    }

    switch(trigger_type) {
      case 'exit':
        exit_triggered.push(client_side_triggered_config[i]);
        break;
      case 'pixels':
        client_side_triggered_config[i].trigger = Number(client_side_triggered_config[i].trigger.replace("px", ""));
        scroll_px_triggered.push(client_side_triggered_config[i]);
        break;
      case 'percent':
        client_side_triggered_config[i].trigger = Number(client_side_triggered_config[i].trigger.replace("%", ""));
        scroll_per_triggered.push(client_side_triggered_config[i]);
        break;
      case 'js':
        js_triggered.push(client_side_triggered_config[i]);
        break;
      case 'seconds':
        time_triggered.push({ seconds: trigger, promo: client_side_triggered_config[i] });
        break;
      default:
        break;
    }
  }

  function addMultistepLightbox(promotion) { 
    if(window.donationLightboxObj) {
      delete window.donationLightboxObj;
    }

    // remove previously set logo fix CSS
    const existing_logo_fix_css = document.querySelectorAll('.multistep-logo-fix');
    for(let i = 0; i < existing_logo_fix_css.length; i++) {
      existing_logo_fix_css[i].remove();
    }

    // add new logo fix CSS (the external multistep script doesn't seem to respect the values we pass in)
    if(promotion.hasOwnProperty('logo_fix_css')) {
      const new_css = document.createElement("style");
      new_css.setAttribute("type", "text/css");
      new_css.classList.add("multistep-logo-fix");
      new_css.textContent = promotion.logo_fix_css;
      document.body.appendChild(new_css);
    }

    window.DonationLightboxOptions = promotion;
    window.DonationLightboxOptions.trigger = 0;

    window.donationLightboxObj = new DonationLightbox();
  }

  function launchPromotion(promotion) {
    switch(promotion.promotion_type) {
      case 'multistep_lightbox':
        if(window.lightbox_triggered) {
          return;
        } else {
          window.lightbox_triggered = true;
          addMultistepLightbox(promotion);
        }
        break;
      case 'raw_code':
        if(promotion.is_lightbox) {          
          if(window.lightbox_triggered) {
            return;
          } else {
            window.lightbox_triggered = true;
          }
        }
        addRawCode(promotion);
        break;
      case 'pushdown':
        addPushdown(promotion);
        break;
      case 'floating_tab':
        addFloatingTab(promotion);
        break;
      case 'overlay':
        if(!window.lightbox_triggered) {
          addOverlay(promotion);
          window.lightbox_triggered = true;
        } else {
          return;
        }
        break;
    }

    if(promotion.cookie_hours) {
      setCookie(promotion.cookie_name, promotion.cookie_hours);
    }
  }

  if(scroll_px_triggered.length) {
    document.addEventListener("scroll", scrollPxTrigger);
  }
  if(scroll_per_triggered.length) {
    document.addEventListener("scroll", scrollPercentTrigger); 
  }
  if(exit_triggered.length) {
    document.body.addEventListener("mouseleave", exitTrigger);
  }
  if(js_triggered.length) {
    addEventListener("trigger-promotion", jsTrigger);
  }
  if(time_triggered.length) {
    let zero_counter = 0;
    for(let i = 0; i < time_triggered.length; i++) {
      if(time_triggered[i].seconds == 0 && zero_counter == 0) {
        launchPromotion(time_triggered[i].promo);
      } else {
        window.setTimeout(() => {
          launchPromotion(time_triggered[i].promo);
        }, time_triggered[i].seconds + (zero_counter * .5));
      }
      if(time_triggered[i].seconds == 0) {
        zero_counter++;
      }
    }
  }

  function exitTrigger() {
    for(let i = exit_triggered.length-1; i >= 0; i--) {
      launchPromotion(exit_triggered[i]);
      exit_triggered.splice(i, 1);
      break;
    }
    if(exit_triggered.length == 0) {
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
    for(let i = scroll_px_triggered.length-1; i >= 0; i--) {
      if(window.scrollY >= scroll_px_triggered[i].trigger) {
        launchPromotion(scroll_px_triggered[i]);
        scroll_px_triggered.splice(i, 1);
      }
    }
    if(scroll_px_triggered.length == 0) {
      document.removeEventListener("scroll", scrollPxTrigger);
    }
  }

  function scrollPercentTrigger() {
    const client_height = document.documentElement.clientHeight;
    const scroll_height = document.documentElement.scrollHeight - client_height;

    for(let i = scroll_per_triggered.length-1; i >= 0; i--) {
      const target = (scroll_per_triggered[i].trigger / 100) * scroll_height;
      if(window.scrollY >= target) {
        launchPromotion(scroll_per_triggered[i]);
        scroll_per_triggered.splice(i, 1);
      }
    }
    if(scroll_per_triggered.length == 0) {
      document.removeEventListener("scroll", scrollPercentTrigger);
    }
  }

  function jsTrigger(e) {
    if(e.detail.promotion_id) {      
      const promotion_id = Number(e.detail.promotion_id);      
      for(let i = js_triggered.length-1; i >= 0; i--) {
        if(js_triggered[i].id == promotion_id) {
          deleteCookie(js_triggered[i].cookie_name);
          launchPromotion(js_triggered[i]);
          js_triggered.splice(i, 1);
          break;          
        }
      }
      if(scroll_per_triggered.length == 0) {
        document.removeEventListener("trigger-promotion", jsTrigger);
      }
    }
  }

  function addRawCode(promotion) {
    if (promotion.html) {
      const html_container = document.createElement("div");
      html_container.classList.add("promotion-html");
      html_container.classList.add("promotion-element");
      html_container.classList.add("promotion-" + promotion.id);
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
      if (promotion.gif != "") {
        pushdownScript.setAttribute("data-pushdown-gif", promotion.gif);
      }
      pushdownScript.setAttribute("data-pushdown-content", promotion.pushdown_title);
      pushdownScript.classList.add("promotion-" + promotion.id);

      document.body.appendChild(pushdownScript);
  }

  function addFloatingTab(promotion) {

    const html_container = document.createElement("div");
    html_container.innerHTML = promotion.html;
    const floating_tab_element = html_container.children[0];
    floating_tab_element.classList.add("floating-tab-html");
    floating_tab_element.classList.add("floating-tab-element");
    floating_tab_element.classList.add("promotion-element");
    floating_tab_element.classList.add("promotion-" + promotion.id);
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

    if(promotion.open_lightbox) {
      if(window.DonationLightboxOptions) {
        delete window.DonationLightboxOptions;
      }
      if(window.donationLightboxObj) {
        delete window.donationLightboxObj;
      }
      clearEventsForFloatingTab();
      window.donationLightboxObj = new DonationLightbox();
    }
  }

  function addOverlay(promotion) {
    let s = document.createElement('script');
    s.setAttribute('type', 'text/javascript');
    s.setAttribute('src', promotion.js_url);
    s.setAttribute('data-title', promotion.title);
    s.setAttribute('data-subtitle', promotion.subtitle);
    s.setAttribute('data-logo', promotion.logo);
    s.setAttribute('data-donation_form', promotion.donation_form);
    s.setAttribute('data-paragraph', promotion.paragraph);
    s.setAttribute('data-other_label', promotion.other_label);
    s.setAttribute('data-button_label', promotion.button_label);
    s.setAttribute('data-cookie_name', promotion.cookie_name);
    s.setAttribute('data-cookie_expiry', promotion.cookie_expiry);
    s.setAttribute('data-max_width', promotion.max_width);
    s.setAttribute('data-max_height', promotion.max_height);
    s.setAttribute('data-cta_type', promotion.cta_type);
    s.setAttribute('data-image', promotion.image);
    s.setAttribute('data-amounts', promotion.amounts);
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

    Array.from(document.getElementById(js_container_id).children).forEach((child) => {
        if (child.innerHTML != "") {

          eval(child.innerHTML);

        } else if (child.tagName == "SCRIPT" && child.src != "") {

          const new_script = document.createElement("script");
          [...child.attributes].forEach(attr => { 
            new_script.setAttribute(attr.nodeName, attr.nodeValue);
          });
          new_script.classList.add("promotion-js");
          new_script.classList.add("promotion-element");
          new_script.classList.add("promotion-" + promotion_id);

          child.remove();
          document.getElementsByTagName("head")[0].appendChild(new_script);

        } else if (child.tagName == "LINK") {

          const new_link = document.createElement("link");
          [...child.attributes].forEach(attr => { 
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
    const expires = new Date(Date.now() + hours * 36e5).toUTCString();
    document.cookie = cookie + "=" + encodeURIComponent(true) + "; expires=" + expires + "; path=" + path;
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

  window.triggerPromotion = function (id) {
    window.dispatchEvent(new CustomEvent("trigger-promotion", { detail: { promotion_id: id } }));
  };
});