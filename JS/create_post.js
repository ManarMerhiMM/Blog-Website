const createForm = document.getElementById("createForm");
const title = document.getElementById("title");
const content = document.getElementById("content");
const errorMessage = document.getElementById("errors");

createForm.addEventListener("submit", (event) => {
    if (!confirm("Are you sure you want to post this?")) {
        event.preventDefault();
    }
    else if (title.value.trim() == "" || content.value.trim() == "") {
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: Empty field(s)!";
        setTimeout(() => { errorMessage.style.display = "none"; }, 3000);
        event.preventDefault();
    }
});