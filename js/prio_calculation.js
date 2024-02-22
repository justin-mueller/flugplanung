function getSelectedButtons() {

  const firstChoiceButtons = document.querySelectorAll('#list_fist_choice_1, #list_fist_choice_2, #list_fist_choice_3');
  const alternativeButtons = document.querySelectorAll('#list_alternative_1, #list_alternative_2');
  const alternativeTitles = [];
  let firstChoiceTitle = '';

  for (let i = 0; i < firstChoiceButtons.length; i++) {
    if (firstChoiceButtons[i].classList.contains('active')) {
      firstChoiceTitle = firstChoiceButtons[i].innerText;
      break;
    }
  }


  for (let i = 0; i < alternativeButtons.length; i++) {
    if (alternativeButtons[i].classList.contains('active')) {
      alternativeTitles.push(alternativeButtons[i].innerText);
    }
  }

  let NGL = 0, HRP = 0, AMD = 0;

  if (alternativeTitles.includes('Neustadt-Glewe')) {
    NGL = 1;
  } else if (!firstChoiceTitle.includes('Neustadt-Glewe')) {
    NGL = 2;
  }

  if (alternativeTitles.includes('Hörpel')) {
    HRP = 1;
  } else if (!firstChoiceTitle.includes('Hörpel')) {
    HRP = 2;
  }

  if (alternativeTitles.includes('Altenmedingen')) {
    AMD = 1;
  } else if (!firstChoiceTitle.includes('Altenmedingen')) {
    AMD = 2;
  }

  return [NGL, HRP, AMD];
}
