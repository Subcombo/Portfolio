#!/usr/bin/env python
"""reducer.py""" #docstring

# Written by Kyle Wholton for Cloud Computing project - 2018
# Hadoop MapReduce project - Reducer

# Mapper feeds to shuffler and the shuffler sorts into alphanumerical order
# before sending to reducer  so i will receive an alphanumerically
# sorted input (act, tac, cat) from hadoop Mapreducer.

# receive key/value pairs where key is an  alphanumerically sorted version of
# the word and value is the word, e.g (act,act), (cat, act), (tac, act).

from operator import itemgetter
import sys

current_wordFormatted = None # check current word against this value
anagramList = []

# the words come in alphanumerical order because shuffle does that for you
# takes an input one at time (key,value), e.g (art,rat)

# input comes from Mapper's print (stdout)
for line in sys.stdin:
    # remove leading and trailing whitespace
    line = line.strip()

    # parse the input we got from mapper.py e.g (art, rat)
    # split into a tuple where the first value is used to compare to see if
    # words are anagrams
    wordFormatted, word = line.split('\t') # e.g wf: art, w: rat

    #if the anagramList contains word, then skip loop to next word
    if word in anagramList:
        continue # skip loop

    #if list not empty, compare the current formatted word to the
    # new formatted word
    if anagramList:
        # if they are equal then the word is an anagram of the other words
        # currently in the anagram list.
        if current_wordFormatted == wordFormatted:
            # add the word to the anagram list
            anagramList.append(word)
        # if it's not in the list, check to see that the list has at least two
        # words (a set of anagrams), if true then print an anagram list.
        else:
            if len(anagramList) > 1 :
                print (anagramList)
            # reintialise an empty anagram list.
            anagramList = []

    #if list empty, initialise the list with first word
    if not anagramList:
        anagramList.append(word)

    #initialise new comparison formatted word for next iteration
    current_wordFormatted = wordFormatted
# end of for-loop


# prints the anagrams for the final iteration (loop doesn't print it otherwise)
if len(anagramList) > 1 :
    print list(set(anagramList))

# end of code solution


# first draft of redundant code I failed to make work
# It's being left here to show how much time I wasted!
""" # if anagram list is empty, initialised current_wordFormatted
    if not anagramList: # al: [act, cat]
        current_wordFormatted = wordFormatted #cwf: act wf: art

    # if it is an anagram then add the word to the list
    # currently misses first value...
    if current_wordFormatted == wordFormatted: # cwf: act, wf = art
        anagramList.append(word) # anagramlist = [act, cat]
    else:
        # if not an anagram then print the stored list anagramList
        # and begin a new anagramList
        #if anagramList:
            # write result to STDOUT
        print (anagramList) # anagramList = [act, cat]
            # now reset the anagramList
        anagramList = []
        current_wordFormatted = wordFormatted"""
