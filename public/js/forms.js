function check_all(id) {
	var obj = document.getElementById(id);
	var boxes = obj.getElementsByTagName("input");
	for(var i = 0; i < boxes.length; i++) {
		if(boxes[i].type == "checkbox" && !boxes[i].disabled)
			boxes[i].checked = true;
	}
}

function uncheck_all(id) {
	var obj = document.getElementById(id);
	var boxes = obj.getElementsByTagName("input");
	for(var i = 0; i < boxes.length; i++) {
		if(boxes[i].type == "checkbox")
			boxes[i].checked = false;
	}
}
