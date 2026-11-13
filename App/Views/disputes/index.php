<?php
$disputes = is_array($disputes ?? null) ? $disputes : [];
$e = static fn($v) => htmlspecialchars(
    (string)$v,
    ENT_QUOTES,
    'UTF-8'
);
?>
<?php $e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); ?>
<div class="container py-4"><h2 class="text-success mb-3">Khiếu nại của tôi</h2>
<a href="index.php?controller=Request&action=history" class="btn btn-outline-secondary mb-3">Lịch sử giao dịch</a>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Món đồ</th><th>Lý do</th><th>Trạng thái</th><th>Thời gian</th><th></th></tr></thead><tbody>
<?php foreach($disputes as $d): ?><tr><td><?= $e($d['item_title']) ?></td><td><?= $e($d['reason']) ?></td><td><?= $e($d['status']) ?></td><td><?= $e($d['created_at']) ?></td><td><a class="btn btn-sm btn-success" href="index.php?controller=Dispute&action=show&id=<?= (int)$d['id'] ?>">Chi tiết</a></td></tr><?php endforeach; ?>
<?php if (!$disputes): ?><tr><td colspan="5" class="text-muted">Chưa có khiếu nại nào.</td></tr><?php endif; ?>
</tbody></table></div></div>
