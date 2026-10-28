document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categorySelect');
    const itemList = document.getElementById('itemList');

    let timeout = null;

    function fetchItems() {
        let keyword = searchInput ? searchInput.value : '';
        let categoryId = categorySelect ? categorySelect.value : '';

        // Fetch API gửi dữ liệu lên HomeController
        fetch(`/home/searchAjax?keyword=${encodeURIComponent(keyword)}&category_id=${encodeURIComponent(categoryId)}`)
            .then(response => response.json())
            .then(data => {
                let htmlString = '';
                
                if (data.length > 0) {
                    // Dùng vòng lặp tạo chuỗi HTML
                    data.forEach(item => {
                        let shortDesc = item.description ? item.description.substring(0, 60) + '...' : '';
                        htmlString += `
                            <div class="col-md-3 mb-4">
                                <div class="card h-100 shadow-sm item-card">
                                    <img src="${item.image_url}" class="card-img-top" alt="${item.title}">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">${item.title}</h5>
                                        <p class="card-text text-success"><small>${item.category_name || 'Khác'}</small></p>
                                        <p class="card-text">${shortDesc}</p>
                                        <a href="/item/detail/${item.id}" class="btn btn-outline-success mt-auto">Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    htmlString = '<div class="col-12 text-center mt-5"><p class="text-muted">Không tìm thấy món đồ nào phù hợp.</p></div>';
                }
                
                // Gắn vào .innerHTML của itemList
                itemList.innerHTML = htmlString;
            })
            .catch(error => console.error('Lỗi khi lấy dữ liệu:', error));
    }

    // Lắng nghe sự kiện keyup trên ô tìm kiếm (Kèm debounce 300ms)
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            clearTimeout(timeout);
            timeout = setTimeout(fetchItems, 300); 
        });
    }
    
    // Lắng nghe sự kiện change trên thanh chọn khu vực/danh mục
    if(categorySelect) {
        categorySelect.addEventListener('change', fetchItems);
    }
});