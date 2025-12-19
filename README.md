# TÀI LIỆU ĐẶC TẢ HỆ THỐNG QUẢN LÝ KHO VÀ BÁN HÀNG

## 1. TỔNG QUAN HỆ THỐNG
Hệ thống nhằm mục đích quản lý vòng đời sản phẩm từ lúc nhập kho, lưu kho, bán hàng (xuất kho) cho đến quản lý doanh thu và đối soát công nợ. Đặc biệt hỗ trợ cơ chế đổi hàng linh hoạt.

## 2. QUẢN LÝ DANH MỤC CỐT LÕI (MASTER DATA)

### 2.1. Quản lý Sản phẩm (CRUD)
*   **Thông tin:** Mã SKU (tự động hoặc thủ công), Tên sản phẩm, Danh mục (Category), Đơn vị tính (Cái, Bộ, Kg...), Giá nhập, Giá bán lẻ, Giá sỉ, Định mức tồn tối thiểu (để cảnh báo).
*   **Trạng thái:** Đang kinh doanh, Ngừng kinh doanh.
*   **Hình ảnh:** Hỗ trợ upload ảnh sản phẩm.

### 2.2. Quản lý Nhà cung cấp (NCC)
*   **Thông tin:** Tên NCC, Mã số thuế, Số điện thoại, Địa chỉ, Người liên hệ.
*   **Công nợ NCC:** Theo dõi số tiền mình còn nợ nhà cung cấp.

### 2.3. Quản lý Khách hàng
*   **Thông tin:** Tên khách hàng, Số điện thoại (dùng làm mã khách hàng), Địa chỉ, Loại khách (Lẻ/Sỉ).
*   **Lịch sử mua hàng:** Theo dõi các đơn hàng đã mua và trạng thái thanh toán.

---

## 3. QUẢN LÝ KHO (INVENTORY MANAGEMENT)

### 3.1. Nhập hàng (Purchase Order)
*   **Tạo phiếu nhập:** Chọn NCC -> Chọn sản phẩm -> Số lượng -> Giá nhập.
*   **Tác động:** Tăng tồn kho tức thời sau khi xác nhận phiếu.
*   **Thanh toán NCC:** Ghi nhận đã trả đủ hoặc nợ NCC.

### 3.2. Xuất - Nhập tồn thực tế
*   Hệ thống tự động trừ kho khi bán hàng (Xuất) và cộng kho khi nhập hàng hoặc khách trả hàng.
*   **Thẻ kho:** Xem lịch sử biến động (Log) của từng sản phẩm (Ngày nào, ai xuất/nhập, lý do gì).

### 3.3. Phiếu kiểm hàng (Stock Audit)
*   **Tính năng:** Tạo phiếu kiểm kê theo định kỳ.
*   **Xử lý chênh lệch:** Cho phép nhập số lượng thực tế -> Hệ thống tự tính toán chênh lệch (thừa/thiếu) -> Cập nhật lại kho thực tế và ghi lại lý do xử lý.

---

## 4. QUẢN LÝ BÁN HÀNG & ĐƠN HÀNG

### 4.1. Tạo đơn hàng (Sales Order)
*   **Giao diện bán hàng:** Chọn sản phẩm (quét barcode hoặc tìm tên) -> Chọn khách hàng -> Áp dụng chiết khấu (nếu có).
*   **Hình thức xuất:** Xuất kho ngay hoặc đặt hàng giao sau.

### 4.2. Thanh toán đa phương thức
*   **Hình thức:** Tiền mặt, Chuyển khoản (QR Code), Quẹt thẻ (POS), Trả góp.
*   **Thanh toán hỗn hợp:** Cho phép kết hợp nhiều hình thức trên một đơn hàng (Ví dụ: Trả trước 5tr tiền mặt, 10tr chuyển khoản).

### 4.3. Trạng thái thanh toán & Công nợ
*   **Đặt cọc:** Ghi nhận số tiền khách trả trước cho các đơn hàng chờ giao.
*   **Thanh toán từng phần:** Theo dõi khách đã trả bao nhiêu, còn nợ bao nhiêu.
*   **Trạng thái đơn:** Mới tạo / Đã đặt cọc / Đã thanh toán / Hoàn tiền.

---

## 5. TÍNH NĂNG ĐẶC THÙ: ĐỔI HÀNG (BARTER/EXCHANGE)
*Đây là tính năng dùng hàng của mình để đổi lấy hàng cũ/mới của khách hàng hoặc đối tác.*

*   **Quy trình:**
    1.  **Xuất hàng của mình:** Chọn sản phẩm trong kho -> Định giá xuất đổi.
    2.  **Thu hồi hàng đối tác:** Khai báo thông tin hàng thu về (Tên, tình trạng) -> Định giá thu mua.
    3.  **Xử lý chênh lệch:**
        *   Nếu giá trị hàng mình > hàng đối tác: Khách phải trả thêm tiền (Ghi vào doanh thu).
        *   Nếu giá trị hàng mình < hàng đối tác: Mình bù thêm tiền hoặc ghi nợ cho khách.
*   **Hệ thống:** Tự động trừ kho sản phẩm của mình và thêm vào kho một mã "Hàng đổi trả/Hàng cũ" (tùy cấu hình).

---

## 6. BÁO CÁO & PHÂN TÍCH (ANALYTICS)

### 6.1. Doanh thu & Lợi nhuận
*   Báo cáo chi tiết theo ngày, tháng, quý, năm.
*   Báo cáo theo phương thức thanh toán (Tiền mặt bao nhiêu, Chuyển khoản bao nhiêu).
*   **Lợi nhuận gộp:** (Giá bán - Giá vốn).

### 6.2. Báo cáo kho
*   Danh sách sản phẩm sắp hết hàng (dưới định mức tồn).
*   Báo cáo giá trị tồn kho hiện tại (Tổng số tiền đang nằm trong kho).

### 6.3. Báo cáo công nợ
*   Danh sách khách hàng còn nợ.
*   Danh sách các khoản phải trả nhà cung cấp.

---

## 7. YÊU CẦU KỸ THUẬT & GIAO DIỆN (BỔ SUNG)

*   **Phân quyền:**
    *   *Admin:* Toàn quyền.
    *   *Nhân viên kho:* Chỉ thấy nhập/xuất/kiểm hàng.
    *   *Nhân viên bán hàng:* Chỉ thấy tạo đơn/thanh toán/đổi hàng.
*   **Tính bảo mật:** Sao lưu dữ liệu hàng ngày (Backup), lưu lại lịch sử chỉnh sửa phiếu (Log).
*   **In ấn:** Hỗ trợ in hóa đơn bán hàng (A4, A5 hoặc hóa đơn nhiệt 80mm), in phiếu kiểm kho.

---

### Tóm tắt các điểm mới được bổ sung so với yêu cầu ban đầu:
1.  **Quản lý Khách hàng:** Cần thiết để theo dõi công nợ và trạng thái thanh toán (mục e).
2.  **Định mức tồn tối thiểu:** Giúp chủ kho chủ động nhập hàng.
3.  **Thẻ kho (Log):** Để truy vết lỗi khi thất thoát hàng hóa.
4.  **Logic xử lý chênh lệch khi đổi hàng:** Giúp hạch toán tài chính chính xác.
5.  **Báo cáo lợi nhuận:** Không chỉ nhìn thấy doanh thu mà còn thấy được hiệu quả kinh doanh.

Bạn thấy bản SPEC này đã bao quát hết các tình huống thực tế tại kho của mình chưa? Nếu cần thêm tính năng quét mã vạch bằng điện thoại hay kết nối với các sàn TMĐT, tôi sẽ bổ sung tiếp.
