const EMAIL_CONFIG = {
  serviceID: "service_ycjv7gb",
  templateID: "template_bvctyxs",
  publicKey: "05ElAwslFIofG-aG5",
};

(function () {
  emailjs.init(EMAIL_CONFIG.publicKey);
})();

const contactForm = document.getElementById("contactForm");
const submitBtn = document.getElementById("submitBtn");
const formAlert = document.getElementById("formAlert");

if (contactForm) {
  contactForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const name = this.from_name.value.trim();
    const email = this.from_email.value.trim();
    const message = this.message.value.trim();

    if (!name || !email || !message) {
      showAlert("Please fill in all fields", "error");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = "SENDING...";
    submitBtn.style.opacity = "0.6";

    emailjs
      .sendForm(EMAIL_CONFIG.serviceID, EMAIL_CONFIG.templateID, this)
      .then(function (response) {
        console.log("SUCCESS!", response.status, response.text);

        showAlert(
          "✓ Message sent successfully! We will contact you soon.",
          "success"
        );

        contactForm.reset();

        resetButton();
      })
      .catch(function (error) {
        console.error("FAILED...", error);

        showAlert(
          "✗ Failed to send message. Please try again or contact us directly.",
          "error"
        );

        resetButton();
      });
  });
}

function showAlert(message, type) {
  formAlert.textContent = message;
  formAlert.className = `form-alert ${type} show`;

  setTimeout(() => {
    formAlert.className = "form-alert";
  }, 5000);
}

function resetButton() {
  submitBtn.disabled = false;
  submitBtn.textContent = "SUBMIT";
  submitBtn.style.opacity = "1";
}

function scrollToForm() {
  contactForm.scrollIntoView({ behavior: "smooth", block: "center" });
}
