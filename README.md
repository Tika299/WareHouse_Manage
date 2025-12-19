# ĐẶC TẢ HỆ THỐNG QUẢN LÝ KHO VÀ BÁN HÀNG

## 1. TỔNG QUAN HỆ THỐNG
Hệ thống hỗ trợ quản lý vòng đời sản phẩm từ khâu nhập kho, tính toán giá vốn bình quân gia quyền, quản lý bán hàng đa phương thức, công nợ khách hàng và nghiệp vụ đổi hàng (Barter).

---

## 2. QUẢN LÝ DANH MỤC SẢN PHẨM (PRODUCT CRUD)
Mỗi sản phẩm lưu trữ các trường dữ liệu sau:
*   **Thông tin cơ bản:** Mã SKU (Barcode), Tên sản phẩm, Đơn vị tính, Danh mục, Hình ảnh, Tồn kho hiện tại.
*   **Nhóm dữ liệu giá:**
    *   **Giá nhập NCC:** Giá mua từ nhà cung cấp (Cập nhật theo phiếu nhập mới nhất).
    *   **Giá vốn (Cost Price):** Tính theo phương pháp Bình quân gia quyền (Có cộng phí phát sinh).
    *   **Mức chênh lệch sỉ:** Số tiền cộng thêm (Người dùng nhập tay).
    *   **Mức chênh lệch lẻ:** Số tiền cộng thêm (Người dùng nhập tay).
    *   **Giá sỉ bán ra:** = Giá vốn + Mức chênh lệch sỉ.
    *   **Giá lẻ bán ra:** = Giá vốn + Mức chênh lệch lẻ.

---

## 3. QUẢN LÝ NHÀ CUNG CẤP & PHIẾU NHẬP HÀNG

### 3.1. Nhà cung cấp (Supplier)
*   Quản lý thông tin: Tên, SĐT, Địa chỉ.
*   Theo dõi **Công nợ NCC**: Tổng tiền nợ tích lũy từ các phiếu nhập chưa thanh toán hết.

### 3.2. Phiếu nhập hàng (Purchase Order)
*   **Thao tác:** Chọn NCC -> Chọn danh sách sản phẩm -> Số lượng nhập -> Giá nhập NCC.
*   **Chi phí phát sinh:** Cho phép nhập tổng chi phí (Vận chuyển, phí bốc xếp, thuế...).
*   **Cơ chế phân bổ chi phí:** 
    *   Tỷ lệ phân bổ sản phẩm A = (Giá trị nhập SP A) / (Tổng giá trị đơn nhập hàng)
    *   Chi phí phân bổ đơn vị SP A = (Tỷ lệ phân bổ A * Tổng chi phí phát sinh) / Số lượng nhập A
*   **Giá nhập thực tế (Landed Cost) của SP A** = Giá nhập NCC + Chi phí phân bổ đơn vị SP A.

---

## 4. LOGIC TÍNH GIÁ VỐN & CẬP NHẬT GIÁ BÁN
Khi phiếu nhập kho được xác nhận (Duyệt), hệ thống tự động thực hiện:

**Bước 1: Tính lại Giá vốn mới (Bình quân gia quyền)**
> **Giá vốn mới** = [(Tồn hiện tại * Giá vốn hiện tại) + (Số lượng nhập mới * Giá nhập thực tế)] / (Tồn hiện tại + Số lượng nhập mới)

**Bước 2: Cập nhật Giá bán tự động**
> **Giá lẻ** = Giá vốn mới + Mức chênh lệch lẻ (Nhập tay)
>
> **Giá sỉ** = Giá vốn mới + Mức chênh lệch sỉ (Nhập tay)

---

## 5. TẠO ĐƠN HÀNG & THANH TOÁN

### 5.1. Tạo đơn hàng (Sales Order)
*   Quét mã vạch hoặc tìm kiếm sản phẩm.
*   Chọn loại giá (Sỉ/Lẻ) tùy theo đối tượng khách hàng.
*   Giảm tồn kho ngay sau khi xuất hóa đơn.

### 5.2. Thanh toán đa phương thức (Payment)
*   **Hình thức:** Tiền mặt, Chuyển khoản, Quẹt thẻ, Trả góp.
*   **Hỗ trợ thanh toán hỗn hợp:** Cho phép khách trả bằng nhiều hình thức trên một đơn hàng.

### 5.3. Trạng thái thanh toán & Công nợ khách hàng
*   **Đặt cọc:** Ghi nhận tiền cọc cho đơn hàng chờ giao.
*   **Trạng thái:** *Chưa thanh toán / Trả một phần / Đã thanh toán.*
*   **Công nợ:** Số tiền khách còn thiếu sẽ được cộng vào sổ nợ của Khách hàng đó.

---

## 6. NGHIỆP VỤ ĐỔI HÀNG (BARTER/EXCHANGE)
Cơ chế trao đổi hàng hóa linh hoạt:
*   **Xuất hàng của mình:** Chọn hàng trong kho -> Xuất theo giá bán (Sỉ/Lẻ). Hệ thống trừ tồn kho và ghi nhận giá vốn để tính lợi nhuận.
*   **Thu hàng đối tác:** Khai báo thông tin hàng thu về và **Giá định giá thu mua**. 
    *   Hàng thu về được nhập vào kho như một phiếu nhập hàng.
    *   Hệ thống tính lại Giá vốn cho mã hàng này (nếu đã có trong danh mục).
*   **Xử lý chênh lệch:**
    *   Nếu (Giá xuất > Giá thu): Khách trả thêm tiền hoặc ghi nợ cho khách.
    *   Nếu (Giá xuất < Giá thu): Mình trả thêm tiền cho khách hoặc ghi nợ NCC.

---

## 7. QUẢN LÝ KHO & KIỂM HÀNG

### 7.1. Phiếu kiểm hàng (Stock Audit)
*   So sánh "Tồn thực tế" và "Tồn hệ thống".
*   Hệ thống tự động điều chỉnh kho về số thực tế.
*   Giá trị chênh lệch (Thừa/Thiếu) được hạch toán theo **Giá vốn hiện tại**.

### 7.2. Thẻ kho (Stock Log)
*   Truy xuất chi tiết lịch sử: Ngày giờ, Người thực hiện, Loại giao dịch (Bán, Nhập, Đổi, Kiểm), Số lượng biến động và Số dư tồn cuối.

---

## 8. BÁO CÁO DOANH THU & CHI TIẾT

### 8.1. Báo cáo Doanh thu
*   Doanh thu chi tiết theo ngày, tháng, năm.
*   Thống kê doanh thu theo từng phương thức thanh toán.

### 8.2. Báo cáo Lợi nhuận
*   **Giá vốn hàng bán (COGS):** Tổng số lượng bán * Giá vốn (tại thời điểm xuất).
*   **Lợi nhuận gộp** = Doanh thu thực tế - Giá vốn hàng bán.

### 8.3. Báo cáo Công nợ
*   Danh sách khách nợ (Phải thu).
*   Danh sách nợ nhà cung cấp (Phải trả).

---

## 9. PHÂN QUYỀN HỆ THỐNG
*   **Quản trị viên (Admin):** Toàn quyền cấu hình, xem báo cáo lợi nhuận, sửa mức chênh lệch giá.
*   **Nhân viên kho:** Thực hiện nhập kho, kiểm hàng, xem thẻ kho. Không xem được doanh thu/giá bán.
*   **Nhân viên bán hàng:** Tạo đơn hàng, thanh toán, đổi hàng. Không xem được giá vốn và báo cáo lãi lỗ.
