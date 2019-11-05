#!/usr/bin/python3
# -*- coding: UTF-8 -*-
import cgi, cgitb, json, os, sys
body = json.loads(sys.stdin.read())
form = cgi.FieldStorage()
os.remove("test.html")
print("Content-Type: text/html")
print()
print("""\
<html>
<body>
<h2>Hello World!</h2>
</body>
</html>
""")
