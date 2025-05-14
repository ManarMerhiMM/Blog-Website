const deleteForm = document.getElementById('deleteForm');

deleteForm.addEventListener('submit', function(event) {
    if (!confirm('This action is irreversible. Do you really want to delete this post?')) {
        event.preventDefault();
    }
});