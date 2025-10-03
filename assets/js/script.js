/*MAKE PASSWORD MORE SECURED / VALIDATE PASSWORD*/
function passwordStrengthCheck() {
  const password = document.getElementById("password").value;
  const strengthMessage = document.getElementById("password-message");

  const minLength = /.{8,}/;
  const uppercase = /[A-Z]/;
  const lowercase = /[a-z]/;
  const digit = /[0-9]/;
  const specialChar = /[!@#$%^&*(),.?":{}|<>]/;

  const checks = [
    minLength.test(password),
    uppercase.test(password),
    lowercase.test(password),
    digit.test(password),
    specialChar.test(password)
  ];

  const passedChecks = checks.filter(Boolean).length;

  // Remove old Bootstrap text color classes
  strengthMessage.classList.remove("text-danger", "text-warning", "text-success");

  if (passedChecks === 0) {
    strengthMessage.textContent = "";
  } else if (passedChecks <= 2) {
    strengthMessage.textContent = "✗ Weak password";
    strengthMessage.classList.add("text-danger");
  } else if (passedChecks === 3 || passedChecks === 4) {
    strengthMessage.textContent = "⚠ Medium password";
    strengthMessage.classList.add("text-warning");
  } else if (passedChecks === 5) {
    strengthMessage.textContent = "✓ Strong password";
    strengthMessage.classList.add("text-success");
  }
}

// SHOW/HIDE PASSWORD
function toggleAllPasswords() {
  const isChecked = document.getElementById('showAllPasswords').checked;

  const fields = ['OldPassword', 'password', 'cpassword'];
  fields.forEach(id => {
    const input = document.getElementById(id);
    if (input) {
      input.type = isChecked ? 'text' : 'password';
    }
  });
}

// PASSWORD MATCHING
function validate_confirm_password() {
  const pass = document.getElementById('password').value.trim();
  const cpass = document.getElementById('cpassword').value.trim();
  const alert = document.getElementById('confirm_pass_alert');
  const submit = document.getElementById('submit_button');

  if (!alert || !submit) return;

  if (pass === "" || cpass === "") {
    alert.innerText = "";
    alert.classList.remove("text-danger", "text-success");
    submit.disabled = true;
    submit.style.opacity = "0.4";
    return;
  }

  alert.classList.remove("text-danger", "text-success");

  if (pass !== cpass) {
    alert.innerText = "✗ Passwords do not match!";
    alert.classList.add("text-danger");
    submit.disabled = true;
    submit.style.opacity = "0.4";
  } else {
    alert.innerText = "✓ Passwords matched!";
    alert.classList.add("text-success");
    submit.disabled = false;
    submit.style.opacity = "1";
  }
}

// EMAIL VALIDATION
function email_validation(input) {
  var email = input.value;
  var container = input.closest('.form-group');
  var errorSpan = container.querySelector('.email-error-message');

  var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (pattern.test(email)) {
    errorSpan.style.color = 'green';
    errorSpan.innerHTML = '';
  } else {
    errorSpan.style.color = 'red';
    errorSpan.innerHTML = 'Please enter a valid email address';
  }
}

// LETTER ONLY
function lettersOnly(input) {
  input.value = input.value.replace(/[^a-zA-Z\s]/g, '');
}

// AUTO CALCULATE AGE
function validateBirthdate(input) {
  const birthdate = new Date(input.value);
  const today = new Date();

  let age = today.getFullYear() - birthdate.getFullYear();
  const m = today.getMonth() - birthdate.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birthdate.getDate())) {
    age--;
  }

  const feedback = input.nextElementSibling;

  if (isNaN(age)) {
    // If no date is selected
    input.setCustomValidity("");
    input.classList.remove("is-invalid", "is-valid");
    if (feedback) feedback.style.display = "none";
    return;
  }

  if (age < 15) {
    const message = "Age must be at least 15 years old.";
    input.setCustomValidity(message);
    input.value = "";
    input.classList.add("is-invalid");
    input.classList.remove("is-valid");
    if (feedback) {
      feedback.textContent = message;
      feedback.style.display = "block";
    }
  } else {
    input.setCustomValidity("");
    input.classList.remove("is-invalid");
    input.classList.add("is-valid");
    if (feedback) feedback.style.display = "none";
  }
}
