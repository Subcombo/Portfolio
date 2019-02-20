#!/usr/bin/env python
"""mapper.py""" #docstring

# Written by Kyle Wholton for Cloud Computing project - 2018
# Hadoop MapReduce project - Mapper

# Mapper does something to every list, i.e applies a function to every element
# in list.
# this produces a new list that the reducer can do something with.
# sort words first into alphabetical order? (cat, act), (car, acr)

# in mapper, arrange all words to be in alphabetical order,
# I.E cat, act and rat end up being act, act and atr.
# this makes it easy to tell if words are anagrams.
# keep the words with their original unarranged version so cat, act and rat
# would have output of (cat, act), (act, act) and (rat, tar).

# (key, value) -> list (key, value) Example:
# (rat,tar,cat) -> list(art,rat),(art,tar),(act,cat)
# (key, list(value)) -> list(key, value) !change to list(value) Example:
# (art, list(rat,tar)) -> (rat, tar)

import sys #for stdin
import string #for translate and punctuation functions

# input comes from STDIN (standard input)
# input text file of 1000's of books, line by line.
# for every line from the input
for line in sys.stdin:

    # remove leading and trailing whitespace of line
    line = line.strip()

    # split the line into words
    words = line.split()


    # loop through words in the temporary line array
    for word in words:
        # write the results to STDOUT (standard output) here;
        # what we output here will be the input for the Reduce step, i.e. the input for reducer.py
        # prints every word and it's alphanumerically sorted string
        # so that we can compare them in the reducer to see if they are anagrams
        # I.E they share the same value, then you add the keys to a list.

        # remove punctuation from the word
        # This allows us to compare words that start or end with punctuation.
        word = word.translate(None, string.punctuation)

        # make word lowercase, stops any case-sensitivity issues
        word = word.lower()

        # if a word is not empty (i.e due to it being all punctuation) then
        if word != "":
            # add the alphabetically formatted words to a new variable.
            # This allows us to compare words to see if they are anagrams
            # e.g anagrams will have the same letters and length when formatted alphabetically
            wordFormatted = "".join(sorted(word))

        # print out the word + whitespace + formatted word as STDOUT
        if  word.isalpha():
            print ('%s\t%s' % (wordFormatted, word)) #/t creates a space literal %s is where the string is replaced with the argument
