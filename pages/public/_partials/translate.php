<?php
// ============================================================
// GOOGLE WEBSITE TRANSLATE — robust, always-visible selector
//
// Google's auto-rendered gadget UI is unreliable (esp. in in-app
// webviews and since Google sunset the free widget). So we render
// OUR OWN language <select> (always visible) and drive translation
// via the `googtrans` cookie — Google's engine applies it on load.
// Works across browsers/webviews and covers 100+ languages.
// Include once per page, near the end of <body>.
// ============================================================
?>
<div id="google_translate_element" style="display:none !important;"></div>
<div class="slm-translate" title="Translate this site">
  <span class="slm-translate__globe" aria-hidden="true">🌐</span>
  <select id="slm-lang" aria-label="Translate this site into your language">
    <option value="">Language</option>
    <option value="en">English</option>
    <option value="af">Afrikaans</option>
    <option value="sq">Albanian</option>
    <option value="am">Amharic</option>
    <option value="ar">Arabic</option>
    <option value="hy">Armenian</option>
    <option value="az">Azerbaijani</option>
    <option value="eu">Basque</option>
    <option value="be">Belarusian</option>
    <option value="bn">Bengali</option>
    <option value="bs">Bosnian</option>
    <option value="bg">Bulgarian</option>
    <option value="ca">Catalan</option>
    <option value="ceb">Cebuano</option>
    <option value="ny">Chichewa</option>
    <option value="zh-CN">Chinese (Simplified)</option>
    <option value="zh-TW">Chinese (Traditional)</option>
    <option value="co">Corsican</option>
    <option value="hr">Croatian</option>
    <option value="cs">Czech</option>
    <option value="da">Danish</option>
    <option value="nl">Dutch</option>
    <option value="eo">Esperanto</option>
    <option value="et">Estonian</option>
    <option value="tl">Filipino</option>
    <option value="fi">Finnish</option>
    <option value="fr">French</option>
    <option value="fy">Frisian</option>
    <option value="gl">Galician</option>
    <option value="ka">Georgian</option>
    <option value="de">German</option>
    <option value="el">Greek</option>
    <option value="gu">Gujarati</option>
    <option value="ht">Haitian Creole</option>
    <option value="ha">Hausa</option>
    <option value="haw">Hawaiian</option>
    <option value="iw">Hebrew</option>
    <option value="hi">Hindi</option>
    <option value="hmn">Hmong</option>
    <option value="hu">Hungarian</option>
    <option value="is">Icelandic</option>
    <option value="ig">Igbo</option>
    <option value="id">Indonesian</option>
    <option value="ga">Irish</option>
    <option value="it">Italian</option>
    <option value="ja">Japanese</option>
    <option value="jw">Javanese</option>
    <option value="kn">Kannada</option>
    <option value="kk">Kazakh</option>
    <option value="km">Khmer</option>
    <option value="rw">Kinyarwanda</option>
    <option value="ko">Korean</option>
    <option value="ku">Kurdish (Kurmanji)</option>
    <option value="ky">Kyrgyz</option>
    <option value="lo">Lao</option>
    <option value="la">Latin</option>
    <option value="lv">Latvian</option>
    <option value="lt">Lithuanian</option>
    <option value="lb">Luxembourgish</option>
    <option value="mk">Macedonian</option>
    <option value="mg">Malagasy</option>
    <option value="ms">Malay</option>
    <option value="ml">Malayalam</option>
    <option value="mt">Maltese</option>
    <option value="mi">Maori</option>
    <option value="mr">Marathi</option>
    <option value="mn">Mongolian</option>
    <option value="my">Myanmar (Burmese)</option>
    <option value="ne">Nepali</option>
    <option value="no">Norwegian</option>
    <option value="or">Odia (Oriya)</option>
    <option value="ps">Pashto</option>
    <option value="fa">Persian</option>
    <option value="pl">Polish</option>
    <option value="pt">Portuguese</option>
    <option value="pa">Punjabi</option>
    <option value="ro">Romanian</option>
    <option value="ru">Russian</option>
    <option value="sm">Samoan</option>
    <option value="gd">Scots Gaelic</option>
    <option value="sr">Serbian</option>
    <option value="st">Sesotho</option>
    <option value="sn">Shona</option>
    <option value="sd">Sindhi</option>
    <option value="si">Sinhala</option>
    <option value="sk">Slovak</option>
    <option value="sl">Slovenian</option>
    <option value="so">Somali</option>
    <option value="es">Spanish</option>
    <option value="su">Sundanese</option>
    <option value="sw">Swahili</option>
    <option value="sv">Swedish</option>
    <option value="tg">Tajik</option>
    <option value="ta">Tamil</option>
    <option value="tt">Tatar</option>
    <option value="te">Telugu</option>
    <option value="th">Thai</option>
    <option value="tr">Turkish</option>
    <option value="tk">Turkmen</option>
    <option value="uk">Ukrainian</option>
    <option value="ur">Urdu</option>
    <option value="ug">Uyghur</option>
    <option value="uz">Uzbek</option>
    <option value="vi">Vietnamese</option>
    <option value="cy">Welsh</option>
    <option value="xh">Xhosa</option>
    <option value="yi">Yiddish</option>
    <option value="yo">Yoruba</option>
    <option value="zu">Zulu</option>
  </select>
</div>

<script type="text/javascript">
  // Google engine (hidden gadget) — applies the googtrans cookie on load.
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({ pageLanguage: 'en', autoDisplay: false }, 'google_translate_element');
  }
  (function () {
    function readLang() {
      var m = document.cookie.match(/(?:^|;\s*)googtrans=\/[^\/]*\/([^;]+)/);
      return m ? decodeURIComponent(m[1]) : '';
    }
    function baseHost() { return location.hostname.replace(/^www\./, ''); }
    function clearCookie() {
      var exp = 'expires=Thu, 01 Jan 1970 00:00:00 GMT';
      document.cookie = 'googtrans=;path=/;' + exp;
      document.cookie = 'googtrans=;path=/;domain=' + baseHost() + ';' + exp;
      document.cookie = 'googtrans=;path=/;domain=.' + baseHost() + ';' + exp;
    }
    function setLang(lang) {
      clearCookie();
      if (lang && lang !== 'en') {
        var val = '/en/' + lang;
        document.cookie = 'googtrans=' + val + ';path=/';
        document.cookie = 'googtrans=' + val + ';path=/;domain=.' + baseHost();
      }
      location.reload();
    }
    document.addEventListener('DOMContentLoaded', function () {
      var sel = document.getElementById('slm-lang');
      if (!sel) return;
      var cur = readLang();
      if (cur) { try { sel.value = cur; } catch (e) {} }
      sel.addEventListener('change', function () { setLang(this.value); });
    });
  })();
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<style>
  .slm-translate {
    position: fixed; left: 18px; bottom: 18px; z-index: 2147482000;
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff; border: 1px solid rgba(0,0,0,.15); border-radius: 999px;
    padding: 4px 10px 4px 12px; box-shadow: 0 8px 24px -8px rgba(0,0,0,.4);
    font-family: system-ui, -apple-system, sans-serif;
  }
  .slm-translate__globe { font-size: 15px; line-height: 1; }
  .slm-translate select {
    appearance: auto; -webkit-appearance: auto;
    border: 0; background: transparent; outline: none;
    font-size: 14px; color: #1C2628; padding: 6px 4px; max-width: 190px; cursor: pointer;
  }
  /* Suppress Google's own banner/gadget chrome so only our control shows */
  .goog-te-banner-frame.skiptranslate, iframe.goog-te-banner-frame { display: none !important; }
  .goog-logo-link, .goog-te-gadget { display: none !important; }
  body { top: 0 !important; position: static !important; }
  @media (max-width: 560px) { .slm-translate { left: 12px; bottom: 12px; } }
  @media print { .slm-translate { display: none; } }
</style>
