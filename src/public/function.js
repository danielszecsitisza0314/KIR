function popup(popup_name) {
	get_popup = document.getElementById(popup_name);
	if (get_popup.style.display == "flex") {
		get_popup.style.display = "none";
	} else {
		get_popup.style.display = "flex";
	}
}

function checkTaxNum() {
	taxNum = document.getElementById("taxnumber").value;
	if (taxNum.length == 13) {
		amount = (9 * taxNum.substring(0, 1)) + (7 * taxNum.substring(1, 2)) + (3 * taxNum.substring(2, 3)) + (1 * taxNum.substring(3, 4)) + (9 * taxNum.substring(4, 5)) + (7 * taxNum.substring(5, 6)) + (3 * taxNum.substring(6, 7));
		amountCastString = amount.toString();
		amountLastNum = amountCastString.substring(amountCastString.length - 1);
		eighthNum = taxNum.substring(7, 8);
		if (amountLastNum != 0) {
			if (eighthNum == (10 - amountLastNum)) {
				document.getElementById("taxnumber").style.backgroundColor = 'rgb(' + 0 + ',' + 255 + ',' + 0 + ')';
				document.getElementById("invalidTaxNum").innerHTML = '';
			} else {
				document.getElementById("taxnumber").style.backgroundColor = 'rgb(' + 233 + ',' + 185 + ',' + 193 + ')';
				document.getElementById("invalidTaxNum").innerHTML = 'Nem megfelelő adószám!';
			}
		} else {
			if (eighthNum == amountLastNum) {
				document.getElementById("taxnumber").style.backgroundColor = 'rgb(' + 0 + ',' + 255 + ',' + 0 + ')';
				document.getElementById("invalidTaxNum").innerHTML = '';
			} else {
				document.getElementById("taxnumber").style.backgroundColor = 'rgb(' + 233 + ',' + 185 + ',' + 193 + ')';
				document.getElementById("invalidTaxNum").innerHTML = 'Nem megfelelő adószám!';
			}
		}
	} else {
		document.getElementById("taxnumber").style.backgroundColor = 'rgb(' + 233 + ',' + 185 + ',' + 193 + ')';
		document.getElementById("invalidTaxNum").innerHTML = 'Adószám kitöltése kötelező!';
	}
}
function setDateAndName(input, name, date) {
	value = document.getElementById(input).value;
	if (value.length != 0) {
		if (value.substring(value.length - 1) != ']' && value.substring(value.length - 1) != '\n') {
			document.getElementById(input).value = value + " - " + name + " [" + date + "]";
		}
	}
	}

	function insertNewLine(input) {
		value = document.getElementById(input).value;
		if (value.substring(value.length - 1) == ']' && value.substring(value.length - 1) != '\n') {
			document.getElementById(input).value = value + "\r\n";
		}
	}

	function fetchData() {
		var action = 'fetchData';
		$.ajax({
			url: "action.php",
			method: "POST",
			data: { action: action },
			success: function(data) {
				//$('#post-list').html(data);
				alert(data);
			}

		});
	}



