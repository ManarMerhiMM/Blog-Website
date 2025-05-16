document.addEventListener("DOMContentLoaded", () => {
    const burger = document.getElementById("burger");
    const sidebar = document.getElementById("sidebar");

    // Sidebar toggle
    burger.addEventListener("click", () => {
        sidebar.classList.toggle("show");
        burger.textContent = burger.textContent === "→" ? "←" : "→";
    });

    // Confirm signout
    document.getElementById("signoutBtn").addEventListener("click", (event) => {
        if (!confirm("Are you sure you want to sign out?")) {
            event.preventDefault();
        }
    });

    // Confirm user actions
    document.querySelectorAll(".actionForms").forEach(form => {
        form.addEventListener("submit", (event) => {
            if (!confirm("Are you sure you want to take this action?")) {
                event.preventDefault();
            }
        });
    });

    // Confirm and prevent article form trigger when deleting
    document.querySelectorAll(".postDeletionForms").forEach(form => {
        form.addEventListener("submit", (event) => {
            event.stopPropagation(); // Prevent article click
            if (!confirm("Are you sure you want to delete this post?")) {
                event.preventDefault();
            }
        });

        // Also prevent bubbling from the delete button itself
        const deleteBtn = form.querySelector("button");
        if (deleteBtn) {
            deleteBtn.addEventListener("click", (e) => {
                e.stopPropagation();
            });
        }
    });

    // Article click for view
    document.querySelectorAll("article").forEach(article => {
        article.addEventListener("click", (event) => {
            const viewForm = article.querySelector("form[action='view_post.php']");
            if (viewForm) viewForm.submit();
        });
    });
});
