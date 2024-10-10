
//not used!!, send longstring with POST method to fullUrl page
function sendDataToPHP(fullUrl, longString) {
	fetch(fullUrl, {

		method: 'POST', // Use POST to send the data
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
        
		body: 'result=' + encodeURIComponent(longString) // Send the data via POST
	});
}


function timer() {
    document.getElementById("wrong").style.display = "none";
    document.getElementById("correct").style.display = "none";
}


//starting function
function start() {
    document.getElementById("StartingWindow").style.display = "none"; //starting window becomes invisible
    document.getElementById("PlayForm").style.display = "inherit"; //test interface becomes visible
    // document.getElementById("downloadData").style.display = "inherit"; //test interface becomes visible

    // take the timestamp when the test starts
    var currentdate = new Date();
    timestamp = currentdate.getFullYear() + "-" + (currentdate.getMonth() + 1) + "-" + currentdate.getDate() + " " + currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();

    random();
    //window.setTimeout("random()", ITI); //test starts after interTrialInterval ms
}


//funzione per implementare l'algoritmo nD1U
function nDOWNoneUP(n) {
    delta = modAmp;

    if (pressedButton == swap) { //correct answer
        history[i] = 1;
        correctAnsw += 1;
        if (correctAnsw == n) { //if there are n consegutive correct answers
            modAmp /= currentFactor;
            
            correctAnsw = 0;
            if (positiveStrike == 0) {
                //there was a reversal
                reversalsPositions[countRev] = i - (n - 1);//save the position of that reversal
                countRev++;
            }
            positiveStrike = 1;
        }
        if (feedback) {
            document.getElementById("correct").style.display = "inherit";
            document.getElementById("wrong").style.display = "none";
            window.setTimeout("timer()", 500);
        }

    } else { //wrong answer
        history[i] = 0;
        correctAnsw = 0;
        modAmp *= currentFactor;

        if (positiveStrike == 1) {
            //there was a reversal
            reversalsPositions[countRev] = i;//save the position of that reversal
            countRev++;
        }
        positiveStrike = 0;

        if (feedback) {
            document.getElementById("correct").style.display = "none";
            document.getElementById("wrong").style.display = "inherit";
            window.setTimeout("timer()", 500);
        }
    }
    // document.getElementById("downloadData").disabled = true;
    stimulus = []; // debug
}
