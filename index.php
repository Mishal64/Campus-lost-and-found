<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoundBridge | Campus Recovery Network</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <span class="brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 2 4 6.5v11L12 22l8-4.5v-11L12 2Zm0 2.2 5.8 3.3L12 10.8 6.2 7.5 12 4.2Zm-6 4.9 5 2.8v6L6 15.1v-6Zm12 0v6l-5 2.8v-6l5-2.8Z" fill="currentColor"/>
                </svg>
            </span>
            <span>FoundBridge</span>
        </div>

        <nav class="nav-actions">
            <a href="student/view_lost.php" class="link-btn">Browse</a>
            <?php if (isset($_SESSION['user_id'])) { ?>
                <a href="dashboard.php" class="cta-btn">Dashboard</a>
            <?php } else { ?>
                <a href="login.php" class="cta-btn">Sign In</a>
            <?php } ?>
        </nav>
    </header>

    <main>
        <section class="hero">
            <p class="chip">Campus Recovery Network</p>
            <h1>
                LOST IT?<br>
                <span>FOUND IT?</span><br>
                POST IT.
            </h1>
            <p class="subcopy">
                One place for students to report, discover, and reclaim belongings across campus.
                Fast, secure, and built for real recovery.
            </p>
            <div class="hero-actions">
                <a href="register.php" class="cta-btn">Get Started</a>
                <a href="student/view_found.php" class="ghost-btn">View Found Items</a>
            </div>
        </section>

        <section class="stats">
            <article>
                <h2>95%</h2>
                <p>Success Rate</p>
            </article>
            <article>
                <h2>24h</h2>
                <p>Avg. Return Time</p>
            </article>
            <article>
                <h2>1.2k+</h2>
                <p>Items Returned</p>
            </article>
        </section>
    </main>

    <footer class="footer">
        <div class="brand">
            <span class="brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 2 4 6.5v11L12 22l8-4.5v-11L12 2Zm0 2.2 5.8 3.3L12 10.8 6.2 7.5 12 4.2Zm-6 4.9 5 2.8v6L6 15.1v-6Zm12 0v6l-5 2.8v-6l5-2.8Z" fill="currentColor"/>
                </svg>
            </span>
            <span>FoundBridge</span>
        </div>
        <p>&copy; <?php echo date('Y'); ?> FoundBridge. All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
            <a href="#">Contact</a>
        </div>
    </footer>
</body>
</html>
