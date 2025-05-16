const userSinceEl = document.querySelector('.userSince');
if (userSinceEl) {
    const isoDate = userSinceEl.dataset.userCreated;
    const created = new Date(isoDate);
    const now = new Date();
    const diffDays = Math.floor((now - created) / (1000 * 60 * 60 * 24));
    let text;
    if (diffDays < 1) text = 'Joined today';
    else if (diffDays === 1) text = 'Joined yesterday';
    else text = `Joined ${diffDays} days ago`;
    userSinceEl.textContent = text;
}


document.querySelectorAll(".deleteCommentForms button").forEach(button => {
    button.addEventListener("click", (event) => {
        event.stopPropagation(); // Stop the article click
        if (!confirm("Are you sure you want to delete this comment?")) {
            event.preventDefault(); // Cancel the delete
        }
    });
});
