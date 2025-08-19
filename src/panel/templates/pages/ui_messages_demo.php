<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UI Mesaj Tasarımları Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 30px;
            gap: 20px;
        }

        /* --- Custom Modal (Onay Kutusu) Stilleri --- */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .custom-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .custom-modal-card {
            overflow: hidden;
            position: relative;
            background-color: #ffffff;
            text-align: left;
            border-radius: 0.5rem;
            max-width: 350px;
            width: 100%;
            box-shadow:
                0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .custom-modal-header {
            padding: 1.25rem 1rem 1rem 1rem;
            background-color: #ffffff;
            position: relative;
        }

        /* Modala özel yatay çubuk */
        .custom-modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 50px;
            height: 5px;
            background-color: #339999;
            border-radius: 0 0 5px 5px;
            transform: translateX(-50%);
            left: 50%;
        }


        .custom-modal-image {
            display: flex;
            margin-left: auto;
            margin-right: auto;
            background-color: #e0f2f1;
            flex-shrink: 0;
            justify-content: center;
            align-items: center;
            width: 3rem;
            height: 3rem;
            border-radius: 9999px;
            margin-top: 1rem;
        }

        .custom-modal-image svg {
            color: #339999;
            width: 1.5rem;
            height: 1.5rem;
        }

        .custom-modal-content {
            margin-top: 0.75rem;
            text-align: center;
            padding: 0 1rem;
        }

        .custom-modal-title {
            color: #111827;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.5rem;
        }

        .custom-modal-message {
            margin-top: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .custom-modal-actions {
            margin: 0.75rem 1rem;
            background-color: #f9fafb;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .custom-modal-button {
            display: inline-flex;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            line-height: 1.5rem;
            font-weight: 500;
            justify-content: center;
            width: 100%;
            border-radius: 0.375rem;
            border-width: 1px;
            border-color: transparent;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            cursor: pointer;
        }

        .custom-modal-desactivate {
            background-color: #339999;
            color: #ffffff;
        }
        .custom-modal-desactivate:hover {
            background-color: #2a7f7f;
        }

        .custom-modal-cancel {
            background-color: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .custom-modal-cancel:hover {
            background-color: #f0f2f5;
        }

        /* --- Custom Toast (Bildirim Kutusu) Stilleri --- */
        #toastContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1060;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .custom-toast {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* Renkli sol kenar ve alt geri sayım çubuğu için */
        .custom-toast .icon-col {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            position: relative;
        }

        .custom-toast .icon-col::before {
            content: '';
            position: absolute;
            left: -15px;
            top: 0;
            bottom: 0;
            width: 5px;
            background-color: currentColor;
            border-radius: 0 5px 5px 0;
        }

        /* Geri Sayım Status Çubuğu */
        .custom-toast::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px; /* Yükseklik */
            background-color: currentColor; /* İlgili alert rengini alır */
            transform-origin: right; /* SAĞDAN SOLA KAYMASI İÇİN BURASI DÜZELTİLDİ */
            transform: scaleX(1); /* Başlangıçta tam genişlik */
            /* transition: transform linear; */ /* Transition süresi JS ile ayarlanacağı için buradan kaldırıldı */
        }

        .custom-toast.hiding::after {
            transform: scaleX(0); /* Sağa doğru küçülerek kaybolsun */
            transition: transform var(--toast-bar-duration) linear; /* BURADA DÜZELTİLDİ */
        }

        /* Mesaj kutusunun kendisinin kaybolma animasyonu */
        .custom-toast.fade-out {
            opacity: 0;
            transform: translateX(100%);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }


        .custom-toast .content-col {
            flex-grow: 1;
        }

        .custom-toast .content-col .toast-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .custom-toast .content-col .toast-message {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .custom-toast .close-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #ccc;
            cursor: pointer;
            padding: 5px;
            line-height: 1;
            margin-left: auto;
        }

        /* Renk varyantları */
        .toast-info { color: #2196f3; }
        .toast-info .icon-col { background-color: #e3f2fd; }

        .toast-warning { color: #ffc107; }
        .toast-warning .icon-col { background-color: #fff3e0; }

        .toast-success { color: #4caf50; }
        .toast-success .icon-col { background-color: #e8f5e9; }

        .toast-alert { color: #f44336; }
        .toast-alert .icon-col { background-color: #ffebee; }

        /* Close butonu için daha spesifik stil */
        .custom-toast .close-btn:hover {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-5">UI Mesaj Tasarımları Demo</h1>

        <h3 class="mb-3">Onay Modalı Örnekleri</h3>
        <button class="btn btn-primary mb-3" onclick="showCustomModal('Bu blog yazısını silmek istediğinizden emin misiniz?', 'Silme Onayı', 'Evet, Sil', 'İptal', () => showCustomToast('Başarılı', 'Silme onaylandı!', 'success'), () => showCustomToast('Bilgi', 'Silme iptal edildi!', 'info'));">
            Silme Onayı Modalını Göster
        </button>
        <button class="btn btn-success mb-5" onclick="showCustomModal('Mevcut öne çıkarılan blog yazısı ile değiştirilecektir. Onaylıyor musunuz?', 'Öne Çıkarma Onayı', 'Evet, Değiştir', 'Vazgeç', () => showCustomToast('Başarılı', 'Öne çıkarma onaylandı!', 'success'), () => showCustomToast('Bilgi', 'Öne çıkarma iptal edildi!', 'info'));">
            Öne Çıkarma Onayı Modalını Göster
        </button>


        <h3 class="mb-3">Mesaj Kutuları Örnekleri</h3>
        <button class="btn btn-info mb-3" onclick="showCustomToast('Bilgi', 'Bu bilgilendirici bir mesajdır.', 'info', 5000);">
            Bilgi Mesajı Göster (5sn)
        </button>
        <button class="btn btn-warning mb-3" onclick="showCustomToast('Uyarı', 'Bu bir uyarı mesajıdır.', 'warning', 7000);">
            Uyarı Mesajı Göster (7sn)
        </button>
        <button class="btn btn-success mb-3" onclick="showCustomToast('Başarılı', 'İşleminiz başarıyla tamamlandı!', 'success', 3000);">
            Başarı Mesajı Göster (3sn)
        </button>
        <button class="btn btn-danger mb-3" onclick="showCustomToast('Hata', 'İşlem sırasında bir hata oluştu.', 'alert', 10000);">
            Hata Mesajı Göster (10sn)
        </button>


        <div class="custom-modal-overlay" id="customConfirmModal">
            <div class="custom-modal-card">
                <div class="custom-modal-header">
                    <div class="custom-modal-image">
                        <svg aria-hidden="true" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" stroke-linejoin="round" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div class="custom-modal-content">
                        <span class="custom-modal-title" id="confirmModalTitle">Başlık</span>
                        <p class="custom-modal-message" id="confirmModalMessage">Mesaj</p>
                    </div>
                    <div class="custom-modal-actions">
                        <button class="custom-modal-button custom-modal-desactivate" type="button" id="confirmModalConfirmBtn">Onayla</button>
                        <button class="custom-modal-button custom-modal-cancel" type="button" id="confirmModalCancelBtn">İptal</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1060; display: flex; flex-direction: column; gap: 10px;">
            </div>

    </div>

    <script>
        // --- Custom Modal (Onay Kutusu) İşlevselliği ---
        const customConfirmModal = document.getElementById('customConfirmModal');
        const confirmModalTitle = document.getElementById('confirmModalTitle');
        const confirmModalMessage = document.getElementById('confirmModalMessage');
        const confirmModalConfirmBtn = document.getElementById('confirmModalConfirmBtn');
        const confirmModalCancelBtn = document.getElementById('confirmModalCancelBtn');

        let onConfirmCallback = null;
        let onCancelCallback = null;

        function showCustomModal(message, title, confirmText, cancelText, confirmCallback, cancelCallback) {
            confirmModalTitle.textContent = title;
            confirmModalMessage.textContent = message;
            confirmModalConfirmBtn.textContent = confirmText;
            confirmModalCancelBtn.textContent = cancelText;

            onConfirmCallback = confirmCallback;
            onCancelCallback = cancelCallback;

            customConfirmModal.classList.add('show');
        }

        confirmModalConfirmBtn.addEventListener('click', function() {
            customConfirmModal.classList.remove('show');
            if (onConfirmCallback) {
                onConfirmCallback();
            }
        });

        confirmModalCancelBtn.addEventListener('click', function() {
            customConfirmModal.classList.remove('show');
            if (onCancelCallback) {
                onCancelCallback();
            }
        });

        // Modal dışına tıklanırsa kapat (isteğe bağlı)
        customConfirmModal.addEventListener('click', function(event) {
            if (event.target === customConfirmModal) { // Sadece overlay'e tıklanırsa
                customConfirmModal.classList.remove('show');
                if (onCancelCallback) { // Dış tıklamayı iptal olarak değerlendirebiliriz
                    onCancelCallback();
                }
            }
        });


        // --- Custom Toast (Bildirim Kutusu) İşlevselliği ---
        const toastContainer = document.getElementById('toastContainer');

        function showCustomToast(title, message, type = 'info', duration = 3000) {
            const toast = document.createElement('div');
            toast.classList.add('custom-toast', `toast-${type}`);

            let iconClass = '';
            switch (type) {
                case 'info': iconClass = 'bi bi-info-circle-fill'; break;
                case 'warning': iconClass = 'bi bi-exclamation-triangle-fill'; break;
                case 'success': iconClass = 'bi bi-check-circle-fill'; break;
                case 'alert': iconClass = 'bi bi-exclamation-circle-fill'; break;
                default: iconClass = 'bi bi-info-circle-fill'; break;
            }

            toast.innerHTML = `
                <div class="icon-col"><i class="${iconClass}"></i></div>
                <div class="content-col">
                    <p class="toast-title">${title}</p>
                    <p class="toast-message">${message}</p>
                </div>
                <button class="close-btn"><i class="bi bi-x"></i></button>
            `;

            // toast'ı önce DOM'a ekle (çünkü getComputedStyle ve offsetHeight gibi özellikler için DOM'da olması gerekir)
            toastContainer.appendChild(toast);

            // Geri sayım çubuğu animasyonu için stil
            // Çubuğun animasyon süresini dinamik olarak ayarla
            const barDuration = duration - 300; // Çıkış animasyonundan biraz daha kısa
            toast.style.setProperty('--toast-bar-duration', `${barDuration / 1000}s`);


            // Kapatma butonu işlevi
            toast.querySelector('.close-btn').addEventListener('click', function() {
                toast.remove(); // Direkt kaldır, bu manuel kapatma
                repositionToasts(); // Diğer toast'ları yeniden konumlandır
            });

            // Otomatik kapanma
            if (duration > 0) {
                // Animasyonu başlatmak için küçük bir gecikme
                setTimeout(() => {
                    toast.classList.add('hiding'); // Çubuğun animasyonunu tetikle
                }, 50); // Küçük bir gecikme

                // Toast'ın tamamen kaybolması
                setTimeout(() => {
                    toast.classList.add('fade-out'); // Toast'ın kendisini kaybolma animasyonu
                    toast.addEventListener('transitionend', function handler() {
                        toast.removeEventListener('transitionend', handler); // Event listener'ı kaldır
                        toast.remove(); // Animasyon bitince kaldır
                        repositionToasts(); // Diğer toast'ları yeniden konumlandır
                    });
                }, duration);
            }

            repositionToasts(); // Yeni toast eklendiğinde diğer toast'ları yeniden konumlandır
        }

        // Toast'ları yeniden konumlandıran fonksiyon
        function repositionToasts() {
            let currentTop = 20;
            Array.from(toastContainer.children).forEach(toast => {
                toast.style.top = currentTop + 'px';
                currentTop += toast.offsetHeight + 10; // Toast yüksekliği + boşluk
            });
        }
    </script>
</body>
</html>