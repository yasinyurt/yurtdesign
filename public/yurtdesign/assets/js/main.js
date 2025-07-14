const links = document.querySelectorAll('#mobilMenu .nav-link');
links.forEach(link => {
    link.addEventListener('click', () => {
        const collapse = document.querySelector('#mobilHizmetler');
        if (collapse && collapse.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            bsCollapse.hide();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.getElementById('mainNavbar');
    const placeholder = document.getElementById('navbarPlaceholder');
    let navbarHeight = navbar.offsetHeight; // Navbar'ın yüksekliğini al
    const scrollThreshold = navbar.offsetTop + 50; // Navbar'ın mevcut pozisyonundan 50px aşağı kayınca başlasın

    let isSticky = false;

    // Navbar yüksekliğini dinamik olarak almak için resize event listener (opsiyonel ama iyi bir pratiktir)
    window.addEventListener('resize', function () {
        navbarHeight = navbar.offsetHeight;
    });

    window.addEventListener('scroll', function () {
        if (window.scrollY > scrollThreshold) {
            if (!isSticky) {
                placeholder.style.height = navbarHeight + 'px'; // Placeholder'a navbar yüksekliğini ver
                navbar.classList.add('sticky-active');
                navbar.classList.remove('banner-bg'); // Scroll sonrası banner-bg'yi kaldır (opsiyonel)
                navbar.classList.remove('py-4'); // Orijinal padding sınıfını kaldır (opsiyonel)
                isSticky = true;
            }
        } else {
            if (isSticky) {
                placeholder.style.height = '0'; // Placeholder yüksekliğini sıfırla
                navbar.classList.remove('sticky-active');
                // Scroll yukarı çıktığında orijinal sınıfları geri ekle (opsiyonel)
                navbar.classList.add('banner-bg');
                navbar.classList.add('py-4');
                isSticky = false;
            }
        }
    });

});

document.addEventListener('DOMContentLoaded', function () {
    var splide = new Splide('#logo-slider', {
        type: 'loop',
        drag: 'free',
        focus: 'center',
        arrows: false,
        pagination: false,
        perPage: 4,

        // Adım adım otomatik oynatma ayarları
        autoplay: 'play',
        pauseOnHover: true,
        interval: 2000, // Her slaytta 3 saniye bekle
        speed: 1500, // Geçiş animasyonu 1 saniye sürsün
        resetProgress: true,

        // Mobil uyumluluk
        breakpoints: {
            992: {
                perPage: 3,
            },
            768: {
                perPage: 2,
            },
            576: {
                perPage: 1,
            }
        }
    });

    splide.mount();
});

