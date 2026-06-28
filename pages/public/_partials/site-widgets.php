<?php
// ============================================================
// SITE WIDGETS — shared across all public pages
//   1. Google Website Translate (100+ languages)
//   2. Smartsupp live chat (replaces the old Telegram widget)
// Include once per page (near the end of <body>).
// ============================================================
?>
<!-- Google Website Translate (shared partial) -->
<?php include __DIR__ . '/translate.php'; ?>

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
