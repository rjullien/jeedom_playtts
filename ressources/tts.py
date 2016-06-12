#!/usr/bin/python
# -*- coding: utf-8 -*-
from gtts import gTTS
import argparse
import os
import subprocess, time
import hashlib
import requests

# Args
desc = "Creates an mp3 file from spoken text via the Google Text-to-Speech API"
parser = argparse.ArgumentParser(description=desc)
text_group = parser.add_mutually_exclusive_group(required=True)
text_group.add_argument('-t', '--text', help="text to speak")
text_group.add_argument('-f', '--file', help="file to speak")
#args = parser.add_argument("destination", default='cache/out.mp3', help="destination mp3 file", action='store')
args = parser.add_argument('-o', '--option', default='', help="mplayer options")
args = parser.add_argument('-u', '--url', default='', help="tts url")
args = parser.add_argument('-l', '--lang', default='en', help="ISO 639-1 language code to speak in: " + str(gTTS.LANGUAGES))
args = parser.add_argument('--debug', default=False, action="store_true")
args = parser.parse_args()

try:
    if args.text:
        text = args.text
    else:
        with open(args.file, "r") as f:
            text = f.read()

    cachepath=os.path.abspath(os.path.join(os.path.dirname(__file__), 'cache')) 
    hashtxt = hashlib.md5(args.lang+'-'+text+args.url).hexdigest()
    hashfile = hashtxt+'.mp3'
    found = 0
    for file in os.listdir(cachepath):
        print(file)
        if str(hashfile) == str(file) :
            found=1
            print 'fichier trouve'
            break
        
    print hashtxt
    if found == 0 :
        if args.url and len(args.url) > 1:
            if args.url == 'pico':
                os.system("pico2wave -l "+args.lang+" -w "+cachepath+"/voice.wav \""+text+"\"")
                os.system("sox "+cachepath+"/voice.wav -r 48k "+cachepath+"/"+hashfile)
            else :
                #mp3file = urllib2.urlopen(args.url+'&text='+text)
                mp3file = requests.get(args.url.replace('#text#',text), stream=True)
                output = open(cachepath+'/'+hashfile,'wb')
                output.write(mp3file.content)
                output.close()
        else :
            tts = gTTS(text=text, lang=args.lang, debug=args.debug)
            tts.save(cachepath+'/'+hashfile)
    
    cmd = ['mplayer']
    cmd.extend(args.option.split())
    cmd.append(cachepath+'/'+hashfile)
    
    with open(os.devnull, 'wb') as nul:
        subprocess.call(cmd, stdin=nul)
except Exception as e:
    print(str(e))
