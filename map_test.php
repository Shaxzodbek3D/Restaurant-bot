<?php
// PHP xatolarini yashirish (Ishlab chiqarish muhiti uchun tavsiya etiladi)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Yandex Map API kalitini yuklash
// map_config.php faylida YANDEX_MAP_API_KEY o'zgaruvchisi borligiga ishonch hosil qiling
require_once __DIR__ . '/map_config.php';

// Agar lokalda ishlayotgan bo'lsangiz va kalit statik yozilgan bo'lsa, uni shu yerga yozing
// Masalan: $yandex_api_key = "SIZNING_API_KALITINGIZ";
// Aks holda, map_config.php dan olinsin
$yandex_api_key = YANDEX_MAP_API_KEY;

// Boshlang'ich koordinatalar (masalan, Toshkent markazi)
// Agar sizda real manzil koordinatalari bo'lsa, ularni bu yerga yozishingiz mumkin
$initial_latitude = 41.311158;
$initial_longitude = 69.279737;
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Yandex Xaritasi Testi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%; /* Xaritani to'liq balandlikka cho'zish uchun */
            overflow: hidden; /* Scroll barlarni olib tashlash */
            background-color: #333; /* Fon rangi */
        }
        #map {
            width: 100%;
            height: 100%;
            /* Xaritani aniq o'lchamlarga ega ekanligiga ishonch hosil qiling */
        }
    </style>
    <script src="https://api-maps.yandex.ru/2.1/?lang=uz_UZ&apikey=<?= htmlspecialchars($yandex_api_key) ?>"></script>
</head>
<body>
    <div id="map"></div>

    <script>
        let myMap; // Xarita obyekti

        // Yandex Map API yuklangandan so'ng ishlaydigan funksiya
        ymaps.ready(function () {
            // Xaritani yaratamiz va uni `map` IDsi bo'lgan divga bog'laymiz
            myMap = new ymaps.Map('map', {
                center: [<?= $initial_latitude ?>, <?= $initial_longitude ?>], // Boshlang'ich markaz koordinatalari
                zoom: 12, // Boshlang'ich kattalashtirish darajasi
                controls: ['zoomControl', 'fullscreenControl'] // Xarita boshqaruvlari
            }, {
                searchControlProvider: 'yandex#search' // Qidiruv boshqaruvi
            });

            // Xaritaga belgi (placemark) qo'shamiz
            let myPlacemark = new ymaps.Placemark(myMap.getCenter(), {
                balloonContent: 'Bu sizning manzilingiz'
            }, {
                draggable: true // Belgini sudrab siljitish imkoniyati
            });

            myMap.geoObjects.add(myPlacemark);

            // Belgini sudrab siljitganda koordinatalarni yangilash
            myPlacemark.events.add('dragend', function () {
                let newCoords = myPlacemark.geometry.getCoordinates();
                console.log('Yangi koordinatalar:', newCoords);
            });

            // Xaritada bosganda belgini siljitish
            myMap.events.add('click', function (e) {
                let newCoords = e.get('coords');
                myPlacemark.geometry.setCoordinates(newCoords);
                console.log('Bosilgan koordinatalar:', newCoords);
            });

            console.log("Yandex Xaritasi muvaffaqiyatli yuklandi va ishga tushirildi.");
        });
    </script>
</body>
</html>