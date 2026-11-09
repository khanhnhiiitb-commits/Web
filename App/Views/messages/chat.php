<?php
$title = 'Trao đổi về món đồ - Give & Take';
$esc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$requestId = (int)$conversation['request_id'];
$otherName = (int)$conversation['giver_id'] === (int)$userId
    ? $conversation['receiver_name'] : $conversation['giver_name'];
?>
<div class="container py-4" style="max-width:850px">
    <a href="index.php?controller=Request&action=history" class="btn btn-link px-0 mb-2">← Lịch sử giao dịch</a>
    <h2 class="h4 fw-bold text-success mb-1">Trao đổi: <?= $esc($conversation['item_title']) ?></h2>
    <p class="text-muted">Đang trò chuyện với <?= $esc($otherName) ?> · Trạng thái: <span id="chat-status"><?= $esc($conversation['status']) ?></span></p>
    <?php if (in_array($conversation['status'], ['accepted','arranged','in_progress','awaiting_confirmation'], true)): ?>
      <a class="btn btn-outline-success mb-3" href="index.php?controller=Delivery&action=tracking&id=<?= $requestId ?>">
        <i class="bi bi-truck me-1"></i> Thiết lập / theo dõi giao nhận
      </a>
    <?php endif; ?>
    <div id="chat-messages" class="border rounded-3 bg-white p-3 mb-3" style="height:400px;overflow-y:auto" aria-live="polite"></div>
    <p id="chat-error" class="text-danger small" role="alert"></p>
    <form id="chat-form" class="d-flex gap-2" <?= $canSend ? '' : 'hidden' ?>>
        <input id="chat-input" name="content" class="form-control" maxlength="2000" placeholder="Nhập tin nhắn..." required autocomplete="off">
        <button class="btn btn-success" type="submit">Gửi</button>
    </form>
    <p id="chat-closed" class="text-muted small" <?= $canSend ? 'hidden' : '' ?>>Cuộc trò chuyện đã đóng. Bạn vẫn có thể xem lịch sử tin nhắn.</p>
</div>
<script>
(() => {
    const requestId = <?= $requestId ?>;
    const currentUserId = <?= (int)$userId ?>;
    const csrf = <?= json_encode($csrfToken) ?>;
    const endpoint = 'index.php?controller=Message&action=';
    const box = document.getElementById('chat-messages');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const error = document.getElementById('chat-error');
    const closed = document.getElementById('chat-closed');
    let lastId = 0, busy = false;

    async function refresh() {
        if (busy || document.hidden) return;
        busy = true;
        try {
            const response = await fetch(endpoint + 'fetch&id=' + requestId + '&after_id=' + lastId, {credentials:'same-origin',cache:'no-store'});
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.error || 'Không thể tải tin nhắn');
            for (const msg of data.messages) {
                const own = Number(msg.sender_id) === currentUserId;
                const wrap = document.createElement('div');
                wrap.className = 'mb-2 d-flex ' + (own ? 'justify-content-end' : 'justify-content-start');
                const bubble = document.createElement('div');
                bubble.className = 'rounded-3 px-3 py-2 ' + (own ? 'bg-success text-white' : 'bg-light text-dark');
                bubble.style.maxWidth = '80%';
                const name = document.createElement('div');
                name.className = 'small fw-semibold';
                name.textContent = msg.sender_name;
                const content = document.createElement('div');
                content.style.whiteSpace = 'pre-wrap';
                content.style.overflowWrap = 'anywhere';
                content.textContent = msg.content;
                const time = document.createElement('div');
                time.className = 'small opacity-75 mt-1';
                time.textContent = msg.created_at;
                bubble.append(name, content, time);
                wrap.appendChild(bubble);
                box.appendChild(wrap);
                lastId = Math.max(lastId, Number(msg.id));
                box.scrollTop = box.scrollHeight;
            }
            form.hidden = !data.can_send;
            closed.hidden = !!data.can_send;
            document.getElementById('chat-status').textContent = data.status;
            error.textContent = '';
        } catch (e) { error.textContent = e.message; }
        finally { busy = false; }
    }
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const content = input.value.trim();
        if (!content) return;
        const button = form.querySelector('button');
        button.disabled = true;
        try {
            const body = new URLSearchParams({content, csrf_token:csrf});
            const response = await fetch(endpoint + 'send&id=' + requestId, {
                method:'POST',credentials:'same-origin',body,
                headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.error || 'Gửi thất bại');
            input.value = '';
            await refresh();
        } catch (e) { error.textContent = e.message; }
        finally { button.disabled = false; }
    });
    refresh();
    const interval = setInterval(refresh, 3000);
    window.addEventListener('pagehide', () => clearInterval(interval));
})();
</script>
