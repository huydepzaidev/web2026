setTimeout(() => {
	rightSlide();
slider2a(2);
}, 5000);
var timer = function () {
    interval = setInterval(function () {
        rightSlide();
    },5000);
  };
    var curpage = 1;
var sliding = false;
var click = true;
var left = document.getElementById("left");
var right = document.getElementById("right");
var pagePrefix = "slide";
var pageShift = 500;
var transitionPrefix = "circle";
var svg = true;

function leftSlide() {
	if (click) {
		if (curpage == 1) curpage = 4;
		console.log("woek");
		sliding = true;
		curpage--;
		svg = true;
		click = false;
		for (k = 1; k <= 3; k++) {
			var a1 = document.getElementById(pagePrefix + k);
			a1.className += " tran";
		}
		setTimeout(() => {
			move();
		}, 200);
		setTimeout(() => {
			for (k = 1; k <= 3; k++) {
				var a1 = document.getElementById(pagePrefix + k);
				a1.classList.remove("tran");
			}
		}, 1400);
	}
}

function rightSlide() {
	if (click) {
		if (curpage == 3) curpage = 0;
		console.log(curpage);
		sliding = true;
		curpage++;
		svg = false;
		click = false;
		for (k = 1; k <= 3; k++) {
			var a1 = document.getElementById(pagePrefix + k);
			a1.className += " tran";
		}
		setTimeout(() => {
			move();
		}, 200);
		setTimeout(() => {
			for (k = 1; k <= 3; k++) {
				var a1 = document.getElementById(pagePrefix + k);
				a1.classList.remove("tran");
			}
		}, 1400);

        timer();
	}
}

function move() {
	if (sliding) {
		sliding = false;
		if (svg) {
			for (j = 1; j <= 9; j++) {
				var c = document.getElementById(transitionPrefix + j);
				c.classList.remove("steap");
				c.setAttribute("class", transitionPrefix + j + " streak");
				console.log("streak");
			}
		} else {
			for (j = 10; j <= 18; j++) {
				var c = document.getElementById(transitionPrefix + j);
				c.classList.remove("steap");
				c.setAttribute("class", transitionPrefix + j + " streak");
				console.log("streak");
			}
		}
		setTimeout(() => {
			for (i = 1; i <= 3; i++) {
				if (i == curpage) {
					var a = document.getElementById(pagePrefix + i);
					a.className += " up1";
				} else {
					var b = document.getElementById(pagePrefix + i);
					b.classList.remove("up1");
				}
			}
			sliding = true;
		}, 600);
		setTimeout(() => {
			click = true;
		}, 1700);

		setTimeout(() => {
			if (svg) {
				for (j = 1; j <= 9; j++) {
					var c = document.getElementById(transitionPrefix + j);
					c.classList.remove("streak");
					c.setAttribute("class", transitionPrefix + j + " steap");
				}
			} else {
				for (j = 10; j <= 18; j++) {
					var c = document.getElementById(transitionPrefix + j);
					c.classList.remove("streak");
					c.setAttribute("class", transitionPrefix + j + " steap");
				}
				sliding = true;
			}
		}, 850);
		setTimeout(() => {
			click = true;
		}, 1700);
	}
}

left.onmousedown = () => {
	leftSlide();
};

right.onmousedown = () => {
	rightSlide();
};

document.onkeydown = e => {
	if (e.keyCode == 37) {
		leftSlide();
	} else if (e.keyCode == 39) {
		rightSlide();
	}
};


function slider2a(id) {
if(id > 8){
	id = 1;
}
	var img_sl = document.getElementById('slider-2.'+id);
	var fc_sl = document.getElementById('slider-2.'+id+'a');
	for (let i = 1; i < 9; i++) {
		if(i === id){
			document.getElementById('slider-2.'+i).style.display='block';
			document.getElementById('slider-2.'+i+'a').classList.add('active-2');
		}else{
			document.getElementById('slider-2.'+i).style.display='none';
			if( document.getElementById('slider-2.'+i+'a').classList.contains('active-2')){
				document.getElementById('slider-2.'+i+'a').classList.remove('active-2');
			}
			
		}
	
	}
	var new_id = id++;
	setTimeout(() => {
	
			// slider2a(id++);
		}, 5000);
	
}

function slider13(id) {
	if(id > 7){
		id = 1;
	}
		var img_sl = document.getElementById('slider-13.'+id);
		var fc_sl = document.getElementById('slider-13.'+id+'a');
		for (let i = 1; i < 8; i++) {
			if(i === id){
				document.getElementById('slider-13.'+i).style.display='block';
				document.getElementById('slider-13.'+i+'a').classList.add('active-2');
			}else{
				document.getElementById('slider-13.'+i).style.display='none';
				if( document.getElementById('slider-13.'+i+'a').classList.contains('active-2')){
					document.getElementById('slider-13.'+i+'a').classList.remove('active-2');
				}
				
			}
		
		}
		// var new_id = id++;
		// setTimeout(() => {
		
		// 		slider13(id++);
		// 	}, 5000);
		
	}

	function slider14(id) {
		if(id > 8){
			id = 1;
		}
			var img_sl = document.getElementById('slider-14.'+id);
			var fc_sl = document.getElementById('slider-14.'+id+'a');
			for (let i = 1; i < 9; i++) {
				if(i === id){
					document.getElementById('slider-14.'+i).style.display='block';
					document.getElementById('slider-14.'+i+'a').classList.add('active-2');
				}else{
					document.getElementById('slider-14.'+i).style.display='none';
					if( document.getElementById('slider-14.'+i+'a').classList.contains('active-2')){
						document.getElementById('slider-14.'+i+'a').classList.remove('active-2');
					}
					
				}
			
			}
			// var new_id = id++;
			// setTimeout(() => {
			
			// 		slider13(id++);
			// 	}, 5000);
			
		}