var h1,
	mseconds = 0, seconds = 0, minutes = 0, hours = 0, t;

function add() {
	mseconds++;
	if (mseconds >= 9) {
		mseconds = 0;
		seconds++;

		if(seconds > 60)
		{
			seconds = 0;
			minutes++;

			if (minutes > 60) {
				minutes = 0;
				hours++;
			}
		}
	}

	h1.textContent = (hours ? (hours > 9 ? hours : "0" + hours) : "00") 
		+ ":" + (minutes ? (minutes > 9 ? minutes : "0" + minutes) : "00") 
		+ ":" + (seconds > 9 ? seconds : "0" + seconds)
		+ "." + mseconds;

	timer(h1);
}

function timer(target) {
	h1 = target;
	var values = target.textContent.split(':');
	hours = parseInt(values[0]);
	minutes = parseInt(values[1]);
	seconds = parseInt(values[2].split('.')[0]);

	t = setTimeout(add, 100);
}