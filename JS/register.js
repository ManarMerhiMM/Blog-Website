const hidepass = document.getElementById("hidepass");
const showpass = document.getElementById("showpass");
const regForm = document.getElementById("registerForm");
const passwordInput = document.getElementById("password");
const usernameInput = document.getElementById("username");
const emailInput = document.getElementById("email");
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

//if php issues an error show it
if(errorMessage.textContent != ""){
    errorMessage.style.display = "block";
}

//form validation: both fields must be there, passwords must be at least 10 characters and must contain at least one number
regForm.addEventListener("submit", (event) => {
    if (usernameInput.value.trim() == "" || passwordInput.value.trim() == "" || emailInput.value.trim() == "") {
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: Empty field(s)!";
        event.preventDefault();
    }
    else if (passwordInput.value.trim().length < 10) {
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: Password must contain at least 10 characters!";
        event.preventDefault();
    }
    else {
        let foundNums = false;
        for (let i = 0; i < passwordInput.value.trim().length; i++) {
            if (passwordInput.value.trim()[i] in ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"]) {
                foundNums = true;
                break;
            }
        }

        if (!foundNums) {
            errorMessage.style.display = "block";
            errorMessage.textContent = "Error: Password must contain numbers!";
            event.preventDefault();
        }
    }
});