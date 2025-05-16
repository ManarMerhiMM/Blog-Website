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