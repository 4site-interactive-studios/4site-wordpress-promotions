const options = {
  logoURL: "http://4site.wordpress.test/logo_amnestyInternational.svg",
  imageURL: "http://4site.wordpress.test/image.jpg",
  title: "Join the movement!", 
  paragraph: "We're a global movement of 10 million activists and growing -- and together, we can build a world where human rights are enjoyed by all. Add your name to hear about opportunities to act when it matters most.", 
  info: "You'll receive updates and urgent action alerts from Amnesty USA. You can unsubscribe at any time.",
  blacklist: [], // ["^/"]
  whitelist: [], // ["^/"]
  dates: [], // ["05/20/2021", "06/28/2021"]
  cookie_name: "hideSignUpForm",
  trigger: 1000, // Trigger Lightbox after 1000 (1 second)
  iframe: "<iframe loading='lazy' width='100%' scrolling='no' class='en-iframe ' data-src='https://e-activist.com/page/84950/data/1?mode=DEMO' frameborder='0' allowfullscreen='' style='display:none' allow='autoplay; encrypted-media'></iframe>"
};

export { options };