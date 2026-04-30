<?php
// ============================================================
//  vendor_dashboard.php  —  Full vendor dashboard with DB data
//  Access: http://localhost/rentease/vendor-dashboard.php
// ============================================================
require_once 'config.php';

// ── Auth check ────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: vendor-login.html');
    exit();
}

$vendor_id = $_SESSION['user_id'];

// ── Fetch vendor info ─────────────────────────────────────
$vstmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
$vstmt->bind_param('i', $vendor_id);
$vstmt->execute();
$vendor = $vstmt->get_result()->fetch_assoc();
$vstmt->close();

// ── Fetch listings ────────────────────────────────────────
$lstmt = $conn->prepare(
    "SELECT l.*, c.name AS cat_name
     FROM listings l
     JOIN categories c ON l.category_id = c.id
     WHERE l.vendor_id = ?
     ORDER BY l.created_at DESC"
);
$lstmt->bind_param('i', $vendor_id);
$lstmt->execute();
$listings = $lstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$lstmt->close();

// Attach images to each listing
foreach ($listings as &$lst) {
    $istmt = $conn->prepare(
        "SELECT image_path, is_cover FROM listing_images WHERE listing_id = ? ORDER BY sort_order ASC"
    );
    $istmt->bind_param('i', $lst['id']);
    $istmt->execute();
    $lst['images'] = $istmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $istmt->close();
}

// ── Fetch bookings ────────────────────────────────────────
$bstmt = $conn->prepare(
    "SELECT b.*, c.full_name AS customer_name, l.title AS listing_title
     FROM bookings b
     JOIN customers c ON b.customer_id = c.id
     JOIN listings  l ON b.listing_id  = l.id
     WHERE b.vendor_id = ?
     ORDER BY b.created_at DESC
     LIMIT 20"
);
$bstmt->bind_param('i', $vendor_id);
$bstmt->execute();
$bookings = $bstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$bstmt->close();

// ── Stats ─────────────────────────────────────────────────
$stats = $conn->prepare(
    "SELECT
       COUNT(*) AS total_bookings,
       COALESCE(SUM(vendor_payout), 0) AS total_earned,
       COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(NOW()) THEN vendor_payout ELSE 0 END), 0) AS month_earned
     FROM bookings WHERE vendor_id = ? AND status IN ('confirmed','completed')"
);
$stats->bind_param('i', $vendor_id);
$stats->execute();
$st = $stats->get_result()->fetch_assoc();
$stats->close();

// ── Categories for add-listing form ──────────────────────
$cats = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vendor Dashboard – Smart Rental Marketplace</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root{--primary:#FF4D00;--pd:#cc3d00;--sec:#0A0A0A;--bg:#F5F3EF;--bg2:#EDEBE5;--w:#fff;--txt:#1a1a1a;--m:#666;--sw:225px;--r:16px;--g:#22c55e}
    *{box-sizing:border-box;margin:0;padding:0}body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--txt);display:flex;min-height:100vh}
    h1,h2,h3,h4{font-family:'Syne',sans-serif}
    .sb{width:var(--sw);background:var(--sec);min-height:100vh;padding:24px 0;position:fixed;left:0;top:0;bottom:0;display:flex;flex-direction:column;z-index:100;overflow-y:auto}
    .sb-logo{padding:0 18px 20px;border-bottom:1px solid rgba(255,255,255,.07);margin-bottom:4px}
    .logo{display:flex;align-items:center;gap:9px;text-decoration:none}
    .li{width:32px;height:32px;background:var(--primary);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem;color:#fff;flex-shrink:0}
    .lt{font-family:'Syne',sans-serif;font-size:.9rem;font-weight:800;color:#fff;line-height:1.25}
    .lt em{color:var(--primary);font-style:normal}
    .sb-menu{padding:10px 0;flex:1}
    .mlbl{padding:10px 18px 5px;font-size:.6rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.2)}
    .mi{display:flex;align-items:center;gap:10px;padding:10px 18px;color:rgba(255,255,255,.5);text-decoration:none;font-size:.83rem;font-weight:500;transition:all .2s;cursor:pointer;border-left:3px solid transparent;border:none;background:none;width:100%;text-align:left}
    .mi:hover,.mi.act{color:#fff;background:rgba(255,255,255,.07);border-left:3px solid var(--primary)}
    .mi i{width:16px;text-align:center;font-size:.84rem;flex-shrink:0}
    .mi .bg{margin-left:auto;background:var(--primary);color:#fff;font-size:.6rem;font-weight:700;padding:2px 7px;border-radius:50px}
    .sb-bot{padding:14px 18px;border-top:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px}
    .uav{width:32px;height:32px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0}
    .ui strong{display:block;color:#fff;font-size:.8rem;font-weight:700}
    .ui span{color:rgba(255,255,255,.35);font-size:.68rem}
    .main{margin-left:var(--sw);flex:1;padding:26px 30px;min-height:100vh}
    .view{display:none}.view.act{display:block}
    .topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
    .topbar h2{font-size:1.4rem;font-weight:800;letter-spacing:-.5px}
    .topbar p{color:var(--m);font-size:.83rem;margin-top:2px}
    .btn-add{background:var(--primary);color:#fff;border:none;padding:10px 18px;border-radius:10px;font-size:.84rem;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;display:flex;align-items:center;gap:6px;transition:all .2s}
    .btn-add:hover{background:var(--pd);transform:translateY(-1px)}
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
    .sc{background:var(--w);border-radius:var(--r);padding:18px;box-shadow:0 2px 14px rgba(0,0,0,.06)}
    .si{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:10px}
    .sc1 .si{background:rgba(34,197,94,.12);color:var(--g)}
    .sc2 .si{background:rgba(59,130,246,.12);color:#3b82f6}
    .sc3 .si{background:rgba(255,77,0,.12);color:var(--primary)}
    .sc4 .si{background:rgba(251,191,36,.12);color:#fbbf24}
    .sv{font-family:'Syne',sans-serif;font-size:1.55rem;font-weight:800;line-height:1;margin-bottom:2px}
    .sl{font-size:.73rem;color:var(--m)}
    .sd{font-size:.7rem;margin-top:4px;color:var(--g)}
    .crd{background:var(--w);border-radius:var(--r);box-shadow:0 2px 14px rgba(0,0,0,.06);overflow:hidden}
    .ch{padding:14px 18px;border-bottom:1px solid var(--bg2);display:flex;align-items:center;justify-content:space-between}
    .ch h4{font-size:.93rem;font-weight:700}
    .lbtn{font-size:.76rem;color:var(--primary);font-weight:600;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif}
    table{width:100%;border-collapse:collapse}
    th{padding:8px 14px;text-align:left;font-size:.68rem;font-weight:700;color:var(--m);text-transform:uppercase;letter-spacing:.8px;background:var(--bg)}
    td{padding:11px 14px;font-size:.84rem;border-bottom:1px solid var(--bg2)}
    tr:last-child td{border-bottom:none}tr:hover td{background:var(--bg)}
    .pill{display:inline-block;padding:3px 9px;border-radius:50px;font-size:.67rem;font-weight:700;text-transform:uppercase}
    .pill.confirmed,.pill.approved{background:rgba(34,197,94,.12);color:#16a34a}
    .pill.pending{background:rgba(251,191,36,.15);color:#d97706}
    .pill.completed{background:rgba(107,114,128,.12);color:#6b7280}
    .pill.rejected{background:rgba(239,68,68,.1);color:#dc2626}
    /* Listing cards */
    .mlg{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:18px}
    .mlc{background:var(--w);border-radius:var(--r);overflow:hidden;box-shadow:0 2px 14px rgba(0,0,0,.07);transition:all .3s}
    .mlc:hover{transform:translateY(-4px);box-shadow:0 14px 40px rgba(0,0,0,.12)}
    .mlw{position:relative;height:190px;background:var(--bg2);overflow:hidden}
    .mlw img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s}
    .mlc:hover .mlw img{transform:scale(1.05)}
    .mlph{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--m);opacity:.4}
    .mlph i{font-size:2.5rem}
    .mlsb{position:absolute;top:8px;left:8px;padding:3px 9px;border-radius:50px;font-size:.66rem;font-weight:700;text-transform:uppercase}
    .mlsb.avail{background:var(--g);color:#fff}
    .mlsb.pend{background:#f59e0b;color:#fff}
    .mlsb.rej{background:#ef4444;color:#fff}
    .mlcnt{position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.55);color:#fff;font-size:.68rem;font-weight:600;padding:2px 7px;border-radius:50px;display:flex;align-items:center;gap:4px}
    .mlthu{display:flex;gap:4px;padding:7px 10px;background:var(--bg);border-bottom:1px solid var(--bg2);overflow-x:auto}
    .mlthu::-webkit-scrollbar{height:3px}
    .mlth{width:38px;height:38px;border-radius:6px;overflow:hidden;flex-shrink:0;border:2px solid transparent;cursor:pointer;transition:border-color .2s}
    .mlth:hover,.mlth.act{border-color:var(--primary)}
    .mlth img{width:100%;height:100%;object-fit:cover;display:block}
    .mlb{padding:13px 14px}
    .mlcat{display:inline-flex;align-items:center;gap:5px;background:rgba(255,77,0,.09);color:var(--primary);font-size:.66rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:3px 8px;border-radius:50px;margin-bottom:7px}
    .mlb h4{font-size:.95rem;font-weight:800;margin-bottom:4px}
    .mlloc{display:flex;align-items:center;gap:4px;color:var(--m);font-size:.76rem;margin-bottom:7px}
    .mlloc i{color:var(--primary);font-size:.7rem}
    .mldesc{font-size:.8rem;color:var(--m);line-height:1.5;margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .mldr{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:11px}
    .mldb{background:var(--bg);border-radius:7px;padding:8px 10px}
    .mldb .ll{font-size:.64rem;font-weight:700;color:var(--m);text-transform:uppercase;letter-spacing:.6px;margin-bottom:2px}
    .mldb .vv{font-size:.86rem;font-weight:700;color:var(--txt)}
    .mldb .vv.p{color:var(--primary);font-family:'Syne',sans-serif;font-size:.97rem}
    .mlf{display:flex;gap:6px;padding-top:10px;border-top:1px solid var(--bg2)}
    .bedit{flex:1;background:var(--sec);color:#fff;border:none;padding:8px;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:5px}
    .bedit:hover{background:var(--primary)}
    .bdel{background:rgba(239,68,68,.1);color:#ef4444;border:none;padding:8px 11px;border-radius:8px;font-size:.8rem;cursor:pointer;transition:all .2s}
    .bdel:hover{background:#ef4444;color:#fff}
    .empty{text-align:center;padding:50px 20px;color:var(--m)}
    .empty i{font-size:2.8rem;opacity:.2;display:block;margin-bottom:12px}
    /* ADD LISTING MODAL */
    .mo{display:none;position:fixed;inset:0;background:rgba(0,0,0,.58);z-index:500;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(5px)}
    .mo.open{display:flex}
    .mbox{background:#fff;border-radius:20px;width:100%;max-width:580px;max-height:94vh;overflow-y:auto;animation:su .3s ease}
    @keyframes su{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
    .mhd{padding:18px 22px;border-bottom:1px solid var(--bg2);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:#fff;z-index:2}
    .mhd h3{font-size:1.05rem;font-weight:800}
    .xcb{background:var(--bg);border:none;width:30px;height:30px;border-radius:50%;cursor:pointer;color:var(--m);display:flex;align-items:center;justify-content:center;transition:all .2s}
    .xcb:hover{background:var(--primary);color:#fff}
    .mbody{padding:20px 22px}
    .fg{margin-bottom:14px}
    .fg label{display:block;font-size:.7rem;font-weight:700;color:var(--m);text-transform:uppercase;letter-spacing:.8px;margin-bottom:5px}
    .rq{color:var(--primary)}
    .fg input,.fg select,.fg textarea{width:100%;border:1.5px solid var(--bg2);border-radius:9px;padding:10px 12px;font-size:.88rem;font-family:'DM Sans',sans-serif;color:var(--txt);outline:none;transition:border-color .2s;background:var(--bg)}
    .fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--primary);background:#fff}
    .fg textarea{min-height:75px;resize:vertical}
    .r2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .uz{border:2.5px dashed #ddd;border-radius:11px;padding:22px 18px;text-align:center;cursor:pointer;background:var(--bg);transition:all .25s}
    .uz:hover,.uz.dv{border-color:var(--primary);background:rgba(255,77,0,.03)}
    .uzi{font-size:2.2rem;color:var(--m);margin-bottom:8px;transition:color .2s;display:block}
    .uz.dv .uzi,.uz:hover .uzi{color:var(--primary)}
    .uz p{font-size:.85rem;color:var(--m);margin-bottom:3px;font-weight:500}
    .uz small{font-size:.72rem;color:#bbb}
    #ifi{display:none}
    .pg{display:grid;grid-template-columns:repeat(4,1fr);gap:7px;margin-top:10px}
    .pi{position:relative;border-radius:8px;overflow:hidden;aspect-ratio:1;background:var(--bg2);border:2px solid transparent;cursor:pointer;transition:border-color .2s}
    .pi:hover,.pi.cov{border-color:var(--primary)}
    .pi img{width:100%;height:100%;object-fit:cover;display:block}
    .pi .rb{position:absolute;top:3px;right:3px;width:18px;height:18px;background:rgba(0,0,0,.65);color:#fff;border:none;border-radius:50%;cursor:pointer;font-size:.55rem;display:flex;align-items:center;justify-content:center;z-index:1}
    .pi .rb:hover{background:#ef4444}
    .pi .cl{position:absolute;bottom:2px;left:2px;background:var(--primary);color:#fff;font-size:.55rem;font-weight:700;padding:2px 5px;border-radius:3px}
    .upinfo{font-size:.74rem;color:var(--m);margin-top:7px;text-align:center}
    .upinfo span{color:var(--primary);font-weight:700}
    .upbar{height:3px;background:var(--bg2);border-radius:2px;margin-top:7px;overflow:hidden;display:none}
    .upfill{height:100%;background:var(--primary);border-radius:2px;transition:width .3s}
    .bsave{width:100%;background:var(--primary);color:#fff;border:none;padding:13px;border-radius:9px;font-size:.92rem;font-weight:700;cursor:pointer;font-family:'Syne',sans-serif;margin-top:16px;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:7px}
    .bsave:hover{background:var(--pd)}
    .bsave:disabled{background:#ccc;cursor:not-allowed}
    .sp{display:none;text-align:center;padding:28px 22px}
    .sp.show{display:block}
    .spc{width:64px;height:64px;background:rgba(34,197,94,.12);border-radius:50%;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--g)}
    .sp h4{font-size:1.1rem;font-weight:800;margin-bottom:7px}
    .sp p{font-size:.83rem;color:var(--m);line-height:1.6;margin-bottom:18px}
    .bano{background:var(--primary);color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s;margin:4px}
    .bano:hover{background:var(--pd)}
    .toast{position:fixed;bottom:20px;right:20px;background:var(--sec);color:#fff;padding:11px 17px;border-radius:10px;font-size:.84rem;display:flex;align-items:center;gap:7px;z-index:9999;transform:translateY(60px);opacity:0;transition:all .3s;box-shadow:0 8px 24px rgba(0,0,0,.2);max-width:300px}
    .toast.show{transform:translateY(0);opacity:1}
    .toast i{color:var(--primary);flex-shrink:0}
    @media(max-width:900px){.stats{grid-template-columns:1fr 1fr}}
    @media(max-width:600px){.stats{grid-template-columns:1fr 1fr}.mlg{grid-template-columns:1fr}.r2{grid-template-columns:1fr}.main{padding:18px 14px}}
  </style>
</head>
<body>
<div class="sb">
  <div class="sb-logo"><a href="index.html" class="logo"><div class="li"><i class="fas fa-bolt"></i></div><div class="lt">Smart Rental<br/><em>Marketplace</em></div></a></div>
  <div class="sb-menu">
    <div class="mlbl">Main</div>
    <button class="mi act" onclick="sv('dash',this)"><i class="fas fa-th-large"></i> Dashboard</button>
    <button class="mi" onclick="sv('mylist',this)"><i class="fas fa-list"></i> My Listings <span class="bg"><?= count($listings) ?></span></button>
    <button class="mi" onclick="sv('bkgs',this)"><i class="fas fa-calendar-check"></i> Bookings <span class="bg"><?= count($bookings) ?></span></button>
    <button class="mi" onclick="sv('earn',this)"><i class="fas fa-rupee-sign"></i> Earnings</button>
    <div class="mlbl">Manage</div>
    <button class="mi" onclick="openAdd();setAM(this)"><i class="fas fa-upload"></i> Add Listing</button>
    <div class="mlbl">Account</div>
    <a href="php/logout.php" class="mi"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
  <div class="sb-bot">
    <div class="uav"><?= strtoupper(substr($vendor['full_name'],0,1)) ?></div>
    <div class="ui"><strong><?= htmlspecialchars($vendor['full_name']) ?></strong><span><?= htmlspecialchars($vendor['business_name']) ?></span></div>
  </div>
</div>

<div class="main">
  <!-- DASHBOARD -->
  <div class="view act" id="view-dash">
    <div class="topbar">
      <div><h2>Vendor Dashboard</h2><p>Welcome back, <?= htmlspecialchars($vendor['full_name']) ?>!</p></div>
      <button class="btn-add" onclick="openAdd()"><i class="fas fa-plus"></i> Add New Listing</button>
    </div>
    <div class="stats">
      <div class="sc sc1"><div class="si"><i class="fas fa-rupee-sign"></i></div><div class="sv">₹<?= number_format($st['month_earned'],2) ?></div><div class="sl">Earnings (Month)</div></div>
      <div class="sc sc2"><div class="si"><i class="fas fa-calendar-check"></i></div><div class="sv"><?= $st['total_bookings'] ?></div><div class="sl">Total Bookings</div></div>
      <div class="sc sc3"><div class="si"><i class="fas fa-boxes"></i></div><div class="sv"><?= count($listings) ?></div><div class="sl">My Listings</div></div>
      <div class="sc sc4"><div class="si"><i class="fas fa-star"></i></div><div class="sv">₹<?= number_format($st['total_earned'],2) ?></div><div class="sl">Total Earned</div></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:18px">
      <div class="crd">
        <div class="ch"><h4>Recent Bookings</h4></div>
        <?php if(empty($bookings)): ?>
          <div style="padding:28px;text-align:center;color:var(--m)"><i class="fas fa-calendar" style="font-size:2rem;opacity:.2;display:block;margin-bottom:10px"></i>No bookings yet.</div>
        <?php else: ?>
        <table><thead><tr><th>Booking Code</th><th>Customer</th><th>Product</th><th>Dates</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach(array_slice($bookings,0,5) as $b): ?>
          <tr>
            <td><strong><?= htmlspecialchars($b['booking_code']) ?></strong></td>
            <td><?= htmlspecialchars($b['customer_name']) ?></td>
            <td><?= htmlspecialchars($b['listing_title']) ?></td>
            <td><?= $b['start_date'] ?> to <?= $b['end_date'] ?></td>
            <td><strong>₹<?= number_format($b['total_amount'],2) ?></strong></td>
            <td><span class="pill <?= $b['status'] ?>"><?= $b['status'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody></table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MY LISTINGS — shows real images from DB -->
  <div class="view" id="view-mylist">
    <div class="topbar"><div><h2>My Listings</h2><p>All your uploaded products with images and details.</p></div><button class="btn-add" onclick="openAdd()"><i class="fas fa-plus"></i> Add Listing</button></div>
    <?php if(empty($listings)): ?>
      <div class="empty"><i class="fas fa-boxes"></i><p>No listings yet. Click "Add Listing" to get started.</p><button class="btn-add" style="display:inline-flex;margin:0 auto" onclick="openAdd()"><i class="fas fa-plus"></i> Add Listing</button></div>
    <?php else: ?>
    <div class="mlg">
      <?php foreach($listings as $l):
        $cover = $l['cover_image'] ?? '';
        $loc   = array_filter([$l['area'],$l['city']]);
        $icons = ['bike'=>'fa-motorcycle','car'=>'fa-car','laptop'=>'fa-laptop','camera'=>'fa-camera'];
        $ico   = $icons[$l['cat_name']] ?? 'fa-tag';
        $sbCls = $l['status']==='approved'?'avail':($l['status']==='rejected'?'rej':'pend');
      ?>
      <div class="mlc">
        <div class="mlw">
          <?php if($cover): ?>
            <img src="<?= htmlspecialchars($cover) ?>" id="mi-<?= $l['id'] ?>" alt="<?= htmlspecialchars($l['title']) ?>"/>
          <?php else: ?>
            <div class="mlph"><i class="fas <?= $ico ?>"></i></div>
          <?php endif; ?>
          <div class="mlsb <?= $sbCls ?>"><?= ucfirst($l['status']) ?></div>
          <?php if(count($l['images'])>1): ?>
            <div class="mlcnt"><i class="fas fa-images"></i> <?= count($l['images']) ?></div>
          <?php endif; ?>
        </div>
        <?php if(count($l['images'])>1): ?>
        <div class="mlthu">
          <?php foreach($l['images'] as $idx=>$img): ?>
            <div class="mlth <?= $idx===0?'act':'' ?>" onclick="swTh(<?= $l['id'] ?>,'<?= htmlspecialchars($img['image_path']) ?>',this)">
              <img src="<?= htmlspecialchars($img['image_path']) ?>"/>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="mlb">
          <div class="mlcat"><i class="fas <?= $ico ?>"></i> <?= ucfirst($l['cat_name']) ?></div>
          <h4><?= htmlspecialchars($l['title']) ?></h4>
          <?php if($loc): ?><div class="mlloc"><i class="fas fa-map-marker-alt"></i><?= implode(', ',$loc) ?></div><?php endif; ?>
          <?php if($l['description']): ?><div class="mldesc"><?= htmlspecialchars($l['description']) ?></div><?php endif; ?>
          <div class="mldr">
            <div class="mldb"><div class="ll">Price/Day</div><div class="vv p">₹<?= number_format($l['price_per_day'],2) ?></div></div>
            <div class="mldb"><div class="ll">Deposit</div><div class="vv">₹<?= number_format($l['security_deposit'],2) ?></div></div>
          </div>
          <div class="mlf">
            <button class="bedit"><i class="fas fa-edit"></i> Edit</button>
            <form method="POST" action="php/delete_listing.php" style="margin:0" onsubmit="return confirm('Delete this listing?')">
              <input type="hidden" name="listing_id" value="<?= $l['id'] ?>"/>
              <button type="submit" class="bdel"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- BOOKINGS -->
  <div class="view" id="view-bkgs">
    <div class="topbar"><div><h2>All Bookings</h2></div></div>
    <div class="crd">
      <?php if(empty($bookings)): ?>
        <div style="padding:28px;text-align:center;color:var(--m)">No bookings yet.</div>
      <?php else: ?>
      <table><thead><tr><th>Code</th><th>Customer</th><th>Product</th><th>Start</th><th>End</th><th>Days</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($bookings as $b): ?>
        <tr>
          <td><strong><?= htmlspecialchars($b['booking_code']) ?></strong></td>
          <td><?= htmlspecialchars($b['customer_name']) ?></td>
          <td><?= htmlspecialchars($b['listing_title']) ?></td>
          <td><?= $b['start_date'] ?></td>
          <td><?= $b['end_date'] ?></td>
          <td><?= $b['total_days'] ?></td>
          <td><strong>₹<?= number_format($b['total_amount'],2) ?></strong></td>
          <td><span class="pill <?= $b['status'] ?>"><?= $b['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
    </div>
  </div>

  <!-- EARNINGS -->
  <div class="view" id="view-earn">
    <div class="topbar"><div><h2>Earnings</h2></div></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px">
      <div class="sc sc1"><div class="si"><i class="fas fa-rupee-sign"></i></div><div class="sv">₹<?= number_format($st['month_earned'],2) ?></div><div class="sl">This Month (After Commission)</div></div>
      <div class="sc sc2"><div class="si"><i class="fas fa-calendar"></i></div><div class="sv">₹<?= number_format($st['total_earned'],2) ?></div><div class="sl">Total Lifetime Earnings</div></div>
      <div class="sc sc4"><div class="si"><i class="fas fa-calendar-check"></i></div><div class="sv"><?= $st['total_bookings'] ?></div><div class="sl">Total Completed Bookings</div></div>
    </div>
    <div class="crd">
      <?php if(empty($bookings)): ?>
        <div style="padding:28px;text-align:center;color:var(--m)">No transaction history yet.</div>
      <?php else: ?>
      <table><thead><tr><th>Date</th><th>Booking Code</th><th>Product</th><th>Subtotal</th><th>Commission (10%)</th><th>Your Payout</th></tr></thead>
      <tbody>
        <?php foreach($bookings as $b): if($b['status']==='cancelled')continue; ?>
        <tr>
          <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
          <td><strong><?= htmlspecialchars($b['booking_code']) ?></strong></td>
          <td><?= htmlspecialchars($b['listing_title']) ?></td>
          <td>₹<?= number_format($b['subtotal'],2) ?></td>
          <td>₹<?= number_format($b['platform_fee'],2) ?></td>
          <td><strong style="color:var(--g)">₹<?= number_format($b['vendor_payout'],2) ?></strong></td>
        </tr>
        <?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ADD LISTING MODAL -->
<div class="mo" id="addMo" onclick="co(event,'addMo')">
  <div class="mbox">
    <div class="mhd"><h3><i class="fas fa-upload" style="color:var(--primary);margin-right:6px"></i>Add New Listing</h3><button class="xcb" onclick="closeAdd()"><i class="fas fa-times"></i></button></div>
    <div class="mbody" id="aform">
      <form id="listingForm" enctype="multipart/form-data">
        <div class="fg"><label>Category <span class="rq">*</span></label>
          <select name="category_id" id="mcat" required>
            <option value="">-- Select Category --</option>
            <?php foreach($cats as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= ucfirst($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Product Title <span class="rq">*</span></label><input type="text" name="title" id="mtitle" placeholder="e.g. Royal Enfield Classic 350 – 2022 Model" required/></div>
        <div class="r2">
          <div class="fg"><label>Price / Day (₹) <span class="rq">*</span></label><input type="number" name="price_per_day" id="mprice" placeholder="499" min="1" required/></div>
          <div class="fg"><label>Security Deposit (₹)</label><input type="number" name="security_deposit" id="mdeposit" placeholder="2000" min="0"/></div>
        </div>
        <div class="r2">
          <div class="fg"><label>City</label><input type="text" name="city" id="mcity" placeholder="Chennai"/></div>
          <div class="fg"><label>Area</label><input type="text" name="area" id="marea" placeholder="Anna Nagar"/></div>
        </div>
        <div class="fg"><label>Description</label><textarea name="description" id="mdesc" placeholder="Year, condition, features, accessories, pickup rules…"></textarea></div>
        <div class="fg">
          <label>Product Images <span class="rq">*</span></label>
          <div class="uz" id="uz" onclick="document.getElementById('ifi').click()" ondragover="dov(event)" ondragleave="dlv()" ondrop="drp(event)">
            <span class="uzi"><i class="fas fa-cloud-upload-alt" id="uzi"></i></span>
            <p id="uzt">Click to upload or drag &amp; drop photos</p>
            <small>JPG · PNG · WEBP · Max 5MB · Multiple allowed</small>
            <input type="file" id="ifi" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple/>
          </div>
          <div class="upbar" id="upbar"><div class="upfill" id="upfill" style="width:0%"></div></div>
          <div class="pg" id="pg"></div>
          <div class="upinfo" id="upinfo" style="display:none"><span id="ucount">0</span> image(s) selected · First = cover photo</div>
        </div>
        <button type="button" class="bsave" onclick="submitListing()"><i class="fas fa-check-circle"></i> Save &amp; Submit for Review</button>
      </form>
    </div>
    <div class="sp" id="sp">
      <div class="spc"><i class="fas fa-check"></i></div>
      <h4>Listing Submitted! 🎉</h4>
      <p>Your product is submitted for admin review.<br/>It will go live within 24 hours.</p>
      <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
        <button class="bano" onclick="resetAdd()">Add Another</button>
        <button class="bano" style="background:var(--sec)" onclick="closeAdd();sv('mylist',null);location.reload()">View My Listings</button>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toastEl"><i class="fas fa-check-circle"></i><span id="tmsg"></span></div>

<script>
function sv(n,btn){
  document.querySelectorAll('.view').forEach(v=>v.classList.remove('act'));
  document.getElementById('view-'+n).classList.add('act');
  if(btn){document.querySelectorAll('.mi').forEach(m=>m.classList.remove('act'));btn.classList.add('act');}
}
function setAM(btn){document.querySelectorAll('.mi').forEach(m=>m.classList.remove('act'));btn.classList.add('act');}
function swTh(lid,src,el){
  const img=document.getElementById('mi-'+lid);
  if(img) img.src=src;
  const card=el.closest('.mlc');
  if(card) card.querySelectorAll('.mlth').forEach(t=>t.classList.toggle('act',t===el));
}

// ── IMAGE PREVIEWS (base64 for instant preview) ──────────
let pending=[];
document.getElementById('ifi').addEventListener('change',function(){readFiles(this.files);this.value='';});
function readFiles(files){
  if(!files||!files.length)return;
  const arr=Array.from(files);
  document.getElementById('upbar').style.display='block';
  let done=0;
  arr.forEach(f=>{
    if(f.size>5*1024*1024){toast('File too large: '+f.name);done++;chk();return;}
    if(!f.type.startsWith('image/')){toast('Not an image: '+f.name);done++;chk();return;}
    const r=new FileReader();
    r.onload=e=>{pending.push({b64:e.target.result,file:f,name:f.name});done++;document.getElementById('upfill').style.width=(done/arr.length*100)+'%';chk();};
    r.onerror=()=>{done++;chk();};
    r.readAsDataURL(f);
  });
  function chk(){if(done===arr.length){setTimeout(()=>{document.getElementById('upbar').style.display='none';document.getElementById('upfill').style.width='0%';},400);renderPrev();}}
}
function renderPrev(){
  const g=document.getElementById('pg');
  g.innerHTML=pending.map((p,i)=>`<div class="pi ${i===0?'cov':''}" onclick="setCov(${i})"><img src="${p.b64}"/>${i===0?'<div class="cl">Cover</div>':''}<button class="rb" onclick="rmImg(${i},event)"><i class="fas fa-times"></i></button></div>`).join('');
  const info=document.getElementById('upinfo');
  info.style.display=pending.length?'block':'none';
  document.getElementById('ucount').textContent=pending.length;
  if(pending.length){document.getElementById('uzi').className='fas fa-plus-circle';document.getElementById('uzt').textContent='Click to add more photos';}
  else{document.getElementById('uzi').className='fas fa-cloud-upload-alt';document.getElementById('uzt').textContent='Click to upload or drag & drop photos';}
}
function rmImg(i,e){e.stopPropagation();pending.splice(i,1);renderPrev();}
function setCov(i){if(!i)return;pending.unshift(pending.splice(i,1)[0]);renderPrev();toast('Cover photo updated!');}
function dov(e){e.preventDefault();document.getElementById('uz').classList.add('dv');}
function dlv(){document.getElementById('uz').classList.remove('dv');}
function drp(e){e.preventDefault();document.getElementById('uz').classList.remove('dv');readFiles(e.dataTransfer.files);}

// ── SUBMIT LISTING via AJAX with real files ───────────────
function submitListing(){
  const cat=document.getElementById('mcat').value;
  const title=document.getElementById('mtitle').value.trim();
  const price=document.getElementById('mprice').value;
  if(!cat){toast('⚠️ Select a category');return;}
  if(!title){toast('⚠️ Enter product title');return;}
  if(!price){toast('⚠️ Enter price per day');return;}
  if(!pending.length){toast('⚠️ Upload at least one image');return;}

  const btn=document.querySelector('.bsave');
  btn.disabled=true;btn.textContent='Saving…';

  const fd=new FormData();
  fd.append('category_id',cat);
  fd.append('title',title);
  fd.append('price_per_day',price);
  fd.append('security_deposit',document.getElementById('mdeposit').value||0);
  fd.append('city',document.getElementById('mcity').value);
  fd.append('area',document.getElementById('marea').value);
  fd.append('description',document.getElementById('mdesc').value);
  // Append real files (not base64) — PHP will receive them as $_FILES
  pending.forEach(p=>fd.append('images[]',p.file));

  fetch('php/add_listing.php',{method:'POST',body:fd})
  .then(r=>r.json())
  .then(data=>{
    btn.disabled=false;btn.innerHTML='<i class="fas fa-check-circle"></i> Save & Submit for Review';
    if(data.success){
      document.getElementById('aform').style.display='none';
      document.getElementById('sp').classList.add('show');
      toast('Listing submitted successfully!');
    } else {
      toast('Error: '+data.message);
    }
  })
  .catch(()=>{btn.disabled=false;toast('Network error. Check XAMPP is running.');});
}

function resetAdd(){
  document.getElementById('aform').style.display='block';
  document.getElementById('sp').classList.remove('show');
  document.getElementById('listingForm').reset();
  pending=[];renderPrev();
}
function openAdd(){resetAdd();document.getElementById('addMo').classList.add('open');}
function closeAdd(){document.getElementById('addMo').classList.remove('open');}
function co(e,id){if(e.target.id===id)document.getElementById(id).classList.remove('open');}

let _t;
function toast(msg){clearTimeout(_t);document.getElementById('tmsg').textContent=msg;document.getElementById('toastEl').classList.add('show');_t=setTimeout(()=>document.getElementById('toastEl').classList.remove('show'),3400);}
</script>
</body>
</html>
