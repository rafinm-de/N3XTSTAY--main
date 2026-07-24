// Main JavaScript functionality for Hotel Booking System

// Global variables
let pendingBookingData = null;

// Initialize immediately if DOM is loaded
function init() {
    initializeDatePickers();
    initializeAuthForms();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Mobile menu toggle
function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    navMenu.classList.toggle('active');
}

// Authentication Modal Functions
function showAuthModal(type = 'login') {
    console.log('[DEBUG] showAuthModal triggered. Type:', type);
    const modal = document.getElementById('authModal');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const modalTitle = document.getElementById('authModalTitle');

    if (!modal) console.error('[DEBUG] #authModal not found in DOM!');
    if (!loginForm) console.error('[DEBUG] #loginForm not found in DOM!');

    if (type === 'login') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        modalTitle.textContent = 'Login to Continue';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        modalTitle.textContent = 'Create Account';
    }

    modal.classList.add('active');
    console.log('[DEBUG] Modal active class added');
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    modal.classList.remove('active');
    clearAuthErrors();
}

function switchAuthForm(type) {
    showAuthModal(type);
}

function clearAuthErrors() {
    const loginError = document.getElementById('loginError');
    const registerError = document.getElementById('registerError');
    if (loginError) loginError.style.display = 'none';
    if (registerError) registerError.style.display = 'none';
}

// Close modal on outside click
document.addEventListener('click', function (e) {
    const modal = document.getElementById('authModal');
    if (modal && e.target === modal) {
        closeAuthModal();
    }
});

// Initialize Auth Forms
function initializeAuthForms() {
    console.log('[DEBUG] initializeAuthForms called');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        console.log('[DEBUG] Attached submit listener to #loginForm');
        loginForm.addEventListener('submit', handleLogin);
    } else {
        console.warn('[DEBUG] #loginForm not available to attach listener');
    }

    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
}

// Handle Login
async function handleLogin(e) {
    e.preventDefault();
    clearAuthErrors();

    const formData = new FormData(e.target);

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.textContent;
    submitBtn.textContent = 'Logging in...';
    submitBtn.disabled = true;

    try {
        const baseUrl = window.SITE_URL ? window.SITE_URL.replace(/\/$/, '') : '';
        const response = await fetch(`${baseUrl}/auth/login.php`, {
            method: 'POST',
            body: formData
        });

        const responseText = await response.text();
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Raw Response:', responseText);
            throw new Error('Server returned invalid response: ' + responseText.substring(0, 100));
        }

        console.log('Login response:', data);

        if (data.success) {
            // Handle redirect based on pending booking and role
            if (pendingBookingData) {
                // If there's an active checkout, always go to booking page, regardless of role
                console.log('Redirecting to pending booking:', pendingBookingData.redirectUrl);
                window.location.href = pendingBookingData.redirectUrl;
            } else if (data.user && data.user.role === 'admin') {
                // Admin users go to dashboard
                console.log('Redirecting admin to:', data.redirect_url);
                window.location.href = data.redirect_url;
            } else {
                // Regular users go to home or data.redirect_url
                console.log('Redirecting user to:', data.redirect_url);
                window.location.href = data.redirect_url;
            }
        } else {
            console.error('Login failed:', data.message);
            document.getElementById('loginError').textContent = data.message;
            document.getElementById('loginError').style.display = 'block';
        }
    } catch (error) {
        console.error('Login error:', error);
        document.getElementById('loginError').textContent = error.message.includes('Server returned') ? error.message : 'An error occurred. Please try again.';
        document.getElementById('loginError').style.display = 'block';
    } finally {
        if (submitBtn) {
            submitBtn.textContent = originalBtnText;
            submitBtn.disabled = false;
        }
    }
}

// Handle Register
async function handleRegister(e) {
    e.preventDefault();
    clearAuthErrors();

    const formData = new FormData(e.target);

    // Validate password match
    const password = formData.get('register_password');
    const confirmPassword = formData.get('register_confirm_password');

    if (password !== confirmPassword) {
        document.getElementById('registerError').textContent = 'Passwords do not match';
        document.getElementById('registerError').style.display = 'block';
        return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.textContent;
    submitBtn.textContent = 'Creating Account...';
    submitBtn.disabled = true;

    try {
        const baseUrl = window.SITE_URL ? window.SITE_URL.replace(/\/$/, '') : '';
        const response = await fetch(`${baseUrl}/auth/register.php`, {
            method: 'POST',
            body: formData
        });

        const responseText = await response.text();
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Raw Response:', responseText);
            throw new Error('Server returned invalid response: ' + responseText.substring(0, 100));
        }

        if (data.success) {
            // Check if there's pending booking data
            if (pendingBookingData) {
                console.log('Redirecting to pending booking:', pendingBookingData.redirectUrl);
                window.location.href = pendingBookingData.redirectUrl;
            } else {
                // Redirect to homepage after successful registration
                window.location.href = `${baseUrl}/index.php`;
            }
        } else {
            document.getElementById('registerError').textContent = data.message;
            document.getElementById('registerError').style.display = 'block';
        }
    } catch (error) {
        document.getElementById('registerError').textContent = error.message.includes('Server returned') ? error.message : 'An error occurred. Please try again.';
        document.getElementById('registerError').style.display = 'block';
        console.error(error);
    } finally {
        if (submitBtn) {
            submitBtn.textContent = originalBtnText;
            submitBtn.disabled = false;
        }
    }
}

// Check authentication before booking
function checkAuthAndBook(hotelId, roomId, checkIn, checkOut, guests, rooms) {
    // Check if user is logged in (this will be set by PHP)
    const isLoggedIn = document.body.dataset.loggedIn === 'true';

    if (!isLoggedIn) {
        // Store booking data
        const baseUrl = window.SITE_URL ? window.SITE_URL.replace(/\/$/, '') : '';
        pendingBookingData = {
            redirectUrl: `${baseUrl}/booking.php?hotel_id=${hotelId}&room_id=${roomId}&check_in=${checkIn}&check_out=${checkOut}&guests=${guests}&rooms=${rooms}`
        };

        // Show auth modal
        showAuthModal('login');
        return false;
    }

    // User is logged in, proceed to booking
    const baseUrl = window.SITE_URL ? window.SITE_URL.replace(/\/$/, '') : '';
    window.location.href = `${baseUrl}/booking.php?hotel_id=${hotelId}&room_id=${roomId}&check_in=${checkIn}&check_out=${checkOut}&guests=${guests}&rooms=${rooms}`;
    return true;
}

// Initialize Date Pickers
function initializeDatePickers() {
    const checkInInput = document.querySelector('input[name="check_in"]');
    const checkOutInput = document.querySelector('input[name="check_out"]');

    if (checkInInput) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        checkInInput.setAttribute('min', today);

        checkInInput.addEventListener('change', function () {
            // Set check-out minimum to check-in date + 1 day
            const checkInDate = new Date(this.value);
            checkInDate.setDate(checkInDate.getDate() + 1);
            const minCheckOut = checkInDate.toISOString().split('T')[0];
            if (checkOutInput) {
                checkOutInput.setAttribute('min', minCheckOut);
                if (checkOutInput.value && checkOutInput.value <= this.value) {
                    checkOutInput.value = minCheckOut;
                }
            }
        });
    }
}

// Search hotels
function searchHotels(e) {
    if (e) e.preventDefault();

    const destination = document.querySelector('select[name="destination"]')?.value;
    const checkIn = document.querySelector('input[name="check_in"]')?.value;
    const checkOut = document.querySelector('input[name="check_out"]')?.value;
    const guests = document.querySelector('input[name="guests"]')?.value;

    const baseUrl = window.SITE_URL || '/WBT/hotel_booking';
    let url = `${baseUrl}/hotels.php?`;
    const params = [];

    if (destination) params.push(`destination=${destination}`);
    if (checkIn) params.push(`check_in=${checkIn}`);
    if (checkOut) params.push(`check_out=${checkOut}`);
    if (guests) params.push(`guests=${guests}`);

    url += params.join('&');
    window.location.href = url;
}

// Filter hotels
function filterHotels() {
    const minPrice = document.getElementById('minPrice')?.value || '';
    const maxPrice = document.getElementById('maxPrice')?.value || '';
    const rating = document.getElementById('rating')?.value || '';
    const type = document.getElementById('type')?.value || '';

    const urlParams = new URLSearchParams(window.location.search);

    if (minPrice) urlParams.set('min_price', minPrice);
    else urlParams.delete('min_price');

    if (maxPrice) urlParams.set('max_price', maxPrice);
    else urlParams.delete('max_price');

    if (rating) urlParams.set('min_rating', rating);
    else urlParams.delete('min_rating');

    if (type) urlParams.set('type', type);
    else urlParams.delete('type');

    window.location.search = urlParams.toString();
}

// Format currency
function formatCurrency(amount) {
    return '৳' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Calculate total price
function calculateTotalPrice() {
    const pricePerNight = parseFloat(document.getElementById('pricePerNight')?.value || 0);
    const numRooms = parseInt(document.getElementById('num_rooms')?.value || 1);
    const checkIn = document.querySelector('input[name="check_in"]')?.value;
    const checkOut = document.querySelector('input[name="check_out"]')?.value;

    if (checkIn && checkOut) {
        const date1 = new Date(checkIn);
        const date2 = new Date(checkOut);
        const nights = Math.max(1, Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24)));

        const totalPrice = pricePerNight * numRooms * nights;

        const totalElement = document.getElementById('totalPrice');
        if (totalElement) {
            totalElement.textContent = formatCurrency(totalPrice);
        }

        const nightsElement = document.getElementById('totalNights');
        if (nightsElement) {
            nightsElement.textContent = nights;
        }
    }
}

// Payment method selection
function selectPaymentMethod(method) {
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(pm => pm.classList.remove('selected'));

    const selectedMethod = document.querySelector(`.payment-method[data-method="${method}"]`);
    if (selectedMethod) {
        selectedMethod.classList.add('selected');
    }

    const paymentInput = document.getElementById('payment_method');
    if (paymentInput) {
        paymentInput.value = method;
    }
}

// Confirm booking
function confirmBooking() {
    const paymentMethod = document.getElementById('payment_method')?.value;

    if (!paymentMethod) {
        alert('Please select a payment method');
        return false;
    }

    return true;
}

// Show loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
}

// Hide loading overlay
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Show cancellation confirmation modal
function showCancellationModal(bookingId) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 500px; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem; color: var(--error);">⚠️</div>
            <h3>Cancel Booking?</h3>
            <p style="color: var(--gray-600); margin: 1rem 0 2rem;">
                Are you sure you want to cancel this booking? This action cannot be undone.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button class="btn btn-outline" onclick="this.closest('.modal').remove()">
                    No, Keep Booking
                </button>
                <button class="btn btn-primary" style="background: var(--error);" onclick="confirmCancellation(${bookingId})">
                    Yes, Cancel Booking
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Confirm and execute cancellation
async function confirmCancellation(bookingId) {
    // Close modal
    const modal = document.querySelector('.modal');
    if (modal) modal.remove();

    console.log('Proceeding with cancellation for booking:', bookingId);
    showLoading();

    try {
        const formData = new FormData();
        formData.append('booking_id', bookingId);

        const baseUrl = window.SITE_URL || '/WBT/hotel_booking';
        const response = await fetch(`${baseUrl}/api/cancel-booking.php`, {
            method: 'POST',
            body: formData
        });

        const responseText = await response.text();
        console.log('Server response:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Raw Response:', responseText);
            throw new Error('Server returned invalid response: ' + responseText.substring(0, 100));
        }

        hideLoading();

        if (data.success) {
            alert('Booking cancelled successfully');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to cancel booking');
        }
    } catch (error) {
        hideLoading();
        const msg = error.message.includes('Server returned') ? error.message : 'An error occurred. Please try again.';
        alert(msg);
        console.error('Error:', error);
    }
}

// Cancel Booking - Entry point
function cancelBooking(bookingId) {
    console.log('cancelBooking called with ID:', bookingId);
    showCancellationModal(bookingId);
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
