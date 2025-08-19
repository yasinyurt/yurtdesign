<?php
// ROOT_PATH ve $pdo nesnesi public_html/panel/index.php üzerinden dahil edilen src/panel/config.php'den gelir.
if (!isset($pdo)) { // $pdo nesnesinin tanımlı olup olmadığını kontrol et
    require_once ROOT_PATH . '/src/panel/config.php';
}

$post_id = null;
if (isset($_GET['id'])) {
    $post_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT); // ID'yi temizle
    if (!is_numeric($post_id) || $post_id <= 0) {
        $_SESSION['message'] = 'Geçersiz blog yazısı ID\'si.';
        $_SESSION['message_type'] = 'danger';
        header('Location: ' . BASE_URL . 'panel/blog');
        exit();
    }
} else {
    $_SESSION['message'] = 'Blog yazısı ID\'si belirtilmedi.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ' . BASE_URL . 'panel/blog');
    exit();
}

$post = null;
try {
    // is_featured sütununu da seçtiğinizden emin olun
    $stmt = $pdo->prepare("SELECT id, title, slug, content, image, category, status, is_featured FROM blog_posts WHERE id = :id LIMIT 1"); // is_featured burada seçiliyor
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        $_SESSION['message'] = 'Belirtilen ID\'ye sahip blog yazısı bulunamadı.';
        $_SESSION['message_type'] = 'danger';
        header('Location: ' . BASE_URL . 'panel/blog');
        exit();
    }
} catch (PDOException $e) {
    error_log("Blog yazısı çekilirken veritabanı hatası: " . $e->getMessage());
    $_SESSION['message'] = 'Sistem hatası: Blog yazısı yüklenirken hata oluştu.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ' . BASE_URL . 'panel/blog');
    exit();
}

$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE type = 'blog' ORDER BY name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Kategoriler çekilirken hata: " . $e->getMessage());
    $_SESSION['message'] = 'Kategoriler yüklenirken bir hata oluştu.';
    $_SESSION['message_type'] = 'danger';
}

// Editor.js içeriği için JSON string'ini ayrıştırma
$editorjs_data = json_decode($post['content'] ?? '{"blocks":[]}', true); // null coalescing operator ile default değer
if (json_last_error() !== JSON_ERROR_NONE) {
    $editorjs_data = ['blocks' => []]; // JSON parse hatası olursa boş veri
    error_log("Blog yazısı ID " . $post_id . " için Editor.js JSON ayrıştırma hatası: " . json_last_error_msg());
}

// Mevcut thumbnail URL'si
$current_thumbnail_url = $post['image'] ?? '';
?>

<div class="container-fluid mt-4">
    <h2>Blog Yazısını Düzenle: <?php echo htmlspecialchars($post['title'] ?? ''); ?></h2>
    <p class="mb-4">Mevcut blog yazısını düzenlemek için aşağıdaki formu kullanın.</p>

    <?php
    // Mesajlar (başarı veya hata) burada gösterilecek
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message_type']) . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <form action="<?php echo BASE_URL; ?>panel/blog/update/<?php echo htmlspecialchars($post['id'] ?? ''); ?>"
        method="POST" id="blogPostForm" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id'] ?? ''); ?>"> <input
            type="hidden" name="existing_image_url" value="<?php echo htmlspecialchars($current_thumbnail_url); ?>">
        <input type="hidden" name="thumbnail_removed" id="thumbnail_removed_flag" value="false">
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="input-container">
                    <input type="text" id="title" name="title"
                        value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required="">
                    <label for="title" class="label">Başlık</label>
                    <div class="underline"></div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="editorjs_content" class="form-label">İçerik</label>
            <div id="editorjs"
                style="border: 1px solid #ced4da; min-height: 250px; padding: 10px; border-radius: 0.25rem;"></div>
            <input type="hidden" name="content" id="editorjs_content_hidden_input" required>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <label class="form-label d-block mb-2">Başlık Görseli (Thumbnail)</label>
                <label class="custum-file-upload" for="thumbnail_file"
                    style="<?php echo $current_thumbnail_url ? 'display: none;' : 'display: flex;'; ?>">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="" viewBox="0 0 24 24">
                            <g stroke-width="0" id="SVGRepo_bgCarrier"></g>
                            <g stroke-linejoin="round" stroke-linecap="round" id="SVGRepo_tracerCarrier"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path fill=""
                                    d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z"
                                    clip-rule="evenodd" fill-rule="evenodd"></path>
                            </g>
                        </svg>
                    </div>
                    <div class="text">
                        <span>Görsel Yükle</span>
                        <span id="fileNameDisplay" style="font-size: 0.8rem; display: block; margin-top: 5px;"></span>
                    </div>
                    <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
                </label>
                <button type="button" id="removeThumbnail" class="btn btn-sm btn-danger mt-2"
                    style="<?php echo $current_thumbnail_url ? 'display:block;' : 'display:none;'; ?>">Görseli
                    Kaldır</button>
                <div id="thumbnailPreview" class="mt-2"
                    style="max-width: 300px; max-height: 200px; overflow: hidden; <?php echo $current_thumbnail_url ? 'display:block;' : 'display:none;'; ?>">
                    <img id="previewImage" src="<?php echo htmlspecialchars($current_thumbnail_url); ?>"
                        alt="Görsel Önizleme" style="width: 100%; height: auto; display: block;">
                </div>
                <input type="hidden" name="image" id="thumbnail_image_url"
                    value="<?php echo htmlspecialchars($current_thumbnail_url); ?>">
            </div>

            <div class="col-md-6">
                <div class="input-container mb-3">
                    <label for="category" class="label-custom">Kategori</label>
                    <select class="input-field-select" id="category" name="category" required>
                        <option value="" disabled></option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>"
                                    <?php echo (($post['category'] ?? '') == $category['name'] ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled selected>Blog İle İlgili Kategoriniz Bulunmamakta</option>
                        <?php endif; ?>
                    </select>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <label for="status" class="label-custom">Durum</label>
                    <select class="input-field-select" id="status" name="status" required>
                        <option value="" disabled></option>
                        <option value="draft" <?php echo (($post['status'] ?? '') == 'draft' ? 'selected' : ''); ?>>Taslak
                        </option>
                        <option value="published" <?php echo (($post['status'] ?? '') == 'published') ? 'selected' : ''; ?>>Yayınlandı</option>
                        <option value="archived" <?php echo (($post['status'] ?? '') == 'archived') ? 'selected' : ''; ?>>
                            Arşivlendi</option>
                    </select>
                    <div class="underline"></div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="checkbox-wrapper-46">
                <input type="checkbox" id="is_featured" name="is_featured" class="inp-cbx" value="1" <?php echo (($post['is_featured'] ?? 0) == 1) ? 'checked' : ''; ?> />
                <label for="is_featured" class="cbx">
                    <span>
                        <svg viewBox="0 0 12 10" height="10px" width="12px">
                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                        </svg>
                    </span>
                    <span>Öne Çıkar</span>
                </label>
            </div>
        </div>
        <div class="mt-4 d-flex gap-3">
            <button type="submit" class="custom-button save-button">
                <span class="text">Yazıyı Güncelle</span>
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path
                            d="M17 3H5C3.89 3 3 3.9 3 5V19C3 20.1 3.89 21 21 19V7L17 3ZM19 19H5V5H16.17L19 7.83V19ZM12 11C10.34 11 9 12.34 9 14C9 15.66 10.34 17 12 17C13.66 17 15 15.66 15 14C15 12.34 13.66 11 12 11ZM17 14H7V10H17V14Z" />
                    </svg>
                </span>
            </button>

            <a href="<?php echo BASE_URL; ?>panel/blog" class="custom-button back-button">
                <span class="text">Geri Dön</span>
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z" />
                    </svg>
                </span>
            </a>
        </div>
    </form>

    <style>
        /* THUMBNAIL CSS STİLLERİ */
        .custum-file-upload {
            height: 200px;
            width: 300px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            border: 2px dashed #cacaca;
            background-color: rgba(255, 255, 255, 1);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0px 48px 35px -48px rgba(0, 0, 0, 0.1);
        }

        .custum-file-upload .icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custum-file-upload .icon svg {
            height: 80px;
            fill: rgba(75, 85, 99, 1);
        }

        .custum-file-upload .text {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .custum-file-upload .text span {
            font-weight: 400;
            color: rgba(75, 85, 99, 1);
        }

        .custum-file-upload input {
            display: none;
        }

        /* YENİ INPUT TASARIMI (input-container) */
        .input-container {
            position: relative;
            margin-top: 30px;
            margin-bottom: 20px;
            width: 100%;
        }

        .input-container input[type="text"],
        .input-container select,
        .input-container textarea {
            font-size: 17px;
            width: 100%;
            border: none;
            border-bottom: 2px solid #9b9b9b;
            padding: 7px 0;
            /* Padding'i artırdık */
            background-color: transparent;
            outline: none;
            color: #333;
        }

        .input-container .label {
            position: absolute;
            top: 0;
            left: 0;
            color: #9b9b9b;
            transition: all 0.3s ease;
            pointer-events: none;
            font-size: 17px;
        }

        .input-container input[type="text"]:focus~.label,
        .input-container input[type="text"]:valid~.label,
        .input-container select:focus~.label,
        .input-container select:not([value=""])~.label,
        .input-container textarea:focus~.label,
        .input-container textarea:not(:placeholder-shown)~.label {
            top: -20px;
            font-size: 13px;
            color: #339999;
            font-weight: 700;
        }

        .input-container .underline {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
            background-color: #339999;
            transform: scaleX(0);
            transition: all 0.3s ease;
        }

        .input-container input[type="text"]:focus~.underline,
        .input-container input[type="text"]:valid~.underline,
        .input-container select:focus~.underline,
        .input-container select:not([value=""])~.underline,
        .input-container textarea:focus~.underline,
        .input-container textarea:not(:placeholder-shown)~.underline {
            transform: scaleX(1);
        }

        /* Select için özel stiller (ok ikonu ve varsayılan görünüm) */
        .input-container select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23333' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }

        /* CHECKBOX VE BUTONLAR İÇİN CSS STİLLERİ (admin.css'den buraya taşındı) */
        /* From Uiverse.io by vishnupprajapat */
        .checkbox-wrapper-46 input[type="checkbox"] {
            display: none;
            visibility: hidden;
        }

        .checkbox-wrapper-46 .cbx {
            margin: auto;
            -webkit-user-select: none;
            user-select: none;
            cursor: pointer;
        }

        .checkbox-wrapper-46 .cbx span {
            display: inline-block;
            vertical-align: middle;
            transform: translate3d(0, 0, 0);
        }

        .checkbox-wrapper-46 .cbx span:first-child {
            position: relative;
            width: 18px;
            height: 18px;
            border-radius: 3px;
            transform: scale(1);
            vertical-align: middle;
            border: 1px solid #9098a9;
            transition: all 0.2s ease;
        }

        .checkbox-wrapper-46 .cbx span:first-child svg {
            position: absolute;
            top: 3px;
            left: 2px;
            fill: none;
            stroke: #ffffff;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 16px;
            stroke-dashoffset: 16px;
            transition: all 0.3s ease;
            transition-delay: 0.1s;
            transform: translate3d(0, 0, 0);
        }

        .checkbox-wrapper-46 .cbx span:first-child:before {
            content: "";
            width: 100%;
            height: 100%;
            background: #506eec;
            display: block;
            transform: scale(0);
            opacity: 1;
            border-radius: 50%;
        }

        .checkbox-wrapper-46 .cbx span:last-child {
            padding-left: 8px;
        }

        .checkbox-wrapper-46 .cbx:hover span:first-child {
            border-color: #506eec;
        }

        .checkbox-wrapper-46 .inp-cbx:checked+.cbx span:first-child {
            background: #506eec;
            border-color: #506eec;
            animation: wave-46 0.4s ease;
        }

        .checkbox-wrapper-46 .inp-cbx:checked+.cbx span:first-child svg {
            stroke-dashoffset: 0;
        }

        .checkbox-wrapper-46 .inp-cbx:checked+.cbx span:first-child:before {
            transform: scale(3.5);
            opacity: 0;
            transition: all 0.6s ease;
        }

        @keyframes wave-46 {
            50% {
                transform: scale(0.9);
            }
        }

        /* Buton Tasarımı (Uiverse.io by cssbuttons-io) */
        .custom-button {
            width: 180px;
            /* Biraz genişletelim */
            height: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Metin ve ikonu ortala */
            border: none;
            border-radius: 5px;
            box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.15);
            position: relative;
            /* İkonun konumlandırılması için */
            overflow: hidden;
            /* İkonun taşmasını engelle */
            text-decoration: none;
            /* Linkler için alt çizgiyi kaldır */
            color: white;
            /* Varsayılan metin rengi */
            font-weight: bold;
            font-size: 1rem;
            padding: 0 10px;
            /* İç boşluk */
        }

        .custom-button,
        .custom-button span {
            transition: 200ms;
        }

        .custom-button .text {
            transform: translateX(0);
            /* Başlangıçta ortada */
            color: white;
            font-weight: bold;
            white-space: nowrap;
            /* Metnin tek satırda kalmasını sağlar */
            z-index: 1;
            /* Metnin ikonun üzerinde olmasını sağlar */
        }

        .custom-button .icon {
            position: absolute;
            right: 10px;
            /* Sağdan boşluk */
            height: 40px;
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translateX(0);
            /* Başlangıçta sağda */
            z-index: 0;
            /* İkonun metnin altında olmasını sağlar */
        }

        .custom-button svg {
            width: 20px;
            /* SVG boyutu */
            fill: #eee;
            /* SVG rengi */
        }

        /* Hover Efektleri */
        .custom-button:hover {
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }

        .custom-button:hover .text {
            transform: translateX(-20px);
            /* Metni sola kaydır */
            color: transparent;
            /* Metni şeffaf yap */
        }

        .custom-button:hover .icon {
            width: 100%;
            /* İkon alanını genişlet */
            transform: translateX(0);
            /* İkonu sola kaydır */
            right: 0;
            /* İkonu sağa sıfırla */
            border-left: 1px solid rgba(255, 255, 255, 0.2);
            /* Sol çizgiyi kaldır */
        }

        .custom-button:focus {
            outline: none;
        }

        .custom-button:active .icon svg {
            transform: scale(0.8);
        }

        /* Buton Renkleri */
        .save-button {
            background: #339999;
            /* Yeşil/Turkuaz tonu */
        }

        .save-button:hover {
            background: #2a7f7f;
            /* Koyu tonu */
        }

        .save-button .icon {
            border-left-color: #2a7f7f;
        }

        .back-button {
            background: #6c757d;
            /* Gri tonu */
        }

        .back-button:hover {
            background: #5a6268;
            /* Koyu tonu */
        }

        .back-button .icon {
            border-left: 1px solid #5a6268;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest/dist/editor.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/paragraph@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/inline-code@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest/dist/bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/link@latest/dist/bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // THUMBNAIL YÜKLEME VE ÖNİZLEME JS KISMI
            const thumbnailFileInput = document.getElementById('thumbnail_file');
            const thumbnailPreviewDiv = document.getElementById('thumbnailPreview');
            const previewImage = document.getElementById('previewImage');
            const custumFileUploadLabel = document.querySelector('.custum-file-upload[for="thumbnail_file"]');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const removeThumbnailButton = document.getElementById('removeThumbnail');
            const thumbnailImageUrlInput = document.getElementById('thumbnail_image_url');

            // Mevcut thumbnail varsa başlangıçta gizle
            if (thumbnailImageUrlInput.value && thumbnailImageUrlInput.value !== '#') {
                custumFileUploadLabel.style.display = 'none';
                removeThumbnailButton.style.display = 'block';
                const urlParts = thumbnailImageUrlInput.value.split('/');
                fileNameDisplay.textContent = urlParts[urlParts.length - 1];
                thumbnailPreviewDiv.style.display = 'block';
                previewImage.style.display = 'block';
            } else {
                custumFileUploadLabel.style.display = 'flex';
                removeThumbnailButton.style.display = 'none';
                thumbnailPreviewDiv.style.display = 'none';
                previewImage.style.display = 'none';
            }

            thumbnailFileInput.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    fileNameDisplay.textContent = file.name;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                        thumbnailPreviewDiv.style.display = 'block';
                        custumFileUploadLabel.style.display = 'none';
                        removeThumbnailButton.style.display = 'block';
                        thumbnailImageUrlInput.value = 'temp_pending_upload';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewImage.src = '#';
                    previewImage.style.display = 'none';
                    thumbnailPreviewDiv.style.display = 'none';
                    custumFileUploadLabel.style.display = 'flex';
                    removeThumbnailButton.style.display = 'none';
                    fileNameDisplay.textContent = '';
                    thumbnailImageUrlInput.value = '';
                }
            });

            removeThumbnailButton.addEventListener('click', function () {
                thumbnailFileInput.value = '';
                fileNameDisplay.textContent = '';
                thumbnailPreviewDiv.style.display = 'none';
                previewImage.src = '#';
                previewImage.style.display = 'none';
                custumFileUploadLabel.style.display = 'flex';
                removeThumbnailButton.style.display = 'none';
                thumbnailImageUrlInput.value = '';
                // thumbnail_removed bayrağını ayarla
                let thumbnailRemovedInput = document.createElement('input');
                thumbnailRemovedInput.type = 'hidden';
                thumbnailRemovedInput.name = 'thumbnail_removed';
                thumbnailRemovedInput.value = 'true';
                document.getElementById('blogPostForm').appendChild(thumbnailRemovedInput);
            });

            // EDITOR.JS BAŞLATMA
            const editor = new EditorJS({
                holder: 'editorjs',
                tools: {
                    header: { class: Header, inlineToolbar: true },
                    list: { class: List, inlineToolbar: true },
                    paragraph: { class: Paragraph, inlineToolbar: true },
                    image: {
                        class: ImageTool,
                        config: {
                            endpoints: {
                                byFile: '<?php echo BASE_URL; ?>panel/image/upload',
                                byUrl: '<?php echo BASE_URL; ?>panel/image/fetch',
                            }
                        }
                    },
                    quote: { class: Quote, inlineToolbar: true, shortcut: 'CMD+SHIFT+O', config: { quotePlaceholder: 'Alıntı metni', captionPlaceholder: 'Yazar veya kaynak' } },
                    code: { class: CodeTool, placeholder: 'Kodu buraya yapıştırın...' },
                    embed: { class: Embed, config: { services: { youtube: true, vimeo: true, codepen: true, instagram: true } } },
                    table: { class: Table, inlineToolbar: true },
                    marker: { class: Marker, shortcut: 'CMD+SHIFT+M' },
                    inlineCode: { class: InlineCode, shortcut: 'CMD+SHIFT+C' },
                    delimiter: Delimiter,
                    linkTool: { class: LinkTool, config: { endpoint: '<?php echo BASE_URL; ?>panel/link_data' } }
                },
                placeholder: 'Yazınızın içeriğini buraya yazın...',
                defaultBlock: 'paragraph',
                autofocus: true,
                readOnly: false,
                data: <?php echo json_encode($editorjs_data); ?>,
            });

            // ÖNE ÇIKARILAN BLOG MANTIĞI - EDIT.PHP İÇİN
            const isFeaturedCheckbox = document.getElementById('is_featured');
            const initialIsFeatured = <?php echo ($post['is_featured'] ?? 0); ?>; // PHP'den gelen başlangıç değeri

            document.getElementById('blogPostForm').addEventListener('submit', function (event) {
                event.preventDefault(); // Varsayılan formu göndermeyi engelle

                const submitForm = (overrideConfirmation = false) => {
                    editor.save().then((outputData) => {
                        document.getElementById('editorjs_content_hidden_input').value = JSON.stringify(outputData);

                        const mainFormData = new FormData(this);
                        // Onay verildiyse bir bayrak ekleyelim
                        if (overrideConfirmation) {
                            mainFormData.append('confirmed_featured_override', 'true');
                        }

                        fetch(this.action, {
                            method: 'POST',
                            body: mainFormData
                        })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().catch(() => {
                                        throw new Error('Sunucu yanıtı hatalı: ' + response.status);
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    window.location.href = '<?php echo BASE_URL; ?>panel/blog'; // Başarılı olursa yönlendir
                                } else {
                                    alert('Hata: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Form gönderimi hatası:', error);
                                alert('Form gönderilirken hata oluştu: ' + error.message);
                            });
                    }).catch((error) => {
                        console.error('Editor.js içeriği kaydedilemedi:', error);
                        alert('Yazı içeriği kaydedilemedi.');
                    });
                };

                // Senaryo 1: Öne çıkarılmış bir yazıyı öne çıkarılmaktan kaldırıyoruz
                if (initialIsFeatured === 1 && !isFeaturedCheckbox.checked) {
                    if (confirm('Bu blog yazısı öne çıkarılan yazıdan kaldırılacak ve son eklenen blog yazısı öne çıkarılacak. Onaylıyor musunuz?')) {
                        submitForm(true); // Onaylandı, formu gönder
                    } else {
                        // İptal edilirse checkbox'ı tekrar işaretli yap (eski haline getir)
                        isFeaturedCheckbox.checked = true;
                        console.log('Öne çıkarmadan kaldırma işlemi iptal edildi.');
                    }
                }
                // Senaryo 2: Öne çıkarılmamış bir yazıyı öne çıkarıyoruz (veya hiç öne çıkarılan yoksa)
                else if (isFeaturedCheckbox.checked) { // isFeatured checkbox işaretliyse (yeni veya mevcut)
                    fetch('<?php echo BASE_URL; ?>panel/blog/check_featured?exclude_id=<?php echo $post['id'] ?? 0; ?>', { // Kendi ID'mizi hariç tut
                        method: 'GET'
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.is_featured_exists) {
                                // Başka öne çıkarılmış varsa onay iste
                                if (confirm('Mevcut öne çıkarılan blog yazısı ile değiştirilecektir. Onaylıyor musunuz?')) {
                                    submitForm(true); // Onaylandı
                                } else {
                                    // İptal edilirse checkbox'ı eski haline getir
                                    isFeaturedCheckbox.checked = false;
                                    console.log('Öne çıkarma işlemi iptal edildi.');
                                }
                            } else {
                                // Başka öne çıkarılmış yoksa direkt gönder
                                submitForm(true); // Onaylandı sayılır
                            }
                        })
                        .catch(error => {
                            console.error('Öne çıkarılan durumu kontrol edilirken hata:', error);
                            alert('Öne çıkarılan durumu kontrol edilirken bir hata oluştu.');
                            // Hata durumunda yine de formu göndermeye izin ver (opsiyonel)
                            submitForm(true); // Yine de göndermeye çalış
                        });
                } else {
                    // Checkbox işaretli değilse (ve başlangıçta da işaretli değilse) direkt formu gönder
                    // Veya mevcut öne çıkarılan kaldırıldı ve onaylandıysa
                    submitForm(false); // Onay gerekmiyor
                }
            });
        });
    </script>
</div>