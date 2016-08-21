/**
 * Runs through the DOM under the element with the given id, finds all
 * checkboxes and sets them to the wanted state.
 * 
 * @param String
 *            id Id of the element containing all the checkboxes
 * @param Boolean
 *            checked True if the checkboxes should be checked
 */
function checkAll(id, checked) {
	var obj = document.getElementById(id);
	var boxes = obj.getElementsByTagName("input");
	for (var i = 0; i < boxes.length; i++) {
		if (boxes[i].type === "checkbox" && !boxes[i].disabled) {
			boxes[i].checked = checked;
		}
	}
}

$(function() {
	/**
	 * Disable every submit button after clicking (to prevent double-clicking)
	 */
	$("form").submit(function(ev) {
		$("input[type='submit']").prop("readonly", true).addClass("disabled");
		return true;
	});
});
