<?php
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/config.php';

Session::start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?>
    </title>
    <meta name="description" content="Book hotels in Bangladesh and worldwide with bKash, Nagad, and card payments">
    <script>
        window.SITE_URL = '<?php echo SITE_URL; ?>';
    </script>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                    🏨
                    <?php echo SITE_NAME; ?>
                </a>

                <ul class="nav-menu" id="navMenu">
                    <?php
                    // Only show Home link if not on homepage
                    $currentPage = basename($_SERVER['PHP_SELF']);
                    if ($currentPage !== 'index.php'):
                        ?>
                        <li><a href="<?php echo SITE_URL; ?>/index.php" class="nav-link">Home</a></li>
                    <?php endif; ?>
                    
                    <?php if ($currentPage !== 'hotels.php'): ?>
                        <li><a href="<?php echo SITE_URL; ?>/hotels.php" class="nav-link">Hotels</a></li>
                    <?php endif; ?>


                    <?php if (Session::isLoggedIn()): ?>
                        <li><a href="<?php echo SITE_URL; ?>/profile.php" class="nav-link">My Profile</a></li>

                        <?php if (Session::isAdmin()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="nav-link">Admin</a></li>
                        <?php endif; ?>

                        <li>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="color: var(--gray-600); font-size: 14px;">
                                    👤
                                    <?php echo Session::getUserName(); ?>
                                </span>
                                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="btn btn-outline btn-sm">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="#" onclick="showAuthModal('login'); return false;"
                                class="btn btn-outline btn-sm">Login</a></li>
                        <li><a href="#" onclick="showAuthModal('register'); return false;"
                                class="btn btn-primary btn-sm">Sign Up</a></li>
                    <?php endif; ?>

                    <!-- Country / Currency Selector -->

                    <li>
                        <div style="position: relative; display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 1rem;">🌍</span>
                            <select id="countryCurrencySelect" onchange="handleCurrencyChange(this.value)"
                                style="border: 1.5px solid var(--gray-200); border-radius: 0.4rem; padding: 0.3rem 0.5rem; font-size: 0.8rem; font-weight: 600; color: var(--gray-700); background: white; cursor: pointer;">
                                <option value="BD|BDT|৳|1">🇧🇩 Bangladesh (BDT ৳)</option>
                                <option value="US|USD|$|0.0091">🇺🇸 USA (USD $)</option>
                                <option value="GB|GBP|£|0.0071">🇬🇧 UK (GBP £)</option>
                                <option value="EU|EUR|€|0.0083">🇪🇺 Europe (EUR €)</option>
                                <option value="IN|INR|₹|0.76">🇮🇳 India (INR ₹)</option>
                                <option value="AU|AUD|A$|0.014">🇦🇺 Australia (AUD A$)</option>
                                <option value="CA|CAD|C$|0.012">🇨🇦 Canada (CAD C$)</option>
                                <option value="AE|AED|د.إ|0.033">🇦🇪 UAE (AED د.إ)</option>
                                <option value="SA|SAR|ر.س|0.034">🇸🇦 Saudi Arabia (SAR ر.س)</option>
                                <option value="MY|MYR|RM|0.042">🇲🇾 Malaysia (MYR RM)</option>
                                <option value="SG|SGD|S$|0.012">🇸🇬 Singapore (SGD S$)</option>
                                <option value="PK|PKR|₨|2.53">🇵🇰 Pakistan (PKR ₨)</option>
                            </select>
                        </div>
                    </li>
                </ul>

                <button class="mobile-menu-toggle" id="mobileToggle" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

<script>
// Currency conversion - rates are relative to BDT (Bangladeshi Taka)
const CURRENCY_RATES = {
    'BD': { code: 'BDT', symbol: '৳', rate: 1 },
    'US': { code: 'USD', symbol: '$', rate: 0.0091 },
    'GB': { code: 'GBP', symbol: '£', rate: 0.0071 },
    'EU': { code: 'EUR', symbol: '€', rate: 0.0083 },
    'IN': { code: 'INR', symbol: '₹', rate: 0.76 },
    'AU': { code: 'AUD', symbol: 'A$', rate: 0.014 },
    'CA': { code: 'CAD', symbol: 'C$', rate: 0.012 },
    'AE': { code: 'AED', symbol: 'د.إ', rate: 0.033 },
    'SA': { code: 'SAR', symbol: 'ر.س', rate: 0.034 },
    'MY': { code: 'MYR', symbol: 'RM', rate: 0.042 },
    'SG': { code: 'SGD', symbol: 'S$', rate: 0.012 },
    'PK': { code: 'PKR', symbol: '₨', rate: 2.53 }
};

function handleCurrencyChange(value) {
    const [country] = value.split('|');
    localStorage.setItem('selectedCountry', country);
    applyCurrencyToPage();
}

function applyCurrencyToPage() {
    const country = localStorage.getItem('selectedCountry') || 'BD';
    const currency = CURRENCY_RATES[country] || CURRENCY_RATES['BD'];

    // Update selector
    const sel = document.getElementById('countryCurrencySelect');
    if (sel) {
        for (let opt of sel.options) {
            if (opt.value.startsWith(country + '|')) {
                sel.value = opt.value;
                break;
            }
        }
    }

    // Store globally for other scripts to read
    window.CURRENT_CURRENCY = currency;

    // Convert all price-display elements
    document.querySelectorAll('[data-price-bdt]').forEach(el => {
        const bdtPrice = parseFloat(el.getAttribute('data-price-bdt'));
        if (!isNaN(bdtPrice)) {
            const converted = (bdtPrice * currency.rate).toFixed(2);
            // Format with commas
            const formatted = parseFloat(converted).toLocaleString('en-US', { maximumFractionDigits: 2 });
            el.textContent = currency.symbol + formatted;
        }
    });
}

// Run on page load
document.addEventListener('DOMContentLoaded', applyCurrencyToPage);
</script>


    <!-- Flash Messages -->
    <?php if (Session::hasFlash('success')): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-success">
                ✓
                <?php echo Session::getFlash('success'); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-error">
                ✗
                <?php echo Session::getFlash('error'); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('warning')): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-warning">
                ⚠
                <?php echo Session::getFlash('warning'); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('info')): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-info">
                ℹ
                <?php echo Session::getFlash('info'); ?>
            </div>
        </div>
    <?php endif; ?>