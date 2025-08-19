<?php
$conn = new mysqli("localhost", "bbhubuz_appuser", "$#@xzod1312", "bbhubuz_appetito", 3306);
if ($conn->connect_error) {
    http_response_code(500);
    echo "0";
    exit;
}

$result = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Yangi'");
if ($row = $result->fetch_assoc()) {
    echo $row['c'];
} else {
    echo "0";
}
$conn->close();
