# TÀI LIỆU ĐẶC TẢ: HỆ THỐNG QUẢN LÝ DOANH NGHIỆP (ERP MINI)

## 1. QUẢN LÝ TÀI CHÍNH & SỔ QUỸ (CASH FLOW)

### 1.1. Thiết lập Số dư ban đầu
*   **Mô tả:** Nhập số tiền thực tế tại quỹ tiền mặt và tài khoản ngân hàng khi bắt đầu dùng hệ thống.
*   **Ràng buộc:** Chỉ nhập **01 lần duy nhất**. Mọi biến động sau đó phải qua phiếu Thu/Chi/Chuyển khoản.

### 1.2. Phiếu Thu (Receipt Vouchers)
*   **Thu nợ khách hàng (Nợ gộp):** Chọn khách hàng -> Nhập số tiền trả -> Hệ thống trừ thẳng vào tổng nợ gộp của khách hàng (không cần chọn từng đơn).
*   **Thu khác:** Các khoản thu ngoài bán hàng.

### 1.3. Phiếu Chi (Payment Vouchers)
*   **Chi trả NCC:** Trả nợ cho Nhà cung cấp, trừ vào tổng nợ NCC.
*   **Chi phí quản lý:** Chi điện, nước, mặt bằng, lương, văn phòng phẩm... (Loại chi phí này trừ trực tiếp vào lợi nhuận ròng).

### 1.4. Chuyển khoản nội bộ (Internal Transfer)
*   **Mô tả:** Chuyển tiền qua lại giữa Tiền mặt và Ngân hàng (Ví dụ: Rút tiền ngân hàng về nhập quỹ).
*   **Hành động:** Giảm tài khoản nguồn, tăng tài khoản đích. Tổng tài sản không đổi.

---

## 2. QUẢN LÝ SẢN PHẨM & CƠ CHẾ GIÁ (PRICING & COSTING)

### 2.1. Cấu trúc giá sản phẩm
*   **Giá nhập NCC:** Cập nhật theo đơn giá phiếu nhập mới nhất.
*   **Giá vốn (Bình quân gia quyền):** Tính lại sau mỗi lần nhập hàng (bao gồm cả chi phí vận chuyển nhập hàng đã phân bổ).
*   **Giá sỉ/lẻ:** = **Giá vốn mới nhất** + **Mức chênh lệch (Nhập tay)**.

### 2.2. Logic tính Giá Vốn (Weighted Average Cost)
Khi nhập kho, hệ thống thực hiện:
1.  **Phân bổ phí nhập:** Chia đều chi phí phát sinh (vận chuyển nhập, thuế) vào giá trị từng món hàng theo tỷ lệ.
2.  **Tính giá vốn mới:** 
    `Giá vốn mới = [(Tồn kho cũ * Giá vốn cũ) + (Số lượng nhập * Giá nhập đã gồm phí)] / (Tổng tồn mới)`

---

## 3. QUẢN LÝ ĐƠN HÀNG & VẬN CHUYỂN (SALES & LOGISTICS)

### 3.1. Tạo đơn hàng (Sales Order)
*   Chọn sản phẩm, khách hàng, phương thức thanh toán (Tiền mặt, Chuyển khoản, Thẻ, Trả góp).
*   Ghi nhận trạng thái: Đặt cọc, Trả một phần hoặc Thanh toán đủ.

### 3.2. Quản lý Vận chuyển (Shipping Management)
Đây là phần mới bổ sung để đối soát đơn vị vận chuyển:
*   **Đơn vị vận chuyển:** Danh sách load từ Database (Ví dụ: GHTK, Viettel Post, Xe tải nhà...).
*   **Phí vận chuyển:** Nhập số tiền phí ship.
*   **Đối tượng chịu phí (Payor):** 
    *   **Khách chịu:** Phí ship cộng vào tổng đơn hàng khách phải trả.
    *   **Mình (Shop) chịu:** Phí ship không cộng vào đơn khách, nhưng ghi nhận vào "Chi phí bán hàng" của doanh nghiệp để trừ vào lợi nhuận.
*   **Mục tiêu:** Để cuối tháng đối soát tổng tiền phí ship phải trả cho từng đơn vị vận chuyển.

---

## 4. NGHIỆP VỤ ĐỐI TÁC & CÔNG NỢ (DEBT & BARTER)

### 4.1. Lịch sử biến động nợ (Credit Log)
Mọi giao dịch liên quan đến tiền nợ đều được ghi nhật ký (không thể sửa):
*   **Cấu trúc Log:** [Ngày giờ] | [Mã phiếu] | [Nội dung lý do] | [Biến động (+/-)] | [Dư nợ mới].
*   Dùng để đối soát khi khách hàng thắc mắc về tổng nợ gộp.

### 4.2. Đổi hàng hóa (Barter)
*   **Xuất hàng của mình:** Tính theo giá bán lẻ/sỉ hiện tại.
*   **Thu hàng đối tác:** Tính theo giá định giá thu mua.
*   **Xử lý chênh lệch:** Cộng/trừ phần chênh lệch trực tiếp vào **Tổng nợ gộp** và ghi nhận vào **Credit Log**.

---

## 5. QUẢN LÝ KHO & KIỂM HÀNG
*   **Phiếu kiểm hàng:** Điều chỉnh kho thực tế. Chênh lệch thừa/thiếu được định giá bằng **Giá vốn hiện tại** để tính toán biến động tài sản.
*   **Thẻ kho:** Nhật ký chi tiết nhập/xuất/tồn của từng sản phẩm.

---

## 6. HỆ THỐNG BÁO CÁO (ANALYTICS)

### 6.1. Báo cáo Kết quả kinh doanh
*   **Doanh thu:** Tổng tiền bán hàng (theo ngày/tháng).
*   **Lợi nhuận gộp:** Doanh thu - Giá vốn hàng bán.
*   **Lợi nhuận ròng:** Lợi nhuận gộp - Phí quản lý (điện nước) - Phí vận chuyển (phần shop chịu).

### 6.2. Báo cáo Công nợ & Sổ quỹ
*   **Sổ quỹ:** Chi tiết thu/chi/chuyển khoản của Tiền mặt và Ngân hàng.
*   **Công nợ:** Danh sách nợ phải thu (Khách hàng) và nợ phải trả (NCC/Đơn vị vận chuyển).

---

## 7. TỔNG HỢP CÔNG THỨC (DÀNH CHO COPY VÀO WORD)

**1. Công thức Giá vốn (Bình quân gia quyền):**
`Giá vốn mới = [(Tồn kho cũ * Giá vốn cũ) + (Số lượng nhập mới * Giá nhập thực tế)] / (Tồn kho cũ + Số lượng nhập mới)`

**2. Công thức Giá bán (Theo yêu cầu):**
`Giá bán = Giá vốn mới nhất + Mức chênh lệch (Do người dùng tự nhập tay)`

**3. Đối soát Vận chuyển:**
*   Nếu khách chịu: `Tổng tiền đơn hàng = Tiền hàng + Phí vận chuyển`.
*   Nếu shop chịu: `Tổng tiền đơn hàng = Tiền hàng`. (Hệ thống ghi nợ đơn vị vận chuyển một khoản bằng Phí vận chuyển).

**4. Số dư tài khoản:**
`Số dư hiện tại = Số dư ban đầu + (Tổng Thu + Tổng nhận chuyển khoản) - (Tổng Chi + Tổng chuyển khoản đi)`

**5. Lịch sử nợ (Credit Log):**
`Dư nợ cuối = Dư nợ đầu kỳ + (Mua đơn hàng mới) - (Trả hàng/Đổi hàng) - (Thanh toán phiếu thu)`

---

## 8. PHÂN QUYỀN (ROLES)
*   **Chủ doanh nghiệp (Admin):** Nhập số dư đầu kỳ, xem báo cáo lợi nhuận ròng, quản lý giá vốn.
*   **Kế toán:** Quản lý Phiếu Thu/Chi, Chuyển khoản nội bộ, Đối soát phí vận chuyển và Công nợ.
*   **Nhân viên bán hàng/Kho:** Tạo đơn hàng, chọn đơn vị vận chuyển, nhập kho, kiểm hàng. (Bị ẩn giá vốn và lợi nhuận).
