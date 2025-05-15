const hidepass = document.getElementById("hidepass");
const showpass = document.getElementById("showpass");
const loginForm = document.getElementById("loginForm");
const passwordInput = document.getElementById("password");
const usernameInput = document.getElementById("username");
const errorMessage = document.getElementById("errors");

//password hiding/showing using the eye picture
hidepass.addEventListener("click", () => {
    hidepass.style.display = "none";
    showpass.style.display = "block";
    passwordInput.type = "text";
});

showpass.addEventListener("click", () => {
    showpass.style.display = "none";
    hidepass.style.display = "block";
    passwordInput.type = "password";
});

//if php issues an error show it for 3 seconds
if (errorMessage.textContent != "") {
    errorMessage.style.display = "block";
}

//form validation: user must enter something in both fields
loginForm.addEventListener("submit", (event) => {
    if (usernameInput.value.trim() == "" || passwordInput.value.trim() == "") {
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: Empty field(s)!";
        event.preventDefault();
    }
});