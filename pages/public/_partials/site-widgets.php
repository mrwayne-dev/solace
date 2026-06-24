<?php
// ============================================================
// SITE WIDGETS — shared across all public pages
//   1. Google Website Translate (100+ languages)
//   2. Smartsupp live chat (replaces the old Telegram widget)
// Include once per page (near the end of <body>).
// ============================================================
?>
<!-- Google Website Translate -->
<div id="google_translate_element" class="gt-widget" aria-label="Translate this site"></div>
<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'en',
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
      autoDisplay: false
    }, 'google_translate_element');
  }
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<style>
  .gt-widget {
    position: fixed; left: 18px; bottom: 18px; z-index: 2147482000;
    background: #fff; border: 1px solid rgba(0,0,0,.12); border-radius: 10px;
    padding: 5px 8px; box-shadow: 0 8px 24px -8px rgba(0,0,0,.35);
    font-family: system-ui, -apple-system, sans-serif; max-width: 220px;
  }
  .gt-widget .goog-te-combo { margin: 0; padding: 4px 6px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15); font-size: 13px; max-width: 200px; }
  .gt-widget .goog-logo-link, .gt-widget .goog-te-gadget span { display: none !important; }
  .gt-widget .goog-te-gadget { font-size: 0 !important; color: transparent !important; }
  /* Stop Google's top banner from pushing the page down */
  .goog-te-banner-frame.skiptranslate, iframe.goog-te-banner-frame { display: none !important; }
  body { top: 0 !important; }
  @media (max-width: 560px) { .gt-widget { left: 12px; bottom: 12px; } }
  @media print { .gt-widget { display: none; } }
</style>

<!-- Smartsupp Live Chat script -->
<script type="text/javascript">
var _smartsupp = _smartsupp || {};
_smartsupp.key = '3c2dbbfc4e90eff8ecbbe0a2f4936d2be60ccec7';
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>
<noscript>Powered by <a href="https://www.smartsupp.com" target="_blank" rel="noopener">Smartsupp</a></noscript>
