<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();
include 'includes/header.php';
?>

<div class="page-header">
    <h1>How It <span style="color:var(--gold);">Works</span></h1>
    <p>Everything you need to know about buying and selling on Pastimes</p>
</div>

<section class="section" style="background:var(--bg-deep);">
    <div class="container">
        <h2 class="section-title">For <span>Buyers</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <div class="steps-grid" style="margin-top:var(--space-2xl);">
            <?php
            $steps = [
                ['fas fa-user-plus',    'Create Account',    'Register as a Buyer for free. No hidden fees.'],
                ['fas fa-search',       'Browse',            'Filter by brand, size, category and condition to find your perfect item.'],
                ['fas fa-comment',      'Message Seller',    'Chat directly with the seller to ask questions before buying.'],
                ['fas fa-shopping-bag', 'Add to Cart',       'Add your favourite items to your cart and checkout securely.'],
                ['fas fa-map-marker',   'Enter Address',     'Provide your delivery address at checkout.'],
                ['fas fa-truck',        'Receive Item',      'Your item is delivered straight to your door.'],
            ];
            foreach ($steps as $i => $s):
            ?>
            <div class="step-card">
                <div class="step-number"><?php echo $i+1; ?></div>
                <div class="step-icon"><i class="<?php echo $s[0]; ?>"></i></div>
                <h3><?php echo $s[1]; ?></h3>
                <p><?php echo $s[2]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">For <span>Sellers</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <div class="steps-grid" style="margin-top:var(--space-2xl);">
            <?php
            $sellerSteps = [
                ['fas fa-store',        'Register as Seller', 'Create a seller account. Our admin team will verify you.'],
                ['fas fa-check-circle', 'Get Verified',       'Once verified by our admin, you can start listing items.'],
                ['fas fa-plus-circle',  'List Your Items',    'Add your clothing with photos, description, size and price.'],
                ['fas fa-comment',      'Respond to Buyers',  'Answer buyer questions through our messaging system.'],
                ['fas fa-hand-holding-usd', 'Make a Sale',    'When a buyer purchases your item, you get paid.'],
                ['fas fa-box',          'Ship the Item',      'Package and ship the item to the buyer\'s address.'],
            ];
            foreach ($sellerSteps as $i => $s):
            ?>
            <div class="step-card">
                <div class="step-number"><?php echo $i+1; ?></div>
                <div class="step-icon"><i class="<?php echo $s[0]; ?>"></i></div>
                <h3><?php echo $s[1]; ?></h3>
                <p><?php echo $s[2]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" style="background:var(--bg-deep);">
    <div class="container">
        <h2 class="section-title">Frequently Asked <span>Questions</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <div style="max-width:700px; margin: var(--space-2xl) auto 0;">
            <?php
            $faqs = [
                ['Is it free to register?',                   'Yes! Creating an account on Pastimes is completely free for buyers and sellers.'],
                ['How do I become a verified seller?',        'Register as a seller and our admin team will review and verify your account before you can list items.'],
                ['Are the items authentic?',                  'Yes. We only allow genuine branded clothing. Sellers are verified and listings are monitored by our admin team.'],
                ['How do I pay for items?',                   'Payment and delivery are coordinated between buyers, sellers and our admin team after checkout.'],
                ['Can I return an item?',                     'If you are not satisfied, contact our admin team who will mediate between you and the seller for a fair outcome.'],
                ['How do I contact a seller?',                'Use the Message button on any product page to chat directly with the seller.'],
            ];
            foreach ($faqs as $faq):
            ?>
            <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-md); padding:var(--space-lg); margin-bottom:var(--space-md);">
                <h3 style="color:var(--gold); font-size:1rem; margin-bottom:var(--space-sm);">
                    <i class="fas fa-question-circle" style="margin-right:.5rem;"></i><?php echo $faq[0]; ?>
                </h3>
                <p style="color:var(--text-secondary); font-size:0.88rem;"><?php echo $faq[1]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!isset($_SESSION['userID'])): ?>
<section class="section">
    <div class="container text-center">
        <h2 class="section-title">Ready to Get <span>Started</span>?</h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <div style="display:flex; gap:var(--space-md); justify-content:center; margin-top:var(--space-xl);">
            <a href="/Pastimes/register.php" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Register Free</a>
            <a href="/Pastimes/browse.php"   class="btn btn-outline btn-lg"><i class="fas fa-search"></i> Browse Items</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>