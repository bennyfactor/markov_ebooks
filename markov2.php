<?php
$words = array();


function MarkovChain(
	$basetext
	) {
	$basetext = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $basetext);
	$basetext = preg_replace(array('/\"/', '/\:/'), '', $basetext);
	$wordlist = array();
	$wordlist = explode(' ', $basetext);
	$wordlist_size = count($wordlist);
	for($i = 0; $i <= ($wordlist_size - 2); $i++) {
		add_word($wordlist[$i], $wordlist[($i+1)]);
	}
	
}

function add_word($word, $nextword)
{
	global $words;
	if (!array_key_exists($word,$words)){
	$words[$word]= array();
	}
	$words[$word][$nextword] += 1;
}



////

MarkovChain(file_get_contents('/Users/benlamb/scripts/php/markov_ebooks/bennyfactor.txt'));
print "<html><body><pre>";
print_r($words);
print "</pre></html>";
