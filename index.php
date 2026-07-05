<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/auth.php';
if (is_logged_in()) {
    $map = ['business'=>'/linkra/business/dashboard.php','creator'=>'/linkra/creator/browse.php','admin'=>'/linkra/admin/dashboard.php'];
    header('Location:'.($map[$_SESSION['user']['role']]??'/linkra/auth/login.php')); exit();
}
?>
<!DOCTYPE html><html lang="en" data-theme="light"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>LINKRA — Creator & Business Collaboration</title>
<link rel="stylesheet" href="/linkra/assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>(function(){const t=localStorage.getItem('linkra_theme')||'light';document.documentElement.setAttribute('data-theme',t);})()</script>
</head><body class="landing-page">

<nav class="lnav">
  <div class="logo-text">LINK<span>RA</span></div>
  <div class="lnav-links">
    <a href="#how" class="nav-desktop">How it works</a>
    <a href="#platforms" class="nav-desktop">Platforms</a>
    <a href="#content" class="nav-desktop">Content types</a>
    <button class="theme-toggle" onclick="toggleTheme()" style="color:rgba(255,255,255,.6);border-color:rgba(255,255,255,.15)"><i class="ti ti-moon"></i><span>Dark</span></button>
    <a href="/linkra/auth/login.php" class="btn btn-secondary btn-sm">Log in</a>
    <a href="/linkra/auth/register.php" class="btn btn-primary btn-sm">Get started</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-eyebrow"><i class="ti ti-bolt"></i>The creator collaboration platform</div>
    <h1 class="hero-title">Where brands meet<br><span class="hl">content creators</span></h1>
    <p class="hero-sub">LINKRA connects businesses with creators for short-form and long-form promotional content — across TikTok, Instagram, YouTube, Facebook, X and more. No agencies. No middlemen. Direct collaboration.</p>
    <div class="hero-cta">
      <a href="/linkra/auth/register.php?role=business" class="btn btn-primary btn-lg"><i class="ti ti-building-store"></i>Post a Campaign</a>
      <a href="/linkra/auth/register.php?role=creator"  class="btn btn-lime btn-lg"><i class="ti ti-video"></i>Join as Creator</a>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-section" id="how">
  <div class="section-eyebrow">How it works</div>
  <h2 class="section-title">Simple. Open. Fair.</h2>
  <p class="section-sub">No applications. No waiting. Creators submit their work directly — businesses review and pay approved content.</p>
  <div class="steps-grid">
    <?php
    $steps = [
      ['num'=>1,'icon'=>'ti-file-description','title'=>'Business posts campaign','desc'=>'Set budget, payout per 1K views, max per creator, platform, and requirements.'],
      ['num'=>2,'icon'=>'ti-search','title'=>'Creators browse & submit','desc'=>'Any creator can view the campaign and submit their video directly — no application needed.'],
      ['num'=>3,'icon'=>'ti-player-play','title'=>'Business reviews','desc'=>'Watch embedded videos from TikTok, YouTube, Instagram, Facebook, or X — all inside LINKRA.'],
      ['num'=>4,'icon'=>'ti-circle-check','title'=>'Approve & pay','desc'=>'Approved submissions get paid. Rejected ones get feedback. Budget updates in real time.'],
    ];
    foreach ($steps as $s): ?>
    <div class="step-card">
      <div class="step-num"><?=$s['num']?></div>
      <i class="ti <?=$s['icon']?>" style="font-size:30px;color:var(--purple);margin-bottom:12px;display:block"></i>
      <h4><?=$s['title']?></h4>
      <p><?=$s['desc']?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- PAYOUT MODEL -->
<section style="padding:80px 64px;background:#fff">
  <div class="section-eyebrow">Transparent payouts</div>
  <h2 class="section-title">Creators earn based on results</h2>
  <p class="section-sub">Businesses set the payout per 1,000 views and a max payout per creator. Remaining campaign budget is always visible.</p>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;max-width:900px;margin:44px auto 0">
    <?php
    $models = [
      ['icon'=>'ti-coin','color'=>'var(--purple)','bg'=>'var(--pl)','title'=>'Per 1K views','desc'=>'Business sets a ₱ amount paid per 1,000 views on the submitted content.'],
      ['icon'=>'ti-user-dollar','color'=>'var(--success)','bg'=>'var(--sl)','title'=>'Max per creator','desc'=>'A cap on how much any single creator can earn from one campaign.'],
      ['icon'=>'ti-chart-bar','color'=>'var(--info)','bg'=>'var(--il)','title'=>'Overall budget','desc'=>'Total campaign budget. Remaining balance updates live as submissions are paid.'],
      ['icon'=>'ti-shield-check','color'=>'var(--warning)','bg'=>'var(--wl)','title'=>'Pay on approval','desc'=>'Creators only get paid after the business reviews and approves their content.'],
    ];
    foreach ($models as $m): ?>
    <div class="feature-card" style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:var(--radius);padding:24px">
      <div style="width:44px;height:44px;background:<?=$m['bg']?>;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;margin-bottom:14px"><i class="ti <?=$m['icon']?>" style="font-size:22px;color:<?=$m['color']?>"></i></div>
      <h5 style="color:#1E293B;margin-bottom:8px"><?=$m['title']?></h5>
      <p style="font-size:13px;color:#64748B;line-height:1.65"><?=$m['desc']?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- PLATFORMS -->
<section class="platform-section" id="platforms">
  <div class="section-eyebrow">Supported platforms</div>
  <h2 class="section-title">Every major platform, one system</h2>
  <p class="section-sub">Creators paste their video link — LINKRA embeds it and plays it directly so businesses can review without leaving the site.</p>
  <div class="platform-grid">
    <?php
    $platforms = [
      ['icon'=>'ti-brand-tiktok','label'=>'TikTok','bg'=>'#010101'],
      ['icon'=>'ti-brand-instagram','label'=>'Instagram','bg'=>'linear-gradient(45deg,#F58529,#E1306C,#833AB4)'],
      ['icon'=>'ti-brand-youtube','label'=>'YouTube','bg'=>'#FF0000'],
      ['icon'=>'ti-brand-facebook','label'=>'Facebook','bg'=>'#1877F2'],
      ['icon'=>'ti-brand-x','label'=>'X (Twitter)','bg'=>'#000'],
    ];
    foreach ($platforms as $p): ?>
    <div class="platform-item">
      <div class="platform-icon" style="background:<?=$p['bg']?>"><i class="ti <?=$p['icon']?>"></i></div>
      <div class="platform-name"><?=$p['label']?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- CONTENT TYPES -->
<section class="content-type-section" id="content">
  <div class="section-eyebrow">Content types</div>
  <h2 class="section-title">Short-form and long-form, all in one place</h2>
  <p class="section-sub">LINKRA supports any content length. Whether you need a 15-second hook or a 10-minute in-depth review, campaigns can be set for either or both.</p>
  <div class="ctype-grid">
    <div class="ctype-card">
      <div class="ctype-icon" style="background:var(--pl)"><i class="ti ti-bolt" style="font-size:22px;color:var(--purple)"></i></div>
      <h4 style="color:#1E293B;margin-bottom:12px">Short-form content</h4>
      <ul style="display:flex;flex-direction:column;gap:8px">
        <?php foreach(['TikTok videos (15s–3min)','Instagram Reels','YouTube Shorts','Facebook Reels','X video clips','Product teasers & hooks'] as $item): ?>
        <li style="display:flex;align-items:center;gap:8px;font-size:13px;color:#475569"><i class="ti ti-check" style="color:var(--success);flex-shrink:0"></i><?=$item?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="ctype-card">
      <div class="ctype-icon" style="background:var(--il)"><i class="ti ti-movie" style="font-size:22px;color:var(--info)"></i></div>
      <h4 style="color:#1E293B;margin-bottom:12px">Long-form content</h4>
      <ul style="display:flex;flex-direction:column;gap:8px">
        <?php foreach(['YouTube product reviews','Facebook Watch videos','Tutorial & how-to content','Unboxing & haul videos','Brand story narratives','In-depth comparisons'] as $item): ?>
        <li style="display:flex;align-items:center;gap:8px;font-size:13px;color:#475569"><i class="ti ti-check" style="color:var(--info);flex-shrink:0"></i><?=$item?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <h2 style="color:#fff;margin-bottom:14px">Ready to collaborate?</h2>
  <p style="color:rgba(255,255,255,.75);font-size:16px;margin-bottom:32px;max-width:480px;margin-left:auto;margin-right:auto;line-height:1.7">Join businesses and creators already using LINKRA to create content that actually works.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="/linkra/auth/register.php?role=business" class="btn btn-lg" style="background:#fff;color:var(--purple)"><i class="ti ti-building-store"></i>Join as Business</a>
    <a href="/linkra/auth/register.php?role=creator"  class="btn btn-lime btn-lg"><i class="ti ti-video"></i>Join as Creator</a>
  </div>
</section>

<!-- FOOTER -->
<footer class="lfooter">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
    <div class="logo-text" style="color:#fff;font-size:20px">LINK<span style="color:var(--purple)">RA</span></div>
    <div style="font-size:13px;color:#484F58">© <?=date('Y')?> LINKRA. All rights reserved.</div>
    <div style="display:flex;gap:16px">
      <a href="/linkra/auth/login.php"    style="font-size:13px;color:#6E7681">Log in</a>
      <a href="/linkra/auth/register.php" style="font-size:13px;color:#6E7681">Register</a>
    </div>
  </div>
</footer>
<script src="/linkra/assets/js/main.js"></script>
</body></html>
