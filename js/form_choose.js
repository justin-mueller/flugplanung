function renameAlternativeButtons(firstChoice) {
	firstChoice = parseInt(firstChoice);
	let alternatives = [];
	for (let i = 0; i < SiteCount; i++) {
		if (i !== firstChoice) alternatives.push(i);
	}
	for (let i = 0; i < alternatives.length; i++) {
		$(`#list_alternative_${i + 1}`)[0].innerHTML = Fluggebiete[alternatives[i]];
	}
}
