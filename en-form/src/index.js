import ENFormParent from "./app/app";
import "./scss/main.scss";
//run();
window.addEventListener("load", function () {
  window.ENFormParent = ENFormParent;
  let enFormParent = new ENFormParent();
  // Set default options
  if (typeof window.ENFormParentOptions !== "undefined") {
    enFormParent.setOptions(window.ENFormParentOptions);
  }
});
