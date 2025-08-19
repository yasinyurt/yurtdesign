<?php
// ROOT_PATH ve $pdo nesnesi public_html/panel/index.php üzerinden dahil edilen src/panel/config.php'den gelir.
if (!isset($pdo)) {
    require_once ROOT_PATH . '/src/panel/config.php';
}

// Parent kategorileri çekme (alt kategori seçimi için)
$parent_categories = [];
try {
    $stmt = $pdo->query("SELECT id, name, type FROM categories ORDER BY type ASC, name ASC");
    $parent_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Kategoriler çekilirken veritabanı hatası: " . $e->getMessage());
    $_SESSION['message'] = 'Sistem hatası: Üst kategoriler yüklenirken hata oluştu.';
    $_SESSION['message_type'] = 'danger';
}
?>

<div class="container-fluid mt-4">
    <h2>Yeni Kategori Ekle</h2>
    <p class="mb-4">Yeni bir kategori oluşturmak için aşağıdaki formu kullanın.</p>

    <?php
    // Mesajlar (başarı veya hata) burada gösterilecek
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message_type'] ?? '') . '">' . htmlspecialchars($_SESSION['message'] ?? '') . '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <form action="<?php echo BASE_URL; ?>panel/category/save" method="POST" id="categoryForm"
        enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form__group field">
                    <input type="input" class="form__field" placeholder="Kategori Adı" name="name" id="name" required>
                    <label for="name" class="form__label">Kategori Adı</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form__group field">
                    <select class="form__field custom-select" name="type" id="type" required>
                        <option value="" disabled selected>Kategori Türü Seçiniz...</option>
                        <option value="blog">Blog</option>
                        <option value="project">Proje</option>
                        <option value="service">Hizmet</option>
                    </select>
                    <label for="type" class="form__label">Kategori Türü</label>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <div class="form__group field">
                    <select class="form__field custom-select" name="parent_id" id="parent_id">
                        <option value="" disabled selected>Üst Kategori Seçiniz (Opsiyonel)</option>
                        <option value="">Yok (Ana Kategori)</option>
                        <?php foreach ($parent_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                                (<?php echo htmlspecialchars(ucfirst($cat['type'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="parent_id" class="form__label">Üst Kategori</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form__group field">
                    <textarea class="form__field custom-textarea" placeholder="Kategori Açıklaması" name="description"
                        id="description" rows="3"></textarea>
                    <label for="description" class="form__label">Kategori Açıklaması</label>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-12">
                <label class="form-label d-block mb-2">Kategori Görseli (Thumbnail)</label>
                <label class="custum-file-upload" for="category_image_file">
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
                        <span id="categoryFileNameDisplay"
                            style="font-size: 0.8rem; display: block; margin-top: 5px;"></span>
                    </div>
                    <input type="file" id="category_image_file" name="category_image_file" accept="image/*">
                </label>
                <button type="button" id="removeCategoryImage" class="btn btn-sm btn-danger mt-2"
                    style="display:none;">Görseli Kaldır</button>
                <div id="categoryImagePreview" class="mt-2"
                    style="max-width: 300px; max-height: 200px; overflow: hidden; display: none;">
                    <img id="previewCategoryImg" src="#" alt="Görsel Önizleme"
                        style="width: 100%; height: auto; display: block;">
                </div>
                <input type="hidden" name="image_url" id="category_image_url_hidden">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-success">Kategoriyi Kaydet</button>
            <a href="<?php echo BASE_URL; ?>panel/category" class="btn btn-secondary ms-2">Geri Dön</a>
        </div>
    </form>

    <style>
        /* Form Input Tasarımı */
        .form__group {
            position: relative;
            padding: 20px 0 0;
            width: 100%;
            margin-bottom: 10px;
        }

        .form__field {
            font-family: inherit;
            width: 100%;
            border: none;
            border-bottom: 2px solid #9b9b9b;
            outline: 0;
            font-size: 17px;
            color: #333;
            padding: 7px 0;
            /* Padding'i artırdık */
            background: transparent;
            transition: border-color 0.2s;
        }

        .form__field::placeholder {
            color: transparent;
        }

        .form__field:placeholder-shown~.form__label {
            font-size: 17px;
            cursor: text;
            top: 20px;
        }

        .form__label {
            position: absolute;
            top: 0;
            display: block;
            transition: 0.2s;
            font-size: 17px;
            color: #9b9b9b;
            pointer-events: none;
        }

        .form__field:focus {
            padding-bottom: 6px;
            font-weight: 700;
            border-width: 3px;
            border-image: linear-gradient(to right, #339999, #000000);
            border-image-slice: 1;
            outline: none;
        }

        .form__field:focus~.form__label {
            position: absolute;
            top: 0;
            display: block;
            transition: 0.2s;
            font-size: 13px;
            color: #339999;
            font-weight: 700;
        }

        /* reset input */
        .form__field:required,
        .form__field:invalid {
            box-shadow: none;
        }

        /* Select ve Textarea için özel stiller */
        .form__field.custom-select,
        .form__field.custom-textarea {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23333' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
            color: #333;
        }

        .form__field.custom-textarea {
            resize: vertical;
            min-height: 50px;
        }

        /* Select ve Textarea için label'ın yukarı çıkmasını sağla (focus veya dolu olduğunda) */
        .form__field.custom-select:not(:placeholder-shown)~.form__label,
        .form__field.custom-textarea:not(:placeholder-shown)~.form__label,
        .form__field.custom-select:focus~.form__label,
        .form__field.custom-textarea:focus~.form__label,
        /* Select elementinin varsayılan olarak seçili bir option'ı varsa */
        .form__field.custom-select:not([value=""])~.form__label {
            top: 0;
            font-size: 13px;
            color: #339999;
            font-weight: 700;
        }

        /* Select'in kendi "Seçiniz..." placeholder'ını gizle */
        .form__field.custom-select option[value=""][disabled][selected] {
            display: none;
        }

        .form__field.custom-select:not([value=""]) {
            color: #333;
        }

        /* Thumbnail CSS Stilleri */
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
    </style>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categoryForm = document.getElementById('categoryForm');

        // Kategori görseli ile ilgili değişkenler
        const categoryImageFileInput = document.getElementById('category_image_file');
        const categoryImagePreviewDiv = document.getElementById('categoryImagePreview');
        const previewCategoryImg = document.getElementById('previewCategoryImg');
        const custumFileUploadLabel = document.querySelector('label.custum-file-upload[for="category_image_file"]');
        const categoryFileNameDisplay = document.getElementById('categoryFileNameDisplay');
        const removeCategoryImageButton = document.getElementById('removeCategoryImage');
        const categoryImageUrlHiddenInput = document.getElementById('category_image_url_hidden');

        if (categoryImageFileInput && categoryImagePreviewDiv) {
            categoryImageFileInput.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    categoryFileNameDisplay.textContent = file.name;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewCategoryImg.src = e.target.result;
                        previewCategoryImg.style.display = 'block';
                        categoryImagePreviewDiv.style.display = 'block';
                        custumFileUploadLabel.style.display = 'none';
                        removeCategoryImageButton.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewCategoryImg.src = '#';
                    previewCategoryImg.style.display = 'none';
                    categoryImagePreviewDiv.style.display = 'none';
                    custumFileUploadLabel.style.display = 'flex';
                    removeCategoryImageButton.style.display = 'none';
                    categoryFileNameDisplay.textContent = '';
                }
            });

            removeCategoryImageButton.addEventListener('click', function () {
                categoryImageFileInput.value = '';
                categoryFileNameDisplay.textContent = '';
                categoryImagePreviewDiv.style.display = 'none';
                previewCategoryImg.src = '#';
                previewCategoryImg.style.display = 'none';
                custumFileUploadLabel.style.display = 'flex';
                removeCategoryImageButton.style.display = 'none';
            });
        }

        categoryForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error(text); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = '<?php echo BASE_URL; ?>panel/category';
                    } else {
                        alert('Hata: ' + data.message);
                        console.error('Sunucu hatası:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Form gönderilirken ağ hatası veya JSON ayrıştırma hatası oluştu:', error);
                    alert('Form gönderilirken bir hata oluştu. Lütfen konsolu kontrol edin.');
                });
        });
    });
</script>