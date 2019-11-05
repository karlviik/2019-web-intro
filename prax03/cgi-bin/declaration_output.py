#!/usr/bin/python3
# -*- coding: UTF-8 -*-
import cgi, sys, json, time
SCORE_FILE = "./declarations.json"
form = cgi.FieldStorage()
code = form.getfirst("code", "")
f = open(SCORE_FILE, "r")
curtime = int(time.time())
lines = f.readlines()
f.close()
declarations = []
searchDeclaration = None
for line in lines:
	if line == "":
		continue
	declaration = json.loads(line)
	if curtime - int(declaration["time"]) < 300:  # if declaration is younger than 5 mins
		declarations.append(declaration)
		if declaration["code"] == code:
			searchDeclaration = declaration

print("Content-Type: text/json")
print()
if code == "":
	print(json.dumps(declarations))
else:
	print(json.dumps(searchDeclaration))

f = open(SCORE_FILE, "w")
for declaration in declarations:
	if searchDeclaration != declaration:
		f.write(json.dumps(declaration) + "\n")
f.close()
