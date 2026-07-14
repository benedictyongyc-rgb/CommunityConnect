<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CommunityConnect Volunteer System</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="site-navbar">
        <div class="brand">
            <img src="images/cc_logo.png" alt="CommunityConnect logo">
            <div class="brand-text">
                <div class="brand-name">COMMUNITY CONNECT</div>
                <div class="brand-tag">TOGETHER, WE GROW</div>
            </div>
        </div>
        <div class="nav-actions">
            <a href="login.php" class="btn secondary">Login</a>
            <a href="register.php" class="btn">Join Us</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-bg" style="background-image:url('images/header_banner.png');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="eyebrow">TOGETHER, WE GROW</div>
            <h1>Building Stronger <span>Communities</span>, Together.</h1>
            <p>CommunityConnect links volunteers with local service opportunities &mdash;
               find a project, apply in minutes, and start making a difference near you.</p>
            <div class="hero-actions">
                <a href="register.php" class="btn">Become a Volunteer</a>
                <a href="login.php" class="btn secondary" style="background:rgba(255,255,255,.15); color:white; border-color:rgba(255,255,255,.5);">Login</a>
            </div>
        </div>
    </section>

    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-icon">🙋</div>
            <div>
                <div class="stat-value">Register</div>
                <div class="stat-label">Create a volunteer account</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">🔍</div>
            <div>
                <div class="stat-value">Browse</div>
                <div class="stat-label">Find service opportunities</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">📝</div>
            <div>
                <div class="stat-value">Apply</div>
                <div class="stat-label">Submit your application</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">🌱</div>
            <div>
                <div class="stat-value">Serve</div>
                <div class="stat-label">Make an impact locally</div>
            </div>
        </div>
    </div>

    <section class="section tint">
        <div class="section-heading">
            <div>
                <h2>Our Commitment</h2>
                <p>Everything we build is guided by these values.</p>
            </div>
        </div>
        <div class="card-grid">
            <div class="feature-card">
                <div class="icon-circle">🌿</div>
                <h3>Sustainability</h3>
                <p>We connect people with opportunities and resources that create lasting community impact.</p>
            </div>
            <div class="feature-card">
                <div class="icon-circle">🤝</div>
                <h3>Togetherness</h3>
                <p>Communities grow stronger when everyone contributes. We make it easy to volunteer and help.</p>
            </div>
            <div class="feature-card">
                <div class="icon-circle">🌳</div>
                <h3>Growth</h3>
                <p>Helping volunteers and communities grow through consistent, well-organized service programs.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <div>
                <h2>Get Started</h2>
                <p>Choose how you'd like to use CommunityConnect.</p>
            </div>
        </div>
        <div class="card-grid">
            <div class="feature-card">
                <div class="icon-circle">🧍</div>
                <h3>New Volunteer</h3>
                <p>Register an account to browse and apply for community service opportunities.</p>
                <br>
                <a href="register.php" class="btn">Register as Volunteer</a>
            </div>
            <div class="feature-card">
                <div class="icon-circle">🔑</div>
                <h3>Returning Volunteer</h3>
                <p>Already registered? Log in to view events and track your applications.</p>
                <br>
                <a href="login.php" class="btn secondary">Volunteer Login</a>
            </div>
            <div class="feature-card">
                <div class="icon-circle">🛠️</div>
                <h3>Administrator</h3>
                <p>Manage services, review applications, and oversee volunteer activity.</p>
                <br>
                <a href="login.php?pane=manager" class="btn secondary">Admin Login</a>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        &copy; <?php echo date('Y'); ?> CommunityConnect &mdash; Connecting Volunteers with Community Service Opportunities
    </footer>

</body>
</html>
