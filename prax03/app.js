let ranks = ["2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K", "A"];
let suits = ["H", "D", "C", "S"];

let game_type, game_size, player_count, game_code, host, poller;
let move = null;
let score_element = gid("score");
let timer_element = gid("time");
let player_score, time, timer, player, host_name, host_score, opponent_name, opponent_score;
let deck = []; // just json values
let card_board = gid("card_board"); // actual elements
let flippedCards = [];
let checkingCards = [];
let game_in_progress = false;
let game_start;
gid("start_button").addEventListener("click", startGame);
gid("join_button").addEventListener("click", joinGame);
gid("score_button").addEventListener("click", openScorePage);
let type_selector = gid("selectType");
let size_selector = gid("selectSize");
let simulating = false;
type_selector.addEventListener("change", optiontoggle);
window.onload = function() {
    updateDeclaredGames();
};
// data for moves: {host (if host made the move): t/f, card1: card1id, card2:card2id, match: t/f, hostname: name, hostscore: score, opponentname: name, opponentscore: score}

function postMove(card1, card2, match) {
    let xhttp = new XMLHttpRequest();
    xhttp.open("POST", "./../cgi-bin/prax3/game_handler.py?code=" + game_code, false);
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    let object;
    if (!host) {
        object = {host: host, card1: card1, card2: card2, match: match, hostname: host_name, hostscore: host_score, opponentname: player, opponentscore: player_score};
    } else {
        opponent_score = move["opponentscore"];
        object = {host: host, card1: card1, card2: card2, match: match, hostname: player, hostscore: player_score, opponentname: move["opponentname"], opponentscore: move["opponentscore"]}
    }
    move = object;
    xhttp.send(JSON.stringify(object));
}
function simulateTurn() {
    console.log("Starting to simulate a turn!");
    simulating = true;
    gid(move["card1"]).addEventListener("click", cardClick);
    gid(move["card2"]).addEventListener("click", cardClick);
    gid(move["card1"]).click();
    gid(move["card2"]).click();
    gid(move["card1"]).removeEventListener("click", cardClick);
    gid(move["card2"]).removeEventListener("click", cardClick);
    simulating = false;
    if (flippedCards.length === 0) {
        startTurn();
        endGame();
    }
}
function startTurn() {
    console.log("Starting my turn");
    if (poller != null) {
        clearInterval(poller);
        poller = null;
    }
    enableAllExistingCards();
}
function endTurn() {
    console.log("Ending my turn");
    disableAllExistingCards();
    poller = setInterval(function() {
        let xhttp = new XMLHttpRequest();
        xhttp.open("GET", "./../cgi-bin/prax3/game_handler.py?code=" + game_code, false);
        xhttp.setRequestHeader("Content-Type", "application/json");
        xhttp.send();
        let response = JSON.parse(xhttp.responseText);
        if (response["host"] !== host && response !== move) {
            move = response;
            simulateTurn();
            if (!move["match"]) {
                startTurn();
            }
        }
    }, 600);
}
function joinGame() {
    endGame(true);
    if (player == null || player === "") {
        let name = prompt("Please enter your name:", "Mr/Ms Default");
        if (name == null || name === "") {
            player = "Mr/Ms Default";
        } else {
            player = name;
        }
    }
    let gameCode = prompt("Please enter code of the game you wish to join:", null);
    if (gameCode == null || gameCode === "") {
        return;
    }
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", "./../cgi-bin/prax3/declaration_output.py?code=" + gameCode, false);
    xhttp.setRequestHeader("Content-Type", "application/json");
    xhttp.send();
    let response = JSON.parse(xhttp.responseText);
    if (response == null) {
        alert("No such outstanding game invite!");
        return;
    }
    player_count = 2;
    player_score = 0;
    game_size = Number(response.size);
    deck = response.deck;
    game_code = gameCode;
    host_name = response.player;
    host_score = 0;
    time = 0;
    host = false;
    if (response.type === "rank_match") {
        game_type = 1;
    } else {
        game_type = 2;
    }
    drawDeck();
    game_in_progress = true;
    timer = setInterval(function() {
        time = time + 1;
        timer_element.innerText = time;
    }, 1000);
}
function sendScoreToServer(score_object) {
    let xhttp = new XMLHttpRequest();
    xhttp.open("POST", "./../cgi-bin/prax3/score_input.py");
    xhttp.setRequestHeader("Content-Type", "application/json");
    xhttp.send(JSON.stringify(score_object));
}

function openScorePage() {
    window.open("./../cgi-bin/prax3/score_display.py");
}

function optiontoggle() {
    size_selector.options[3].hidden = type_selector.value === "1";
    if (type_selector.value === "1" && size_selector.selectedIndex === 3) {
        size_selector.selectedIndex = 0;
    }
}

function startGame() {
    endGame(true);
    if (player == null || player === "") {
        let name = prompt("Please enter your name:", "Mr/Ms Default");
        if (name == null || name === "") {
            player = "Mr/Ms Default";
        } else {
            player = name;
        }
    }
    game_type = Number(gid("selectType").value);
    game_size = Number(gid("selectSize").value);
    time = 0;
    deck = generateDeck(game_type, game_size);
    player_count = Number(gid("selectPlayers").value);
    player_score = 0;
    if (player_count === 1) {
        startSoloGame();
    } else {
        setupDuoGame();
    }
}
function setupDuoGame() {
    game_code = generateGameCode();
    host = true;
    let type;
    if (game_type === 1) {
        type = "rank_match"
    } else {
        type = "rank_and_color_match"
    }
    let xhttp = new XMLHttpRequest();
    xhttp.open("POST", "./../cgi-bin/prax3/declaration_input.py", false);
    xhttp.setRequestHeader("Content-Type", "application/json");
    xhttp.send(JSON.stringify({code: game_code, player: player, type: type, size: game_size, deck: deck}));
    alert("Game created, game code is " + game_code + "\nOpponent will have the first move.");
    drawDeck();
    disableAllExistingCards();
    poller = setInterval(function() {
        let xhttp = new XMLHttpRequest();
        xhttp.open("GET", "./../cgi-bin/prax3/game_handler.py?code=" + game_code, false);
        xhttp.setRequestHeader("Content-Type", "application/json");
        xhttp.send();
        let response = JSON.parse(xhttp.responseText);
        if (response !== move) {
            timer = setInterval(function() {
                time = time + 1;
                timer_element.innerText = time;
            }, 1000);
            move = response;
            opponent_name = move["opponentname"];
            simulateTurn();
            let dt = new Date();
            game_start = dt.toUTCString();
            if (!move["match"]) {
                startTurn();
            }
        }
    }, 600);
    game_in_progress = true;
}
function startSoloGame() {
    game_in_progress = true;
    let dt = new Date();
    game_start = dt.toUTCString();
    host_name = null;
    score_element.innerText = player_score;
    timer_element.innerText = time;
    timer = setInterval(function() {
        time = time + 1;
        timer_element.innerText = time;
    }, 1000);
    flippedCards = [];
    checkingCards = [];
    opponent_name = null;
    opponent_score = null;
    drawDeck();
}

function generateGameCode() {
    let chars = "0123456789abcdefghijklmnopqrstuvwxyz";
    let len = chars.length;
    let code = "";
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(len * Math.random()));
    }
    return code;
}

function drawDeck() {
    while (card_board.hasChildNodes()) {
        card_board.removeChild(card_board.lastChild);
    }
    deck.forEach(element => {
        let newCard = document.createElement("div");
        newCard.className = "card";
        newCard.classList.add(element.suit);
        newCard.classList.add("r-" + element.rank);
        newCard.classList.add("flipped");
        newCard.addEventListener("click", cardClick);
        newCard.id = element.suit + element.rank;
        flippedCards.push(newCard.id);
        card_board.appendChild(newCard);
    });
}

function cardClick(event) {
    let card = event.target;
    if (checkingCards.indexOf(card.id) >= 0) {
        return;
    } else {
        removeCardFromFlippedCards(card);
        toggleFlip(card);
        checkingCards.push(card.id);
    }
    if (checkingCards.length === 2) {
        checkCards(simulating);
        // checkCardsRedux(card);
    }
    if (flippedCards.length === 0) {
        endGame(false);
    }
}

function endGame(interrupt) {
    if (!game_in_progress) {
        return;
    }
    game_in_progress = false;
    clearInterval(timer);
    if (poller != null) {
        clearInterval(poller);
    }
    poller = null;
    timer = null;
    if (interrupt) {
        return;
    }
    let type;
    if (game_type === 1) {
        type = "rank_match"
    } else {
        type = "rank_and_color_match"
    }
    if (player_count === 1 || player_count === 2 && host) {
        let score_object = {
            game_start: game_start,
            player_count: player_count,
            time: time,
            player: player,
            player_score: player_score,
            opponent: opponent_name,
            opponent_score: opponent_score,
            size: game_size,
            type: type
        };
        sendScoreToServer(score_object);
    }
    alert("Neat!\nYour score was " + player_score + " points.\nYour time was " + time + " seconds.");
}

function updateScore() {
    score_element.innerText = player_score;
}

function checkCards(doesntaffectscore) {
    let card1 = gid(checkingCards[0]);
    let card2 = gid(checkingCards[1]);
    let card1_values = getCardData(card1);
    let card2_values = getCardData(card2);
    let match = false;
    if (game_type === 1) {
        if (card1_values.rank === card2_values.rank) {
            disableCard(card1);
            disableCard(card2);
            setTimeout(function(){
                hide(card1);
                hide(card2);
            }, 1000);
            if (!doesntaffectscore) {
                player_score += 10;
            }
            updateScore();
            if (player_count === 2 && !simulating) {
                postMove(card1.id, card2.id, true)
            }
            return;
        }
    }
    if (game_type === 2) {
        if (card1_values.rank === card2_values.rank) {
            let suits1 = suits.slice(0, 2);
            let suits2 = suits.slice(2);
            if (suits1.includes(card1_values.suit) && suits1.includes(card2_values.suit) ||
            suits2.includes(card1_values.suit) && suits2.includes(card2_values.suit)) {
                disableCard(card1);
                disableCard(card2);
                setTimeout(function(){
                    hide(card1);
                    hide(card2);
                }, 1000);
                if (!doesntaffectscore) {
                    player_score += 10;
                }
                updateScore();
                if (player_count === 2 && !simulating) {
                    postMove(card1.id, card2.id, true);
                }
                return;
            }
        }
    }
    if (!doesntaffectscore) {
        player_score -= 2;
    }
    updateScore();
    removeCardFromCheckingCards(card1);
    removeCardFromCheckingCards(card2);
    flippedCards.push(card1.id);
    flippedCards.push(card2.id);
    setTimeout(function(){
        toggleFlip(card1);
        toggleFlip(card2);
    }, 1000);
    if (player_count === 2 && !simulating) {
        postMove(card1.id, card2.id, false);
        endTurn();
    }

}

function getCardData(card) {
    let suit = card.id.slice(0, 1);
    let rank = card.id.slice(1);
    return {suit: suit, rank: rank};
}

function toggleFlip(card) {
    card.classList.toggle("flipped");
}

function disableCard(card) {
    card.removeEventListener("click", cardClick);
    removeCardFromCheckingCards(card);
}

function disableAllExistingCards() {
    flippedCards.forEach(card => {
        gid(card).removeEventListener("click", cardClick);
    });
}

function enableAllExistingCards() {
    flippedCards.forEach(card => {
        gid(card).addEventListener("click", cardClick)
    });
}

function hide(card) {
    card.classList.add("hidden");
}

function removeCardFromFlippedCards(card) {
    let index = flippedCards.indexOf(card.id);
    if (index > -1) {
        flippedCards.splice(index, 1);
        return true;
    }
    return false;
}

function removeCardFromCheckingCards(card) {
    let index = checkingCards.indexOf(card.id);
    if (index > -1) {
        checkingCards.splice(index, 1);
        return true;
    }
    return false;
}

/**
 * Shuffles array in place. ES6 version
 * Taken from https://stackoverflow.com/questions/6274339/how-can-i-shuffle-an-array
 * @param {Array} a items An array containing the items.
 */
function shuffle(a) {
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

/**
 * Generate deck based on type and size requried.
 * Type 1 means just card rank has to be same.
 * Type 2 means card rank and suit colour has to match, meaning S2 and C2 are same
 */
function generateDeck(type, size) {
    let tempRanks = ranks.slice();
    if (type === 1 && size > 26) {
        size = 26;
    }
    if (size % 2 === 1) {
        size = size - 1;
    }
    let generatedDeck = [];
    if (type === 1) {
        let combinationAmount = size / 2;
        console.log(combinationAmount);
        shuffle(tempRanks);
        for (let i = 0; i < combinationAmount; i++) {
            let suitOne = Math.floor(Math.random() * 4);
            let suitTwo = (suitOne + Math.floor(Math.random() * 3) + 1) % 4;
            generatedDeck.push({rank: tempRanks[i], suit: suits[suitOne]});
            generatedDeck.push({rank: tempRanks[i], suit: suits[suitTwo]});
        }
        return shuffle(generatedDeck);
    }
    if (type === 2) {
        let tempSuits = ["S", "H"];
        let tempCards = [];
        ranks.forEach(rank => {
            tempSuits.forEach(suit => {
                tempCards.push(
                    {rank: rank, suit: suit}
                );
            });
        });
        shuffle(tempCards);
        let combinationAmount = size / 2;
        console.log(combinationAmount);
        for (let i = 0; i < combinationAmount; i++) {
            let tempCard = tempCards[i];
            generatedDeck.push(tempCard);
            let pairCardSuit;
            if (tempCard.suit === "S") {
                pairCardSuit = "C";
            } else {
                pairCardSuit = "D";
            }
            generatedDeck.push({rank: tempCard.rank, suit: pairCardSuit});
        }
        return shuffle(generatedDeck);
    }
}

function gid(name) {
    return document.getElementById(name);
}

function updateDeclaredGames() {
    let declarations_table = gid("declared_games_table");
    while (declarations_table.rows.length > 1) {
        scoreBoard.deleteRow(1);
    }
    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let responseObject = JSON.parse(this.responseText);
            responseObject.forEach(element => {
                let row = declarations_table.insertRow(-1);
                let col1 = row.insertCell(-1);
                let col2 = row.insertCell(-1);
                let col3 = row.insertCell(-1);
                let col4 = row.insertCell(-1);
                col1.innerText = element.code;
                col2.innerText = element.player;
                col3.innerText = element.type;
                col4.innerText = element.size;
            });
        }
    };
    xhttp.open("GET", "./../cgi-bin/prax3/declaration_output.py");
    xhttp.setRequestHeader("Content-Type", "application/json");
    xhttp.send();
}