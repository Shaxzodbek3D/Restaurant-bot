<?php
ini_set('display_errors',0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require __DIR__ . '/bot/db_mysqli.php'; // db_mysqli.php fayliga yo'l to'g'ri ekanligiga ishonch hosil qiling
require_once __DIR__ . '/map_config.php'; // Yandex Map API kaliti borligiga ishonch hosil qiling

session_start();

$user_id = $_GET['user_id'] ?? $_SESSION['user_id'] ?? null;

if (empty($user_id)) {
    die("\u274c Foydalanuvchi aniqlanmadi.");
}

// User_id ni sessiyaga saqlash, keyinchalik foydalanish uchun
$_SESSION['user_id'] = $user_id;

// Manzilni AJAX orqali yangilash (Bu qism confirm.php dan shu yerga ko'chirildi)
// Forma POST orqali yuborilganda ishlaydi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_address_only') {
    $new_address = $_POST['address'] ?? '';
    $new_latitude = $_POST['latitude'] ?? null;
    $new_longitude = $_POST['longitude'] ?? null;

    // users jadvalida tg_user_id VARCHAR bo'lgani uchun "s" ishlatamiz
    $stmt = $conn->prepare("UPDATE users SET address = ?, latitude = ?, longitude = ? WHERE tg_user_id = ?");
    $stmt->bind_param("ssss", $new_address, $new_latitude, $new_longitude, $user_id);
    $stmt->execute();
    $stmt->close();

    // user_addresses jadvalida tg_user_id VARCHAR bo'lgani uchun "s" ishlatamiz
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM user_addresses WHERE tg_user_id = ? AND address = ?");
    $stmt->bind_param("ss", $user_id, $new_address);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row['cnt'] == 0 && !empty($new_address)) {
        $stmt = $conn->prepare("INSERT INTO user_addresses (tg_user_id, address, latitude, longitude, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $user_id, $new_address, $new_latitude, $new_longitude);
        $stmt->execute();
        $stmt->close();
    }

    // Muvaffaqiyatli saqlangandan so'ng qayta yo'naltirish
$redirect_url = $_POST['redirect_url'] ?? 'index.php';
header("Location: $redirect_url");
exit;

}
// Foydalanuvchining hozirgi manzilini olish (input maydonlariga to'ldirish uchun)
$address_to_display = '';
$latitude_to_display = '';
$longitude_to_display = '';

$stmt = $conn->prepare("SELECT address, latitude, longitude FROM users WHERE tg_user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user_current_address = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user_current_address) {
    $address_to_display = $user_current_address['address'];
    $latitude_to_display = $user_current_address['latitude'];
    $longitude_to_display = $user_current_address['longitude'];
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <title>Manzilni tahrirlash</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
  <script src="https://api-maps.yandex.ru/2.1/?lang=uz_UZ&apikey=<?= YANDEX_MAP_API_KEY ?>"></script>
  <style>
    body { background: #111; color: #fff; font-family: 'Nunito', sans-serif; }
    .container { max-width: 480px; margin: auto; background: #1f1f1f; padding: 30px; border-radius: 16px; box-shadow: 0 0 10px rgba(212, 175, 55, 0.2); }
    h2 { color: #d4af37; margin-bottom: 25px; }
    .form-control, .form-select { background: #222; color: #fff; border: 1px solid #444; }
    .form-control:focus, .form-select:focus { border-color: #d4af37; box-shadow: 0 0 5px #d4af37; }
    .btn-gold { background: #d4af37; color: #000; font-weight: bold; }
    .address-block { background: #2a2a2a; padding: 10px; border-radius: 8px; margin-bottom: 10px; }
  </style>
</head>
<body style="margin-top: 80px;">
  <header class="d-flex justify-content-between align-items-center px-3 py-2 fixed-top dark-header ">
    <div class="d-flex align-items-center gap-2">
       <img src="logo.png" alt="Chinor" style="max-width: 50px; margin-left: 10px;" />
       <img src="logoT.png" alt="Chinor" style="max-width: 100px; margin-left: 20px;" />
    </div>
  </header>

  <div class="container">
    <h2>Manzilni tahrirlash</h2>
    <form method="POST" action="edit_address.php?user_id=<?= htmlspecialchars($user_id) ?>">
      <div class="mb-3">
        <label class="form-label fw-bold text-gold">📍 Manzilni aniqlash:</label>
        <div class="mb-3">
          <div class="btn-group w-100">
              <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'index.php') ?>">
            <input type="radio" class="btn-check" name="mode" id="manual" value="manual" checked>
            <label class="btn btn-outline-light" for="manual">✍️ Qo‘lda</label>
            <input type="radio" class="btn-check" name="mode" id="auto" value="auto">
            <label class="btn btn-outline-light" for="auto">📍 Avtomatik</label>
          </div>
        </div>
        <input type="text" id="address" name="address" class="form-control mb-2" value="<?= htmlspecialchars($address_to_display) ?>">
        <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($latitude_to_display) ?>">
        <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($longitude_to_display) ?>">
        <div id="loading-indicator" class="text-center my-3" style="display: none;">
          <div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>
          <div class="mt-2 text-warning">Manzil aniqlanmoqda...</div>
        </div>
        <div id="map-block" class="mb-3" style="display:none;">
          <div id="map" style="width: 100%; height: 300px; border-radius: 8px;"></div>
        </div>
      </div>
      <input type="hidden" name="action" value="update_address_only"> <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
      <button type="submit" class="btn btn-gold w-100">Manzilni saqlash</button>
    </form>
  </div>

<script>
let mapInited = false, map, placemark;
let initialLat = parseFloat(document.getElementById("latitude").value);
let initialLng = parseFloat(document.getElementById("longitude").value);
let initialCoords = (isNaN(initialLat) || isNaN(initialLng) || initialLat == 0 || initialLng == 0) ? [41.311158, 69.279737] : [initialLat, initialLng]; // Toshkent markazi, agar koordinata bo'lmasa

ymaps.ready(function() {
    // Agar manzil maydonlari to'liq bo'lsa, xaritani avtomatik rejimda ochish
    if (document.getElementById("address").value && !isNaN(initialLat) && !isNaN(initialLng) && initialLat != 0 && initialLng != 0) {
        document.getElementById("auto").checked = true;
        document.getElementById("map-block").style.display = "block";
        initMap(initialCoords);
        mapInited = true;
    }
});


function initMap(coords) {
  if (typeof ymaps === 'undefined') return;
  if (map) map.destroy();
  map = new ymaps.Map("map", { center: coords, zoom: 18, controls: ["zoomControl"] });
  placemark = new ymaps.Placemark(coords, {}, { draggable: true });
  map.geoObjects.add(placemark);
  placemark.events.add("dragend", function () {
    const newCoords = placemark.geometry.getCoordinates();
    updateAddress(newCoords);
  });
  map.events.add("click", function (e) {
    const newCoords = e.get("coords");
    placemark.geometry.setCoordinates(newCoords);
    updateAddress(newCoords);
  });
}
function updateAddress(coords) {
  ymaps.geocode(coords).then(res => {
    const addr = res.geoObjects.get(0)?.getAddressLine() || '';
    document.getElementById("address").value = addr;
    document.getElementById("latitude").value = coords[0];
    document.getElementById("longitude").value = coords[1];
  });
}
function detectLocationAndMap() {
  const loading = document.getElementById("loading-indicator");
  if (loading) loading.style.display = "block";

  if (!navigator.geolocation) {
    alert("📵 Brauzeringiz geolokatsiyani qo‘llamaydi.");
    loading.style.display = "none";
    return;
  }

  navigator.geolocation.getCurrentPosition(
    function (pos) {
      const coords = [pos.coords.latitude, pos.coords.longitude];
      updateAddress(coords);
      document.getElementById("map-block").style.display = "block";

      ymaps.ready(() => {
        if (!mapInited) {
          initMap(coords);
          mapInited = true;
        } else {
          map.setCenter(coords);
          placemark.geometry.setCoordinates(coords);
        }
        loading.style.display = "none";
      });
    },
    function (error) {
      loading.style.display = "none";
      if (error.code === 1) {
        alert("❌ Siz geolokatsiyaga ruxsat bermadingiz. Iltimos, brauzeringiz sozlamalaridan uni yoqing.");
      } else if (error.code === 2) {
        alert("❗ Joylashuvni aniqlab bo‘lmadi. GPS yoki internet tekshiring.");
      } else if (error.code === 3) {
        alert("⏱ So‘rov vaqti tugadi. Iltimos, qayta urinib ko‘ring.");
      } else {
        alert("⚠️ Noma’lum xato yuz berdi.");
      }
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    }
  );
}


document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("input[name='mode']").forEach(r => {
    r.addEventListener("change", function () {
      if (this.value === "manual") {
        document.getElementById("map-block").style.display = "none";
        document.getElementById("loading-indicator").style.display = "none";
      } else {
        detectLocationAndMap();
      }
    });
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>