/*
CharacterCount = function(TextArea,FieldToCount){
	var CharsLeft = document.getElementById(TextArea);
	var myLabel = document.getElementById(FieldToCount); 
	if(!CharsLeft || !myLabel){return false}; // catches errors
	var MaxChars =  CharsLeft.maxLengh;
	if(!MaxChars){MaxChars =  CharsLeft.getAttribute('maxlength') ; }; 	if(!MaxChars){return false};
	var remainingChars =   MaxChars - CharsLeft.value.length
	myLabel.innerHTML = remainingChars+" tekens over van maximaal "+MaxChars
}

//SETUP!!
setInterval(function(){CharacterCount('CharsLeft','CharCountLabel1')},55);
*/

CharacterCount = function(TextArea,FieldToCount){
	var CharsLeft = document.getElementById(TextArea);
	var myLabel = document.getElementById(FieldToCount); 
	//var two_chars = "\n\\^~[]{}|€ÜÇ";
	//var two_chars_count = 0;	
	if(!CharsLeft || !myLabel){return false}; // catches errors
	var MaxChars =  CharsLeft.maxLengh;
	if(!MaxChars){MaxChars =  CharsLeft.getAttribute('maxlength') ; }; 	if(!MaxChars){return false};
	var remainingChars =   MaxChars - CharsLeft.value.length
	var usedChars  = CharsLeft.value.length;
	var two_chars = "\n\\^~[]{}|€ÜÇ";
	var two_chars_count = 0;
	var status = '';
	if (usedChars <= 160) {
	  if (usedChars == 160) {
	    status = '<span style="color:red">0</span>, 1 SMS';
    }
    else {
      status = (160 - usedChars) + ',<span style="color:green"> 1 SMS</span>';
    }
	}	
	//else if (cut) {
	//  status = '0, 1 SMS';
	//  textarea.val(message.substr(0, 160 - two_chars_count));
	//}
	else if (usedChars == 4 * 153) {
	  status = '<span style="color:red">0, 4 SMS</span>';
	}
	else if (usedChars > 4 * 153) {
	  status = '<span style="color:red">0, 4 SMS</span>';
	  //textarea.val(message.substr(0, 4 * 153 - two_chars_count));
	  //textarea[0].scrollTop = textarea[0].scrollHeight;
	}
	else {
	  status = (usedChars % 153 == 0 ? '<span style="color:red">0</span>' : (153 - (usedChars % 153)));
	  status += ', <span style="color:orange">' + Math.ceil(usedChars / 153) + ' SMS</span>';
	}	
	usedChars = usedChars + two_chars_count;
	myLabel.innerHTML = status;
}

//SETUP!!
setInterval(function(){CharacterCount('CharsLeft','CharCountLabel1')},55);
