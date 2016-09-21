function ready(fn) {
	if (document.readyState != 'loading') {
		fn();
	} else if (document.addEventListener) {
		document.addEventListener('DOMContentLoaded', fn);
	} else {
		document.attachEvent('onreadystatechange', function () {
			if (document.readyState != 'loading')
				fn();
		});
	}
}

function exchangeClass(el, classNameA, classNameB) {

	if (el.classList) {
		el.classList.remove(classNameA);
		el.classList.add(classNameB);
	} else {
		var classes = el.classNameA.split(' ');
		var existingIndex = -1;
		for (var i = classes.length; i--;) {
			if (classes[i] === classNameA)
				existingIndex = i;
		}

		if (existingIndex >= 0)
			classes[existingIndex] = classNameB;
		else
			classes.push(classNameB);

		el.classNameA = classes.join(' ');
	}

}


function addEventListener(el, eventName, handler) {
	if (el.addEventListener) {
		el.addEventListener(eventName, handler);
	} else {
		el.attachEvent('on' + eventName, function () {
			handler.call(el);
		});
	}
}

function activateSources(mailContent) {

	var imgs = mailContent.querySelectorAll('img');

	for(var i=0; i<imgs.length; i++) {

		var img = imgs[i];
		var src = img.getAttribute('lazyl');

		if(src.length > 0) {
			img.setAttribute('src', src);
			img.setAttribute('lazyl', src);
		}

	}
}


ready(function () {

	var openers = document.querySelectorAll(".action--open");
	var closers = document.querySelectorAll(".action--close");

	for (var i = 0; i < closers.length; i++) {
		var closer = closers[i];
		addEventListener(closer, 'click', function () {

			var mailNode = this.parentNode.parentNode;
			exchangeClass(mailNode, 'mail--opened', 'mail--closed');

		});
	}

	for (var i = 0; i < openers.length; i++) {

		var opener = openers[i];
		addEventListener(opener, 'click', function () {

			var mailNode = this.parentNode.parentNode;
			var contentNode = mailNode.querySelector('.mail-content');

			activateSources(contentNode);
			exchangeClass(mailNode, 'mail--closed', 'mail--opened');

		});

	}

});