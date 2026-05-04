<?php
/**
 * How It Works Page - PASTIMES
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How It Works - PASTIMES</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="how-it-works-page">
        <div class="page-header">
            <h1>How It Works</h1>
            <p>Your guide to buying and selling on PASTIMES</p>
        </div>
        
        <div class="container">
            <!-- For Buyers -->
            <section class="guide-section">
                <h2>For Buyers</h2>
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <h3>Browse & Discover</h3>
                        <p>Explore our curated collection of premium second-hand clothing. Use filters to find exactly what you're looking for by brand, size, condition, and price.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </div>
                        <h3>Ask Questions</h3>
                        <p>Have questions about an item? Message the seller directly through our secure messaging system to get all the details you need.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </div>
                        <h3>Add to Cart</h3>
                        <p>Found something you love? Add it to your cart and continue shopping or proceed to checkout when you're ready.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                <line x1="1" y1="10" x2="23" y2="10"></line>
                            </svg>
                        </div>
                        <h3>Secure Checkout</h3>
                        <p>Complete your purchase with our secure payment system. Enter your delivery address and pay safely.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">5</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                        </div>
                        <h3>Receive Your Items</h3>
                        <p>Your items will be shipped to your door. Track your delivery and enjoy your new pre-loved fashion finds!</p>
                    </div>
                </div>
            </section>
            
            <!-- For Sellers -->
            <section class="guide-section">
                <h2>For Sellers</h2>
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                        </div>
                        <h3>Create an Account</h3>
                        <p>Sign up for free and complete your profile. All new accounts start as Buyers - you'll need to request seller status.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <h3>Get Verified</h3>
                        <p>Request seller verification from your profile. Our admin team will review your request and approve you to start selling.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                        <h3>List Your Items</h3>
                        <p>Take great photos and write detailed descriptions. Include brand, size, condition, and any flaws. Set a fair price.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </div>
                        <h3>Respond to Buyers</h3>
                        <p>Answer questions from potential buyers promptly. Good communication leads to more sales and positive reviews.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">5</div>
                        <div class="step-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <h3>Get Paid</h3>
                        <p>When your item sells, ship it promptly and receive your payment. Build your reputation and grow your fashion business!</p>
                    </div>
                </div>
            </section>
            
            <!-- Tips Section -->
            <section class="tips-section">
                <h2>Tips for Success</h2>
                <div class="tips-grid">
                    <div class="tip-card">
                        <h3>For Buyers</h3>
                        <ul>
                            <li>Check measurements and size guides carefully</li>
                            <li>Read item descriptions thoroughly</li>
                            <li>Look at all photos and ask for more if needed</li>
                            <li>Check seller ratings and reviews</li>
                            <li>Message sellers with questions before buying</li>
                        </ul>
                    </div>
                    <div class="tip-card">
                        <h3>For Sellers</h3>
                        <ul>
                            <li>Take clear, well-lit photos from multiple angles</li>
                            <li>Be honest about condition and any flaws</li>
                            <li>Include accurate measurements</li>
                            <li>Price competitively by researching similar items</li>
                            <li>Ship items promptly and package carefully</li>
                        </ul>
                    </div>
                </div>
            </section>
            
            <!-- FAQ Section -->
            <section class="faq-section">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-list">
                    <div class="faq-item">
                        <h3>How do I become a verified seller?</h3>
                        <p>After creating an account, go to your Profile Settings and click "Request Seller Status". Our admin team will review your request within 24-48 hours.</p>
                    </div>
                    <div class="faq-item">
                        <h3>What items can I sell?</h3>
                        <p>We accept quality branded clothing, footwear, and accessories in good condition. Items must be clean, undamaged, and authentic.</p>
                    </div>
                    <div class="faq-item">
                        <h3>How does shipping work?</h3>
                        <p>Sellers are responsible for shipping items to buyers. We recommend using a tracked shipping service for security.</p>
                    </div>
                    <div class="faq-item">
                        <h3>What if I have a problem with my order?</h3>
                        <p>Contact the seller directly through our messaging system. If you can't resolve the issue, our support team is here to help.</p>
                    </div>
                </div>
            </section>
            
            <!-- CTA Section -->
            <section class="cta-section">
                <h2>Ready to Get Started?</h2>
                <p>Join thousands of South Africans buying and selling pre-loved fashion</p>
                <div class="cta-buttons">
                    <a href="browse.php" class="btn btn-primary">Start Shopping</a>
                    <a href="register.php" class="btn btn-outline">Create Account</a>
                </div>
            </section>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>