document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categorySelect');
    const itemList = document.getElementById('itemList');

    if (searchInput && categorySelect && itemList) {
        const fetchItems = () => {
            const keyword = searchInput.value;
            const categoryId = categorySelect.value;
            
            // Gọi endpoint trả JSON
            fetch(`/home/searchAjax?keyword=${encodeURIComponent(keyword)}&category_id=${encodeURIComponent(categoryId)}`)
                .then(response => response.json())
                .then(data => {
                    itemList.innerHTML = ''; // Xóa dữ liệu cũ
                    
                    if (data.length === 0) {
                        itemList.innerHTML = '<div class="col-12"><p class="text-center text-muted">Không tìm thấy món đồ phù hợp.</p></div>';
                        return;
                    }
                    
                    // Render danh sách Card bằng template literal
                    data.forEach(item => {
                        const cardHtml = `
                            <div class="col-md-3 mb-4">
                                <div class="card h-100">
                                    <img src="${item.image_url}" class="card-img-top" alt="${item.title}">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">${item.title}</h5>
                                        <p class="card-text text-success"><small>${item.category_name}</small></p>
                                        <p class="card-text">${item.description.substring(0, 60)}...</p>
                                        <a href="/item/detail/${item.id}" class="btn btn-outline-success mt-auto">Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        `;
                        itemList.innerHTML += cardHtml;
                    });
                })
                .catch(error => console.error('Lỗi khi tải dữ liệu AJAX:', error));
        };

        // Lắng nghe sự kiện
        searchInput.addEventListener('keyup', fetchItems);
        categorySelect.addEventListener('change', fetchItems);
    }
});