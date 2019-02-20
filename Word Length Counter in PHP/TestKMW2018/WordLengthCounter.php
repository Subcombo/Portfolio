<?php
/* Author: Kyle Wholton 2018
 *
 * Program: WordLengthCounter
 * Description: Parses an input line by line and returns word counts, lengths and frequency of those lengths occuring */
$wordsTotal = 0; // total words parsed
$wordLengthArray = array(); // an array that contains wordlengths as Key and occurances of wordLength as value 
$charactersTotal = 0; // total characters parsed
$mostFrequentWordLength = 0; // stores word length with highest quantity
$mostFrequentWordLengthValue = 0; // stores frequency of the above word length
$mostFrequentWordLengthShared = ""; // a string containing all the most frequent word lengths seperated by '&';

while($line = fgets(STDIN)){ // loop through all lines of input
    $line = trim($line); // remove trailing and leading whitespace
    $words = preg_split('/\s+/', $line); // split current line into an array of words (whitespace seperator)
    foreach($words as $word){ // loop through words in the line
        $wordsTotal += 1; // add to total word counter   
        if($word !== '&'){$word = preg_replace("/[^a-z0-9]+/i","",$word);} // regexp replace punctuation with nothing
        $charactersTotal += strlen($word); // add to total character counter
        $wordLengthArray[strlen($word)] += 1; // Create key if doesnt exist, then/else add 1 to that value. Acts as a counter for wordLengths
    }
}

echo "Word Count = $wordsTotal\n";
$wordLengthAverage = $charactersTotal / $wordsTotal; // mean word length average

echo "Average word length = $wordLengthAverage\n";

foreach($wordLengthArray as $wordLength => $wordFrequency){ // Loop through word lengths and store most frequent word length and its frequency
    echo "Number of words of length $wordLength is $wordFrequency\n";
    if($wordLengthArray[$mostFrequentWordLength] <= $wordFrequency){ // if current word length has more words than previous word length max
        $mostFrequentWordLength = $wordLength; // make current wordLength new max.
        $mostFrequentWordLengthValue = $wordFrequency; // store the frequency of that wordLength
    }
}

// loop a second time and store key values of any wordLength that have the same frequency
foreach($wordLengthArray as $wordLength => $wordFrequency){
    if($wordFrequency == $mostFrequentWordLengthValue){
        $mostFrequentWordLengthShared = $mostFrequentWordLengthShared . $wordLength . " & ";
    }
}
$mostFrequentWordLengthShared = substr($mostFrequentWordLengthShared,0,-3);// clean up text that was just created
echo "The most occurences of a particular word length is ".$mostFrequentWordLengthValue.", for words of length of $mostFrequentWordLengthShared\n ";
?>
