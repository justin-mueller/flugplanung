$(document).ready(function () {
    $("#delete-user-form").submit(function (event) {

      event.preventDefault();
  
      $("#delete-error").addClass("d-none");

      $("#update-error").addClass("d-none");
      if( $("#confirm_delete_account").val() !== 'bitte lösche meinen account') {
          $("#update-error").removeClass("d-none");
          $("#update-error").html("Die Phrase ist nicht korrekt!");
          return;
         }
  
      var formDataArray = $(this).serializeArray();

      formDataArray.push({
        name: "pilot_id",
        value: User_Information.pilot_id,
      });
  
      var formData = $.param(formDataArray);
  
      $.ajax({
        url: "delete_user.php",
        method: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          window.location.href = "logout.php";
        },
        error: function (error) {
          $("#update-error").removeClass("d-none");
          $("#update-error").html(
            "Das Löschen hat leider nicht geklappt. Versuch es gern nochmal…"
          );
          console.error("Error:", error);
        },
      });
    });
  });
  