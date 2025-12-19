# ĐẶC TẢ HỆ THỐNG QUẢN LÝ KHO VÀ BÁN HÀNG CHUYÊN SÂU

## I. QUẢN LÝ DANH MỤC CỐT LÕI (MASTER DATA)

### 1. Quản lý Sản phẩm (Products)
*   **Thông tin cơ bản:** Mã SKU (Barcode), Tên sản phẩm, Đơn vị tính, Danh mục, Hình ảnh.
*   **Cấu trúc giá (Trọng tâm):**
    *   **Giá nhập:** Lưu giá mua từ Nhà cung cấp của phiên nhập hàng gần nhất.
    *   **Giá vốn (Cost Price):** Tính theo phương pháp Bình quân gia quyền (có tính cả chi phí phát sinh).
    *   **Giá lẻ & Giá sỉ:** Được thiết lập dựa trên Giá nhập (Ví dụ: Giá lẻ = Giá nhập + 30%).
*   **Định mức tồn kho:** Cảnh báo khi tồn kho dưới mức tối thiểu.

### 2. Quản lý Nhà cung cấp (Suppliers)
*   Thông tin liên hệ, lịch sử nhập hàng.
*   Theo dõi **Công nợ phải trả** nhà cung cấp.

### 3. Quản lý Khách hàng (Customers)
*   Thông tin liên hệ, nhóm khách (Sỉ/Lẻ).
*   Theo dõi **Công nợ phải thu** và lịch sử mua/đổi hàng.

---

## II. QUẢN LÝ NHẬP KHO & LOGIC TÍNH GIÁ

### 1. Phiếu Nhập hàng (Purchase Order)
*   **Nghiệp vụ:** Chọn NCC -> Chọn sản phẩm -> Nhập số lượng & Giá nhập (từ NCC).
*   **Chi phí phát sinh:** Ô nhập tổng chi phí (Vận chuyển, bốc xếp, thuế...).
*   **Cơ chế phân bổ chi phí:** 
    *   Tỷ lệ phân bổ sản phẩm A = (Tổng giá trị nhập SP A) / (Tổng giá trị đơn nhập).
    *   Chi phí phân bổ cho mỗi đơn vị SP A = (Tỷ lệ phân bổ A * Tổng chi phí phát sinh) / Số lượng nhập A.
    *   **Giá nhập thực tế (Landed Cost)** = Giá nhập từ NCC + Chi phí phân bổ mỗi đơn vị.

### 2. Cập nhật Giá vốn & Giá nhập
Khi xác nhận "Nhập kho", hệ thống tự động thực hiện:
1.  **Cập nhật Giá nhập:** Lấy giá từ NCC trong phiếu này đè vào trường "Giá nhập" của sản phẩm.
2.  **Tính lại Giá vốn (Bình quân gia quyền):**
    $$\text{Giá vốn mới} = \frac{(\text{Tồn hiện tại} \times \text{Giá vốn hiện tại}) + (\text{Số lượng nhập mới} \times \text{Giá nhập thực tế})}{\text{Tồn hiện tại} + \text{Số lượng nhập mới}}$$
3.  **Tăng tồn kho** thực tế.

---

## III. QUẢN LÝ BÁN HÀNG & ĐƠN HÀNG

### 1. Tạo đơn hàng (Sales Order)
*   Hỗ trợ quét mã vạch, tìm kiếm tên sản phẩm.
*   Tự động áp dụng Giá sỉ hoặc Giá lẻ tùy theo loại khách hàng.
*   Giảm tồn kho ngay khi đơn hàng được xác nhận xuất kho.

### 2. Thanh toán đa phương thức (Payment)
*   **Hình thức:** Tiền mặt, Chuyển khoản, Quẹt thẻ, Trả góp.
*   **Thanh toán hỗn hợp:** Một đơn hàng có thể trả bằng nhiều cách (Ví dụ: 50% tiền mặt, 50% trả góp).

### 3. Trạng thái thanh toán & Công nợ
*   **Đặt cọc:** Ghi nhận tiền cọc trước khi xuất hàng.
*   **Trạng thái:** *Chưa thanh toán / Thanh toán một phần / Đã thanh toán.*
*   Hệ thống tự động đẩy phần tiền còn thiếu vào **Công nợ khách hàng**.

---

## IV. NGHIỆP VỤ ĐỔI HÀNG (BARTER/EXCHANGE)
Đây là quy trình trao đổi hàng của mình lấy hàng của đối tác/khách hàng.

1.  **Phần Xuất (Hàng của mình):**
    *   Chọn sản phẩm trong kho -> Xuất theo Giá bán (Sỉ/Lẻ).
    *   Hệ thống trừ kho và ghi nhận giá vốn tại thời điểm đó để tính lợi nhuận.
2.  **Phần Thu (Hàng của khách):**
    *   Nhập thông tin hàng thu về và **Giá định giá thu mua** (Giá này được coi là Giá nhập thực tế).
    *   Hệ thống cộng kho hàng thu về và tính lại Giá vốn cho mã hàng đó (theo công thức bình quân gia quyền).
3.  **Đối trừ giá trị:**
    *   `Giá trị chênh lệch = Tổng giá trị hàng xuất - Tổng giá trị hàng thu`.
    *   Nếu chênh lệch > 0: Khách trả thêm (Tiền mặt/Chuyển khoản) hoặc ghi nợ khách.
    *   Nếu chênh lệch < 0: Mình trả thêm cho khách hoặc ghi nợ NCC.

---

## V. KIỂM KHO & TỒN KHO (INVENTORY CONTROL)

### 1. Phiếu kiểm hàng (Stock Audit)
*   Cho phép kiểm kê theo danh mục hoặc toàn bộ kho.
*   Nhập "Số lượng thực tế" -> Hệ thống tính "Số lượng chênh lệch".
*   **Xử lý chênh lệch:** 
    *   Tự động cập nhật tồn kho về số thực tế.
    *   Ghi nhận giá trị tài sản thừa/thiếu dựa trên **Giá vốn hiện tại**.

### 2. Xuất - Nhập tồn
*   Lưu trữ lịch sử mọi biến động kho (Thẻ kho).
*   Mỗi dòng log phải hiển thị: Thời gian, Loại giao dịch (Bán hàng, Nhập hàng, Đổi hàng, Kiểm kho), Số lượng biến động, Số dư cuối.

---

## VI. BÁO CÁO & DOANH THU (ANALYTICS)

### 1. Báo cáo Doanh thu & Lợi nhuận
*   **Chi tiết theo ngày/tháng:** Doanh thu thực tế, Số đơn hàng, Giá trị trung bình đơn.
*   **Báo cáo Lãi/Lỗ:**
    *   Doanh thu thuần.
    *   Giá vốn hàng bán (được chốt tại thời điểm xuất kho).
    *   Lợi nhuận gộp = Doanh thu - Giá vốn.

### 2. Báo cáo Công nợ
*   Danh sách khách hàng nợ tiền (phải thu).
*   Danh sách nhà cung cấp mình còn nợ (phải trả).

### 3. Báo cáo Kho
*   Giá trị tồn kho hiện tại (tính theo giá vốn).
*   Danh sách sản phẩm bán chạy/bán chậm.

---

## VII. YÊU CẦU KỸ THUẬT VÀ BẢO MẬT

1.  **Phân quyền (Roles):**
    *   *Chủ cửa hàng (Admin):* Xem báo cáo lợi nhuận, chỉnh sửa giá, xóa dữ liệu.
    *   *Nhân viên bán hàng (Sales):* Chỉ tạo đơn, thu tiền, không xem được giá vốn.
    *   *Nhân viên kho (Storekeeper):* Chỉ làm phiếu nhập, kiểm hàng, không xem được doanh thu.
2.  **Toàn vẹn dữ liệu:** Không cho phép xóa các phiếu đã phát sinh giao dịch tài chính (chỉ cho phép Hủy và lưu vết).
3.  **In ấn:** Hỗ trợ in hóa đơn (Bill) và phiếu nhập/xuất kho.
