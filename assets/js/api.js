/* =======================================================
    TitanXHoldings - API.js
    Purpose: Unified frontend API logic for Auth & Backend
    ======================================================= */


function getApiPath(endpoint) {
  // Full URL already? return as is
  if (/^https?:\/\//i.test(endpoint)) return endpoint;

  // Force single leading slash
  if (!endpoint.startsWith('/')) endpoint = '/' + endpoint;

  // Detect if running from localhost (Laragon)
  const origin = window.location.origin;
  const isLocal = origin.includes('localhost');

  // For Laragon, API path starts directly under /
  // For production, API is still under /api/
  const basePath = isLocal ? '' : '';

  return origin + basePath + endpoint;
}


/**
 * Universal API fetch wrapper (Defaults to POST, handles GET via internal routing)
 */
async function fetchApi(endpoint, payload = {}, method = 'POST') {
  // 🟢 CRITICAL: Reroute GET requests to the GET handler.
  if (method.toUpperCase() === 'GET') {
      return fetchApiGet(endpoint, payload);
  }
  
  // --- POST Logic ---
  const loader = document.getElementById('loader');
  loader?.classList.remove('hidden');
  loader?.classList.remove('fade-out');

  try {
    const fullEndpoint = getApiPath(endpoint);
    const response = await fetch(fullEndpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify(payload),
    });

    // Read raw text for debugging invalid JSON
    const raw = await response.text();

    let result = {};
    try {
      result = JSON.parse(raw);
    } catch (e) {
      console.warn(
        `%cInvalid JSON from ${fullEndpoint}`,
        'color: orange; font-weight: bold;',
        '\nRaw response:\n',
        raw
      );
      return { status: 'error', message: 'Server returned invalid response', raw };
    }

    if (!response.ok) {
      return {
        status: 'error',
        message: result?.message || `Request failed (HTTP ${response.status})`,
      };
    }

    return result;

  } catch (error) {
    console.error('API Error (POST):', error);
    return { status: 'error', message: 'Network error — please try again.' };
  } finally {
    loader?.classList.add('fade-out');
    setTimeout(() => loader?.classList.add('hidden'), 300);
  }
}


/**
 * GET variant of fetchApi (payload is converted to URL query parameters)
 */
async function fetchApiGet(endpoint, params = {}) {
  const loader = document.getElementById('loader');
  loader?.classList.remove('hidden');
  loader?.classList.remove('fade-out');

  try {
    let fullEndpoint = getApiPath(endpoint);
    
    // 🟢 Build query string from params object
    const queryString = new URLSearchParams(params).toString();
    if (queryString) {
        fullEndpoint += (fullEndpoint.includes('?') ? '&' : '?') + queryString;
    }
    
    const response = await fetch(fullEndpoint, {
      method: 'GET',
      credentials: 'include',
    });

    const raw = await response.text();
    let result;
    try {
      result = JSON.parse(raw);
    } catch (e) {
      console.warn(
        `%cInvalid JSON from ${fullEndpoint}`,
        'color: orange; font-weight: bold;',
        '\nRaw response:\n',
        raw
      );
      return { status: 'error', message: 'Server returned invalid response', raw };
    }

    // Check response status outside of response.ok to extract error message
    if (!response.ok) {
        return { 
            status: 'error', 
            message: result?.message || `Request failed (HTTP ${response.status})`
        };
    }
    
    return result;

  } catch (error) {
    console.error('API Error (GET):', error);
    return { status: 'error', message: error.message || 'Network error' };
  } finally {
    loader?.classList.add('fade-out');
    setTimeout(() => loader?.classList.add('hidden'), 300);
  }
}


/**
 * Inline message (used within forms)
 */
function displayMessage(message, isError = true) {
  let messageBox = document.getElementById('message');
  if (!messageBox) {
    messageBox = document.createElement('div');
    messageBox.id = 'message';
    messageBox.className = 'auth-message';
    const activeForm = document.querySelector('form.auth-form') || document.querySelector('form');
    if (activeForm) activeForm.prepend(messageBox);
  }

  messageBox.textContent = message; // textContent (not innerHTML) — never render messages as HTML
  messageBox.className = `auth-message ${isError ? 'error' : 'success'}`;
  messageBox.style.display = 'block';
}

/* =======================================================
    Toast Notification System
    ======================================================= */
function showToast(message, type = 'info', duration = 4000) {
  const container = document.getElementById('toast-container');
  if (!container) {
    console.warn('Toast container not found');
    return;
  }

  // Create toast element
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;

  // Build inner content (icon + message)
  toast.innerHTML = `
    <span class="iconify" data-icon="${getToastIcon(type)}"></span>
    <div class="toast-message">${message}</div>
  `;

  // Append to container
  container.appendChild(toast);

  // Force reflow for animation (Chrome bug fix)
  toast.offsetHeight;

  // Remove after duration
  setTimeout(() => {
    toast.style.animation = 'fadeOut 0.4s forwards';
    setTimeout(() => toast.remove(), 400);
  }, duration);
}

/**
 * Returns icon name based on toast type
 */
function getToastIcon(type) {
  switch (type) {
    case 'success': return 'mdi:check-circle-outline';
    case 'error': return 'mdi:alert-circle-outline';
    case 'info': return 'mdi:information-outline';
    case 'warning': return 'mdi:alert-outline';
    default: return 'mdi:bell-outline';
  }
}


/**
 * Detect endpoint path (user/admin)
 */
function getAuthEndpoint(type) {
  const path = window.location.pathname;
  const isAdmin = path.includes('/admin');
  return isAdmin ? `/api/auth/admin_${type}.php` : `/api/auth/${type}.php`;
}

/* =======================================================
    LOGIN HANDLER
    ======================================================= */
const loginForm = document.getElementById('login-form');
if (loginForm) {
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const emailEl = document.getElementById('email') || loginForm.querySelector('input[name="email"]');
    const passwordEl = document.getElementById('password') || loginForm.querySelector('input[name="password"]');

    const email = emailEl?.value.trim() || '';
    const password = passwordEl?.value.trim() || '';

    if (!email || !password) {
      showToast('Please fill in all fields.', 'error');
      return displayMessage('Please fill in all fields.', true);
    }

    const res = await fetchApi(getAuthEndpoint('login'), { email, password });
    if (res.status === 'success') {
      showToast('Login successful! Redirecting...', 'success');
      const isAdmin = window.location.pathname.includes('/admin');
      setTimeout(() => {
        window.location.href = res.data?.redirect || (isAdmin ? '/admin' : '/pages/user/dashboard.php');
      }, 600);
    } else if (res.data?.requires_verification) {
      // Unverified account — reveal the OTP step instead of failing outright.
      pendingVerifyUserId = res.data.user_id;
      showToast(res.message || 'Please verify your email to continue.', 'info');
      revealVerifyStep(loginForm);
    } else {
      showToast(res.message || 'Login failed. Please try again.', 'error');
    }
  });
}

/* =======================================================
    REGISTER HANDLER
    ======================================================= */
const registerForm = document.getElementById('register-form');
if (registerForm) {
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const firstName = (document.getElementById('first_name') || registerForm.querySelector('input[name="first_name"]'))?.value.trim() || '';
    const lastName  = (document.getElementById('last_name') || registerForm.querySelector('input[name="last_name"]'))?.value.trim() || '';
    const email     = (document.getElementById('email') || registerForm.querySelector('input[name="email"]'))?.value.trim() || '';
    const password  = (document.getElementById('password') || registerForm.querySelector('input[name="password"]'))?.value.trim() || '';

    const isAdmin = window.location.pathname.includes('/admin');
    let data;
    if (isAdmin) {
      // Admin sign-up: username + invite code (no first/last name).
      const username   = (document.getElementById('username') || registerForm.querySelector('input[name="username"]'))?.value.trim() || '';
      const inviteCode = (document.getElementById('invite_code') || registerForm.querySelector('input[name="invite_code"]'))?.value.trim() || '';
      if (!username || !inviteCode || !email || !password) {
        showToast('Please complete all fields.', 'error');
        return displayMessage('Please complete all fields.', true);
      }
      data = { username, invite_code: inviteCode, email, password };
    } else {
      if (!firstName || !lastName || !email || !password) {
        showToast('Please complete all fields.', 'error');
        return displayMessage('Please complete all fields.', true);
      }
      data = { first_name: firstName, last_name: lastName, email, password };
    }

    const res = await fetchApi(getAuthEndpoint('register'), data);
    if (res.status === 'success' && res.data?.requires_verification) {
      // Email verification required — reveal the OTP step.
      pendingVerifyUserId = res.data.user_id;
      showToast(res.message || 'Check your email for a 6-digit code.', 'success');
      revealVerifyStep(registerForm);
    } else if (res.status === 'success') {
      // Fallback (e.g. admin register) — straight redirect.
      showToast('Registration successful! Redirecting...', 'success');
      setTimeout(() => {
        window.location.href = res.data?.redirect || (isAdmin ? '/admin' : '/pages/user/dashboard.php');
      }, 600);
    } else {
      showToast(res.message || 'Registration failed. Try again.', 'error');
    }
  });
}

/* =======================================================
    EMAIL VERIFICATION (shared by register + login pages)
    ======================================================= */
let pendingVerifyUserId = null;

// Hide the primary auth form and show the OTP verify step.
function revealVerifyStep(primaryForm) {
  const verifyForm = document.getElementById('verify-form');
  if (!verifyForm) return;
  if (primaryForm) primaryForm.classList.add('hidden');
  verifyForm.classList.remove('hidden');
  document.getElementById('verify-otp')?.focus();
}

const verifyForm = document.getElementById('verify-form');
if (verifyForm) {
  verifyForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const otp = document.getElementById('verify-otp')?.value.trim() || '';
    if (!pendingVerifyUserId) return showToast('Please sign up or sign in first.', 'error');
    if (!otp) return showToast('Enter the 6-digit code.', 'error');

    const res = await fetchApi('/api/auth/verify_email.php', { user_id: pendingVerifyUserId, otp });
    if (res.status === 'success') {
      showToast(res.message || 'Email verified! Redirecting...', 'success');
      setTimeout(() => {
        window.location.href = res.data?.redirect || '/dashboard';
      }, 600);
    } else {
      showToast(res.message || 'Verification failed. Try again.', 'error');
    }
  });

  const resendLink = document.getElementById('verify-resend');
  if (resendLink) {
    resendLink.addEventListener('click', async (e) => {
      e.preventDefault();
      if (!pendingVerifyUserId) return showToast('Please sign up or sign in first.', 'error');
      const res = await fetchApi('/api/auth/verify_email.php', { user_id: pendingVerifyUserId, resend: true });
      showToast(res.message || (res.status === 'success' ? 'A new code has been sent.' : 'Could not resend code.'),
        res.status === 'success' ? 'success' : 'error');
    });
  }
}

/* =======================================================
    FORGOT PASSWORD FLOW (3-Step Dynamic)
    ======================================================= */
const step1Form = document.getElementById('forgot-step1');
const step2Form = document.getElementById('forgot-step2');
const step3Form = document.getElementById('forgot-step3');

let tempUserId = null;

// STEP 1 — Send OTP
if (step1Form) {
  step1Form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('forgot-email').value.trim();
    if (!email) return showToast('Please enter your registered email.', 'error');

    const res = await fetchApi(getAuthEndpoint('forgotpassword'), { email });
    if (res.status === 'success') {
      showToast('OTP sent successfully!', 'success');
      tempUserId = res.data?.user_id;
      localStorage.setItem('reset_user_id', tempUserId);
      step1Form.classList.add('hidden');
      step2Form.classList.remove('hidden');
    } else {
      showToast(res.message || 'Failed to send OTP.', 'error');
    }
  });
}

// STEP 2 — Verify OTP
if (step2Form) {
  step2Form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const otp = document.getElementById('otp').value.trim();
    const user_id = tempUserId || localStorage.getItem('reset_user_id');
    if (!otp) return showToast('Please enter the OTP.', 'error');

    const res = await fetchApi(getAuthEndpoint('resetpassword'), { user_id, otp, verify_only: true });
    if (res.status === 'success') {
      showToast('OTP verified successfully!', 'success');
      step2Form.classList.add('hidden');
      step3Form.classList.remove('hidden');
    } else {
      showToast(res.message || 'Invalid or expired OTP.', 'error');
    }
  });
}

// STEP 3 — Reset Password
if (step3Form) {
  step3Form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const new_password = document.getElementById('new_password').value.trim();
    const user_id = tempUserId || localStorage.getItem('reset_user_id');
    const otp = document.getElementById('otp').value.trim();

    if (!new_password) return showToast('Please enter your new password.', 'error');
    if (new_password.length < 8) return showToast('Password must be at least 8 characters long.', 'error');

    const res = await fetchApi(getAuthEndpoint('resetpassword'), { user_id, otp, new_password });
    if (res.status === 'success') {
      showToast('Password updated successfully! Redirecting...', 'success');
      localStorage.removeItem('reset_user_id');
      setTimeout(() => window.location.href = '/login.php', 1500);
    } else {
      showToast(res.message || 'Failed to update password.', 'error');
    }
  });
}

/* =======================================================
    LOGOUT HANDLER (Graceful Transition)
    ======================================================= */
const logoutBtn = document.getElementById('logout-btn');
if (logoutBtn) {
  logoutBtn.addEventListener('click', (e) => {
    e.preventDefault();

    const loader = document.getElementById('loader');
    if (loader) {
      loader.classList.remove('hidden');
      loader.classList.remove('fade-out');
      loader.style.display = 'flex';
      loader.style.opacity = '1';
    }

    showToast('Logging out...', 'success');

    // Store a flag in localStorage to persist loader state after redirect
    localStorage.setItem('showLoaderAfterRedirect', 'true');

    // Ensure loader paints before navigating
    requestAnimationFrame(() => {
      setTimeout(() => {
        window.location.href = '/pages/user/logout.php';
      }, 800); // 0.8s visible before redirect
    });
  });
}


/* =======================================================
    Password Visibility Toggle
    ======================================================= */
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure Iconify has rendered the icons
    setTimeout(function() {
        const passwordInput = document.getElementById('password');
        const showPass = document.querySelector('.show-pass');

        if (showPass && passwordInput) {
            const viewIcon = showPass.querySelector('.view');
            const hideIcon = showPass.querySelector('.hide');

            // Ensure initial state
            viewIcon.style.display = 'inline';
            hideIcon.style.display = 'none';

            showPass.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent any form interference
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';

                // Toggle icons
                viewIcon.style.display = isHidden ? 'none' : 'inline';
                hideIcon.style.display = isHidden ? 'inline' : 'none';
            });
        }
    }, 100); // 100ms delay for Iconify rendering
});