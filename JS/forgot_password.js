const forgotForm = document.getElementById("forgotForm");
const email = document.getElementById("email");
const errorMessage = document.getElementById("errors");

forgotForm.addEventListener("submit", (event)=>{
    if(email.value.trim() == ""){
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: You must enter an email!";
        event.preventDefault();
    }
});