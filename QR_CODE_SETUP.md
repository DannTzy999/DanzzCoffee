# QR Code Configuration

## Instruksi Setup QR Code Payment

Setelah perubahan payment method dari "Transfer Bank" menjadi "Transfer QR", Anda perlu menyimpan gambar QR code di lokasi berikut:

**Path**: `public/storage/qr-code.png`

### Langkah-langkah:

1. **Persiapkan gambar QR code** Anda (format PNG, JPG, atau GIF)
   - Ukuran rekomendasi: 300x300px atau lebih
   - File size: Maksimal 2MB

2. **Simpan gambar** dengan nama `qr-code.png` di folder:
   ```
   public/storage/qr-code.png
   ```

3. **Gambar QR akan ditampilkan di**:
   - **Halaman Order**: Ketika user memilih "Transfer QR" di formulir pemesanan
   - **Detail Order Modal**: Ketika user/admin melihat detail order

### File yang sudah diupdate:

- ✅ `PaymentSeeder.php` - Ubah "Transfer Bank" → "Transfer QR"
- ✅ `resources/views/order/make_order.blade.php` - Tampilkan QR code di form order
- ✅ `resources/views/partials/order/order_lists.blade.php` - Update condition untuk "Transfer QR"
- ✅ `resources/views/partials/order/order_detail_modal.blade.php` - Tampilkan QR di modal detail
- ✅ `app/Http/Controllers/OrderController.php` - Hapus requirement bank_id
- ✅ `public/js/order_data.js` - Update logic untuk tampilkan QR daripada bank info

### Fitur Baru:

1. **Form Pemesanan**: 
   - User bisa lihat QR code saat memilih "Transfer QR"
   - Ditampilkan total price yang harus dibayar

2. **Daftar Order**: 
   - Button "Upload Proof" tetap muncul untuk payment QR
   - User bisa upload bukti pembayaran setelah order dibuat

3. **Detail Order**:
   - Admin dan user bisa lihat QR code di modal detail
   - QR code ditampilkan di bawah status payment

### Testing:

Setelah setup, test dengan:
1. Login sebagai customer
2. Pilih produk → "Make Order"
3. Pilih payment method "Transfer QR"
4. Lihat apakah QR code muncul dengan benar
5. Submit order
6. Upload bukti pembayaran
