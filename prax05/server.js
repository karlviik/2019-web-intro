const app = require('express')();
const http = require('http').createServer(app);
const io = require('socket.io')(http);
const fs = require('fs');

io.on('connection', function(socket){
	socket.on("move", function(move){
		let moveJson = JSON.parse(move);
		let dataJson;
		dataJson = fs.readFileSync("state.json", {"encoding": "utf8"});
		console.log(dataJson);
		dataJson = JSON.parse(dataJson);
		if (dataJson == null) {
			return;
		}
		if (dataJson.winner == null && moveJson.player === dataJson.turn) {
			let y = Math.floor(moveJson.cell / 3);
			let x = moveJson.cell % 3;
			if (dataJson.board[y][x] === 0) {
				dataJson.board[y][x] = moveJson.player;
			}
			else {
				return;
			}
			let board = dataJson.board;
			if (board[0][0] === board[0][1] && board[0][0] === board[0][2] && board[0][0] !== 0 ||
						board[1][0] === board[1][1] && board[1][0] === board[1][2] && board[1][0] !== 0 ||
						board[2][0] === board[2][1] && board[2][0] === board[2][2] && board[2][0] !== 0 ||
						board[0][0] === board[1][0] && board[0][0] === board[2][0] && board[0][0] !== 0 ||
						board[0][1] === board[1][1] && board[0][1] === board[2][1] && board[0][1] !== 0 ||
						board[0][2] === board[1][2] && board[0][2] === board[2][2] && board[0][2] !== 0 ||
						board[0][0] === board[1][1] && board[0][0] === board[2][2] && board[0][0] !== 0 ||
						board[2][0] === board[1][1] && board[2][0] === board[0][2] && board[2][0] !== 0) {
				dataJson.winner = dataJson.turn;
			}
			else {
				dataJson.turn = dataJson.turn === "X" ? "O" : "X";
			}
			io.emit("state", JSON.stringify(dataJson));
			fs.writeFile('state.json', JSON.stringify(dataJson), function (err) {
				if (err) {
					console.log(err);
				}
			});
		}
	});
	socket.on("start", function() {
		// reset the field, emit it to everyone but the sender
		let dataJson = {"turn":"X","winner":null,"board":[[0, 0, 0],[0, 0, 0],[0, 0, 0]]};
		fs.writeFile('state.json', JSON.stringify(dataJson), function (err) {
			if (err) {
				console.log(err);
			}
		});
		io.emit("state", JSON.stringify(dataJson));
		socket.broadcast.emit("you", "O");
	});
});

http.listen(7569, function(){
	console.log('listening on port 7569');
});