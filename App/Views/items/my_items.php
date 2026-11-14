<?php
$esc = static fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

// [class badge, chữ hiển thị, icon]
$statusNames = [
    'pending_review' => ['bg-warning text-dark', 'Chờ admin duyệt',  'fa-hourglass-half'],
    'available'      => ['bg-success',           'Đang chia sẻ',     'fa-share-nodes'],
    'reserved'       => ['bg-info text-dark',    'Đã có người nhận', 'fa-user-check'],
    'gifted'         => ['bg-secondary',         'Đã tặng',          'fa-gift'],
    'rejected'       => ['bg-danger',            'Bị từ chối',       'fa-circle-xmark'],
];

// Ảnh món đồ (nếu có cột image_url thì hiển thị, không thì dùng khung placeholder)
$itemImageUrl = static function ($image): string {
    $image = trim((string)($image ?? ''));
    if ($image === '') return '';
    if (preg_match('~^https?://~i', $image)) return $image;
    $image = ltrim(str_replace('\\', '/', $image), '/');
    if (strpos($image, 'public/') === 0) return $image;
    if (strpos($image, 'uploads/items/') === 0) return 'public/' . $image;
    return 'public/uploads/items/' . basename($image);
};

// Thống kê cho thanh số liệu và tab lọc
$items  = $items ?? [];
$total  = count($items);
$counts = [];
foreach ($items as $it) {
    $s = $it['status'] ?? '';
    $counts[$s] = ($counts[$s] ?? 0) + 1;
}
$tabLabels = [
    'available'      => 'Đang chia sẻ',
    'pending_review' => 'Chờ duyệt',
    'reserved'       => 'Đã có người nhận',
    'gifted'         => 'Đã tặng',
    'rejected'       => 'Bị từ chối',
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
    /* Tranh minh họa hai bên (chỉ hiện trên màn hình rộng) */
    .mi-side {
        position: fixed;
        top: 130px;
        z-index: 0;
        pointer-events: none;
        display: none;
        width: 230px;
    }
    .mi-side-left  { left: 1.5%; }
    .mi-side-right { right: 1.5%; }
    @media (min-width: 1500px) { .mi-side { display: block; } }
    .mi-page { position: relative; z-index: 1; }

    /* ===== Banner đầu trang ===== */
    .mi-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 2rem 2.25rem;
        color: #fff;
        background: linear-gradient(120deg, #146c43 0%, #198754 55%, #2fb37a 100%);
        box-shadow: 0 12px 32px rgba(25, 135, 84, .28);
    }
    .mi-hero::before, .mi-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, .09);
    }
    .mi-hero::before { width: 260px; height: 260px; right: -60px; top: -90px; }
    .mi-hero::after  { width: 170px; height: 170px; right: 150px; bottom: -90px; background: rgba(255, 193, 7, .20); }
    .mi-hero-icon {
        position: absolute;
        right: 3rem;
        top: 50%;
        transform: translateY(-50%) rotate(-10deg);
        font-size: 7rem;
        color: rgba(255, 255, 255, .16);
    }
    .mi-hero-body { position: relative; z-index: 1; max-width: 560px; }
    .mi-kicker {
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
    .mi-hero h2 { font-weight: 800; font-size: 1.9rem; margin-bottom: .35rem; }
    .mi-hero p  { color: rgba(255, 255, 255, .88); margin-bottom: 1.1rem; }
    .mi-cta {
        background: #ffd666;
        color: #5a4300;
        font-weight: 700;
        border: 0;
        border-radius: 50rem;
        padding: .55rem 1.3rem;
        box-shadow: 0 6px 16px rgba(0, 0, 0, .15);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .mi-cta:hover { background: #ffcd45; color: #5a4300; transform: translateY(-2px); }

    /* ===== Thanh số liệu ===== */
    .mi-stat {
        display: flex;
        align-items: center;
        gap: .85rem;
        background: #fff;
        border: 1px solid #e6efe9;
        border-radius: 1rem;
        padding: .9rem 1rem;
        box-shadow: 0 4px 16px rgba(20, 60, 40, .05);
    }
    .mi-stat-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .85rem;
        font-size: 1.15rem;
    }
    .mi-stat-num   { font-size: 1.4rem; font-weight: 800; line-height: 1.1; color: #212529; }
    .mi-stat-label { font-size: .8rem; color: #6c7a72; }
    .ic-all   { background: #e1f3e8; color: #198754; }
    .ic-live  { background: #d9f2e4; color: #0f8a4f; }
    .ic-wait  { background: #fff3cd; color: #b58100; }
    .ic-done  { background: #e7ebf0; color: #5b6b7c; }

    /* ===== Tab lọc + tìm kiếm ===== */
    .mi-toolbar {
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
    .mi-tabs { display: flex; gap: .4rem; overflow-x: auto; flex: 1 1 auto; scrollbar-width: none; }
    .mi-tabs::-webkit-scrollbar { display: none; }
    .mi-tab {
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
    .mi-tab:hover { background: #eef7f1; color: #146c43; }
    .mi-tab.active { background: #198754; color: #fff; box-shadow: 0 4px 10px rgba(25, 135, 84, .3); }
    .mi-tab .mi-count {
        display: inline-block;
        min-width: 1.4rem;
        margin-left: .3rem;
        padding: 0 .4rem;
        border-radius: 50rem;
        background: rgba(0, 0, 0, .07);
        font-size: .75rem;
    }
    .mi-tab.active .mi-count { background: rgba(255, 255, 255, .25); }
    .mi-search { position: relative; flex: 0 1 260px; min-width: 190px; }
    .mi-search i { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: #8a948f; font-size: .85rem; }
    .mi-search input {
        width: 100%;
        border: 1px solid #d3ebdc;
        background: #f7fbf9;
        border-radius: 50rem;
        padding: .5rem 1rem .5rem 2.3rem;
        font-size: .88rem;
        outline: none;
    }
    .mi-search input:focus { border-color: #198754; box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .18); background: #fff; }

    /* ===== Thẻ món đồ ===== */
    .mi-card {
        background: #fff;
        border: 1px solid #e6efe9;
        border-radius: 1.1rem;
        box-shadow: 0 4px 18px rgba(20, 60, 40, .06);
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .mi-card:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(20, 60, 40, .14); border-color: #bfdccb; }
    .mi-thumb {
        position: relative;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #dff2e6 0%, #f3faf6 100%);
        color: #86c7a1;
        font-size: 3rem;
    }
    .mi-thumb img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .35s ease;
    }
    .mi-card:hover .mi-thumb img { transform: scale(1.06); }
    .mi-thumb::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        height: 45%;
        background: linear-gradient(to top, rgba(0, 0, 0, .28), transparent);
        pointer-events: none;
    }
    .mi-status {
        position: absolute;
        top: .75rem;
        left: .75rem;
        z-index: 2;
        font-weight: 600;
        font-size: .74rem;
        padding: .42rem .75rem;
        box-shadow: 0 3px 10px rgba(0, 0, 0, .18);
    }
    .mi-cat {
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
    .mi-title {
        font-weight: 700;
        font-size: 1.02rem;
        color: #212529;
        margin: 0 0 .85rem;
        min-height: 2.6em;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .mi-card .btn {
        border-radius: 50rem;
        font-weight: 600;
        font-size: .85rem;
        transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .mi-card .btn:focus-visible { box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .35); }

    .mi-empty {
        background: #fff;
        border: 1px dashed #bfdccb;
        border-radius: 1rem;
        padding: 3.5rem 1.5rem;
        text-align: center;
    }
    .mi-empty-icon {
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
        .mi-hero { padding: 1.4rem 1.25rem; }
        .mi-hero h2 { font-size: 1.5rem; }
        .mi-hero-icon { font-size: 4.5rem; right: 1rem; }
        .mi-search { flex-basis: 100%; }
    }
</style>

<!-- Tranh minh họa hai bên -->
<svg class="mi-side mi-side-left" viewBox="0 0 230 440" aria-hidden="true">
    <circle cx="105" cy="120" r="92" fill="#198754" opacity=".10"/>
    <circle cx="40" cy="250" r="22" fill="#ffc107" opacity=".25"/>
    <!-- cây trong chậu -->
    <g transform="translate(60 40)">
        <path d="M60 170 C58 120 60 80 62 40" stroke="#198754" stroke-width="5" fill="none" stroke-linecap="round" opacity=".55"/>
        <ellipse cx="38" cy="78" rx="28" ry="13" transform="rotate(-35 38 78)" fill="#198754" opacity=".45"/>
        <ellipse cx="86" cy="62" rx="28" ry="13" transform="rotate(35 86 62)" fill="#2fb37a" opacity=".5"/>
        <ellipse cx="40" cy="120" rx="26" ry="12" transform="rotate(-30 40 120)" fill="#2fb37a" opacity=".45"/>
        <ellipse cx="84" cy="108" rx="26" ry="12" transform="rotate(30 84 108)" fill="#198754" opacity=".45"/>
        <ellipse cx="62" cy="34" rx="12" ry="24" fill="#198754" opacity=".5"/>
        <path d="M30 170 H92 L84 212 H38 Z" fill="#ffc107" opacity=".55"/>
    </g>
    <!-- chồng thùng đồ -->
    <g opacity=".6">
        <rect x="30" y="300" width="100" height="62" rx="6" fill="#a9d9bd"/>
        <rect x="74" y="300" width="12" height="62" fill="#fff" opacity=".6"/>
        <rect x="92" y="244" width="84" height="56" rx="6" fill="#ffd666"/>
        <rect x="128" y="244" width="12" height="56" fill="#fff" opacity=".6"/>
        <rect x="46" y="372" width="120" height="48" rx="6" fill="#7cc79d"/>
        <rect x="98" y="372" width="12" height="48" fill="#fff" opacity=".6"/>
    </g>
</svg>
<svg class="mi-side mi-side-right" viewBox="0 0 230 440" aria-hidden="true">
    <circle cx="125" cy="130" r="92" fill="#ffc107" opacity=".16"/>
    <circle cx="190" cy="290" r="26" fill="#198754" opacity=".12"/>
    <!-- hộp quà -->
    <g transform="translate(40 70)" opacity=".7">
        <rect x="10" y="62" width="130" height="96" rx="8" fill="#198754"/>
        <rect x="0" y="40" width="150" height="34" rx="8" fill="#2fb37a"/>
        <rect x="64" y="40" width="22" height="118" fill="#ffd666"/>
        <path d="M75 40 C45 0 12 18 36 40 Z" fill="#ffd666"/>
        <path d="M75 40 C105 0 138 18 114 40 Z" fill="#ffc107"/>
    </g>
    <!-- vòng tái chế -->
    <g transform="translate(115 330)" opacity=".5" fill="none" stroke="#198754" stroke-width="9" stroke-linecap="round">
        <path d="M-52 -4 A52 52 0 0 1 20 -48"/>
        <path d="M48 -26 A52 52 0 0 1 28 44"/>
        <path d="M-6 54 A52 52 0 0 1 -48 20"/>
        <path d="M8 -62 L22 -46 L2 -38 Z" fill="#198754" stroke="none"/>
        <path d="M58 -30 L48 -12 L66 -14 Z" fill="#198754" stroke="none"/>
        <path d="M-52 24 L-40 6 L-58 10 Z" fill="#198754" stroke="none"/>
    </g>
    <!-- trái tim -->
    <path d="M30 250 c-14 -16 8 -34 20 -18 c12 -16 34 2 20 18 l-20 20 Z" fill="#e8576b" opacity=".35"/>
</svg>

<div class="container-xl py-4 mi-page">

    <!-- Banner -->
    <div class="mi-hero mb-4">
        <i class="fa-solid fa-box-open mi-hero-icon" aria-hidden="true"></i>
        <div class="mi-hero-body">
            <span class="mi-kicker"><i class="fa-solid fa-recycle"></i> Give &amp; Take</span>
            <h2>Kho đồ của tôi</h2>
            <p>Quản lý những món đồ bạn đã đăng, theo dõi trạng thái và lan tỏa giá trị cho cộng đồng.</p>
            <a href="index.php?controller=Item&amp;action=create" class="btn mi-cta">
                <i class="fa-solid fa-plus me-1"></i> Đăng món đồ
            </a>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <div class="mi-empty">
            <div class="mi-empty-icon">📦</div>
            <h5 class="text-secondary fw-semibold">Bạn chưa đăng món đồ nào.</h5>
            <p class="text-muted small">Những món đồ không dùng nữa có thể mang lại giá trị cho người khác.</p>
            <a href="index.php?controller=Item&amp;action=create" class="btn btn-success btn-sm rounded-pill px-3">
                <i class="fa-solid fa-plus me-1"></i> Đăng món đồ đầu tiên
            </a>
        </div>
    <?php else: ?>

        <!-- Số liệu -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="mi-stat">
                    <span class="mi-stat-icon ic-all"><i class="fa-solid fa-boxes-stacked"></i></span>
                    <div><div class="mi-stat-num"><?= $total ?></div><div class="mi-stat-label">Tổng số món đồ</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mi-stat">
                    <span class="mi-stat-icon ic-live"><i class="fa-solid fa-share-nodes"></i></span>
                    <div><div class="mi-stat-num"><?= (int)($counts['available'] ?? 0) ?></div><div class="mi-stat-label">Đang chia sẻ</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mi-stat">
                    <span class="mi-stat-icon ic-wait"><i class="fa-solid fa-hourglass-half"></i></span>
                    <div><div class="mi-stat-num"><?= (int)($counts['pending_review'] ?? 0) ?></div><div class="mi-stat-label">Chờ admin duyệt</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mi-stat">
                    <span class="mi-stat-icon ic-done"><i class="fa-solid fa-gift"></i></span>
                    <div><div class="mi-stat-num"><?= (int)(($counts['reserved'] ?? 0) + ($counts['gifted'] ?? 0)) ?></div><div class="mi-stat-label">Đã có người nhận / đã tặng</div></div>
                </div>
            </div>
        </div>

        <!-- Tab lọc + tìm kiếm -->
        <div class="mi-toolbar mb-4">
            <div class="mi-tabs" role="tablist">
                <button type="button" class="mi-tab active" data-filter="all">Tất cả<span class="mi-count"><?= $total ?></span></button>
                <?php foreach ($tabLabels as $key => $label): ?>
                    <?php if (!empty($counts[$key])): ?>
                        <button type="button" class="mi-tab" data-filter="<?= $esc($key) ?>"><?= $esc($label) ?><span class="mi-count"><?= (int)$counts[$key] ?></span></button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <label class="mi-search mb-0">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="miSearch" placeholder="Tìm trong kho đồ..." autocomplete="off">
            </label>
        </div>

        <!-- Lưới món đồ -->
        <div class="row g-4" id="miGrid">
        <?php foreach ($items as $item): ?>
            <?php
                [$color, $name, $icon] = $statusNames[$item['status'] ?? '']
                    ?? ['bg-secondary', $item['status'] ?? 'Không rõ', 'fa-circle-question'];
                $img = $itemImageUrl($item['image_url'] ?? '');
            ?>
            <div class="col-sm-6 col-lg-4 col-xl-3 mi-col"
                 data-status="<?= $esc($item['status'] ?? '') ?>"
                 data-title="<?= $esc(($item['title'] ?? '') . ' ' . ($item['category_name'] ?? '')) ?>">
                <div class="mi-card h-100 d-flex flex-column">
                    <div class="mi-thumb">
                        <i class="fa-solid fa-box-open"></i>
                        <?php if ($img !== ''): ?>
                            <img src="<?= $esc($img) ?>" alt="<?= $esc($item['title']) ?>" loading="lazy"
                                 onerror="this.style.display='none';">
                        <?php endif; ?>
                        <span class="badge rounded-pill mi-status <?= $color ?>">
                            <i class="fa-solid <?= $icon ?> me-1"></i><?= $esc($name) ?>
                        </span>
                        <span class="mi-cat"><i class="fa-solid fa-tag"></i> <?= $esc($item['category_name']) ?></span>
                    </div>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <h5 class="mi-title" title="<?= $esc($item['title']) ?>"><?= $esc($item['title']) ?></h5>
                        <a href="index.php?controller=Item&amp;action=detail&amp;id=<?= (int)$item['id'] ?>"
                           class="btn btn-outline-success btn-sm mt-auto">
                            Xem chi tiết <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="mi-empty mt-2 d-none" id="miNoResult">
            <div class="mi-empty-icon">🔍</div>
            <h6 class="text-secondary fw-semibold mb-0">Không có món đồ nào phù hợp.</h6>
        </div>

        <script>
        (function () {
            var cols = document.querySelectorAll('.mi-col');
            var tabs = document.querySelectorAll('.mi-tab');
            var box = document.getElementById('miSearch');
            var none = document.getElementById('miNoResult');
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
