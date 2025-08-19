// profile.js

(function () {
  const tg = window.Telegram.WebApp;
  const user = tg.initDataUnsafe?.user;

  if (user) {
    const userInfo = {
      id: user.id,
      first_name: user.first_name || "",
      last_name: user.last_name || "",
      username: user.username || "",
      full_name: `${user.first_name || ""} ${user.last_name || ""}`.trim(),
    };

    localStorage.setItem("tg_user", JSON.stringify(userInfo));
    console.log("Telegram foydalanuvchisi aniqlandi:", userInfo);
  } else {
    console.warn("Telegram foydalanuvchisi ma'lumotlari topilmadi.");
  }
})();
