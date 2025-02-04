$(document).ready(function () {
  $("#update-user-info-form").submit(function (event) {

    event.preventDefault();

    $("#update-error").addClass("d-none");
    if( $("#password").val() !== '' && $("#password_conirm").val() !== $("#password").val()) {
        $("#update-error").removeClass("d-none");
        $("#update-error").html("Die Passwörter stimmen nicht überein!");
        return;
       }

    var formDataArray = $(this).serializeArray();

    // Ensure unchecked checkboxes are also included

    let fluggeraete_combined = ''
    fluggeraete_combined += $("#fluggeraet_gleitschirm").is(":checked") ? "G" : ""
    fluggeraete_combined += $("#fluggeraet_drachen").is(":checked") ? "D" : ""
    fluggeraete_combined += $("#fluggeraet_sonstiges").is(":checked") ? "S" : "";

    formDataArray.push({
      name: 'fluggeraete_combined',
      value: fluggeraete_combined
    });

    // Add windenfahrer checkbox explicitly
    var windenfahrerCheckbox = $("#windenfahrer");
    formDataArray.push({
      name: windenfahrerCheckbox.attr("name"),
      value: windenfahrerCheckbox.is(":checked") ? 1 : 0,
    });

    var formData = $.param(formDataArray);

    $.ajax({
      url: "update_user.php",
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        $(".login").addClass("flash-login");
        showToast(
          "Juhu!",
          "Das hat geklappt",
          "Du bist jetzt up-to-date!",
          "success"
        );
      },
      error: function (error) {
        $("#update-error").removeClass("d-none");
        $("#update-error").html(
          "Das hat leider nicht geklappt. Versuch es gern nochmal…"
        );
        console.error("Error:", error);
      },
    });
  });
});
