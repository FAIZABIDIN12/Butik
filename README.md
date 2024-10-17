<p align="center">
    
</p>

## Tentang Aplikasi

Aplikasi POS atau point of sales adalah aplikasi yang digunakan untuk mengelola transaksi pada sebuah toko atau oleh kasir. Aplikasi ini dibuat menggunakan Laravel v8.* dan minimal PHP v7.4.

## Beberapa Fitur yang tersedia:
- Manajemen Kategori Produk
- penjualan & pembelian
- cashflow
- neraca
- profitloss
- cetak nota
- dll


## Setup Aplikasi
Jalankan perintah 
```bash
composer update
```
atau:
```bash
composer install
```
Copy file .env dari .env.example
```bash
cp .env.example .env
```
Konfigurasi file .env
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE="nama db mu"
DB_USERNAME=root
DB_PASSWORD=
```
Opsional
```bash
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:QGRW4K7UVzS2M5HE2ZCLlUuiCtOIzRSfb38iWApkphE=
APP_DEBUG=true
APP_URL=http://example-app.test
```
Generate key
```bash
php artisan key:generate
```
Migrate database
```bash
php artisan migrate
```
Seeder table User, Pengaturan
```bash
php artisan db:seed
```
Menjalankan aplikasi
```bash
php artisan serve
```

## License
