const resetForm = document.getElementById("resetForm");
const pass1 = document.getElementById("password");
const pass2 = document.getElementById("confirm_password");
const errorMessage = document.getElementById("errors");


resetForm.addEventListener("submit", (event) => {
    if (pass1.value.trim() == "" || pass2.value.trim() == "") {
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: Empty field(s)!";
        event.preventDefault();
    }
    else if (pass1.value.trim() != pass2.value.trim()) {
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: Passwords do not match!";
        event.preventDefault();
    }
    else if (pass1.value.trim().length < 10) {
        errorMessage.style.display = "block";
        errorMessage.textContent = "Error: New password must be at least 10 characters long!";
        event.preventDefault();
    }
    else {
        let foundNums = false;
        for (let i = 0; i < pass1.value.trim().length; i++) {
            if (pass1.value.trim()[i] in ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"]) {
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