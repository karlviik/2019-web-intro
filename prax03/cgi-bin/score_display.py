#!/usr/bin/python3
# -*- coding: UTF-8 -*-
import cgi, cgitb, json

cgitb.enable()
print("Content-Type: text/html")
print()

form = cgi.FieldStorage()
SCORE_FILE = "./scores.json"
f = open(SCORE_FILE, "r")
scores = []
scoreStrings = f.readlines()
for scoreString in scoreStrings:
	scores.append(json.loads(scoreString))
name = form.getfirst("name", None)
if name is not None and name != "":
	name = name.lower()
	scores = filter(lambda x: x["player"].lower() == name or x["opponent"] == name, scores)
players = form.getfirst("players", 0)
if int(players) != 0:
	scores = filter(lambda x: int(x["player_count"]) == int(players), scores)
gametype = form.getfirst("type", None)
if gametype is not None and gametype != "0":
	scores = filter(lambda x: x["type"] == gametype, scores)
gamesize = form.getfirst("size", 0)
if int(gamesize) != 0:
	scores = filter(lambda x: int(x["size"]) == int(gamesize), scores)
tabletype = None
scores = list(scores)
if len(scores) == 0:
	tabletype = 0
elif int(players) == 1 or len(list(filter(lambda x: int(x["player_count"]) == 2, scores))) == 0:
	tabletype = 1
else:
	tabletype = 2
sortkey = form.getfirst("sortby", None)
reverse_or_not = int(form.getfirst("reversed", "0"))
if sortkey is not None:
	scores.sort(key=lambda x: x[sortkey], reverse=bool(reverse_or_not))

print("""\
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scores</title>
    <style>
        body {
            text-align: center;
            background-color: #3a4146;
            color: white;
            font-family: "Calibri L", serif;
            font-size: 15px
        }

        select {
            font-size: 15px;
            height: 20%;
            margin: 4px;
            color: white;
            border-radius: 4px;
            border: none;
            text-align: center;
            text-align-last: center;
            background-color: #656e73;
            padding: 5px 10px;
            cursor: pointer;
        }
        input[type=text] {
            font-size: 15px;
            height: 20%;
            margin: 4px;
            color: white;
            border-radius: 4px;
            border: none;
            text-align: center;
            text-align-last: center;
            background-color: #656e73;
            padding: 5px 10px;
        }

        input[type=submit]:hover {
            background-color: #58659f;
        }

        input[type=submit] {
            margin: 6px;
            background-color: #465386;
            border: none;
            color: white;
            border-radius: 6px;
            padding: 6px 50px;
            font-size: 20px;
            cursor: pointer;
        }

        table {
            border-collapse:collapse;
            border-spacing:0;
            width:70%;
            margin: 0 auto;
            display:table;
            border:1px solid #ccc;
        }

        th, td {
            padding: 8px;
        }

        tr:nth-child(odd){
            background-color: #6a6a6a
        }

        th {
            background-color: #be3600;
            color: white;
            cursor: pointer;
        }
        th:hover {
            background-color: #e03800;
        }
    </style>
</head>
<body>
    <form action="">
        <div class="searchComponent">
            <label for="formname">Name: </label>
            <input id="formname" type="text" name="name">
            <label for="formplayercount">Players: </label>
            <select id="formplayercount" name="players">
                <option value="0">Any</option>
                <option value="1">1</option>
                <option value="2">2</option>
            </select>
            <label for="formgametype">Game type: </label>
            <select id="formgametype" name="type">
                <option value="0">Any</option>
                <option value="rank_match">Rank match</option>
                <option value="rank_and_color_match">Rank and color match</option>
            </select>
            <label for="formgamesize">Game size: </label>
            <select id="formgamesize" name="size">
                <option value="0">Any</option>
                <option value="6">6</option>
                <option value="16">16</option>
                <option value="26">26</option>
                <option value="52">52</option>
            </select>
            <input type="submit" value="Search">
        </div>
    </form>\
""")
if tabletype == 0:
	print("""
	<p style="font-size: 300%">No results match the filters.</p>
    <p style="font-size: 1000%">:(</p>
	""")
elif tabletype == 1:
	print("""\
	<table id="scoreTable">
        <tr>
            <th onclick="requestSort('game_start')">Game start time</th>
            <th onclick="requestSort('player_count')">Player count</th>
            <th onclick="requestSort('time')">Elapsed time</th>
            <th onclick="requestSort('player')">Player name</th>
            <th onclick="requestSort('player_score')">Player score</th>
            <th onclick="requestSort('size')">Game size</th>
            <th onclick="requestSort('type')">Game type</th>
        </tr>""")
	for score in scores:
		print("""\
	<tr>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
        </tr>
        """.format(score["game_start"], score["player_count"], score["time"], score["player"], score["player_score"], score["size"], score["type"]))
else:
	print("""
	    <table id="scoreTable">
        <tr>
            <th onclick="requestSort('game_start')">Game start time</th>
            <th onclick="requestSort('player_count')">Player count</th>
            <th onclick="requestSort('time')">Elapsed time</th>
            <th onclick="requestSort('player')">Player name</th>
            <th onclick="requestSort('player_score')">Player score</th>
            <th onclick="requestSort('opponent')">Opponent name</th>
            <th onclick="requestSort('opponent_score')">Opponent score</th>
            <th onclick="requestSort('size')">Game size</th>
            <th onclick="requestSort('type')">Game type</th>
        </tr>
	""")
	for score in scores:
		print("""
		<tr>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
            <td>{}</td>
        </tr>
		""".format(score["game_start"], score["player_count"], score["time"], score["player"], score["player_score"], score["opponent"], score["opponent_score"], score["size"], score["type"]))
print("""\
	</table>
    <script>
        let type_selector = gid("formgametype");
        let size_selector = gid("formgamesize");
        type_selector.addEventListener("change", optiontoggle);

        function optiontoggle() {
            size_selector.options[4].hidden = type_selector.value === "rank_match";
            if (type_selector.value === "rank_match" && size_selector.selectedIndex === 4) {
                size_selector.selectedIndex = 0;
            }
        }

        function requestSort(sortBy) {
            let search = "sortby=" + sortBy;
            let param = getUrlParam("sortby", null);
            if (param != null && param === sortBy) {
                if (getUrlParam("reversed", "0") === "1") {
                    window.location.href = window.location.href.replace("&reversed=1", "")
                } else {
                    window.location.search += "&reversed=1";
                }
            } else {
                let current = getUrlParam("sortby", null);
                if (current != null) {
                    window.location.href = window.location.href.replace("&sortby=" + current, "&" + search).replace("&reversed=1", "");
                } else {
                    window.location.search += ("&" + search);
                }
            }
        }
        window.onload = function() {
            let name = getUrlParam("name", null);
            let players = getUrlParam("players", null);
            let type = getUrlParam("type", null);
            let size = getUrlParam("size", null);
            if (name != null) {
                gid("formname").value = name;
            }
            if (players != null) {
                gid("formplayercount").value = players;
            }
            if (type != null) {
                gid("formgametype").value = type;
            }
            if (size != null) {
                gid("formgamesize").value = size;
            }
        };
        function gid(name) {
            return document.getElementById(name);
        }
        // gotten from https://html-online.com/articles/get-url-parameters-javascript/
        function getUrlVars() {
            let vars = {};
            let parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                vars[key] = value;
            });
            return vars;
        }
        function getUrlParam(parameter, defaultvalue){
            let urlparameter = defaultvalue;
            if(window.location.href.indexOf(parameter) > -1){
                urlparameter = getUrlVars()[parameter];
            }
            return urlparameter;
        }
    </script>
</body>
</html>
""")
