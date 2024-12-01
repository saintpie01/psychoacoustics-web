
const algorithmMapping = {
    SimpleUpDown: 1,
    TwoDownOneUp: 2,
    ThreeDownOneUp: 3,
};


const upDownParam = algorithmMapping[algorithm] || 2; //decide which parameter to pass on nDOWNoneUP based on alg passed
var context = new AudioContext();

// array and variables for data storage
var history = [];				// will have the answers ('1' if right, '0' if wrong)
var reversalsPositions = [];	// will have the position of the i-th reversal in the history array 
var i = 0;						// next index of the array
var countRev = 0;				// count of reversals 
var results = [[], [], [], [], [], [], [], []];		// block, trial, delta, variable value, variable position, pressed button, correct answer?, reversals
var score = 0					// final score
var geometric_score = 1
var positiveStrike = -1;		// -1 = unsetted, 0 = negative strike, 1 = positive strike
var result = "";				// final results that will be saved on the db
var timestamp = 0;				// timestamp of the starting of the test
var pressedButton;
var swap = -1;						// position of variable sound
var correctAnsw = 0;				// number of correct answers
var currentFactor = factor;			// first or second factor, depending on the number of reversals
var checkReversal = 0;
var description;



//starting function for all the tests
function start() {
    document.getElementById("StartingWindow").style.display = "none"; //starting window becomes invisible
    document.getElementById("PlayForm").style.display = "inherit"; //test interface becomes visible

    // take the timestamp when the test starts
    var currentdate = new Date();
    timestamp = currentdate.getFullYear() + "-" + (currentdate.getMonth() + 1) + "-" + currentdate.getDate() + " " + currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();

    createRandomizedOutput();
}


function timer() {
    document.getElementById("wrong").style.display = "none";
    document.getElementById("correct").style.display = "none";
}


document.addEventListener('keypress', function keypress(event) {
    if (!document.getElementById("button1").disabled) {
        if ((event.code >= 'Digit1' && event.code <= 'Digit' + nAFC) ||
            (event.code >= 'Numpad1' && event.code <= 'Numpad' + nAFC)) {
            computeResponse(event.key);
            console.log('You pressed ' + event.key + ' button');
        }
    }
});


function nDOWNoneUPTest(n) {
    revDirection = 0 //'0->no reversal' '1->reversal up' '-1->reversal down'

    if (pressedButton == swap) { //correct answer
        history[i] = 1;
        correctAnsw += 1;

        if (correctAnsw == n) { //if there are n consegutive correct answers
            correctAnsw = 0;

            if (positiveStrike == 0) {
                //there is a reversal
                reversalsPositions[countRev] = i - (n - 1);//save the position of that 
                countRev++;
                if (countRev > reversals)
                    currentFactor = secondFactor;

            }
            revDirection = 1;
            positiveStrike = 1;
        }

        if (feedback) {
            document.getElementById("correct").style.display = "inherit";
            document.getElementById("wrong").style.display = "none";
        }

    } else { //wrong answer
        history[i] = 0;
        correctAnsw = 0;

        if (positiveStrike == 1) {
            //there was a reversal
            reversalsPositions[countRev] = i;//save the position of that reversal
            countRev++;
            if (countRev > reversals)
                currentFactor = secondFactor;

        }
        revDirection = -1;
        positiveStrike = 0;

        if (feedback) {
            document.getElementById("correct").style.display = "none";
            document.getElementById("wrong").style.display = "inherit";
        }
    }

    window.setTimeout("timer()", 500);
    return revDirection;
}


function createResults() {

    //format datas as a csv file
    //format: block;trials;delta;variableValue;variablePosition;button;correct;reversals;";
    for (var j = 0; j < i; j++) {
        result += results[0][j] + ";" + results[1][j] + ";" + results[2][j] + ";" + results[3][j] + ";"
        result += results[4][j] + ";" + results[5][j] + ";" + results[6][j] + ";" + results[7][j] + ",";
    }

    //calculate score
    for (var j = countRev - reversalThreshold; j < countRev; j++) {
        deltaBefore = results[2][reversalsPositions[j] - 1]; //delta before the reversal
        deltaAfter = results[2][reversalsPositions[j]]; //delta after the reversal
        score += (deltaBefore + deltaAfter) / 2; //average delta of the reversal
        geometric_score *= (deltaBefore + deltaAfter) / 2;
    }
    geometric_score = Math.pow(geometric_score, 1 / reversalThreshold);
    geometric_score = parseFloat(parseInt(geometric_score * 100) / 100);
    score /= reversalThreshold; //average deltas of every reversal
    score = parseFloat(parseInt(score * 100) / 100); //approximate to 2 decimal digits
    description = "&blocks=" + blocks + "&sampleRate=" + context.sampleRate;

}


function enableResponseButtons() {
    //after playing the sound, the response buttons are reactivated
    source.onended = () => { //waiting for the sound to ends
        for (var j = 1; j <= nAFC; j++)
            document.getElementById("button" + j).disabled = false;
    }
}


function disableResponseButtons() {
    for (var j = 1; j <= nAFC; j++)
        document.getElementById("button" + j).disabled = true;
}


//this function could be written as monolithic with only createRandomizedOutput as a function
//but i find it's more clear this way, other than more reusable
//i know this is dangerously close to spaghetticode but i'm not rewriting every single line of this code
//since it's kinda acceptable now
function continueTest() {
    if (countRev < reversals + secondReversals) {
        // disable the response buttons until the new sounds are heared
        disableResponseButtons();
        //randomize and play the next sounds
        createRandomizedOutput();

    } else {
        //test ended
        createResults();
        //pass the data to the php file
        endTest()
    }
}


function endTest() {
    location.href =
        "php/save_test.php?result=" + result + description +
        "&timestamp=" + timestamp +
        "&currentBlock=" + currentBlock +
        "&score=" + score +
        "&geometric_score=" + geometric_score;
}




