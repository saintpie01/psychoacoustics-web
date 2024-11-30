//contesto e dichiarazione variabili da cambiare durante il test, probabilmente andranno tolte molte variabili globali da qui una volta terminato l'algoritmo
var context = new AudioContext();

var carAmp = carAmpDb;
var modAmp = modAmpDb;

// minimum initial variation
var startingDelta = modAmp;
delta = modAmp;

carDur /= 1000;             // cambio unità di misura in secondi



//funzione per randomizzare l'output
function random() {
    var rand = Math.floor(Math.random() * nAFC);// the variable sound will be the rand-th sound played
    for (var j = 0; j < nAFC; j++) {
        if (j == rand)
            playModulatedNoise((ITI / 1000) + (j * carDur) + j * (ISI / 1000), carAmp, carDur, modAmp, modFreq, modPhase, onRamp / 1000, offRamp / 1000, false);
        else
            playNoise((ITI / 1000) + (j * carDur) + j * (ISI / 1000), carAmp, carDur, onRamp / 1000, offRamp / 1000);
    }

    swap = rand + 1;

    activateButtons()
}


//funzione per implementare l'algoritmo SimpleUpDown
function select(button) {
    pressedButton = button;

    results[0][i] = currentBlock;				// block
    results[1][i] = i + 1;						// trial
    results[2][i] = parseFloat(parseInt(delta * 1000) / 1000); 	// approximated delta
    results[3][i] = parseFloat(parseInt(delta * 1000) / 1000);				// approximated variable value
    results[4][i] = swap;	            // variable position

    //apply the algorithm to check for reversals, modify the delta parameter if needed

    checkReversal = nDOWNoneUPTest(upDownParam);

    modAmp = delta;

    if (checkReversal == 1)
        delta /= currentFactor;
    else if (checkReversal == -1)
        delta *= currentFactor;



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
        var description = "&amp=" + carAmp + "&dur=" + carDur + "&freq=0" + "&onRamp=" + onRamp + "&offRamp=" + offRamp + "&modAmp=" + modAmp + "&modFreq=" + modFreq + "&modPhase=" + modPhase + "&blocks=" + blocks + "&delta=" + startingDelta + "&nAFC=" + nAFC + "&ISI=" + ISI + "&ITI=" + ITI;
        description += "&fact=" + factor + "&secFact=" + secondFactor + "&rev=" + reversals + "&secRev=" + secondReversals + "&threshold=" + reversalThreshold + "&alg=" + algorithm + "&sampleRate=" + context.sampleRate;

        //pass the datas to the php file
        location.href = "php/save_test.php?result=" + result + "&timestamp=" + timestamp + "&type=nmod" + description + "&currentBlock=" + currentBlock + "&score=" + score + "&geometric_score=" + geometric_score + "&saveSettings=" + saveSettings;
    }
    //if the test is not ended
    else {
        // disable the response buttons until the new sounds are heared
        for (var j = 1; j <= nAFC; j++) document.getElementById("button" + j).disabled = true;

        //randomize and play the next sounds
        random();
        //window.setTimeout("random()", ITI); //next sounds after interTrialInterval ms
    }
}
