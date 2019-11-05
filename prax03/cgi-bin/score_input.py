#!/usr/bin/python3
# -*- coding: UTF-8 -*-
import cgi, sys
score = sys.stdin.read()
SCORE_FILE = "./scores.json"
f = open(SCORE_FILE, "a")
f.write(score)
f.write("\n")
f.close()
