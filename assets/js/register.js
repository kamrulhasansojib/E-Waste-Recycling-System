const radios = document.querySelectorAll('input[name="roleRadio"]');
const companyField = document.querySelector(".company-field");
const companyInput = document.querySelector("#companyName");
const roleInput = document.getElementById("roleInput");

radios.forEach((radio) => {
  radio.addEventListener("change", () => {
    if (radio.id === "company" && radio.checked) {
      companyField.style.display = "flex";
      companyInput.disabled = false;
      roleInput.value = "company";
    } else {
      companyField.style.display = "none";
      companyInput.disabled = true;
      roleInput.value = "user";
    }
  });
});
