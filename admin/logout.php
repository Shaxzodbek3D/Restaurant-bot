<?php
session_start(); // Sessiyani ishga tushiramiz

// Sessiyani tozalash
session_unset();       // Barcha session o‘zgaruvchilarni olib tashlash
session_destroy();     // Sessiyani butunlay yo‘q qilish

// Login sahifaga qaytarish
header("Location: admin.php");
exit;
?>
