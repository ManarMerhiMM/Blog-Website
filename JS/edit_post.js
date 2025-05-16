const updateForm = document.getElementById('updateForm');
const titleInput = document.getElementById('title');
const contentInput = document.getElementById('content');
const errorMessage = document.getElementById('errors');

updateForm.addEventListener('submit', function(event) {
    if (titleInput.value.trim() === '' || contentInput.value.trim() === '') {
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'Error: Empty field(s)!';
        event.preventDefault();
    }
    else if (!confirm("Are you sure you want to apply these changes?")) {
        event.preventDefault();
    }
});

if(errorMessage.textContent != ""){
    errorMessage.style.display = "block";
}