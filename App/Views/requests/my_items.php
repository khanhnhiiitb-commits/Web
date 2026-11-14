<?php
$title = 'Quản lý đồ nhận - Give & Take';

if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('itemImageUrl')) {
    function itemImageUrl($image): string {
        $image = trim((string)($image ?? ''));
        if ($image === '') return 'public/no-image.jpg';
        if (preg_match('~^https?://~i', $image)) return $image;
        $image = ltrim(str_replace('\\', '/', $image), '/');
        if (strpos($image, 'public/') === 0) return $image;
        if (strpos($image, 'uploads/items/') === 0) return 'public/' . $image;
        return 'public/uploads/items/' . basename($image);
    }
}

// [class badge, chữ hiển thị, icon] theo trạng thái yêu cầu
$statusMap = [
    'pending'   => ['bg-warning text-dark', 'Đang chờ duyệt',    'fa-hourglass-half'],
    'accepted'  => ['bg-success',           'Đã được chấp nhận', 'fa-circle-check'],
    'rejected'  => ['bg-danger',            'Đã bị từ chối',     'fa-circle-xmark'],
    'completed' => ['bg-success',           'Đã hoàn thành',     'fa-flag-checkered'],
    'cancelled' => ['bg-secondary',         'Đã hủy',            'fa-ban'],
];

// Tiến trình: số mốc đã đạt (pending=1, accepted=2, completed=3); 0 = dừng giữa chừng
$stepOf = ['pending' => 1, 'accepted' => 2, 'completed' => 3];

// Thống kê cho thanh số liệu và tab lọc
$requests = $requests ?? [];
$total    = count($requests);
$counts   = [];
foreach ($requests as $r) {
    $k = $r['request_status'] ?? '';
    $counts[$k] = ($counts[$k] ?? 0) + 1;
}
$tabLabels = [
    'pending'   => 'Đang chờ duyệt',
    'accepted'  => 'Đã được chấp nhận',
    'completed' => 'Đã hoàn thành',
    'rejected'  => 'Bị từ chối',
    'cancelled' => 'Đã hủy',
];
?>

<style>
    /* ===== Nền trang: gradient + chấm bi mờ hai bên ===== */
    body {
        background:
            radial-gradient(ellipse 560px 460px at 0% 16%, rgba(25, 135, 84, .18), transparent 70%),
            radial-gradient(ellipse 560px 500px at 100% 58%, rgba(255, 193, 7, .20), transparent 70%),
            radial-gradient(ellipse 460px 380px at 100% 6%, rgba(25, 135, 84, .12), transparent 70%),
            radial-gradient(ellipse 460px 380px at 0% 94%, rgba(25, 135, 84, .12), transparent 70%),
            #f4f9f6;
        background-attachment: fixed;
    }
    body::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background-image: radial-gradient(rgba(25, 135, 84, .22) 1.5px, transparent 1.5px);
        background-size: 24px 24px;
        -webkit-mask-image: linear-gradient(90deg, #000 0, transparent 22%, transparent 78%, #000 100%);
        mask-image: linear-gradient(90deg, #000 0, transparent 22%, transparent 78%, #000 100%);
    }
    .mr-side {
        position: fixed;
        top: 130px;
        z-index: 0;
        pointer-events: none;
        display: none;
        width: 230px;
    }
    .mr-side-left  { left: 1.5%; }
    .mr-side-right { right: 1.5%; }
    @media (min-width: 1500px) { .mr-side { display: block; } }
    .mr-page { position: relative; z-index: 1; }

    /* ===== Banner đầu trang ===== */
    .mr-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 2rem 2.25rem;
        color: #fff;
        background: linear-gradient(120deg, #146c43 0%, #198754 55%, #2fb37a 100%);
        box-shadow: 0 12px 32px rgba(25, 135, 84, .28);
    }
    .mr-hero::before, .mr-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, .09);
    }
    .mr-hero::before { width: 260px; height: 260px; right: -60px; top: -90px; }
    .mr-hero::after  { width: 170px; height: 170px; right: 150px; bottom: -90px; background: rgba(255, 193, 7, .20); }
    .mr-hero-icon {
        position: absolute;
        right: 3rem;
        top: 50%;
        transform: translateY(-50%) rotate(-10deg);
        font-size: 7rem;
        color: rgba(255, 255, 255, .16);
    }
    .mr-hero-body { position: relative; z-index: 1; max-width: 600px; }
    .mr-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .25rem .8rem;
        border-radius: 50rem;
        background: rgba(255, 255, 255, .18);
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: .75rem;
    }
    .mr-hero h2 { font-weight: 800; font-size: 1.9rem; margin-bottom: .35rem; }
    .mr-hero p  { color: rgba(255, 255, 255, .88); margin-bottom: 1.1rem; }
    .mr-cta {
        background: #ffd666;
        color: #5a4300;
        font-weight: 700;
        border: 0;
        border-radius: 50rem;
        padding: .55rem 1.3rem;
        box-shadow: 0 6px 16px rgba(0, 0, 0, .15);
        transition: transform .15s ease;
    }
    .mr-cta:hover { background: #ffcd45; color: #5a4300; transform: translateY(-2px); }

    /* ===== Số liệu ===== */
    .mr-stat {
        display: flex;
        align-items: center;
        gap: .85rem;
        background: #fff;
        border: 1px solid #e6efe9;
        border-radius: 1rem;
        padding: .9rem 1rem;
        box-shadow: 0 4px 16px rgba(20, 60, 40, .05);
    }
    .mr-stat-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .85rem;
        font-size: 1.15rem;
    }
    .mr-stat-num   { font-size: 1.4rem; font-weight: 800; line-height: 1.1; color: #212529; }
    .mr-stat-label { font-size: .8rem; color: #6c7a72; }
    .ic-all  { background: #e1f3e8; color: #198754; }
    .ic-wait { background: #fff3cd; color: #b58100; }
    .ic-ok   { background: #d9f2e4; color: #0f8a4f; }
    .ic-no   { background: #fde4e7; color: #c4384a; }

    /* ===== Tab + tìm kiếm ===== */
    .mr-toolbar {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        background: #fff;
        border: 1px solid #e6efe9;
        border-radius: 1rem;
        padding: .6rem .75rem;
        box-shadow: 0 4px 16px rgba(20, 60, 40, .05);
    }
    .mr-tabs { display: flex; gap: .4rem; overflow-x: auto; flex: 1 1 auto; scrollbar-width: none; }
    .mr-tabs::-webkit-scrollbar { display: none; }
    .mr-tab {
        border: 0;
        background: transparent;
        color: #52605a;
        font-weight: 600;
        font-size: .87rem;
        padding: .5rem .95rem;
        border-radius: 50rem;
        white-space: nowrap;
        transition: background-color .15s ease, color .15s ease;
    }
    .mr-tab:hover { background: #eef7f1; color: #146c43; }
    .mr-tab.active { background: #198754; color: #fff; box-shadow: 0 4px 10px rgba(25, 135, 84, .3); }
    .mr-tab .mr-count {
        display: inline-block;
        min-width: 1.4rem;
        margin-left: .3rem;
        padding: 0 .4rem;
        border-radius: 50rem;
        background: rgba(0, 0, 0, .07);
        font-size: .75rem;
    }
    .mr-tab.active .mr-count { background: rgba(255, 255, 255, .25); }
    .mr-search { position: relative; flex: 0 1 260px; min-width: 190px; }
    .mr-search i { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: #8a948f; font-size: .85rem; }
    .mr-search input {
        width: 100%;
        border: 1px solid #d3ebdc;
        background: #f7fbf9;
        border-radius: 50rem;
        padding: .5rem 1rem .5rem 2.3rem;
        font-size: .88rem;
        outline: none;
    }
    .mr-search input:focus { border-color: #198754; box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .18); background: #fff; }

    /* ===== Thẻ yêu cầu ===== */
    .mr-card {
        background: #fff;
        border: 1px solid #e6efe9;
        border-radius: 1.1rem;
        box-shadow: 0 4px 18px rgba(20, 60, 40, .06);
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .mr-card:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(20, 60, 40, .14); border-color: #bfdccb; }
    .mr-thumb {
        position: relative;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background: linear-gradient(135deg, #dff2e6 0%, #f3faf6 100%);
    }
    .mr-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .35s ease;
    }
    .mr-card:hover .mr-thumb img { transform: scale(1.06); }
    .mr-thumb::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        height: 45%;
        background: linear-gradient(to top, rgba(0, 0, 0, .28), transparent);
        pointer-events: none;
    }
    .mr-status {
        position: absolute;
        top: .75rem;
        left: .75rem;
        z-index: 2;
        font-weight: 600;
        font-size: .74rem;
        padding: .42rem .75rem;
        box-shadow: 0 3px 10px rgba(0, 0, 0, .18);
    }
    .mr-cat {
        position: absolute;
        left: .75rem;
        bottom: .7rem;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .22rem .65rem;
        background: rgba(255, 255, 255, .92);
        color: #146c43;
        border-radius: 50rem;
        font-size: .75rem;
        font-weight: 600;
    }
    .mr-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: #212529;
        margin: 0 0 .7rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Thanh tiến trình 3 mốc */
    .mr-steps { display: flex; margin-bottom: .9rem; }
    .mr-step { position: relative; flex: 1; text-align: center; font-size: .7rem; color: #8a948f; }
    .mr-step::before {
        content: "";
        position: absolute;
        top: 10px;
        left: -50%;
        width: 100%;
        height: 3px;
        background: #dfe9e3;
    }
    .mr-step:first-child::before { display: none; }
    .mr-dot {
        position: relative;
        z-index: 1;
        width: 22px;
        height: 22px;
        margin: 0 auto .25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #dfe9e3;
        color: #fff;
        font-size: .65rem;
    }
    .mr-step.done { color: #146c43; font-weight: 600; }
    .mr-step.done::before { background: #198754; }
    .mr-step.done .mr-dot { background: #198754; }
    .mr-step.stop { color: #c4384a; font-weight: 600; }
    .mr-step.stop::before { background: #e8a0a9; }
    .mr-step.stop .mr-dot { background: #dc3545; }
    .mr-step.stop.cancel { color: #6c757d; }
    .mr-step.stop.cancel::before { background: #c8cfd4; }
    .mr-step.stop.cancel .mr-dot { background: #8a949c; }

    .mr-info { font-size: .84rem; color: #6c7a72; margin-bottom: .45rem; display: flex; gap: .5rem; align-items: flex-start; }
    .mr-info i { width: 1rem; text-align: center; color: #198754; margin-top: .2rem; flex: 0 0 1rem; }
    .mr-info .mr-v { color: #212529; }
    .mr-quote {
        padding: .4rem .7rem;
        background: #f7f9f8;
        border-left: 3px solid #a9d9bd;
        border-radius: .25rem .6rem .6rem .25rem;
        color: #5c6762;
        font-style: italic;
        font-size: .85rem;
        margin-bottom: .6rem;
        word-break: break-word;
    }
    .mr-card .btn {
        border-radius: 50rem;
        font-weight: 600;
        font-size: .85rem;
        transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .mr-card .btn:focus-visible { box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .35); }
    .mr-gone {
        font-size: .8rem;
        color: #6c7a72;
        background: #f3f5f4;
        border-radius: .6rem;
        padding: .5rem .75rem;
        text-align: center;
    }

    .mr-empty {
        background: #fff;
        border: 1px dashed #bfdccb;
        border-radius: 1rem;
        padding: 3.5rem 1.5rem;
        text-align: center;
    }
    .mr-empty-icon {
        width: 88px;
        height: 88px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eef7f1;
        font-size: 2.6rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 767.98px) {
        .mr-hero { padding: 1.4rem 1.25rem; }
        .mr-hero h2 { font-size: 1.5rem; }
        .mr-hero-icon { font-size: 4.5rem; right: 1rem; }
        .mr-search { flex-basis: 100%; }
    }
</style>

<!-- Tranh minh họa hai bên -->
<svg class="mr-side mr-side-left" viewBox="0 0 230 440" aria-hidden="true">
    <circle cx="105" cy="120" r="92" fill="#198754" opacity=".10"/>
    <circle cx="40" cy="250" r="22" fill="#ffc107" opacity=".25"/>
    <g transform="translate(60 40)">
        <path d="M60 170 C58 120 60 80 62 40" stroke="#198754" stroke-width="5" fill="none" stroke-linecap="round" opacity=".55"/>
        <ellipse cx="38" cy="78" rx="28" ry="13" transform="rotate(-35 38 78)" fill="#198754" opacity=".45"/>
        <ellipse cx="86" cy="62" rx="28" ry="13" transform="rotate(35 86 62)" fill="#2fb37a" opacity=".5"/>
        <ellipse cx="40" cy="120" rx="26" ry="12" transform="rotate(-30 40 120)" fill="#2fb37a" opacity=".45"/>
        <ellipse cx="84" cy="108" rx="26" ry="12" transform="rotate(30 84 108)" fill="#198754" opacity=".45"/>
        <ellipse cx="62" cy="34" rx="12" ry="24" fill="#198754" opacity=".5"/>
        <path d="M30 170 H92 L84 212 H38 Z" fill="#ffc107" opacity=".55"/>
    </g>
    <g opacity=".6">
        <rect x="30" y="300" width="100" height="62" rx="6" fill="#a9d9bd"/>
        <rect x="74" y="300" width="12" height="62" fill="#fff" opacity=".6"/>
        <rect x="92" y="244" width="84" height="56" rx="6" fill="#ffd666"/>
        <rect x="128" y="244" width="12" height="56" fill="#fff" opacity=".6"/>
        <rect x="46" y="372" width="120" height="48" rx="6" fill="#7cc79d"/>
        <rect x="98" y="372" width="12" height="48" fill="#fff" opacity=".6"/>
    </g>
</svg>
<svg class="mr-side mr-side-right" viewBox="0 0 230 440" aria-hidden="true">
    <circle cx="125" cy="130" r="92" fill="#ffc107" opacity=".16"/>
    <circle cx="190" cy="290" r="26" fill="#198754" opacity=".12"/>
    <g transform="translate(40 70)" opacity=".7">
        <rect x="10" y="62" width="130" height="96" rx="8" fill="#198754"/>
        <rect x="0" y="40" width="150" height="34" rx="8" fill="#2fb37a"/>
        <rect x="64" y="40" width="22" height="118" fill="#ffd666"/>
        <path d="M75 40 C45 0 12 18 36 40 Z" fill="#ffd666"/>
        <path d="M75 40 C105 0 138 18 114 40 Z" fill="#ffc107"/>
    </g>
    <g transform="translate(115 330)" opacity=".5" fill="none" stroke="#198754" stroke-width="9" stroke-linecap="round">
        <path d="M-52 -4 A52 52 0 0 1 20 -48"/>
        <path d="M48 -26 A52 52 0 0 1 28 44"/>
        <path d="M-6 54 A52 52 0 0 1 -48 20"/>
        <path d="M8 -62 L22 -46 L2 -38 Z" fill="#198754" stroke="none"/>
        <path d="M58 -30 L48 -12 L66 -14 Z" fill="#198754" stroke="none"/>
        <path d="M-52 24 L-40 6 L-58 10 Z" fill="#198754" stroke="none"/>
    </g>
    <path d="M30 250 c-14 -16 8 -34 20 -18 c12 -16 34 2 20 18 l-20 20 Z" fill="#e8576b" opacity=".35"/>
</svg>

<div class="container-xl py-4 mr-page">

    <!-- Banner -->
    <div class="mr-hero mb-4">
        <i class="fa-solid fa-hand-holding-heart mr-hero-icon" aria-hidden="true"></i>
        <div class="mr-hero-body">
            <span class="mr-kicker"><i class="fa-solid fa-recycle"></i> Give &amp; Take</span>
            <h2>Đồ tôi đăng ký nhận</h2>
            <p>Theo dõi trạng thái các món đồ bạn đã gửi yêu cầu xin từ cộng đồng.</p>
            <a href="index.php?controller=Search&amp;action=index" class="btn mr-cta">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm thêm đồ khác
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['request_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 border-start border-4 border-success shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= e($_SESSION['request_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['request_message']); ?>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="mr-empty">
            <div class="mr-empty-icon">📦</div>
            <h5 class="text-secondary fw-semibold">Bạn chưa đăng ký nhận món đồ nào.</h5>
            <p class="text-muted small">Hãy khám phá các món đồ đang được chia sẻ và gửi yêu cầu nhận nhé!</p>
            <a href="index.php?controller=Search&amp;action=index" class="btn btn-success btn-sm rounded-pill px-3 mt-2">
                <i class="fa-solid fa-compass me-1"></i> Khám phá ngay
            </a>
        </div>
    <?php else: ?>

        <!-- Số liệu -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="mr-stat">
                    <span class="mr-stat-icon ic-all"><i class="fa-solid fa-paper-plane"></i></span>
                    <div><div class="mr-stat-num"><?= $total ?></div><div class="mr-stat-label">Yêu cầu đã gửi</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mr-stat">
                    <span class="mr-stat-icon ic-wait"><i class="fa-solid fa-hourglass-half"></i></span>
                    <div><div class="mr-stat-num"><?= (int)($counts['pending'] ?? 0) ?></div><div class="mr-stat-label">Đang chờ duyệt</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mr-stat">
                    <span class="mr-stat-icon ic-ok"><i class="fa-solid fa-circle-check"></i></span>
                    <div><div class="mr-stat-num"><?= (int)(($counts['accepted'] ?? 0) + ($counts['completed'] ?? 0)) ?></div><div class="mr-stat-label">Được chấp nhận / hoàn thành</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mr-stat">
                    <span class="mr-stat-icon ic-no"><i class="fa-solid fa-circle-xmark"></i></span>
                    <div><div class="mr-stat-num"><?= (int)(($counts['rejected'] ?? 0) + ($counts['cancelled'] ?? 0)) ?></div><div class="mr-stat-label">Bị từ chối / đã hủy</div></div>
                </div>
            </div>
        </div>

        <!-- Tab lọc + tìm kiếm -->
        <div class="mr-toolbar mb-4">
            <div class="mr-tabs" role="tablist">
                <button type="button" class="mr-tab active" data-filter="all">Tất cả<span class="mr-count"><?= $total ?></span></button>
                <?php foreach ($tabLabels as $key => $label): ?>
                    <?php if (!empty($counts[$key])): ?>
                        <button type="button" class="mr-tab" data-filter="<?= e($key) ?>"><?= e($label) ?><span class="mr-count"><?= (int)$counts[$key] ?></span></button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <label class="mr-search mb-0">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="mrSearch" placeholder="Tìm theo tên đồ, người tặng..." autocomplete="off">
            </label>
        </div>

        <!-- Lưới yêu cầu -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="mrGrid">
            <?php foreach ($requests as $req): ?>
                <?php
                    [$statusClass, $statusText, $statusIcon] = $statusMap[$req['request_status']] ?? ['bg-secondary', 'Không xác định', 'fa-circle-question'];
                    $stillAvailable = ($req['item_status'] ?? '') === 'available';
                    $stepNow = $stepOf[$req['request_status'] ?? ''] ?? 0;
                    $stopped = ($stepNow === 0);
                    $done    = $stopped ? 1 : $stepNow;
                    $stopCls = (($req['request_status'] ?? '') === 'cancelled') ? 'stop cancel' : 'stop';
                    $stepLabels = ['Đã gửi', 'Được chấp nhận', 'Hoàn thành'];
                ?>
                <div class="col mr-col"
                     data-status="<?= e($req['request_status'] ?? '') ?>"
                     data-title="<?= e(($req['item_title'] ?? '') . ' ' . ($req['owner_name'] ?? '') . ' ' . ($req['category_name'] ?? '')) ?>">
                    <div class="mr-card h-100 d-flex flex-column">
                        <div class="mr-thumb">
                            <img src="<?= e(itemImageUrl($req['image_url'] ?? '')) ?>"
                                 alt="<?= e($req['item_title']) ?>" loading="lazy"
                                 onerror="this.onerror=null;this.src='public/no-image.jpg';">
                            <span class="badge rounded-pill mr-status <?= $statusClass ?>">
                                <i class="fa-solid <?= $statusIcon ?> me-1"></i><?= $statusText ?>
                            </span>
                            <span class="mr-cat"><i class="fa-solid fa-tag"></i> <?= e($req['category_name']) ?></span>
                        </div>

                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <h5 class="mr-title" title="<?= e($req['item_title']) ?>"><?= e($req['item_title']) ?></h5>

                            <div class="mr-steps">
                                <?php foreach ($stepLabels as $i => $lb): $n = $i + 1; ?>
                                    <?php
                                        $cls = '';
                                        if ($stopped && $n === 2) { $cls = $stopCls; }
                                        elseif ($n <= $done && !($stopped && $n > 1)) { $cls = 'done'; }
                                    ?>
                                    <div class="mr-step <?= $cls ?>">
                                        <span class="mr-dot">
                                            <i class="fa-solid <?= (strpos($cls, 'stop') !== false) ? 'fa-xmark' : (($cls === 'done') ? 'fa-check' : 'fa-circle') ?>"></i>
                                        </span>
                                        <?= $stopped && $n === 2 ? e($req['request_status'] === 'cancelled' ? 'Đã hủy' : 'Bị từ chối') : $lb ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mr-info">
                                <i class="fa-solid fa-user"></i>
                                <span>Người đăng: <span class="mr-v"><?= e($req['owner_name']) ?></span></span>
                            </div>

                            <?php if (!empty($req['pickup_location'])): ?>
                                <div class="mr-info">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span class="mr-v"><?= e($req['pickup_location']) ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="mr-info">
                                <i class="fa-solid fa-clock"></i>
                                <span>Ngày gửi: <span class="mr-v"><?= e(date('d/m/Y H:i', strtotime($req['created_at']))) ?></span></span>
                            </div>

                            <div class="mr-quote">"<?= e($req['message'] ?: 'Không có lời nhắn') ?>"</div>

                            <a class="btn btn-outline-success btn-sm mb-2 w-100"
                               href="index.php?controller=Message&amp;action=chat&amp;id=<?= (int)$req['request_id'] ?>">
                                <i class="bi bi-chat-dots me-1"></i> Nhắn tin với người tặng
                            </a>
                            <div class="mt-auto pt-2">
                                <?php if ($stillAvailable): ?>
                                    <a href="index.php?controller=Item&amp;action=detail&amp;id=<?= (int)$req['item_id'] ?>"
                                       class="btn btn-success btn-sm w-100">
                                        Xem chi tiết đồ <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <div class="mr-gone">
                                        <i class="fa-solid fa-circle-info me-1"></i>
                                        Món đồ này không còn trong danh sách chia sẻ.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mr-empty mt-2 d-none" id="mrNoResult">
            <div class="mr-empty-icon">🔍</div>
            <h6 class="text-secondary fw-semibold mb-0">Không có yêu cầu nào phù hợp.</h6>
        </div>

        <script>
        (function () {
            var cols = document.querySelectorAll('.mr-col');
            var tabs = document.querySelectorAll('.mr-tab');
            var box  = document.getElementById('mrSearch');
            var none = document.getElementById('mrNoResult');
            var current = 'all';

            function norm(s) {
                return (s || '').toLowerCase().normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd');
            }
            function apply() {
                var q = norm(box.value.trim()), shown = 0;
                cols.forEach(function (c) {
                    var ok = (current === 'all' || c.dataset.status === current) &&
                             (q === '' || norm(c.dataset.title).indexOf(q) !== -1);
                    c.classList.toggle('d-none', !ok);
                    if (ok) shown++;
                });
                none.classList.toggle('d-none', shown !== 0);
            }
            tabs.forEach(function (t) {
                t.addEventListener('click', function () {
                    tabs.forEach(function (x) { x.classList.remove('active'); });
                    t.classList.add('active');
                    current = t.dataset.filter;
                    apply();
                });
            });
            box.addEventListener('input', apply);
        })();
        </script>
    <?php endif; ?>
</div>
