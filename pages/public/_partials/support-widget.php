<?php
// ============================================================
// TELEGRAM SUPPORT WIDGET — floating icon button (bottom-right)
// Opens a Telegram chat with the support number (+1 317 366 1701).
// Self-contained: include once per page (position: fixed).
//
// Set $support_widget_paired = true; before including on pages that
// ALSO show the Smartsupp live-chat launcher (homepage) so the two
// sit side by side instead of overlapping.
// ============================================================
$paired = !empty($support_widget_paired);
?>
<a href="https://t.me/+13173661701" target="_blank" rel="noopener"
   class="tg-support<?= $paired ? ' tg-support--paired' : '' ?>"
   aria-label="Chat with Solace Mining support on Telegram">
  <span class="tg-support__icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>
  </span>
</a>
<style>
  .tg-support {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 2147483000;            /* sit on the same layer as the chat launcher */
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #229ED9;
    color: #fff;
    text-decoration: none;
    box-shadow: 0 10px 28px -6px rgba(34, 158, 217, 0.55);
  }
  .tg-support__icon { display: inline-flex; width: 28px; height: 28px; flex: 0 0 auto; }
  .tg-support__icon svg { width: 100%; height: 100%; display: block; }

  /* Paired with the Smartsupp launcher → sit to its left (homepage) */
  .tg-support--paired { right: 96px; }

  @media (max-width: 560px) {
    .tg-support { right: 16px; bottom: 16px; }
    .tg-support--paired { right: 84px; bottom: 18px; }
  }
  @media print { .tg-support { display: none; } }
</style>
