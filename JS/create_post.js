const createForm = document.getElementById("createForm");
const title = document.getElementById("title");
const content = document.getElementById("content");
const errorMessage = document.getElementById("errors");

createForm.addEventListener("submit", (event) => {
    if (title.value.trim() == "" || content.value.trim() == "") {
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: Empty field(s)!";
        event.preventDefault();
    }
    else if (!confirm("Are you sure you want to post this?")) {
        event.preventDefault();
    }
});

if(errorMessage.textContent != ""){
    errorMessage.style.display = "block";
}