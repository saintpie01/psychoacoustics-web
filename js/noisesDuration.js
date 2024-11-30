//contesto e dichiarazione variabili da cambiare durante il test, probabilmente andranno tolte molte variabili globali da qui una volta terminato l'algoritmo
var context = new AudioContext();

// minimum initial variation
delta /= 1000;                      // cambio unità di misura in secondi
var startingDelta = delta;

dur /= 1000;                        // cambio unità di misura in secondi
var stdDur = dur;					// duration of the standard 
var varDur = dur + delta;				// duration of the variable

var stdAmp = amp;					// intensity of the variable
var varAmp = amp;					// intensity of the standard 



//funzione per randomizzare l'output
function random() {
    var rand = Math.floor(Math.random() * nAFC);// the variable sound will be the rand-th sound played
    for (var j = 0; j < nAFC; j++) {
        if (j == rand)
            playNoise((ITI / 1000) + (j * stdDur) + j * (ISI / 1000), varAmp, varDur, onRamp / 1000, offRamp / 1000, false);
        else if (j < rand)
            playNoise((ITI / 1000) + (j * stdDur) + j * (ISI / 1000), stdAmp, stdDur, onRamp / 1000, offRamp / 1000);
        else if (j > rand)
            playNoise(((ITI / 1000) + (j - 1) * stdDur) + varDur + j * (ISI / 1000), stdAmp, stdDur, onRamp / 1000, offRamp / 1000);
    }

    swap = rand + 1;

    activateButtons()
}


//funzione per implementare l'algoritmo SimpleUpDown
function select(button) {
    pressedButton = button;

    results[0][i] = currentBlock;				// block
    results[1][i] = i + 1;						// trial
    results[2][i] = parseFloat(parseInt((varDur - stdDur) * 1000)); 	// approximated delta
    results[3][i] = parseFloat(parseInt(varDur * 1000));				// approximated variable value
    results[4][i] = swap;

    //apply the algorithm to check for reversals, modify the delta parameter if needed
    delta = varDur - stdDur;

    checkReversal = nDOWNoneUPTest(upDownParam);

    if (checkReversal == 1)
        delta /= currentFactor;
    else if (checkReversal == -1)
        delta *= currentFactor;

    varDur = stdDur + delta;

    results[5][i] = pressedButton; 				// pressed button
    results[6][i] = pressedButton == swap ? 1 : 0;	// is the answer correct? 1->yes, 0->no
    results[7][i] = countRev; // reversals counter is updated in nDOWNoneUP() function and saved after it

    //increment counter
    i++;


    //end of the test
    if (countRev == reversals + secondReversals) {
        
        createResults();

        //format description as a csv file
        //prima tutti i nomi, poi tutti i dati
        var description = "&amp=" + amp + "&freq=" + freq + "&dur=" + dur + "&onRamp=" + onRamp + "&offRamp=" + offRamp +/*"&phase="+phase+*/"&blocks=" + blocks + "&delta=" + startingDelta + "&nAFC=" + nAFC + "&ISI=" + ISI + "&ITI=" + ITI;
        description += "&fact=" + factor + "&secFact=" + secondFactor + "&rev=" + reversals + "&secRev=" + secondReversals + "&threshold=" + reversalThreshold + "&alg=" + algorithm + "&sampleRate=" + context.sampleRate;

        //pass the datas to the php file
        location.href = "php/save_test.php?result=" + result + "&timestamp=" + timestamp + "&type=ndur" + description + "&currentBlock=" + currentBlock + "&score=" + score + "&geometric_score=" + geometric_score + "&saveSettings=" + saveSettings;
    }
    //if the test is not ended
    else {

        // disable the response buttons until the new sounds are heared
        for (var j = 1; j <= nAFC; j++)
            document.getElementById("button" + j).disabled = true;

        //randomize and play the next sounds
        random();
        //window.setTimeout("random()", ITI); //next sounds after interTrialInterval ms
    }
}



