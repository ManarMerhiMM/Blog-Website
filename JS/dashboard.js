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

document.getElementById("signoutBtn").addEventListener("click", (event) => {
    if (!confirm("Are you sure you want to sign out?")) {
        event.preventDefault();
    }
});

document.querySelectorAll("article").forEach(article => article.addEventListener("click", (event) => {
    if (event.target.tag !== "button" ) {
        article.querySelector("form").submit();
    }
}));

document.querySelectorAll(".editBtn").forEach(btn => {
    btn.addEventListener("click", function (event) {
        event.stopPropagation(); // Prevent triggering article click event
        const postID = this.closest("form").querySelector("input[name='postID']").value;
        window.location.href = `edit_post.php?edit=1&postID=${postID}`;
    });
});

document.querySelectorAll(".deleteBtn").forEach(btn => {
    btn.addEventListener("click", function () {
        event.stopPropagation(); // Prevent triggering article click event
        const postID = this.closest("form").querySelector("input[name='postID']").value;
        window.location.href = `delete_post.php?delete=1&postID=${postID}`;
    });
});