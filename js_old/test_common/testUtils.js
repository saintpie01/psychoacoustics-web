
const algorithmMapping = {
    SimpleUpDown: 1,
    TwoDownOneUp: 2,
    ThreeDownOneUp: 3,
};


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

//test
function start_2() {
    document.getElementById("StartingWindow").style.display = "none"; //starting window becomes invisible
    document.getElementById("PlayForm").style.display = "inherit"; //test interface becomes visible
    // document.getElementById("downloadData").style.display = "inherit"; //test interface becomes visible

    // take the timestamp when the test starts
    var currentdate = new Date();
    timestamp = currentdate.getFullYear() + "-" + (currentdate.getMonth() + 1) + "-" + currentdate.getDate() + " " + currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();

    //window.setTimeout("random()", ITI); //test starts after interTrialInterval ms
}


document.addEventListener('keypress', function keypress(event) {
    if (!document.getElementById("button1").disabled) {
        if ((event.code >= 'Digit1' && event.code <= 'Digit' + nAFC) ||
            (event.code >= 'Numpad1' && event.code <= 'Numpad' + nAFC)) {
            select(event.key);
            console.log('You pressed ' + event.key + ' button');
        }
    }
});

