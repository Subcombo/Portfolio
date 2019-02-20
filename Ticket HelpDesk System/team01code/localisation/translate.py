#!/usr/bin/env python

import urllib2
import urllib
import json
import sys
from xml.dom import minidom
import os

def clear():
    os.system(['clear','cls'][os.name == 'nt'])

try:
    if sys.argv.index("--help"):
        print "gettranslation.py:\nUsage:\n\t--help\t\tDisplay this page\n\t--lang {A} {B}\tTranslate from {A} to {B}\n\t--quietly\tDo not display any verbose messages\n"
        exit()
except:
	ishelp = 0

try:
	lang_pos = sys.argv.index("--lang")
	from_lang = sys.argv[lang_pos+1]
	to_lang = sys.argv[lang_pos+2]
except:
	from_lang = "en"
	to_lang = "pl"

try:
	text_pos = sys.argv.index("--text")
	text = sys.argv[text_pos+1]
except:
	text = sys.stdin.readline()

try:
	index = sys.argv.index("--quietly")
except:
    index = 0
    clear()
    print "Translation from \"%s\" to \"%s\"." %(from_lang, to_lang)
    print "Text to translate: "
	

if index == 0:
	print "\n> Fetching ACCESS_TOKEN..."

url = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13"
form_values = {
	"client_id" : "szymonlopaciuk",
	"client_secret" : "OsKsT59KK9MbTvVuaFa/skD668RS78dKZ3oembCrTHQ",
	"scope" : "http://api.microsofttranslator.com",
	"grant_type" : "client_credentials"
}

data = urllib.urlencode(form_values)
request = urllib2.Request(url, data)
response = urllib2.urlopen(request)

output = response.read()
parsed = json.loads(output)

access_token = parsed['access_token']

if index == 0:
	print "> Fetching translation...\n"

translator_url = "http://api.microsofttranslator.com/V2/HTTP.svc/Translate?appid=Bearer%20" + urllib.quote(access_token, '') + "&to=" + to_lang + "&from=" + from_lang + "&text=" + urllib.quote(text, '')

translator_request = urllib2.Request(translator_url)
translator_response = urllib2.urlopen(translator_request)
translator_output = translator_response.read()

xml_in = minidom.parseString(translator_output)
xml_out = xml_in.childNodes[0]

print "\"%s\" : \"%s\"" % (text, xml_out.childNodes[0].toxml())




