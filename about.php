<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();
include 'includes/header.php';
?>

<div class="page-header">
    <h1>About <span style="color:var(--gold);">Pastimes</span></h1>
    <p>South Africa's premier second-hand fashion marketplace</p>
</div>

<!-- Mission -->
<section class="section" style="background:var(--bg-deep);">
    <div class="container">
        <div class="grid-2" style="align-items:center; gap:var(--space-3xl);">
            <div>
                <p style="color:var(--gold); font-size:0.8rem; letter-spacing:.3em; text-transform:uppercase; margin-bottom:var(--space-sm);">Our Mission</p>
                <h2 style="font-family:var(--font-display); font-size:2.2rem; color:var(--text-primary); margin-bottom:var(--space-lg);">
                    Fashion That Tells a <span style="color:var(--gold);">Story</span>
                </h2>
                <p style="color:var(--text-secondary); line-height:1.8; margin-bottom:var(--space-md);">
                    Pastimes was born from a simple belief — that great fashion should never go to waste.
                    Every piece of clothing carries history, character and craftsmanship that deserves a second life.
                </p>
                <p style="color:var(--text-secondary); line-height:1.8; margin-bottom:var(--space-xl);">
                    We connect passionate South African buyers and sellers in a trusted marketplace where
                    authenticity, sustainability and style come together.
                </p>
                <a href="/Pastimes/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i> Join Our Community
                </a>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-lg);">
                <?php
                $stats = [
                    ['500+',  'Listings',       'fas fa-tshirt'],
                    ['200+',  'Verified Sellers','fas fa-store'],
                    ['50+',   'Brands',          'fas fa-tags'],
                    ['1000+', 'Happy Buyers',    'fas fa-smile'],
                ];
                foreach ($stats as $s):
                ?>
                <div class="stat-card">
                    <div class="stat-icon"><i class="<?php echo $s[2]; ?>"></i></div>
                    <div class="stat-number"><?php echo $s[0]; ?></div>
                    <div class="stat-label"><?php echo $s[1]; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Our <span>Values</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <div class="grid-3" style="margin-top:var(--space-2xl);">
            <?php
            $values = [
                ['fas fa-shield-alt',  'Authenticity',   'Every item is verified. No fakes, no replicas — only genuine branded clothing.'],
                ['fas fa-leaf',        'Sustainability',  'Give quality clothing a second life and reduce fashion waste across South Africa.'],
                ['fas fa-handshake',   'Community',       'We bring buyers and sellers together in a fair, transparent and supportive marketplace.'],
                ['fas fa-lock',        'Trust & Safety',  'Verified sellers, secure transactions and admin oversight on every interaction.'],
                ['fas fa-star',        'Quality',         'Only clothing in good condition or better is listed on Pastimes.'],
                ['fas fa-heart',       'Passion',         'We love fashion. Everything we do is driven by a passion for style and self-expression.'],
            ];
            foreach ($values as $v):
            ?>
            <div class="card" style="padding:var(--space-xl);">
                <div style="font-size:1.8rem; color:var(--gold); margin-bottom:var(--space-md);"><i class="<?php echo $v[0]; ?>"></i></div>
                <h3 style="color:var(--text-primary); font-size:1rem; margin-bottom:var(--space-sm);"><?php echo $v[1]; ?></h3>
                <p style="color:var(--text-muted); font-size:0.85rem;"><?php echo $v[2]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section" style="background:var(--bg-deep);">
    <div class="container">
        <h2 class="section-title">How It <span>Works</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <div class="steps-grid" style="margin-top:var(--space-2xl);">
            <?php
            $steps = [
                ['Register',         'Create your free account and choose your role — Buyer, Seller or Admin.'],
                ['Browse or List',   'Buyers browse hundreds of verified listings. Sellers list their items with photos and descriptions.'],
                ['Connect',          'Message sellers directly to ask questions and negotiate before you commit.'],
                ['Buy or Sell',      'Buyers checkout securely. Sellers earn money from items they no longer need.'],
            ];
            foreach ($steps as $i => $s):
            ?>
            <div class="step-card">
                <div class="step-number"><?php echo $i+1; ?></div>
                <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                <h3><?php echo $s[0]; ?></h3>
                <p><?php echo $s[1]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<?php if (!isset($_SESSION['userID'])): ?>
<section class="section">
    <div class="container text-center">
        <h2 class="section-title">Ready to Join <span>Pastimes</span>?</h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <p class="section-subtitle">Create your free account today.</p>
        <div style="display:flex; gap:var(--space-md); justify-content:center; flex-wrap:wrap; margin-top:var(--space-xl);">
            <a href="/Pastimes/register.php" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Register Free</a>
            <a href="/Pastimes/browse.php"   class="btn btn-outline btn-lg"><i class="fas fa-search"></i> Browse First</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>