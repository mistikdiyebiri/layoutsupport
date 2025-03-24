import './bootstrap';

// jQuery'nin doğru yüklendiğinden emin olalım
document.addEventListener("DOMContentLoaded", function() {
    // jQuery kontrolü
    if (typeof $ === 'undefined') {
        console.error('jQuery yüklenmedi!');
        return;
    }

    // Bootstrap 5 scriptlerini dahil et
    const bootstrapScript = document.createElement('script');
    bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
    document.body.appendChild(bootstrapScript);

    // Sadece kullanıcı giriş yapmışsa bildirimleri başlat
    if (document.querySelector('.notification-dropdown')) {
        initNotifications();
    }
});

// Bildirim sistemini başlat
function initNotifications() {
    // İlk bildirimleri yükle
    loadNotifications();
    
    // Her 30 saniyede bildirimleri yenile
    setInterval(loadNotifications, 30000);
    
    // Tüm bildirimleri okunan olarak işaretle
    $(document).on('click', '#markAllAsRead', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadNotifications();
            }
        });
    });
}

// Bildirimleri yükle
function loadNotifications() {
    if (!$('#notificationDropdown').length) return;
    
    var notificationBadge = $('.notification-badge');
    var notificationDropdown = $('.notification-dropdown');
    var notificationLoader = $('.notification-loader');
    var notificationList = $('.notification-list');
    
    // Yükleme göstergesini göster
    notificationLoader.show();
    notificationList.html('');
    
    $.ajax({
        url: '/notifications/unread',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            // Yükleme göstergesini gizle
            notificationLoader.hide();
            
            if (response.count > 0) {
                // Bildirim sayısını göster
                notificationBadge.text(response.count).show();
                
                // Bildirimleri listeye ekle
                var notifications = response.notifications;
                var html = '';
                
                for (var i = 0; i < notifications.length; i++) {
                    html += `
                    <a class="dropdown-item" href="${notifications[i].url}">
                        <div class="d-flex align-items-center">
                            <div>
                                <span class="badge bg-${notifications[i].type === 'info' ? 'info' : (notifications[i].type === 'warning' ? 'warning' : 'danger')} me-2">
                                    <i class="fas fa-${notifications[i].type === 'info' ? 'info-circle' : (notifications[i].type === 'warning' ? 'exclamation-triangle' : 'bell')}"></i>
                                </span>
                            </div>
                            <div>
                                <div class="font-weight-bold">${notifications[i].title}</div>
                                <div class="small text-muted">${notifications[i].time}</div>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    `;
                }
                
                notificationList.append(html);
                $('#markAllAsRead').show();
            } else {
                // Bildirim yoksa badge'i gizle ve mesaj göster
                notificationBadge.hide();
                notificationList.html('<div class="text-center p-3">Yeni bildirim yok</div>');
                $('#markAllAsRead').hide();
            }
        },
        error: function(xhr, status, error) {
            console.error("Bildirimler yüklenemedi:", error);
            notificationLoader.hide();
            notificationList.html('<div class="text-center p-3">Bildirimler yüklenemedi</div>');
        }
    });
}
