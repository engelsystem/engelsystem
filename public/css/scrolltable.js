function scrolltable(elem) {
	var widths = new Array();
	var thead = elem.getElementsByTagName('thead')[0];
	var tbody = elem.getElementsByTagName('tbody')[0];
	var ths = Array.prototype.slice.call(thead.getElementsByTagName('th'),0);
	ths = ths.concat(Array.prototype.slice.call(tbody.getElementsByTagName('tr')[0].getElementsByTagName('td'), 0));
	ths.push(tbody.getElementsByTagName('th')[0]);
	console.debug(ths);
	for(var i = 0; i < ths.length; i++)
		widths.push(ths[i].offsetWidth);
	widths.push(tbody.offsetWidth);
	elem.className = elem.className + ' scrollable';
	var tbodywidth = widths.pop();
	tbody.style.width = (tbodywidth + 16) + 'px';
<<<<<<< HEAD
	tbody.style.height = (window.innerHeight - 50) + 'px';
=======
	tbody.style.height = (window.innerHeight - 100) + 'px';
>>>>>>> cc8f117ed128cf9b046f9835640b84362d151883
	for(var i = 0; i < ths.length; i++) {
		var paddingLeft = parseInt(window.getComputedStyle(ths[i], null).getPropertyValue('padding-left'));
		var paddingRight = parseInt(window.getComputedStyle(ths[i], null).getPropertyValue('padding-right'));
		var borderLeft = parseInt(window.getComputedStyle(ths[i], null).getPropertyValue('border-left-width'));
		var borderRight = parseInt(window.getComputedStyle(ths[i], null).getPropertyValue('border-right-width'));
		var targetwidth = widths.shift();
		ths[i].style.maxWidth = ths[i].style.minWidth = (targetwidth - paddingLeft - paddingRight - borderRight) + 'px';
		if (ths[i].offsetWidth > targetwidth)
			ths[i].style.maxWidth = ths[i].style.minWidth = (parseInt(ths[i].style.minWidth) - 1) + 'px';
	}
}
