<?php
$h = static fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
 
// Chữ cái đầu + màu avatar theo tên (không cần ảnh đại diện)
$initial = static function ($name): string {
    $name = trim((string)($name ?? ''));
    if ($name === '') return '?';
    return function_exists('mb_substr') ? mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8') : strtoupper($name[0]);
};
$tone = static function ($name): string {
    $palette = ['#198754', '#0f8a4f', '#2fb37a', '#b58100', '#3b8ea5', '#8a6bbe', '#d6638a'];
    return $palette[abs(crc32((string)$name)) % count($palette)];
};
 
$posts          = $posts ?? [];
$commentsByPost = $commentsByPost ?? [];
$postCsrf       = $postCsrf ?? '';
$totalPosts     = count($posts);
?>
 
<style>
    /* Nền trang: gradient xanh lá + vàng rất nhạt */
    body {
        background:
            radial-gradient(ellipse 560px 460px at 0% 16%, rgba(25, 135, 84, .14), transparent 70%),
            radial-gradient(ellipse 560px 500px at 100% 58%, rgba(255, 193, 7, .16), transparent 70%),
            #f4f9f6;
        background-attachment: fixed;
    }
    .pf-wrap { max-width: 780px; margin: 0 auto; }
 
    /* Banner */
    .pf-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 1.75rem 2rem;
        color: #fff;
        background: linear-gradient(120deg, #146c43 0%, #198754 55%, #2fb37a 100%);
        box-shadow: 0 12px 32px rgba(25, 135, 84, .26);
    }
    .pf-hero::before, .pf-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, .09);
    }
    .pf-hero::before { width: 240px; height: 240px; right: -60px; top: -90px; }
    .pf-hero::after  { width: 150px; height: 150px; right: 130px; bottom: -80px; background: rgba(255, 193, 7, .20); }
    .pf-hero-icon {
        position: absolute;
        right: 2.5rem;
        top: 50%;
        transform: translateY(-50%) rotate(-8deg);
        font-size: 5.5rem;
        color: rgba(255, 255, 255, .16);
    }
    .pf-hero-body { position: relative; z-index: 1; }
    .pf-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .25rem .8rem;
        border-radius: 50rem;
        background: rgba(255, 255, 255, .18);
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: .7rem;
    }
    .pf-hero h2 { font-weight: 800; font-size: 1.75rem; margin-bottom: .3rem; }
    .pf-hero p  { color: rgba(255, 255, 255, .88); margin-bottom: 0; }
 
    /* Khung "viết bài" kiểu mạng xã hội */
    .pf-composer {
        display: flex;
        align-items: center;
        gap: .8rem;
        background: #fff;
        border: 1px solid #e6efe9;
        border-radius: 1rem;
        padding: .85rem 1rem;
        box-shadow: 0 4px 16px rgba(20, 60, 40, .05);
    }
    .pf-composer-input {
        flex: 1;
        display: block;
        padding: .6rem 1.1rem;
        border-radius: 50rem;
        background: #f1f6f3;
        color: #6c7a72;
        text-decoration: none;
        transition: background-color .15s ease, color .15s ease;
    }
    .pf-composer-input:hover { background: #e3f1e9; color: #146c43; }
    .pf-composer .btn { border-radius: 50rem; font-weight: 600; white-space: nowrap; }
 
    .pf-avatar {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        font-weight: 700;
        font-size: 1.05rem;
        background: #198754;
    }
    .pf-avatar.sm { width: 32px; height: 32px; flex-basis: 32px; font-size: .85rem; }
 
    /* Thông báo */
    .pf-alert {
        display: flex;
        align-items: center;
        gap: .6rem;
        border-radius: .9rem;
        padding: .8rem 1rem;
        border: 1px solid transparent;
        border-left-width: 4px;
        box-shadow: 0 3px 12px rgba(20, 60, 40, .06);
    }
    .pf-alert.success { background: #eaf7ef; border-color: #198754; color: #146c43; }
    .pf-alert.danger  { background: #fdecee; border-color: #dc3545; color: #a52834; }
 
    /* Bài viết */
    .pf-post {
        background: #fff;
        border: 1px solid #e6efe9;
        border-radius: 1.1rem;
        box-shadow: 0 4px 18px rgba(20, 60, 40, .06);
        overflow: hidden;
        transition: box-shadow .2s ease, border-color .2s ease;
    }
    .pf-post:hover { box-shadow: 0 10px 26px rgba(20, 60, 40, .11); border-color: #cfe5d8; }
    .pf-post-head { display: flex; align-items: center; gap: .75rem; padding: 1.1rem 1.25rem 0; }
    .pf-author { font-weight: 700; color: #212529; line-height: 1.2; }
    .pf-meta   { font-size: .8rem; color: #8a948f; }
    .pf-badge {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .7rem;
        border-radius: 50rem;
        background: #eef7f1;
        color: #146c43;
        font-size: .78rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .pf-post-body { padding: .9rem 1.25rem 1rem; }
    .pf-title { font-weight: 800; font-size: 1.2rem; color: #1d3b2c; margin-bottom: .55rem; }
    .pf-content {
        color: #343a40;
        line-height: 1.65;
        word-break: break-word;
        max-height: 9.9em;
        overflow: hidden;
        position: relative;
    }
    .pf-content.expanded { max-height: none; }
    .pf-more {
        border: 0;
        background: none;
        padding: .25rem 0 0;
        color: #198754;
        font-weight: 600;
        font-size: .88rem;
    }
    .pf-more:hover { text-decoration: underline; }
 
    /* Bình luận */
    .pf-comments-wrap { border-top: 1px solid #eef3f0; background: #fafdfb; padding: 1rem 1.25rem 1.1rem; }
    .pf-comments-title { font-size: .85rem; font-weight: 700; color: #52605a; margin-bottom: .75rem; }
    .pf-comment { display: flex; gap: .6rem; margin-bottom: .65rem; }
    .pf-bubble {
        background: #eef4f0;
        border-radius: .3rem 1rem 1rem 1rem;
        padding: .5rem .9rem;
        font-size: .9rem;
        word-break: break-word;
    }
    .pf-bubble strong { display: block; font-size: .82rem; color: #146c43; }
    .pf-older {
        border: 0;
        background: none;
        padding: 0 0 .6rem;
        color: #52605a;
        font-size: .83rem;
        font-weight: 600;
    }
    .pf-older:hover { color: #198754; text-decoration: underline; }
    .pf-nocomment { font-size: .85rem; color: #8a948f; margin-bottom: .75rem; }
 
    .pf-form { display: flex; gap: .6rem; align-items: flex-start; margin-top: .4rem; }
    .pf-form-main { flex: 1; }
    .pf-form textarea {
        width: 100%;
        border: 1px solid #d3ebdc;
        background: #fff;
        border-radius: 1rem;
        padding: .55rem 1rem;
        font-size: .9rem;
        resize: vertical;
        outline: none;
    }
    .pf-form textarea:focus { border-color: #198754; box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .18); }
    .pf-form .btn { border-radius: 50rem; font-weight: 600; font-size: .85rem; }
    .pf-form .btn:focus-visible { box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .35); }
 
    .pf-empty {
        background: #fff;
        border: 1px dashed #bfdccb;
        border-radius: 1rem;
        padding: 3.5rem 1.5rem;
        text-align: center;
    }
    .pf-empty-icon {
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
    @media (max-width: 575.98px) {
        .pf-hero { padding: 1.3rem 1.2rem; }
        .pf-hero h2 { font-size: 1.4rem; }
        .pf-hero-icon { font-size: 3.5rem; right: 1rem; }
        .pf-post-body, .pf-comments-wrap { padding-left: 1rem; padding-right: 1rem; }
    }
</style>
 
<div class="container py-4">
  <div class="pf-wrap">
 
    <!-- Banner -->
    <div class="pf-hero mb-4">
        <i class="fa-solid fa-comments pf-hero-icon" aria-hidden="true"></i>
        <div class="pf-hero-body">
            <span class="pf-kicker"><i class="fa-solid fa-recycle"></i> Give &amp; Take</span>
            <h2>Bảng tin chia sẻ</h2>
            <p>Nơi cộng đồng trao đổi, kể chuyện và lan tỏa việc cho đi
                <?php if ($totalPosts > 0): ?>&middot; <strong><?= $totalPosts ?></strong> bài viết<?php endif; ?>.</p>
        </div>
    </div>
 
    <!-- Khung viết bài (chỉ khi đã đăng nhập) -->
    <?php if (!empty($_SESSION['user_id'])): ?>
        <div class="pf-composer mb-4">
            <span class="pf-avatar"><i class="fa-solid fa-user"></i></span>
            <a class="pf-composer-input" href="index.php?controller=Post&amp;action=create">Bạn muốn chia sẻ điều gì với cộng đồng?</a>
            <a class="btn btn-success" href="index.php?controller=Post&amp;action=create"><i class="fa-solid fa-plus me-1"></i> Viết bài</a>
        </div>
    <?php endif; ?>
 
    <!-- Thông báo -->
    <?php foreach (['post_success' => 'success', 'post_error' => 'danger'] as $key => $style): ?>
        <?php if (!empty($_SESSION[$key])): ?>
            <div class="pf-alert <?= $style ?> mb-4" role="alert">
                <i class="fa-solid <?= $style === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= $h($_SESSION[$key]) ?></span>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>
 
    <!-- Danh sách bài viết -->
    <?php foreach ($posts as $post): ?>
        <?php $comments = $commentsByPost[(int)$post['id']] ?? []; ?>
        <article class="pf-post mb-4">
            <div class="pf-post-head">
                <span class="pf-avatar" style="background: <?= $tone($post['author_name']) ?>;"><?= $h($initial($post['author_name'])) ?></span>
                <div>
                    <div class="pf-author"><?= $h($post['author_name']) ?></div>
                    <div class="pf-meta">Đăng bởi thành viên cộng đồng</div>
                </div>
                <span class="pf-badge"><i class="fa-regular fa-comment"></i> <?= (int)$post['comment_count'] ?> bình luận</span>
            </div>
 
            <div class="pf-post-body">
                <h5 class="pf-title"><?= $h($post['title']) ?></h5>
                <div class="pf-content"><?= nl2br($h($post['content'])) ?></div>
            </div>
 
            <div class="pf-comments-wrap">
                <div class="pf-comments-title"><i class="fa-regular fa-comments me-1"></i> Bình luận</div>
 
                <div class="pf-comment-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="pf-comment">
                            <span class="pf-avatar sm" style="background: <?= $tone($comment['commenter_name']) ?>;"><?= $h($initial($comment['commenter_name'])) ?></span>
                            <div class="pf-bubble">
                                <strong><?= $h($comment['commenter_name']) ?></strong>
                                <?= nl2br($h($comment['comment_text'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (empty($comments)): ?>
                    <div class="pf-nocomment">Chưa có bình luận nào.</div>
                <?php endif; ?>
 
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <form method="post" action="index.php?controller=Post&amp;action=comment" class="pf-form">
                        <input type="hidden" name="csrf" value="<?= $h($postCsrf) ?>">
                        <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                        <span class="pf-avatar sm"><i class="fa-solid fa-user"></i></span>
                        <div class="pf-form-main">
                            <label class="visually-hidden">Viết bình luận</label>
                            <textarea name="comment_text" maxlength="2000" rows="2" placeholder="Viết bình luận..." required></textarea>
                            <div class="text-end mt-2">
                                <button class="btn btn-sm btn-success" type="submit">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Gửi bình luận
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
 
    <?php if (!$posts): ?>
        <div class="pf-empty">
            <div class="pf-empty-icon">📝</div>
            <h5 class="text-secondary fw-semibold">Chưa có bài viết nào.</h5>
            <p class="text-muted small mb-0">Hãy là người đầu tiên chia sẻ câu chuyện của bạn với cộng đồng.</p>
        </div>
    <?php endif; ?>
 
  </div>
</div>
 
<script>
(function () {
    // Bài dài: rút gọn, có nút "Xem thêm"
    document.querySelectorAll('.pf-content').forEach(function (el) {
        if (el.scrollHeight > el.clientHeight + 4) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pf-more';
            btn.textContent = 'Xem thêm';
            btn.addEventListener('click', function () {
                var open = el.classList.toggle('expanded');
                btn.textContent = open ? 'Thu gọn' : 'Xem thêm';
            });
            el.insertAdjacentElement('afterend', btn);
        }
    });
 
    // Nhiều bình luận: chỉ hiện 3 bình luận mới nhất
    document.querySelectorAll('.pf-comment-list').forEach(function (list) {
        var items = list.querySelectorAll('.pf-comment');
        var keep = 3;
        if (items.length > keep) {
            var hidden = items.length - keep;
            for (var i = 0; i < hidden; i++) items[i].style.display = 'none';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pf-older';
            btn.textContent = 'Xem ' + hidden + ' bình luận trước';
            btn.addEventListener('click', function () {
                for (var i = 0; i < hidden; i++) items[i].style.display = '';
                btn.remove();
            });
            list.insertAdjacentElement('beforebegin', btn);
        }
    });
})();
</script>
 
