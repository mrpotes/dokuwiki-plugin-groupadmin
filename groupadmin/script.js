function add() {
	doMove("allusers", "groupusers", true, true);
}

function addall() {
	doMove("allusers", "groupusers", false, true);
}

function remove() {
	doMove("groupusers", "allusers", true, false);
}

function removeall() {
	doMove("groupusers", "allusers", false, false);
}
 
function doMove(fromselect, toselect, needsselected, setselected) {
	var allusers = document.getElementById(fromselect);
	var grpusers = document.getElementById(toselect);
	for (var i = allusers.options.length - 1; i >= 0; i--) {
		if ((needsselected && allusers.options[i].selected) || !needsselected) {
			var userInput = document.getElementById("users."+allusers.options[i].value);
			if (setselected) {
				userInput.name = 'users[]';
			} else {
				userInput.name = null;
			}
			allusers.options[i].selected = false;
			grpusers.add(allusers.options[i], null);
		}
	}
}