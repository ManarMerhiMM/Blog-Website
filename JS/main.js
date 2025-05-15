const burger = document.getElementById("burger");
const sidebar = document.getElementById("sidebar");

burger.addEventListener("click", () => {
    sidebar.classList.toggle("show");
    if (burger.textContent == "→") {
        burger.textContent = "←";
    }
    else {
        burger.textContent = "→";
    }
});

if (document.getElementById("signoutBtn")) {
    document.getElementById("signoutBtn").addEventListener("click", (event) => {
        if (!confirm("Are you sure you want to sign out?")) {
            event.preventDefault();
        }
    });
}

document.querySelectorAll("article").forEach(article => article.addEventListener("click", (event) => {
    article.querySelector("form").submit();
}));