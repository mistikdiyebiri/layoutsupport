<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Pazmanya Support - Destek Talep Sistemi

Pazmanya Support, müşteri destek taleplerini yönetmek için geliştirilmiş kapsamlı bir web uygulamasıdır. Laravel 10 ile geliştirilen bu sistem, müşteri talepleri, departman yönetimi, personel performans takibi ve iş yükü dengeleme gibi özelliklere sahiptir.

## Özellikler

### Kullanıcı ve Rol Yönetimi
- Çoklu rol yapısı: Admin, Staff, Teknik Destek, Müşteri
- Her role özel yetkiler ve görünüm
- Departmanlara bağlı kullanıcı yönetimi (çoklu departman ataması)

### Destek Talepleri (Ticket)
- Müşteriler için talep oluşturma ve takip imkanı
- Departmanlara göre otomatik veya manuel atama
- Duruma göre filtreleme (açık, kapalı, beklemede)
- Öncelik düzeyi belirleme
- Personel için biletleri transfer etme özelliği
- Dosya eki yükleme desteği

### Hazır Yanıtlar
- Personel için sık kullanılan yanıtları kaydetme
- Departmana özel veya tüm personele açık hazır yanıtlar
- Dinamik değişken desteği (ticket bilgileri otomatik ekleniyor)

### Raporlama
- Personel performans raporları
- Departman iş yükü raporu
- Mesai saati raporları

### Bildirim Sistemi
- Önemli olaylar için sistem bildirimleri
- Yeni destek talebi bildirimleri

### Mesai Takibi
- Personel çalışma saatlerini düzenleme
- Aktif personelleri görüntüleme

## Teknik Altyapı

### Veritabanı Şeması
- **users**: Kullanıcı bilgileri
- **departments**: Departman bilgileri
- **department_user**: Kullanıcı ve departman ilişkileri (many-to-many)
- **tickets**: Destek talepleri
- **ticket_replies**: Talep yanıtları
- **ticket_files**: Talep ekleri
- **roles ve permissions**: Spatie/Laravel-permission paketi ile rol ve izin yönetimi
- **canned_responses**: Hazır yanıtlar
- **notifications**: Sistem bildirimleri
- **settings**: Sistem ayarları

### Önemli Kod Dosyaları
- **app/Http/Controllers/TicketController.php**: Bilet işlemleri kontrolcüsü
- **app/Http/Controllers/AdminController.php**: Admin paneli işlemleri
- **app/Http/Controllers/DashboardController.php**: Dashboard görünümleri
- **app/Http/Controllers/CannedResponseController.php**: Hazır yanıt işlemleri
- **app/Models/Ticket.php**: Bilet modeli
- **app/Models/User.php**: Kullanıcı modeli
- **app/Models/Department.php**: Departman modeli
- **app/Services/TicketAssignmentService.php**: Bilet atama servisi

### Middleware
- **app/Http/Middleware/CheckRole.php**: Rol kontrol middleware'i
- **app/Http/Middleware/TrackUserActivity.php**: Kullanıcı aktivite izleme

## Kurulum

1. Repoyu klonlayın:
```bash
git clone https://github.com/kullanici/layoutsupport.git
cd layoutsupport
```

2. Bağımlılıkları yükleyin:
```bash
composer install
npm install
```

3. .env dosyasını oluşturun:
```bash
cp .env.example .env
php artisan key:generate
```

4. .env dosyasını düzenleyerek veritabanı ayarlarını yapın.

5. Migrasyonları ve demo verileri yükleyin:
```bash
php artisan migrate --seed
```

6. Uygulamayı çalıştırın:
```bash
php artisan serve
```

## Yönetici Hesabı
- **Email**: admin@example.com
- **Şifre**: password

## Uyarılar ve Önemli Notlar

### Bilinmesi Gereken Çözümler
- SQLite kullanırken HAVING clause hatası çözümü için AdminController.php içinde staffWorkload metodu düzeltildi
- department_id sütunu users tablosundan kaldırılarak many-to-many ilişkisi kuruldu
- TicketAssignmentService ile bilet atama otomatikleştirildi

### Bilinen Hatalar ve Çözümleri
- CannedResponseController'da form gönderiminde body/message alanları çakışması çözüldü
- AdminController içindeki withAvg metodu hatası düzeltildi
- Transferring tickets functionality was added to adapt to departmental changes

## Özelleştirme Kılavuzu

### Yeni Rol Ekleme
Yeni rol eklemek için roles tablosuna veri eklenmeli ve ilgili izinler tanımlanmalıdır.

### Yeni Filtre Ekleme
Ticket listeleme ekranlarında filtreler `TicketController.php` dosyasında tanımlanıyor.

### Mail Konfigürasyonu
Bildirimler için mail ayarları `.env` dosyasından yapılandırılabilir.

## Tamamlanan İyileştirmeler
- Hazır yanıtlar yönetimi eklendi
- Bilet transfer özellikleri geliştirildi
- Footer güncellendi ve uygulama adı "Pazmanya Support" olarak değiştirildi
- Personel - Departman ilişkisi many-to-many olarak güncellendi
- Müşteri ekranından profil ve şifre değiştirme seçenekleri kaldırıldı
- Admin menüsüne hazır yanıtlar modülü eklendi
- Rapor sisteminde performans ölçümleri eklendi

## Planlanan Geliştirmeler
- Bilgilendirme e-posta şablonları eklenmesi
- Otomatik yanıt kuralları oluşturma
- Tatil günleri yönetimi
- Daha kapsamlı istatistik paneli

## İletişim
Bu proje Pazmanya tarafından geliştirilmektedir. Sorularınız için info@pazmanya.tr adresine e-posta gönderebilirsiniz.

## Lisans
Bu proje özel kullanım içindir ve tüm hakları saklıdır. İzinsiz kullanımı yasaktır.
