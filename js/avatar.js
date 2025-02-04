function updatePreview(index) {

    var selectBox = document.getElementById("avatar");
    var selectedValue = selectBox.options[index || selectBox.selectedIndex].value;
    var previewDiv = document.getElementById("avatar-preview");


    var imageUrl = "img/a" + selectedValue + ".png";
    previewDiv.innerHTML = "<img src='" + imageUrl + "' alt='Avatar Preview'>";

  }
