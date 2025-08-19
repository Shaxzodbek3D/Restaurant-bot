<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <title>Xarita Test</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      background: #111;
      color: #fff;
      font-family: Arial, sans-serif;
      text-align: center;
    }
    #map {
      width: 100%;
      height: 300px;
      margin-top: 20px;
      border-radius: 12px;
    }
  </style>
  <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
</head>
<body>

  <h2 style="color: #d4af37;">🗺️ Xarita Test Sahifasi</h2>
  <div id="map"></div>

  <script>
    ymaps.ready(function () {
      let map = new ymaps.Map("map", {
        center: [39.767, 64.423], // Buxoro
        zoom: 13,
        controls: ["zoomControl"]
      });

      map.events.add("click", function (e) {
        let coords = e.get('coords');
        let placemark = new ymaps.Placemark(coords, {}, { draggable: true });
        map.geoObjects.removeAll(); // eski markerlarni tozalash
        map.geoObjects.add(placemark);

        ymaps.geocode(coords).then(function (res) {
          const address = res.geoObjects.get(0)?.getAddressLine();
          alert("Tanlangan manzil: " + address);
        });
      });
    });
  </script>

</body>
</html>
