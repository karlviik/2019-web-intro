let ranks = ["2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K", "A"];
let suits = ["H", "D", "C", "S"];

let game_type, game_size;
let score_element = gid("score");
let timer_element = gid("time");
let score, time, timer;
let deck = []; // just json values
let card_board = gid("card_board"); // actual elements
let flippedCards = [];
let checkingCards = [];
let game_in_progress = false;
let results = [];
let scoreBoard = gid("score_table");
let game_counter = 1;
gid("start_button").addEventListener("click", startGame);
let type_selector = gid("selectType");
let size_selector = gid("selectSize");
type_selector.addEventListener("change", optiontoggle);


function optiontoggle() {
    size_selector.options[3].hidden = type_selector.value === "1";
    if (type_selector.value === "1" && size_selector.selectedIndex === 3) {
        size_selector.selectedIndex = 0;
    }
}

// startGame();
function startGame() {
    endGame(true);
    game_in_progress = true;
    score = 100;
    time = 0;
    score_element.innerText = score;
    timer_element.innerText = time;
    timer = setInterval(function() {
        score = score - 1;
        time = time + 1;
        score_element.innerText = score;
        timer_element.innerText = time;
    }, 1000);

    game_type = Number(gid("selectType").value);
    game_size = Number(gid("selectSize").value);
    deck = [];
    flippedCards = [];
    checkingCards = [];
    deck = generateDeck(game_type, game_size);
    drawDeck();

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
        checkCards();
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
    timer = null;
    if (interrupt) {
        return;
    }
    results.push({game_number: game_counter, score: score, time: time});
    alert("Neat!\nYour score was " + score + " points.\nYour time was " + time + " seconds.");
    game_counter++;
    updateScoreBoard();
}

function updateScoreBoard() {
    while (scoreBoard.rows.length > 1) {
        scoreBoard.deleteRow(1);
    }
    results.sort(function(a, b) {return b.score - a.score});
    results.forEach(element => {
        let row = scoreBoard.insertRow(-1);
        let col1 = row.insertCell(-1);
        let col2 = row.insertCell(-1);
        let col3 = row.insertCell(-1);
        col1.innerText = element.game_number;
        col2.innerText = element.score;
        col3.innerText = element.time;
    })
}

function updateScore() {
    score_element.innerText = score;
}

function checkCards() {
    let card1 = gid(checkingCards[0]);
    let card2 = gid(checkingCards[1]);
    let card1_values = getCardData(card1);
    let card2_values = getCardData(card2);
    if (game_type === 1) {
        if (card1_values.rank === card2_values.rank) {
            disableCard(card1);
            disableCard(card2);
            setTimeout(function(){
                hide(card1);
                hide(card2);
            }, 1000);
            score += 10;
            updateScore();
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
                score += 10;
                updateScore();
                return;
            }
        }
    }
    score -= 2;
    updateScore();
    removeCardFromCheckingCards(card1);
    removeCardFromCheckingCards(card2);
    flippedCards.push(card1.id);
    flippedCards.push(card2.id);
    setTimeout(function(){
        toggleFlip(card1);
        toggleFlip(card2);
    }, 1000);

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