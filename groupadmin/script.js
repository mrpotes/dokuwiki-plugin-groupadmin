function add() {
	doMove("allusers", "groupusers", true);
}

function addall() {
	doMove("allusers", "groupusers", false);
}

function remove() {
	doMove("groupusers", "allusers", true);
}

function removeall() {
	doMove("groupusers", "allusers", false);
}

function doMove(fromselect, toselect, needsselected) {
	var allusers = document.getElementById(fromselect);
	var grpusers = document.getElementById(toselect);
	for (var i = allusers.options.length - 1; i >= 0; i--) {
		if ((needsselected && allusers.options[i].selected) || !needsselected) {
			grpusers.add(allusers.options[i], null);
			allusers.remove(i);
		}
	}
}