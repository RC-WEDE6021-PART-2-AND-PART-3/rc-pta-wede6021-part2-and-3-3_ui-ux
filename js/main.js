/**
 * PASTIMES - Main JavaScript File
 * Handles client-side interactivity
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    initMobileMenu();
    
    // Password visibility toggle
    initPasswordToggle();
    
    // Form validation
    initFormValidation();
    
    // Search functionality
    initSearch();
    
    // Cart functionality
    initCart();
    
    // Filter functionality
    initFilters();
    
    // Message functionality
    initMessages();
});

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });
    }
}

/**
 * Password Visibility Toggle
 */
function initPasswordToggle() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('svg');
            
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.add('showing');
            } else {
                input.type = 'password';
                this.classList.remove('showing');
            }
        });
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    // Registration form validation
    const registerForm = document.querySelector('.register-form');
    if (registerForm) {
        const password = registerForm.querySelector('#password');
        const confirmPassword = registerForm.querySelector('#confirm_password');
        const requirements = registerForm.querySelectorAll('.requirement');
        
        if (password) {
            password.addEventListener('input', function() {
                const value = this.value;
                
                // Check requirements
                requirements.forEach(function(req) {
                    const type = req.dataset.requirement;
                    let met = false;
                    
                    switch(type) {
                        case 'length':
                            met = value.length >= 8;
                            break;
                        case 'number':
                            met = /\d/.test(value);
                            break;
                        case 'case':
                            met = /[a-z]/.test(value) && /[A-Z]/.test(value);
                            break;
                        case 'match':
                            met = value === confirmPassword.value && value.length > 0;
                            break;
                    }
                    
                    if (met) {
                        req.classList.add('met');
                        req.classList.remove('unmet');
                    } else {
                        req.classList.remove('met');
                        req.classList.add('unmet');
                    }
                });
            });
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                const matchReq = registerForm.querySelector('[data-requirement="match"]');
                if (matchReq) {
                    if (this.value === password.value && this.value.length > 0) {
                        matchReq.classList.add('met');
                        matchReq.classList.remove('unmet');
                    } else {
                        matchReq.classList.remove('met');
                        matchReq.classList.add('unmet');
                    }
                }
            });
        }
    }
    
    // Username validation
    const usernameInput = document.querySelector('#username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
        });
    }
}

/**
 * Search Functionality
 */
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.querySelector('.search-form');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            if (searchInput && searchInput.value.trim() === '') {
                e.preventDefault();
            }
        });
    }
}

/**
 * Cart Functionality
 */
function initCart() {
    // Quantity controls
    const quantityControls = document.querySelectorAll('.quantity-control');
    
    quantityControls.forEach(function(control) {
        const minus = control.querySelector('.qty-minus');
        const plus = control.querySelector('.qty-plus');
        const input = control.querySelector('.qty-input');
        
        if (minus && plus && input) {
            minus.addEventListener('click', function() {
                let value = parseInt(input.value) || 1;
                if (value > 1) {
                    input.value = value - 1;
                    updateCartItem(input);
                }
            });
            
            plus.addEventListener('click', function() {
                let value = parseInt(input.value) || 1;
                input.value = value + 1;
                updateCartItem(input);
            });
        }
    });
    
    // Remove item buttons
    const removeButtons = document.querySelectorAll('.remove-item');
    removeButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to remove this item?')) {
                e.preventDefault();
            }
        });
    });
}

function updateCartItem(input) {
    const form = input.closest('form');
    if (form) {
        form.submit();
    }
}

/**
 * Filter Functionality
 */
function initFilters() {
    const filterForm = document.querySelector('.filter-form');
    const clearFilters = document.querySelector('.clear-filters');
    
    if (clearFilters && filterForm) {
        clearFilters.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Clear all inputs
            const inputs = filterForm.querySelectorAll('input, select');
            inputs.forEach(function(input) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else if (input.type !== 'submit') {
                    input.value = '';
                }
            });
            
            // Submit form to reload without filters
            filterForm.submit();
        });
    }
    
    // Auto-submit on filter change (optional)
    const autoSubmitFilters = document.querySelectorAll('.filter-auto-submit');
    autoSubmitFilters.forEach(function(input) {
        input.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
    });
    
    // Price range slider
    const priceMin = document.querySelector('#price-min');
    const priceMax = document.querySelector('#price-max');
    const priceDisplay = document.querySelector('.price-display');
    
    if (priceMin && priceMax && priceDisplay) {
        function updatePriceDisplay() {
            priceDisplay.textContent = 'R' + priceMin.value + ' - R' + priceMax.value;
        }
        
        priceMin.addEventListener('input', updatePriceDisplay);
        priceMax.addEventListener('input', updatePriceDisplay);
    }
}

/**
 * Message Functionality
 */
function initMessages() {
    const messageForm = document.querySelector('.message-form');
    const messageInput = document.querySelector('.message-input');
    const conversationList = document.querySelector('.conversation-list');
    
    if (messageForm && messageInput) {
        messageForm.addEventListener('submit', function(e) {
            if (messageInput.value.trim() === '') {
                e.preventDefault();
                messageInput.focus();
            }
        });
        
        // Auto-scroll to bottom of messages
        const messagesContainer = document.querySelector('.messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
    
    // Search conversations
    const conversationSearch = document.querySelector('.conversation-search');
    if (conversationSearch && conversationList) {
        conversationSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const conversations = conversationList.querySelectorAll('.conversation-item');
            
            conversations.forEach(function(conversation) {
                const name = conversation.querySelector('.conversation-name').textContent.toLowerCase();
                const preview = conversation.querySelector('.conversation-preview').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || preview.includes(searchTerm)) {
                    conversation.style.display = '';
                } else {
                    conversation.style.display = 'none';
                }
            });
        });
    }
}

/**
 * Add to Wishlist
 */
function addToWishlist(clothingId) {
    fetch('ajax/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&clothing_id=' + clothingId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item added to wishlist!', 'success');
            updateWishlistButton(clothingId, true);
        } else {
            showNotification(data.message || 'Failed to add item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

/**
 * Remove from Wishlist
 */
function removeFromWishlist(clothingId) {
    fetch('ajax/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=remove&clothing_id=' + clothingId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item removed from wishlist', 'success');
            updateWishlistButton(clothingId, false);
        } else {
            showNotification(data.message || 'Failed to remove item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function updateWishlistButton(clothingId, isInWishlist) {
    const button = document.querySelector('[data-wishlist-id="' + clothingId + '"]');
    if (button) {
        if (isInWishlist) {
            button.classList.add('in-wishlist');
            button.setAttribute('onclick', 'removeFromWishlist(' + clothingId + ')');
        } else {
            button.classList.remove('in-wishlist');
            button.setAttribute('onclick', 'addToWishlist(' + clothingId + ')');
        }
    }
}

/**
 * Add to Cart
 */
function addToCart(clothingId) {
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&clothing_id=' + clothingId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item added to cart!', 'success');
            updateCartCount(data.cartCount);
        } else {
            showNotification(data.message || 'Failed to add item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function updateCartCount(count) {
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'flex' : 'none';
    }
}

/**
 * Show Notification
 */
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'notification notification-' + type;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(function() {
        notification.classList.add('show');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}

/**
 * Confirm Delete
 */
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

/**
 * Format Currency
 */
function formatCurrency(amount) {
    return 'R' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Toggle Element Visibility
 */
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.toggle('hidden');
    }
}

/**
 * Smooth Scroll
 */
function smoothScroll(target) {
    const element = document.querySelector(target);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}