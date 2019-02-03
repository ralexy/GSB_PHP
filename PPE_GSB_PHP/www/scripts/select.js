var lstVisiteurs = document.getElementById("lstVisiteurs");
var lstMois = document.getElementById("lstMois");

lstVisiteurs.addEventListener("change", function() {
    submitForm();
});

lstMois.addEventListener("change", function() {
    submitForm();
});

function submitForm() { document.getElementById("formChoixVisiteur").submit(); }

// Todo Autohide alerts
//$('.alert').alert();