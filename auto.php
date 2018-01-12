<?php

$stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone",   "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "inteValuest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");



header("Access-Control-Allow-Origin: *");
// get the q parameter from URL
if (isset($_GET["term"])){
    $q = $_GET["term"];
    if ($q !== "") {
    $json_file = file_get_contents("http://localhost:8983/solr/myexample/suggest?indent=on&q=$q&wt=json");
     
    $jfoLook = json_decode($json_file,TRUE);
//var_dump($jfoLook);
     $arr2 = array();
        $m=0;
        foreach($jfoLook['suggest']['suggest'][$q]['suggestions'] as $val)
        {       
          //  $arr1 = array();
           // $arr1['value'] = $val->term;
           // $arr1['label'] = $val->term;
            if($val['term']==$q){
	     continue;
	    }  

	if(((in_array($val['term'],$stopwords)))){
	 continue;
	 }   

	if (strpos($val['term'], '.') !== FALSE)
{
 continue;
}
	if (strpos($val['term'], ':') !== FALSE)
{
 continue;
}
	if (strpos($val['term'], '_') !== FALSE)
{
 continue;
}
                    
            $arr2[$m]=$val['term'];
            $m++;
                       
        }   
      
      $jfo = json_encode($arr2,TRUE);
        //$jfo = json_encode($json_file,TRUE);
    echo $jfo;


    }   
    
} 




?>
