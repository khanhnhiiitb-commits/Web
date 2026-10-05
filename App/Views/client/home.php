<h1>Danh sách Danh mục hiện có:</h1>
<ul>
    <?php foreach($categories as $cat): ?>
        <li><?= htmlspecialchars($cat['name']) ?> - <?= htmlspecialchars($cat['description']) ?></li>
    <?php endforeach; ?>
</ul>