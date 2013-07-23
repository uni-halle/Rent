$(function() {
	if (typeof dateToChoose != 'undefined' && dateToChoose == 'end') {
		$('table.calendar tbody td').each(function(indexMo, tdMo) {
			var mouseOverDate = $(tdMo).data('date');
			$(tdMo).bind('mouseover', function() {
				$('table.calendar tbody td').each(function(index, td) {
					var currentDate = $(td).data('date');
					if ((currentDate > 0 && currentDate <= mouseOverDate && currentDate >= startDate) || currentDate == startDate) {
						$(td).addClass('active');
					} else {
						$(td).removeClass('active');
					}
				});
			});
		});
	}
});
