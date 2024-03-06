function manageUserRights() {

	const HDGFMember = User_Information.vereinId === localClubId;

	if (HDGFMember) {
		$('#nav-wunschliste-tab').css('display', 'block');
	}

	if (User_Information.dienste_admin == true) {
		$('#nav-dienste-tab').css('display', 'block');
		$('#nav-flugtage-tab').css('display', 'block');
	}
}




