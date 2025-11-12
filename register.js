const radios = document.querySelectorAll('input[name="role"]');
const companyField = document.querySelector(".company-field");
const companyInput = document.querySelector("#companyName");

radios.forEach((radio) => {
  radio.addEventListener("change", () => {
    if (radio.id === "company" && radio.checked) {
      companyField.style.display = "flex";
      companyInput.disabled = false;
    } else {
      companyField.style.display = "none";
      companyInput.disabled = true;
    }
  });
});
