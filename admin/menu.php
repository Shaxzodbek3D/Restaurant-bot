<?php
require __DIR__ . '/../bot/db_mysqli.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: admin.php");
    exit();
}

require_once "db.php";
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buyurtmalar paneli</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #f7f8fa, #e2e6ea);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
    }
    .container {
      margin-top: 40px;
    }
    .table thead {
      background-color: #212529;
      color: white;
    }
    ul {
      padding-left: 18px;
      margin-bottom: 0;
    }
    tbody tr.status-new td {
      background-color: #d1e7dd !important;
    }
    tbody tr.status-accepted td {
      background-color: #fff3cd !important;
    }
    tbody tr.status-completed td {
      background-color: #f8f9fa !important;
    }
    .btn, .form-control, .form-select {
      border-radius: 0.5rem;
      transition: all 0.3s ease;
    }
    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-outline-primary {
      color: #0d6efd;
      border-color: #0d6efd;
    }
    .btn-outline-primary:hover {
      background-color: #0d6efd;
      color: white;
    }
    .btn-outline-success {
      color: #198754;
      border-color: #198754;
    }
    .btn-outline-success:hover {
      background-color: #198754;
      color: white;
    }
    .btn-outline-danger {
      color: #dc3545;
      border-color: #dc3545;
    }
    .btn-outline-danger:hover {
      background-color: #dc3545;
      color: white;
    }
    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
    }
    .btn-danger:hover {
      background-color: #bb2d3b;
    }
    .card {
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .card-title {
      font-weight: 600;
    }
    .badge {
      font-size: 0.9rem;
      padding: 0.5em 0.75em;
    }
    @media (max-width: 768px) {
      .d-flex.justify-content-between {
        flex-direction: column;
        align-items: stretch !important;
        gap: 1rem;
      }
      .d-flex.justify-content-between .btn {
        width: 100%;
      }
      form.d-flex {
        flex-direction: column;
        gap: 0.5rem;
        max-width: 100% !important;
      }
      .card-title, .card-text {
        text-align: center;
      }
      table {
        font-size: 0.9rem;
      }
      .table-responsive {
        overflow-x: auto;
      }
    }
  </style>
</head>
<body>

<!-- The rest of the HTML stays the same -->

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-list"></i> Menyuni boshqarish</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary">Ortga</a>
  </div>

  <div class="form-section">
      
      
    <h4>Kategoriya qo‘shish</h4>
    <form method="POST" action="add_category.php" class="row g-3">
      <div class="col-md-6">
        <input type="text" name="name" class="form-control" placeholder="Kategoriya nomi" required>
      </div>
      <div class="col-md-6">
        <button type="submit" class="btn btn-primary">Qo‘shish</button>
      </div>
    </form>
  </div>

  <div class="form-section">
  <h4>Mahsulot qo‘shish</h4>
  <form method="POST" action="add_product.php" class="row g-3" enctype="multipart/form-data">
    <div class="col-md-3">
      <input type="text" name="name" class="form-control" placeholder="Nomi" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="price" class="form-control" placeholder="Narxi" required>
    </div>
    <div class="col-md-3">
      <select name="category" class="form-select" required>
        <option value="">Kategoriya</option>
        <?php while ($cat = $categories->fetch_assoc()): ?>
          <option value="<?= $cat['name'] ?>"><?= $cat['name'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-4">
      <input type="file" name="image" class="form-control" required>
    </div>
    <div class="col-md-12">
         <textarea name="description" class="form-control" placeholder="Mahsulot izohi"></textarea>
      <button type="submit" class="btn btn-success">Mahsulotni qo‘shish</button>
    </div>
  </form>
</div>


  <h4 class="mt-5">Mavjud Kategoriyalar</h4>
<table class="table table-bordered">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nomi</th>
      <th>Amallar</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
    $modalBuffer = ''; // modal oynalarni buferga yozamiz
    while ($cat = $categories->fetch_assoc()):
    ?>
      <tr>
        <td><?= $cat['id'] ?></td>
        <td><?= htmlspecialchars($cat['name']) ?></td>
        <td>
          <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $cat['id'] ?>">✏️</button>
          <a href="delete_category.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('O‘chirishga ishonchingiz komilmi?')">🗑</a>
        </td>
      </tr>

      <?php
      // Modalni buferga yozamiz
      $modalBuffer .= '
      <div class="modal fade" id="editCategoryModal' . $cat['id'] . '" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <form method="POST" action="update_category.php" class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Kategoriyani tahrirlash</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id" value="' . $cat['id'] . '">
              <input type="text" name="name" class="form-control" value="' . htmlspecialchars($cat['name']) . '" required>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Saqlash</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
            </div>
          </form>
        </div>
      </div>
      ';
      ?>
    <?php endwhile; ?>
  </tbody>
</table>

<!-- MODAL OYNALAR (jadvallardan tashqarida) -->
<?= $modalBuffer ?>

    
    
   <h4 class="mt-5">Mavjud Mahsulotlar</h4>
<table class="table table-bordered">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nomi</th>
      <th>Narxi</th>
      <th>Kategoriya</th>
      <th>Izoh</th>
      <th>Rasm</th>
      <th>Amallar</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
    while ($prod = $products->fetch_assoc()):
    ?>
      <tr>
        <td><?= $prod['id'] ?></td>
        <td><?= htmlspecialchars($prod['name']) ?></td>
        <td><?= number_format($prod['price'], 0, '', ' ') ?> so'm</td>
        <td><?= htmlspecialchars($prod['category']) ?></td>
        <td><?= nl2br(htmlspecialchars($prod['description'] ?? '-')) ?></td>
        <td>
          <?php
            $imgPath = "../" . $prod['image'];
            if (!empty($prod['image']) && file_exists($imgPath)) {
                echo "<img src='$imgPath' width='50' alt=''>";
            } else {
                echo "<span class='text-muted'>-</span>";
            }
          ?>
        </td>
        <td>
          <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $prod['id'] ?>">✏️</button>
          <a href="delete_product.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Mahsulotni o‘chirishga ishonchingiz komilmi?')">🗑</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>


  <!-- 🧩 Modal: Mahsulotlarni tahrirlash (alohida pastda joylashadi) -->
  <?php
  $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
  while ($prod = $products->fetch_assoc()):
  ?>
  <div class="modal fade" id="editProductModal<?= $prod['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" action="update_product.php" enctype="multipart/form-data" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Mahsulotni tahrirlash</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" value="<?= $prod['id'] ?>">
          <div class="mb-3">
            <label class="form-label">Nomi</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($prod['name']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Narxi</label>
            <input type="number" name="price" class="form-control" value="<?= $prod['price'] ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kategoriya</label>
            <select name="category" class="form-select" required>
              <?php
              $allCats = $conn->query("SELECT * FROM categories");
              while ($cat = $allCats->fetch_assoc()):
                $selected = ($cat['name'] === $prod['category']) ? 'selected' : '';
              ?>
                <option value="<?= $cat['name'] ?>" <?= $selected ?>><?= $cat['name'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Izoh</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($prod['description'] ?? '') ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Yangi rasm (ixtiyoriy)</label>
            <input type="file" name="image" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Saqlash</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
        </div>
      </form>
    </div>
  </div>
  <?php endwhile; ?>

  </tbody>
</table>



</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
