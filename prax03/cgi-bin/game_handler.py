#!/usr/bin/python3
# -*- coding: UTF-8 -*-
import cgi, cgitb, json, os, sys
cgitb.enable()
print("Content-Type: text/json")
print()
body = sys.stdin.read()
form = cgi.FieldStorage()
code = form.getfirst("code", "")
if code == "":
	exit(0)
if os.environ["REQUEST_METHOD"] == "GET":
	try:
		f = open((code + ".json"), "r")
		print(f.readline())
		f.close()
	except:
		print("null")
else:
	file = code + ".json"
	f = open(file, "w")
	f.write(body)
	f.close()

