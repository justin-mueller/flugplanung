function getSelectedButtons() {

  // With only 1 site, auto-select it as first choice
  if (SiteCount === 1) {
    return [0];
  }

  const firstChoiceButtons = document.querySelectorAll('[id^="list_fist_choice_"]');
  const alternativeButtons = document.querySelectorAll('[id^="list_alternative_"]');
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

  let result = new Array(SiteCount).fill(2); // default: not chosen

  for (let i = 0; i < SiteCount; i++) {
    if (firstChoiceTitle.includes(Fluggebiete[i])) {
      result[i] = 0;
    } else if (alternativeTitles.includes(Fluggebiete[i])) {
      result[i] = 1;
    }
  }

  return result;
}
