function updatePreview(form, index) {

    var selectBox = document.getElementById("avatar-" + form);
    var selectedValue = selectBox.options[index || selectBox.selectedIndex].value;
    var previewDiv = document.getElementById("avatar-preview-" + form);


    var imageUrl = "img/a" + selectedValue + ".png"; 
    previewDiv.innerHTML = "<img src='" + imageUrl + "' alt='Avatar Preview'>";

  }
