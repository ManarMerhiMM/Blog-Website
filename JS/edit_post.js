const updateForm = document.getElementById('updateForm');
const titleInput = document.getElementById('title');
const contentInput = document.getElementById('content');
const errorMessage = document.getElementById('errors');

updateForm.addEventListener('submit', function(event) {
    if (!confirm('Are you sure you want to update this post?')) {
        event.preventDefault();
        return;
    }
    if (titleInput.value.trim() === '' || contentInput.value.trim() === '') {
        event.preventDefault();
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'Error: Empty field(s)!';
        setTimeout(() => { errorMessage.style.display = 'none'; }, 3000);
    }
});