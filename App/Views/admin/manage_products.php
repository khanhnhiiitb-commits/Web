<?php
$esc = static fn($x) => htmlspecialchars((string)($x ?? ''), ENT_QUOTES, 'UTF-8');
$labels = [
    'pending_review' => ['bg-warning text-dark', 'Chờ duyệt'],
    'available' => ['bg-success', 'Đang hiển thị'],
    'reserved' => ['bg-info text-dark', 'Đã giữ chỗ'],
    'gifted' => ['bg-secondary', 'Đã tặng'],
    'rejected' => ['bg-danger', 'Bị từ chối'],
];
?>
<div class="container-fluid px-0">
    <h2 class="mb-3">Kiểm duyệt bài đăng</h2>
    <p class="text-muted">Chỉ bài được duyệt mới xuất hiện công khai.</p>
    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr>
                <th>Món đồ</th><th>Người đăng</th><th>Danh mục</th>
                <th>Trạng thái</th><th class="text-center">Thao tác</th>
            </tr></thead>
            <tbody>
            <?php foreach (($products ?? []) as $post): ?>
                <?php $status = (string)($post['status'] ?? '');
                      [$badge, $label] = $labels[$status] ?? ['bg-secondary', $status]; ?>
                <tr>
                    <td><?= $esc($post['title'] ?? '') ?></td>
                    <td><?= $esc($post['user_name'] ?? '') ?></td>
                    <td><?= $esc($post['category'] ?? '') ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $esc($label) ?></span></td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                            <?php if ($status === 'pending_review'): ?>
                                <!-- Nút Duyệt -->
                                <form method="post" action="index.php?controller=Admin&amp;action=approveProduct" class="m-0">
                                    <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-success px-3" style="width: 75px;">Duyệt</button>
                                </form>
                                <!-- Nút Từ chối -->
                                <form method="post" action="index.php?controller=Admin&amp;action=rejectProduct" class="m-0" onsubmit="return confirm('Từ chối bài đăng này?');">
                                    <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3" style="width: 75px;">Từ chối</button>
                                </form>
                            <?php endif; ?>

                            <!-- Nút Xóa bài đăng (Luôn hiển thị cho mọi trạng thái) -->
                            <form method="post" action="index.php?controller=Admin&amp;action=deleteProduct" class="m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">
                                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger px-3" style="width: 75px;">Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Chưa có bài đăng.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>