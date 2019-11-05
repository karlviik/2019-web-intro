#!/usr/bin/python3
# -*- coding: UTF-8 -*-
import cgi, sys, json, time
declaration = json.loads(sys.stdin.read())
declaration["time"] = str(int(time.time()))
SCORE_FILE = "./declarations.json"
f = open(SCORE_FILE, "a")
f.write(json.dumps(declaration))
f.write("\n")
f.close()
