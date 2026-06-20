// =============================
// main.js — Solace Mining
// =============================

document.addEventListener('DOMContentLoaded', () => {

  /**
   * ======================
   * 1. Navbar Toggle (Mobile)
   * ======================
   */
  const navToggler = document.querySelector('[data-nav-toggler]');
  const navbar = document.getElementById('navbar');
  const body = document.body;

  if (navToggler && navbar) {
    navToggler.addEventListener('click', () => {
      const isActive = navbar.classList.contains('navbar-mobile-active');
      if (!isActive) {
        navbar.classList.add('navbar-mobile-active');
        body.style.overflow = 'hidden';
        setTimeout(() => navbar.classList.add('appear'), 10);
        navToggler.classList.add('active');
        navToggler.setAttribute('aria-expanded', 'true');
      } else {
        navbar.classList.remove('appear');
        navToggler.classList.remove('active');
        navToggler.setAttribute('aria-expanded', 'false');
        setTimeout(() => {
          navbar.classList.remove('navbar-mobile-active');
          body.style.overflow = '';
        }, 400);
      }
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (
        navbar.classList.contains('navbar-mobile-active') &&
        !navbar.contains(e.target) &&
        !navToggler.contains(e.target)
      ) {
        navbar.classList.remove('appear');
        navToggler.classList.remove('active');
        navToggler.setAttribute('aria-expanded', 'false');
        setTimeout(() => {
          navbar.classList.remove('navbar-mobile-active');
          body.style.overflow = '';
        }, 400);
      }
    });

    // Close on resize to desktop
    window.addEventListener('resize', () => {
      if (window.innerWidth > 992) {
        navbar.classList.remove('appear', 'navbar-mobile-active');
        navToggler.classList.remove('active');
        navToggler.setAttribute('aria-expanded', 'false');
        body.style.overflow = '';
      }
    });
  }


  /**
   * ======================
   * 2. Header Scroll Effect
   * ======================
   */
  const header = document.querySelector('.header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.classList.toggle('scrolled', window.scrollY > 10);
    });
  }


  /**
   * ======================
   * 3. Hero Carousel (Homepage Only)
   * ======================
   */
  const hero = document.querySelector('.home-hero');
  if (hero) {
    const titleEl = hero.querySelector('#hero-title');
    const subtitleEl = hero.querySelector('#hero-subtitle');
    const dotsContainer = hero.querySelector('#hero-dots');

    const slides = [
      {
        image: "url('../../assets/images/bgimage2.webp')",
        title: "Healthcare, Connected and Simplified.",
        subtitle: "Solace Mining brings patients, doctors, and pharmacies together in one trusted digital platform. From diagnostics to payments, we make care clear, secure, and accessible."
      },
      {
        image: "url('../../assets/images/bgimage.webp')",
        title: "Empowering Doctors, Supporting Patients.",
        subtitle: "We provide tools to enhance consultations, streamline prescriptions, and keep patients engaged in their wellness journey."
      }
    ];

    let currentSlide = 0;
    let carouselInterval;

    function updateSlide(index) {
      const { image, title, subtitle } = slides[index];
      hero.style.backgroundImage = image;
      titleEl.textContent = title;
      subtitleEl.textContent = subtitle;
      updateDots(index);
    }

    function updateDots(index) {
      const buttons = dotsContainer.querySelectorAll('button');
      buttons.forEach((btn, i) => btn.classList.toggle('active', i === index));
    }

    function createDots() {
      dotsContainer.innerHTML = '';
      slides.forEach((_, i) => {
        const btn = document.createElement('button');
        btn.setAttribute('aria-label', `Go to slide ${i + 1}`);
        if (i === currentSlide) btn.classList.add('active');
        btn.addEventListener('click', () => {
          clearInterval(carouselInterval);
          currentSlide = i;
          updateSlide(currentSlide);
          startCarousel();
        });
        dotsContainer.appendChild(btn);
      });
    }

    function startCarousel() {
      clearInterval(carouselInterval);
      carouselInterval = setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlide(currentSlide);
      }, 15000);
    }

    createDots();
    updateSlide(currentSlide);
    startCarousel();
  }


  /**
   * ======================
   * 4. Interactive Cards
   * ======================
   */
  const interactiveCards = document.querySelectorAll('.platform-card, .testimonial-card');
  interactiveCards.forEach(card => {
    card.addEventListener('mousemove', e => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      card.style.setProperty('--x', `${x}px`);
      card.style.setProperty('--y', `${y}px`);
    });

    card.addEventListener('mouseleave', () => {
      card.style.setProperty('--x', '50%');
      card.style.setProperty('--y', '50%');
    });
  });


  /**
   * ======================
   * 5. FAQ Accordion
   * ======================
   */
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const button = item.querySelector('.faq-question');
    const answer = item.querySelector('.faq-answer');
    const icon = button.querySelector('i');

    button.addEventListener('click', () => {
      const isOpen = item.classList.contains('active');

      if (isOpen) {
        item.classList.remove('active');
        answer.style.maxHeight = null;
        button.setAttribute('aria-expanded', 'false');
        icon.classList.replace('uil-minus', 'uil-plus');
      } else {
        item.classList.add('active');
        answer.style.maxHeight = '0px';
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            answer.style.maxHeight = answer.scrollHeight + 'px';
          });
        });
        button.setAttribute('aria-expanded', 'true');
        icon.classList.replace('uil-plus', 'uil-minus');
      }
    });
  });


  /**
   * ======================
   * 6. Scroll Animations + CountUp Stats
   * ======================
   */
  const animatedElements = document.querySelectorAll('[data-appear], [data-appear-left], [data-appear-right], [data-appear-stagger]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          if (entry.target.hasAttribute('data-appear-stagger')) {
            const parent = entry.target.parentElement;
            const siblings = parent.querySelectorAll('[data-appear-stagger]');
            siblings.forEach((el, i) => {
              setTimeout(() => el.classList.add('appear'), i * 300);
              observer.unobserve(el);
            });
          } else {
            entry.target.classList.add('appear');
            observer.unobserve(entry.target);
          }
        }
      });
    }, { threshold: 0.7 });

    animatedElements.forEach(el => observer.observe(el));
  } else {
    animatedElements.forEach(el => el.classList.add('appear'));
  }

  // Target any element with a data-count (the "Our Numbers" spans use data-count
  // without the .stat-number class, so the old selector matched nothing).
  const statNumbers = document.querySelectorAll('[data-count]');
  function animateCountUp(el, target, duration = 2000) {
    let startTime = null;
    const step = (timestamp) => {
      if (!startTime) startTime = timestamp;
      const progress = Math.min((timestamp - startTime) / duration, 1);
      const value = Math.floor(progress * target);
      el.textContent = value.toLocaleString();
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const target = parseInt(el.getAttribute('data-count'), 10);
          if (!el.classList.contains('counted')) {
            el.classList.add('counted');
            animateCountUp(el, target);
          }
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.6 });
    statNumbers.forEach(el => observer.observe(el));
  }

}); // end DOMContentLoaded block 1



/**
 * ======================
 * 7. Workflow Tabs
 * ======================
 */
document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.workflow-tab');
  const contentCard = document.querySelector('.workflow-content-card');
  const visualImg = document.querySelector('.workflow-visual img');
  const contentTitle = document.querySelector('.workflow-content-title');
  const contentSubtitle = document.querySelector('.workflow-content-subtitle');
  const contentDesc = document.querySelector('.workflow-content-desc');
  const arrowBtn = document.querySelector('.workflow-arrow-btn');

  if (!tabs.length || !contentCard) return;

  const tabContent = {
    automate: {
      title: "Automate",
      subtitle: "Streamline repetitive healthcare tasks with AI precision",
      desc: "Solace Mining automates appointment reminders, prescription renewals, claims processing, and medical record updates — freeing providers to focus on patients, not paperwork. Each workflow adapts to your team’s needs and improves over time.",
      image: "../../assets/images/workflow-automate.png"
    },
    enrich: {
      title: "Enrich",
      subtitle: "Turn medical data into actionable insights",
      desc: "Our AI transforms fragmented records into rich, predictive insights. From patient risk scores to community health trends, every dataset becomes an engine for better decisions and equitable outcomes.",
      image: "../../assets/images/workflow-enrich.png"
    },
    triage: {
      title: "Triage",
      subtitle: "AI-powered triage that prioritizes what matters most",
      desc: "Solace Mining's smart engine analyzes urgency, symptoms, and available care capacity in real time — routing each case to the right specialist, at the right time, with zero delays.",
      image: "../../assets/images/workflow-triage.png"
    },
    report: {
      title: "Report",
      subtitle: "Instant visibility into performance and compliance",
      desc: "Generate real-time analytics for patient outcomes, funding transparency, and operational efficiency. Export to regulators or partners with full audit trails and compliance-grade accuracy.",
      image: "../../assets/images/workflow-report.png"
    },
    collaborate: {
      title: "Collaborate",
      subtitle: "Unite teams, partners, and patients in one secure network",
      desc: "Doctors, pharmacists, donors, and employers collaborate seamlessly on Solace Mining — sharing verified data, updates, and impact reports across a unified, encrypted platform.",
      image: "../../assets/images/workflow-collaborate.png"
    }
  };

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      contentCard.style.opacity = '0';
      contentCard.style.transform = 'translateY(20px)';
      setTimeout(() => {
        const tabKey = tab.dataset.tab;
        const data = tabContent[tabKey];
        contentTitle.textContent = data.title;
        contentSubtitle.textContent = data.subtitle;
        contentDesc.textContent = data.desc;
        visualImg.src = data.image;
        contentCard.style.opacity = '1';
        contentCard.style.transform = 'translateY(0)';
      }, 300);
    });
  });

  // ✅ Safe check for arrowBtn
  let currentTab = 0;
  if (arrowBtn) {
    arrowBtn.addEventListener('click', () => {
      currentTab = (currentTab + 1) % tabs.length;
      tabs[currentTab].click();
    });
  }
});



/**
 * ======================
 * 8. Modal System (Wing Details)
 * ======================
 */
document.addEventListener('DOMContentLoaded', () => {
  const modalOverlay = document.getElementById('wingDetailModal');
  const modalTitle = document.getElementById('modalTitle');
  const modalDescription = document.getElementById('modalDescription');
  const closeModalBtn = document.getElementById('closeModal');

  if (!modalOverlay || !modalTitle || !modalDescription) return;

  const wingData = {
    '/solutions/patients': {
      title: "AI & Patient Tools",
      description: `
        Solace Mining puts advanced healthcare directly in your hands. 
        Our AI-driven platform helps patients identify potential health concerns early, 
        track ongoing symptoms, and access remote consultations — all in a secure, easy-to-use space.
      `
    },
    '/charity': {
      title: "Charity & Donations",
      description: `
        Every act of care deserves to make an impact. Through Solace Mining’s verified donation system, 
        your giving directly supports patients, clinics, and emergency programs — 
        without intermediaries or hidden fees.
      `
    },
    '/investment': {
      title: "Mining Pools",
      description: `
        Solace Mining transforms healthcare investment into measurable impact.  
        Our tokenized investment pools allow individuals and institutions to fund verified medical innovations 
        and infrastructure — while earning transparent ROI.
      `
    },
    '/trust-fund': {
      title: "Trust Funds",
      description: `
        Solace Mining’s Trust Funds help families, patients, and employers plan ahead for health.  
        Secure, automated, and compliant — healthcare peace of mind.
      `
    },
    '/infrastructure': {
      title: "Mining Rigs",
      description: `
        Healthcare access starts with infrastructure. Co-fund clinics, labs, 
        and telehealth centers in the communities that need them most.
      `
    }
  };

  function openModal(data) {
    modalTitle.textContent = data.title;
    modalDescription.innerHTML = data.description.trim();
    modalOverlay.classList.remove('hidden');
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modalOverlay.classList.remove('active');
    setTimeout(() => modalOverlay.classList.add('hidden'), 300);
    document.body.style.overflow = '';
  }

  const wingModalButtons = document.querySelectorAll('.wing-card-btn');
  wingModalButtons.forEach(button => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      const href = button.getAttribute('href');
      const data = wingData[href];
      if (data) openModal(data);
      else console.warn(`No data found for ${href}`);
    });
  });

  // ✅ Safe checks
  if (closeModalBtn) {
    closeModalBtn.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) closeModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modalOverlay.classList.contains('active')) closeModal();
    });
  }
});
/**
 * ======================
 * 9. Smartsupp Live Chat Integration
 * ======================
 */
(function() {
  try {
    // Prevent duplicate loading
    if (window.smartsupp) return;

    window._smartsupp = window._smartsupp || {};
    window._smartsupp.key = '3c2dbbfc4e90eff8ecbbe0a2f4936d2be60ccec7';

    // Create Smartsupp script dynamically
    const s = document.createElement('script');
    s.type = 'text/javascript';
    s.charset = 'utf-8';
    s.async = true;
    s.src = 'https://www.smartsuppchat.com/loader.js?';
    
    // Append to head safely
    const firstScript = document.getElementsByTagName('script')[0];
    firstScript.parentNode.insertBefore(s, firstScript);

    console.log('✅ Smartsupp chat loaded');
  } catch (err) {
    console.error('❌ Smartsupp failed to load:', err);
  }
})();

/**
 * ======================
 * 10. Contact form (AJAX submit → /contact/submit)
 * ======================
 */
(function () {
  const form = document.querySelector('form[action="/contact/submit"]');
  if (!form) return;

  const loader  = document.getElementById('loader');
  const success = document.getElementById('successModal');
  const submitBtn = form.querySelector('button[type="submit"]');

  function showLoader(on) { if (loader) loader.classList.toggle('hidden', !on); }
  function showSuccess(msg) {
    if (!success) { alert(msg); return; }
    const p = success.querySelector('p');
    if (p && msg) p.textContent = msg;
    success.classList.remove('hidden');
    setTimeout(() => success.classList.add('hidden'), 6000);
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (submitBtn) { submitBtn.disabled = true; }
    showLoader(true);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      const data = await res.json().catch(() => ({ status: 'error', message: 'Unexpected server response.' }));
      showLoader(false);
      if (data.status === 'success') {
        form.reset();
        showSuccess(data.message || 'Message sent! Please check your email.');
      } else {
        alert(data.message || 'Something went wrong. Please try again.');
      }
    } catch (err) {
      showLoader(false);
      alert('Network error. Please check your connection and try again.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; }
    }
  });
})();
