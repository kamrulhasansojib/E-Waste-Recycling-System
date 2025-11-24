const form = document.getElementById("ewasteForm");
const msgDiv = document.getElementById("message");

form.addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(form);

  fetch("../backend/process_request.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.text())
    .then((data) => {
      msgDiv.innerHTML = `<p class="success-msg">${data}</p>`;
      form.reset();
    })
    .catch((err) => {
      msgDiv.innerHTML = `<p class="error-msg">Something went wrong!</p>`;
    });
});
