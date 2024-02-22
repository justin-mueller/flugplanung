function manageUserRights() {

	const HDGFMember = User_Information.verein == 'HDGF';
	const DiensteAdmin = User_Information.dienste_admin == '1';

	if (HDGFMember) {
		$('#nav-wunschliste-tab').css('display', 'block');
	}

	if (DiensteAdmin) {
		$('#nav-dienste-tab').css('display', 'block');
		$('#nav-flugtage-tab').css('display', 'block');
	}
}




