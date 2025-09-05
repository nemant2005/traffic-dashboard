// main.js – Handles general UI interactions

document.addEventListener("DOMContentLoaded", () => {
    // Dark mode toggle
    const themeSelect = document.getElementById("theme-select");
    if (themeSelect) {
        themeSelect.addEventListener("change", function () {
            if (this.value === "dark") {
                document.body.classList.add("dark-mode");
            } else {
                document.body.classList.remove("dark-mode");
            }
        });
    }

    // Auto-hide alerts after 5 seconds
    const successMsg = document.querySelector('.success');
    const errorMsg = document.querySelector('.error');
    if (successMsg || errorMsg) {
        setTimeout(() => {
            if (successMsg) successMsg.style.display = "none";
            if (errorMsg) errorMsg.style.display = "none";
        }, 5000);
    }
});
