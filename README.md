# Give & Take - Hệ Thống Web Chia Sẻ Đồ Tái Sử Dụng
**Đồ án cuối kỳ môn Phát triển Ứng dụng Web**  

Giải pháp kết nối cộng đồng chia sẻ đồ cũ miễn phí, góp phần giảm thiểu rác thải tiêu dùng và lan tỏa lối sống xanh (Zero Waste).
---

**Thành Viên**
1. Tăng Khánh Nhi
2. Lê Thị Hồng Nhã
3. Ngô Thị Thu Duyên
4. Giáp Thị Thu Liễu

---
<img width="973" height="567" alt="image" src="https://github.com/user-attachments/assets/9aa05741-dfb1-48bc-ae00-cd4a2a1c9497" />


## 1. Mô tả tổng quan đồ án

Trong cuộc sống hiện đại, rất nhiều vật dụng (sách vở, quần áo, đồ gia dụng, thiết bị điện tử...) vẫn còn giá trị sử dụng tốt nhưng bị bỏ phí khi chủ nhân không còn nhu cầu. Ngược lại, nhiều người khác (sinh viên, người lao động...) lại đang rất cần những món đồ đó.

**Give & Take** được xây dựng nhằm tạo ra một nền tảng trung gian trực tuyến, nơi:
* **Người cho (Giver):** Dễ dàng đăng tải thông tin, hình ảnh và tình trạng món đồ muốn tặng lại kèm địa điểm giao nhận.
* **Người nhận (Taker):** Tìm kiếm món đồ phù hợp theo danh mục hoặc khu vực, gửi lời nhắn đăng ký nhận đồ nhanh chóng.
* **Quản trị viên (Admin):** Quản lý danh mục, kiểm duyệt các món đồ chia sẻ và đăng tải các bài viết tuyên truyền lối sống xanh.

Đồ án được phát triển tuân thủ chặt chẽ kiến trúc **Hướng đối tượng (OOP) kết hợp mô hình MVC**, thiết kế giao diện đáp ứng (Responsive) bằng **Bootstrap 5**, đồng thời tích hợp **AJAX & Webservice (truyền tải qua JSON)** nhằm mang lại trải nghiệm mượt mà không cần tải lại trang.

---

## 2. Các chức năng cơ bản của hệ thống

Hệ thống được phân chia thành **3 phân hệ chức năng** chính:

### Phân hệ Khách vãng lai (Public / Guest)
* **Trang chủ (Home):** Hiển thị banner giới thiệu, danh mục đồ nổi bật, các món đồ mới đăng và tin tức sống xanh (sử dụng *Bootstrap Carousel & Grid Card*).
* **Tìm kiếm & Lọc đồ tức thì (Tích hợp AJAX + JSON):** 
  * Tìm kiếm theo từ khóa, lọc theo Danh mục hoặc Tỉnh/Thành phố.
  * Sử dụng **AJAX** gửi yêu cầu ngầm và nhận dữ liệu **JSON** từ Server để cập nhật danh sách món đồ ngay lập tức mà không cần reload trang.
* **Xem chi tiết món đồ:** Xem hình ảnh, mô tả tình trạng thực tế (mới 90%, còn dùng tốt...), khu vực nhận đồ và thông tin người đăng.
* **Góc Sống Xanh (Blog):** Xem danh sách và đọc các bài viết hướng dẫn tái chế, phân loại rác.

### Phân hệ Thành viên (Member - Người cho & Người nhận)
* **Quản lý Tài khoản:** Đăng ký, Đăng nhập, Đăng xuất và cập nhật thông tin liên hệ cá nhân.
* **Đăng tin chia sẻ đồ cũ (Tích hợp Webservice JSON):**
  * Cho phép thành viên tải lên hình ảnh, chọn danh mục, nhập mô tả tình trạng món đồ.
  * **Sử dụng Webservice bên thứ 3:** Tự động gọi Public API Hành chính Việt Nam (dữ liệu **JSON**) để hiển thị danh sách *Tỉnh/Thành phố -> Quận/Huyện -> Phường/Xã* khi chọn địa chỉ giao nhận.
* **Đăng ký "Xin nhận đồ" (Bootstrap Modal + AJAX):**
  * Gửi lời nhắn xin nhận đồ trực tiếp thông qua hộp thoại *Bootstrap Modal*. Dữ liệu được gửi và phản hồi qua **AJAX (JSON)**.
* **Quản lý đồ cá nhân:** Xem danh sách đồ đã đăng, duyệt yêu cầu xin nhận từ người khác và đổi trạng thái món đồ (*"Đang còn"* ↔ *"Đã tặng"*).

### Phân hệ Quản trị viên (Admin Panel - Cơ bản)
* **Dashboard thống kê:** Xem tổng số món đồ, số đồ đã tặng thành công, tổng số bài viết và thành viên.
* **Quản lý Danh mục (Category CRUD):** Thêm mới, sửa, xóa các danh mục đồ tái sử dụng.
* **Quản lý Đồ chia sẻ (Product/Item CRUD):** Thêm mới, cập nhật thông tin, duyệt hoặc xóa nhanh các món đồ vi phạm (hỗ trợ xóa/đổi trạng thái qua **AJAX**).
* **Quản lý Bài viết (Post CRUD):** Thêm, sửa, xóa các bài viết tin tức ở chuyên mục Góc Sống Xanh.
* **Quản lý Người dùng:** Xem danh sách thành viên và phân quyền cơ bản (*Admin / Member*).

---

## 3. Công nghệ sử dụng (Tech Stack)

| Thành phần | Công nghệ / Công cụ áp dụng |
| :--- | :--- |
| **Frontend** | HTML5, CSS3, **Bootstrap 5.3**, JavaScript (Fetch API / jQuery AJAX) |
| **Backend** | PHP 8.x xây dựng theo chuẩn **OOP (Hướng đối tượng) + MVC** |
| **Cơ sở dữ liệu** | **MySQL** (Triển khai trên **Shared Cloud Host** để cả nhóm truy cập chung) |
| **Tích hợp & Truyền tải** | **AJAX** nội bộ & **RESTful Webservice** Tỉnh/Thành phố (Định dạng **JSON**) |
| **Quản lý mã nguồn** | **Git & GitHub** (Quản lý phân nhánh theo tính năng - Branching Workflow) |
| **Quản lý tiến độ nhóm** | **Microsoft Teams** & **Planner** (Phân chia task, họp nhóm, lưu minh chứng) |
