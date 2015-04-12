
$j(document).on('click', 'a.dropdown-toggle', function() {
	var $this = $j(this);

	var p = $this.parent();
	var isActive = p.hasClass('open');
	
	p.toggleClass('open');
});