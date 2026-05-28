<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Meta -->
  <meta charset="UTF-8">
  <meta name="description" content="HealthRunCare is a unified healthcare platform connecting patients, doctors, pharmacies, and employers through AI-driven, secure solutions.">
  <meta name="author" content="HealthRunCare">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://healthruncare.com/">


  <!-- Title -->
  <title>HealthRunCare – Connected, Transparent, Human-Centered Healthcare</title>

  <!-- Preload CSS -->
  <link rel="preload" href="../../assets/css/main.css" as="style">

  <!-- Preload Fonts -->
<link rel="preload" href="../fonts/HostGrotesk-Regular.woff2" as="font" type="font/woff2" crossorigin>

  <!-- Unicons CDN -->
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../../assets/css/main.css">
  <link rel="stylesheet" href="../../assets/css/responsive.css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../../assets/favicon/favicon-32x32.png" sizes="32x32">
  <link rel="shortcut icon" href="../../assets/favicon/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicon/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-title" content="Lymora">
  <link rel="manifest" href="../../assets/favicon/site.webmanifest">
      <!-- Smartsupp Live Chat script -->
  <script type="text/javascript">
  var _smartsupp = _smartsupp || {};
  _smartsupp.key = '74f4168b124ca112a5c2ecfde6804a6e6e6306e6';
  window.smartsupp||(function(d) {
    var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
    s=d.getElementsByTagName('script')[0];c=d.createElement('script');
    c.type='text/javascript';c.charset='utf-8';c.async=true;
    c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
  })(document);
  </script>
  <noscript> Powered by <a href=“https://www.smartsupp.com” target=“_blank”>Smartsupp</a></noscript>
</head>
<body>

<header class="header" data-header>
  <div class="container">
    <div class="header-left">
      <a href="/" class="logo">
        <img src="../../assets/images/logo2.png" alt="Lymora logo" loading="lazy">
        <span class="logo-text">HealthRunCare</span>
      </a>
    </div>

    <!-- Center: Navigation -->
<nav class="navbar" id="navbar" data-navbar>
  <ul class="navbar-list">
    <li class="navbar-item"><a href="/whyhrc" class="navbar-link">Why HRC</a></li>
    <li class="navbar-item"><a href="/platform" class="navbar-link">Platform</a></li>
    <li class="navbar-item"><a href="/solutions" class="navbar-link">Solutions</a></li>
    <li class="navbar-item"><a href="/about" class="navbar-link">About</a></li>

      <li class="navbar-item mobile-only"><a href="/login" class="navbar-link">Login</a></li>
  <li class="navbar-item mobile-only"><a href="/contact" class="navbar-link">Contact</a></li>
  </ul>
</nav>


    <!-- Right: Actions (Login, Sign Up, etc.) -->
    <div class="header-right">
      <a href="/login" class="btn btn--glass">Login</a>
      <a href="/login" class="btn btn--glass">Contact</a>
    </div>

    <!-- Mobile toggle -->
    <button class="nav-toggle-btn" aria-label="Toggle menu" data-nav-toggler>
      <span class="line line-1"></span>
      <span class="line line-2"></span>
      <span class="line line-3"></span>
    </button>
  </div>
</header>

<section class="hero contact-hero" id="hero">
  <div class="hero-content container" data-appear>
    <div class="hero-main">
      <h1 class="hero-title">Let’s Build Healthcare Together</h1>
      <p class="hero-subtitle">
        Whether you’re a patient, healthcare provider, charity, or investor — HealthRunCare is ready to collaborate.  
        Reach out to learn how our secure, AI-powered ecosystem can help you deliver better care, fund impactful initiatives,  
        and redefine what connected healthcare means.
      </p>
      <a href="#" class="btn btn--glass">Contact Us</a>
    </div>
  </div>
</section>

<section class="about-hrc" id="about-hrc">
  <div class="container">
    <div class="about-header" data-appear>
      <h2 class="section-title">Get in Touch with HealthRunCare</h2>
      <p class="section-description">
        We believe great healthcare starts with real human connection.  
        Our team in <strong>Wales</strong> works with hospitals, innovators, and public health partners across the UK and beyond  
        to create transparent, ethical, and AI-driven healthcare systems.  
        Whether you’re looking to <strong>integrate with our platform</strong>, <strong>launch a health initiative</strong>,  
        or <strong>learn more about our technology</strong>, we’re always open to meaningful conversations.
      </p>
      <p class="section-description">
        For media inquiries, partnerships, or general support, please contact us using the form below or email  
        <a href="mailto:hello@healthruncare.com">hello@healthruncare.com</a>.
      </p>
    </div>
  </div>
</section>


<!-- Contact Form Section -->
<section class="contact-form-section">
  <div class="container">
    <div class="contact-form-wrapper">
      <form class="contact-form" action="/contact/submit" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
          <div class="form-group">
            <label for="name">Full Name</label>
            <div class="input-wrapper">
              <i class="uil uil-user input-icon"></i>
              <input type="text" id="name" name="name" required placeholder="Jane Doe">
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrapper">
              <i class="uil uil-envelope input-icon"></i>
              <input type="email" id="email" name="email" required placeholder="you@example.com">
            </div>
          </div>

          <div class="form-group">
            <label for="type">Type of Message</label>
            <div class="input-wrapper">
              <i class="uil uil-question-circle input-icon"></i>
              <select id="type" name="type" required>
                <option value="" disabled selected>Select message type</option>
                <option value="general">General Inquiry</option>
                <option value="services">Services</option>
                <option value="support">Support</option>
                <option value="feedback">Feedback</option>
                <option value="partnership">Partnership</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="service">Related Role/Interest (Optional)</label>
            <div class="input-wrapper">
              <i class="uil uil-user-md input-icon"></i>
              <select id="service" name="service">
                <option value="" selected>-- Select an interest --</option>
                <option value="patient">Patient</option>
                <option value="provider">Healthcare Provider</option>
                <option value="donor">Donor/Charity</option>
                <option value="employer">Employer</option>
                <option value="investor">Investor</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>

          <div class="form-group full-width">
            <label for="subject">Subject</label>
            <div class="input-wrapper">
              <i class="uil uil-file-alt input-icon"></i>
              <input type="text" id="subject" name="subject" required placeholder="What’s this about?">
            </div>
          </div>

          <div class="form-group full-width">
            <label for="message">Message</label>
            <div class="input-wrapper textarea-wrapper">
              <i class="uil uil-comment input-icon"></i>
              <textarea id="message" name="message" rows="6" required placeholder="Tell us more..."></textarea>
            </div>
          </div>

          <div class="form-group full-width">
            <label for="attachment">Attach a File (Optional)</label>
            <div class="input-wrapper">
              <i class="uil uil-paperclip input-icon"></i>
              <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.png,.doc,.docx">
            </div>
          </div>

          <div class="form-group full-width">
            <button type="submit" class="btn btn-primary auth-btn">Send Message</button>
          </div>
        </div>
      </form>

      <!-- Optional: Contact Info Sidebar -->
      <div class="contact-info">
        <h3>Get in Touch</h3>
        <ul>
          <li><i class="uil uil-envelope"></i> support@healthruncare.com</li>
          <li><i class="uil uil-phone"></i> +44 123 456 7890</li> <!-- Example number -->
          <li><i class="uil uil-map-marker"></i>Chester Road / Powell Road, Acton, Wrexham</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Loader -->
<div id="loader" class="loader hidden">
  <div class="loader-spinner"></div>
</div>

<!-- Brief Success Modal -->
<div id="successModal" class="modal-message hidden">
  <div class="modal-content">
    <p>Message sent! Please check your email.</p>
  </div>
</div>

<!-- ==============================
     Footer Stamp / Wordmark
     ============================== -->
<div class="footer-stamp" aria-hidden="true">
  <span>HealthRunCare Ltd</span>
</div>
<!-- ==============================
     Static Footer
     ============================== -->
<footer class="static-footer">
  <div class="container">
    <!-- Grid Layout -->
    <div class="footer-grid">

      <!-- Column 1: Solutions -->
      <div class="footer-column">
        <h4>Solutions</h4>
        <ul>
          <li><a href="/solutions">Charity & Donations</a></li>
          <li><a href="/solutions">Mount Infrastructure</a></li>
        </ul>
      </div>

      <!-- Column 2: Using HRC -->
      <div class="footer-column">
        <h4>Using HealthRunCare</h4>
        <ul>
          <li><a href="/login">Pricing</a></li>
          <li><a href="/login">Integrations</a></li>
        </ul>
      </div>

      <!-- Column 3: Resources -->
      <div class="footer-column">
        <h4>Contact</h4>
        <ul>
          <li><a href="mailto:hello@healthruncare.com">hello@healthruncare.com</a></li>
          <li><a href="mailto:sales@healthruncare.com">support@healthruncare.com</a></li>
        </ul>
      </div>

      <!-- Column 4: About -->
      <div class="footer-column">
        <h4>About HealthRunCare</h4>
        <ul>
          <li><a href="/about">Who We Are</a></li>
          <li><a href="/about">Our History</a></li>
        </ul>
      </div>

      <!-- Column 5: Legal + Contact -->
      <div class="footer-column">
        <h4>Legal</h4>
        <ul>
          <li><a href="/about">Privacy Policy</a></li>
          <li><a href="/about">Terms of Use</a></li>
        </ul>
      </div>
    </div>
    
    <!-- Bottom Row -->
    <div class="footer-bottom-row">
      <div class="footer-logo">
        <img src="../../assets/images/logo2.png" alt="HealthRunCare logo" loading="lazy">
        <span class="logo-text">HealthRunCare</span>
      </div>


      <div class="footer-copyright">
        <p>© 2025 HealthRunCare Inc. All rights reserved.</p>
      </div>
    </div>
  </div>
</footer>



  <!-- Scripts -->
  <script src="../../assets/js/main.js" defer></script>
  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "HealthRunCare",
  "url": "https://healthruncare.com",
  "logo": "https://healthruncare.com/assets/images/logo.png",
  "sameAs": [
    "https://www.linkedin.com/company/healthruncare",
    "https://twitter.com/healthruncare",
    "https://instagram.com/healthruncare"
  ]
}
</script>



</body>
</html>
