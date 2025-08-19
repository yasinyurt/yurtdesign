<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script><div class="container-fluid general-pad d-flex justify-content-between align-items-center home-intro" data-aos="fade-up" data-aos-duration="1000">
    <!-- Sol -->
    <div class="text-content" data-aos="fade-right" data-aos-delay="200" data-aos-duration="1000">
        <h4 class="mini-title">Web Tasarım</h4>
        <h1 class="main-title">Müşterilerinize giden en hızlı ve sade yol.</h1>
        <p class="description">İnternet sitesi oluşturmak teknik bir iştir. Sizler artan müşterilerinizle
            ilgilenirken tüm teknik dijital işleri bizlere bırakın!</p>
        <a href="#" class="btn-animated" data-aos="zoom-in" data-aos-delay="400" data-aos-duration="800">İletişime Geç!</a>
    </div>

    <!-- Sağ -->
    <div class="gif-container text-end" data-aos="fade-left" data-aos-delay="300" data-aos-duration="1000">
        <img src="assets/images/animation-slider.gif" alt="çorlu web tasarım" class="img-fluid" width="650">
    </div>
</div>

<div class="container-fluid general-pad section-spacer section-bg-1" data-aos="fade-up" data-aos-duration="1000">
    <!-- Başlık & Açıklama -->
    <div class="text-center" style="margin-bottom: 12rem;" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
        <p class="text-muted text-font" style="font-weight: 400; letter-spacing: 0.3rem; margin-bottom: -5px">
            Yurtdesign</p>
        <h2 class="fw-bold title-font" style="font-weight: 700; margin-bottom: 1rem;">Bizim Farkımız Ne?</h2>
        <p class="text-muted text-font" style="font-size: 20px;">Hiçbir teknik bilgiye maruz kalmadan kısa süre
            içerisinde etkili çözümlere
            sahip olacaksınız.</p>
    </div>

    <!-- Kartlar -->
    <div class="row justify-content-around g-5">
        <!-- Kart 1 -->
        <div
            class="col-12 col-md-4 border-0 shadow-sm text-center hover-effect bg-white d-flex flex-column align-items-center p-4"
            data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            <div class="image-wrapper">
                <img src="assets/images/seo.png" alt="Tasarım" class="card-img-top floating-image">
            </div>
            <div class="card-body card-pt">
                <h3 class="h5 fw-bold title-font">Gözümüz Yükseklerde</h3>
                <p class="text-muted text-font">Bölgenizdeki tüm işletmelerden her zaman daha önde olun.</p>
            </div>
        </div>

        <!-- Kart 2 -->
        <div
            class="col-12 col-md-4 border-0 shadow-sm text-center hover-effect bg-white d-flex flex-column align-items-center p-4"
            data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
            <div class="image-wrapper">
                <img src="assets/images/card-3.png" alt="Tasarım" class="card-img-top floating-image" width="200">
            </div>
            <div class="card-body card-pt">
                <h3 class="h5 fw-bold title-font">Tüm Karmaşıklığa Son</h3>
                <p class="text-muted text-font">Hiçbir süreçte kafanızı karıştırıp gereksiz detaylarla ilgilenmenize
                    gerek yok.</p>
            </div>
        </div>

        <!-- Kart 3 -->
        <div
            class="col-12 col-md-4 border-0 shadow-sm text-center hover-effect bg-white d-flex flex-column align-items-center p-4"
            data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000">
            <div class="image-wrapper">
                <img src="assets/images/card-2.png" alt="Tasarım" class="card-img-top floating-image" width="200">
            </div>
            <div class="card-body card-pt">
                <h3 class="h5 fw-bold title-font">Hızlı ve Etkili İletişim</h3>
                <p class="text-muted text-font">Projelerimizi planlayarak daha erişilebilir ve sorunsuz bir hizmet
                    alabilirsiniz. Aynı zamananda destek talepleriniz hızla yanıtlanır.</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid d-flex justify-content-center align-items-center"
    style="background-image: url('assets/images/bg-pattern.png'); height: clamp(400px, 100vh - 50vw, 600px);"
    data-aos="fade-up" data-aos-duration="1000">
    <div class="w-75 bg-def d-flex justify-content-center p-5 shadow-sm border-2 flex-column" style="height: 85%;">
        <h3 class="h5 fw-bold title-font mb-2 text-center" data-aos="fade-up" data-aos-delay="100" data-aos-duration="800">Projelerimiz</h3>
        <div id="logo-slider" class="splide" role="group" aria-label="Projelerimiz" data-aos="zoom-in" data-aos-delay="200" data-aos-duration="1000">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide" data-aos="fade-right" data-aos-delay="300" data-aos-duration="800">
                        <img src="assets/images/reference-1.svg" alt="Logo 1">
                    </li>
                    <li class="splide__slide" data-aos="fade-right" data-aos-delay="400" data-aos-duration="800">
                        <img src="assets/images/reference-2.svg" alt="Logo 2">
                    </li>
                    <li class="splide__slide" data-aos="fade-right" data-aos-delay="500" data-aos-duration="800">
                        <img src="assets/images/reference-3.svg" alt="Logo 3">
                    </li>
                    <li class="splide__slide" data-aos="fade-right" data-aos-delay="600" data-aos-duration="800">
                        <img src="assets/images/reference-4.svg" alt="Logo 4">
                    </li>
                    <li class="splide__slide" data-aos="fade-right" data-aos-delay="700" data-aos-duration="800">
                        <img src="assets/images/reference-5.svg" alt="Logo 5">
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section style="background-color: #f3f3f3; padding-top: 3rem; padding-bottom: 3rem;" class="general-pad" data-aos="fade-up" data-aos-duration="1000">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Sol Kısım: Yazılar -->
            <div class="col-lg-6 mb-4 mb-lg-0 section-frst-left-side" data-aos="fade-right" data-aos-delay="200" data-aos-duration="1000">
                <p class="text-muted text-font" style="font-weight: 400; letter-spacing: 0.3rem; margin-bottom: -5px">
                    Çorlu Web Tasarım</p>
                <h2 class="h5 fw-bold title-font mb-5">
                    Yönetimi Tamamen <span style="color: #339999;">Sizin Elinizde</span> Olan Bir Web Sitesi
                </h2>
                <p class="text-font banner-text">
                    Gelişmiş altyapımız ile kişisel, amaca yönelik ve sektörünüze özel internet sitenizi istediğiniz
                    her yerden, her platformdan özgürce ve sınırsız bir şekilde yönetmek hiç bu kadar kolay
                    olmamıştı.
                </p>
            </div>

            <!-- Sağ Kısım: Görsel -->
            <div class="col-lg-6 text-center" data-aos="fade-left" data-aos-delay="300" data-aos-duration="1000">
                <img src="assets/images/progress.png" alt="Örnek Görsel" class="img-fluid"
                    style="max-height: 100%; max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</section>

<section
    style="background: center url('assets/images/banner-background.jpg'); margin-top: 3rem; margin-bottom: 3rem; height: 400px;"
    class="general-pad d-flex align-items-center justify-content-center flex-column" data-aos="fade-up" data-aos-duration="1000">
    <h2 class="fw-bold title-font" style="font-weight: 700; margin-bottom: 1rem; color: #f3f3f3;" data-aos="fade-up" data-aos-delay="100" data-aos-duration="800">
        Hemen Tekliflerimizi İnceleyin!
    </h2>
    <p class="text-font" style="font-size: 20px; color: #f3f3f3;" data-aos="fade-up" data-aos-delay="200" data-aos-duration="800">
        Dijital hizmetlerimiz için aşağıdaki formu doldurup en kısa süre içerisinde tekliflerimizi
        inceleyebilirsiniz.
    </p>
    <!-- Form başlıyor -->
    <form class="d-flex mt-3" style="max-width: 500px; width: 100%;" data-aos="zoom-in" data-aos-delay="300" data-aos-duration="800">
        <input type="email" class="form-control me-2 border-0" placeholder="E-posta adresiniz" required>
        <button type="submit" class="btn btn-primary" style="background-color: #f3f3f3; color: #339999;">Gönder</button>
    </form>
</section>

<section style="background-color: #f3f3f3; padding-top: 3rem; padding-bottom: 3rem; margin-bottom: 185px"
    class="general-pad" data-aos="fade-up" data-aos-duration="1000">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Sol Kısım: Görsel -->
            <div class="col-lg-6 text-center" data-aos="fade-right" data-aos-delay="200" data-aos-duration="1000">
                <img src="assets/images/growing-up.png" alt="Örnek Görsel" class="img-fluid"
                    style="max-height: 100%; max-width: 100%; height: auto;">
            </div>
            <!-- Sağ Kısım: Yazılar -->
            <div class="col-lg-6 mb-4 mb-lg-0 section-frst-left-side" data-aos="fade-left" data-aos-delay="300" data-aos-duration="1000">
                <p class="text-muted text-font" style="font-weight: 400; letter-spacing: 0.3rem; margin-bottom: -5px">
                    Kurumsal Kimlik</p>
                <h2 class="h5 fw-bold title-font mb-5">
                    Daha Fazla <span style="color: #339999;">Müşteri</span> İçin Güçlü SEO Uygulamaları
                </h2>
                <p class="text-font banner-text">
                    Arama motorlarına tam uyum sağlayan ve sizlerin yerine en ince ayrıntısına kadar ilgilenilen
                    sayfanız ile gücünüze güç, müşterilerinize müşteri katın!
                </p>
            </div>
        </div>
    </div>
</section>