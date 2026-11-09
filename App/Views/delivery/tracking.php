<?php
$title = 'Thiết lập giao nhận - Give & Take';
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$id=(int)$delivery['request_id'];
$isGiver=(int)$delivery['giver_id']===(int)$userId;
$isReceiver=(int)$delivery['requester_id']===(int)$userId;
$canPropose=$isGiver && in_array($delivery['status'],['accepted','arranged'],true);
$canAgree=$isReceiver && $delivery['status']==='accepted' && !empty($delivery['proposed_at']);
$method=$delivery['delivery_method']??'';
?>
<div class="container py-4" style="max-width:850px">
  <a href="index.php?controller=Message&action=chat&id=<?= $id ?>" class="btn btn-link px-0">← Quay lại chat</a>
  <h2 class="text-success fw-bold">Thiết lập giao nhận: <?= $e($delivery['item_title']) ?></h2>
  <p class="text-muted">Yêu cầu #<?= $id ?> · Trạng thái: <strong><?= $e($delivery['status']) ?></strong></p>
  <?php if (!empty($_SESSION['delivery_notice'])): ?>
    <div class="alert alert-info"><?= $e($_SESSION['delivery_notice']) ?></div>
    <?php unset($_SESSION['delivery_notice']); ?>
  <?php endif; ?>
  <?php if ($method): ?>
    <div class="card mb-4"><div class="card-body">
      <h5>Đề xuất hiện tại: <?= $method==='pickup'?'Nhận trực tiếp':'Gửi vận chuyển' ?></h5>
      <?php if ($method==='pickup'): ?>
        <p>Địa điểm: <?= $e($delivery['meeting_location']) ?></p>
        <p>Thời gian: <?= $e($delivery['meeting_time']) ?></p>
      <?php else: ?>
        <p>Đơn vị vận chuyển: <?= $e($delivery['shipping_carrier']) ?></p>
        <p>Địa chỉ nhận: <?= $e($delivery['shipping_address']) ?></p>
        <p>Phí ship đề xuất: <strong><?= number_format((int)$delivery['shipping_fee_vnd'],0,',','.') ?> đ</strong></p>
        <p class="text-warning-emphasis">Chưa thanh toán. VNPay Sandbox sẽ tích hợp ở Bước 3B.</p>
      <?php endif; ?>
      <?php if ($delivery['delivery_note']): ?><p>Ghi chú: <?= nl2br($e($delivery['delivery_note'])) ?></p><?php endif; ?>
      <p><?= $delivery['agreed_at'] ? 'Hai bên đã thống nhất phương thức.' : 'Đang chờ người nhận đồng ý.' ?></p>
    </div></div>
  <?php endif; ?>
  <?php if ($canPropose): ?>
    <form method="post" action="index.php?controller=Delivery&action=propose&id=<?= $id ?>" class="card card-body mb-4">
      <h5>Người tặng đề xuất phương thức</h5>
      <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
      <label class="form-label" for="method">Phương thức</label>
      <select name="method" id="method" class="form-select mb-3" required>
        <option value="pickup" <?= $method==='pickup'?'selected':'' ?>>Người nhận đến lấy</option>
        <option value="shipping" <?= $method==='shipping'?'selected':'' ?>>Người tặng gửi vận chuyển</option>
      </select>
      <div id="pickup-fields">
        <label class="form-label">Địa điểm hẹn</label>
        <input class="form-control mb-2" name="meeting_location" maxlength="255" value="<?= $e($delivery['meeting_location']) ?>">
        <label class="form-label">Ngày giờ hẹn</label>
        <input class="form-control mb-3" name="meeting_time" type="datetime-local" value="<?= $e($delivery['meeting_time']?date('Y-m-d\TH:i',strtotime($delivery['meeting_time'])):'') ?>">
      </div>
      <div id="shipping-fields" hidden>
        <label class="form-label">Đơn vị vận chuyển dự kiến</label>
        <input class="form-control mb-2" name="carrier" maxlength="150" value="<?= $e($delivery['shipping_carrier']) ?>">
        <label class="form-label">Địa chỉ giao hàng đã thống nhất</label>
        <textarea class="form-control mb-2" name="shipping_address" maxlength="500" rows="2"><?= $e($delivery['shipping_address']) ?></textarea>
        <label class="form-label">Phí vận chuyển đề xuất (VND)</label>
        <input class="form-control mb-3" name="shipping_fee_vnd" type="number" min="0" max="10000000" step="1" value="<?= $e($delivery['shipping_fee_vnd']??'0') ?>">
      </div>
      <label class="form-label">Ghi chú</label>
      <textarea class="form-control mb-3" name="note" maxlength="2000" rows="2"><?= $e($delivery['delivery_note']) ?></textarea>
      <button class="btn btn-success" type="submit">Gửi / cập nhật đề xuất</button>
      <small class="text-muted mt-2">Cập nhật đề xuất sẽ yêu cầu người nhận đồng ý lại.</small>
    </form>
    <script>
    (()=>{const m=document.getElementById('method'),p=document.getElementById('pickup-fields'),s=document.getElementById('shipping-fields');
    function sync(){const pickup=m.value==='pickup';p.hidden=!pickup;s.hidden=pickup;
    p.querySelector('[name=meeting_location]').required=pickup;p.querySelector('[name=meeting_time]').required=pickup;
    s.querySelector('[name=carrier]').required=!pickup;s.querySelector('[name=shipping_address]').required=!pickup;
    s.querySelector('[name=shipping_fee_vnd]').required=!pickup;}
    m.addEventListener('change',sync);sync();})();
    </script>
  <?php endif; ?>
  <?php if ($canAgree): ?>
    <form method="post" action="index.php?controller=Delivery&action=agree&id=<?= $id ?>">
      <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
      <button type="submit" class="btn btn-success" onclick="return confirm('Bạn đồng ý phương thức và mức phí vận chuyển được đề xuất? Đây chưa phải thanh toán.');">Đồng ý phương thức giao nhận</button>
    </form>
  <?php endif; ?>
  <?php if ($delivery['status']==='arranged' && $method==='shipping'): ?>
    <div class="alert alert-warning mt-3">Đã thống nhất phí ship, chưa có thanh toán. Không bàn giao hàng cho đơn vị vận chuyển trước khi Bước 3B xác nhận thanh toán thành công.</div>
  <?php elseif ($delivery['status']==='arranged'): ?>
    <div class="alert alert-success mt-3">Đã thống nhất lịch hẹn. Chờ chức năng cập nhật giao nhận ở Bước 3C.</div>
  <?php endif; ?>
</div>
