<?php
/**
 * About Page - PASTIMES
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - PASTIMES</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="about-page">
        <div class="page-header">
            <h1>About PASTIMES</h1>
            <p>South Africa's premier marketplace for quality second-hand branded clothing</p>
        </div>
        
        <div class="container">
            <section class="about-section">
                <div class="about-content">
                    <h2>Our Story</h2>
                    <p>Founded in 2026, PASTIMES was born from a passion for sustainable fashion and a belief that quality clothing deserves a second life. We created a platform where South Africans can buy and sell premium pre-loved fashion with confidence.</p>
                    <p>Our mission is to make sustainable fashion accessible to everyone while reducing the environmental impact of the clothing industry. Every item sold on PASTIMES helps reduce waste and gives beautiful garments a new home.</p>
                </div>
                <div class="about-image">
                    <div class="image-placeholder">
                        <span>P</span>
                    </div>
                </div>
            </section>
            
            <section class="values-section">
                <h2>Our Values</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3>Trust & Safety</h3>
                        <p>Every seller is verified, and we ensure secure transactions for peace of mind.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                        </div>
                        <h3>Quality First</h3>
                        <p>We focus on premium branded items in excellent condition.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <h3>Local Community</h3>
                        <p>Supporting South African fashion lovers and building a sustainable community.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 3v18"></path>
                                <path d="M5.5 8.5l3-3 3 3"></path>
                                <path d="M18.5 15.5l-3 3-3-3"></path>
                            </svg>
                        </div>
                        <h3>Sustainability</h3>
                        <p>Reducing fashion waste and promoting circular economy practices.</p>
                    </div>
                </div>
            </section>
            
            <section class="stats-section">
                <h2>PASTIMES in Numbers</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number">5,000+</span>
                        <span class="stat-label">Active Listings</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">2,500+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">100+</span>
                        <span class="stat-label">Verified Sellers</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Premium Brands</span>
                    </div>
                </div>
            </section>
            
            <section class="team-section">
                <h2>Meet the Team</h2>
                <div class="team-grid">
                    <div class="team-card">
                        <div class="team-avatar">
                            <span>F</span>
                        </div>
                        <h3>Founder</h3>
                        <p>Fashion Enthusiast & Entrepreneur</p>
                    </div>
                    <div class="team-card">
                        <div class="team-avatar">
                            <span>D</span>
                        </div>
                        <h3>Developer</h3>
                        <p>Building the PASTIMES Platform</p>
                    </div>
                    <div class="team-card">
                        <div class="team-avatar">
                            <span>C</span>
                        </div>
                        <h3>Community Manager</h3>
                        <p>Connecting Buyers & Sellers</p>
                    </div>
                </div>
            </section>
            
            <section class="cta-section">
                <h2>Join the PASTIMES Community</h2>
                <p>Start buying and selling pre-loved fashion today</p>
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