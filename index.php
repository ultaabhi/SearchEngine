<?php
error_reporting(0);
include 'simple_html_dom.php';
include 'SpellCorrector1.php';

header('Content-Type: text/html; charset=utf-8');
ini_set('memory_limit', '1000M');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;


$csv = file('mapCNNFile.csv');


foreach($csv as $line) {
    $line = str_getcsv($line);
    $array[$line[0]] = trim($line[1]);
}
$csv1 = file('mapUSATodayFile.csv');


foreach($csv1 as $line) {
    $line = str_getcsv($line);
    $array[$line[0]] = trim($line[1]);
}

if ($query) {
    require_once('Apache/Solr/Service.php');
  

    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');

    /*$corrector = SpellCorrector::correct($query);
    if ($corrector == $query) {
        $corrector = "";
    }*/

    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }


        $splitwords = [];
        $corrected_word = "";
        $splitwords = explode(" ", $query);
        $i = 0;
        foreach ($splitwords as $term){
            $corrected  = "";
            $corrected = SpellCorrector::correct($term);
            if ($i != 0) {
$corrected_word = $corrected_word." ".$corrected;
                
            }
            else{
                $corrected_word = $corrected;
            }
            
            $i++;
        }

 if ($corrected_word == $query) {
$corrected = "";
            
}else{
$corrected = $corrected_word;
}


    $param = [];
    if (array_key_exists("pagerank", $_REQUEST)) {
        $param['sort'] ="pageRankFile desc";
    }
    $results = $solr->search($query, 0, $limit, $param);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <title>PHP Solr Client</title>
     
</head>
<body>

<script>
   $( function() {
    
 
    $( "#q" ).autocomplete({
      source: "http://localhost/solr-php-client/auto.php",
     
      
    } );
  } );

  </script>



    <form accept-charset="utf-8" method="get">
        <label for="q">Search:</label>
        <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
        <input type="submit"/>
        <br>
        <input type="checkbox" name="pagerank" value="your value" <?php if(isset($_REQUEST['pagerank'])) echo "checked='checked'"; ?>  />Sort using Page Rank<br>
        <br>
    </form>
    <?php if ($results): ?>
        <?php 
            $total = (int)$results->response->numFound;
            $start = min(1, $total);
            $end = min($limit, $total);
	$pattern_array = [];
 $query = $results->responseHeader->params->q;
 $query_list = preg_split('/\s/', $query);
 foreach ($query_list as $q){

 	array_push($pattern_array, "/.*?(.*? ".$q."[\.?| .*?]).*?/i");
 	
 }
		

        ?>

         <?php if ($corrected != ""): ?>
            <p>Do you want to search: <a href="http://localhost/solr-php-client/index.php?q=<?php echo $corrected; ?>"><?php echo $corrected; ?></a></p>
        <?php endif; ?>

        <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
        <hr>
        <?php foreach ($results->response->docs as $doc): ?>
            <?php 
                $id = $doc->id;
		$url = $doc->og_url;
		$url = urldecode($url);
		if($url=="")
		{
                $substring = '/';
                $url1 = $id;
		$lastIndex = strripos($url1, $substring);
		$url1 = substr($url1,$lastIndex+1);
		$url1 = urldecode($url1); 
		if (array_key_exists($url1,$array))
                {
                 $finalurl =  $array[$url1];
                }
   
		}
		
                if($url=="")
		{
		$url=$finalurl;
                //echo $url;
		}
		
                    $gettext = file_get_html($url);
//$yel =  " http://www.cnn.com/2015/01/27/opinion/obeidallah-american-sniper/index.html";
//echo "******************************888";

//echo file_get_html($yel)->plaintext;
//echo "<br>";

$data = $gettext->find('body', 0)->plaintext;

		    
                     
                    $sentences = preg_split("/(?<!\w\.\w.)(?<![A-Z][a-z]\.)(?<=\.|\?)\s/", $data);
                   
$findme = "www.usatoday.com";
if(strpos($url,$findme)){
array_shift($sentences);
array_shift($sentences);
}else{
array_shift($sentences);
}

			$best_sentence = "";
  			$test = 0;
		    $best_match = 0;
		    foreach ($sentences as $sentence) {
			//echo $sentence."<br>";
			//echo "<br>";
		    	$num = 0;
		        $getnum = 0;
				foreach ($pattern_array as $p)
				{
				  if (preg_match($p, $sentence,$matches,PREG_OFFSET_CAPTURE))
				  {

					$getnum = $test + 1;
				    $num = $num + 1;
				    
				 
				  }else{
                                      $test = $test + 1;
			             }
				}

				
				if( $best_match  < $num ){
					$best_sentence = $sentence;
                                         $test = $test - 1;
					$best_match = $num;
					
					if ($best_match == count($query_list)) {
						break;
					}
				}
			}
                         //echo $best_sentence_so_far;
			$snippet = mb_strimwidth($best_sentence, 0, 1200, "...");
			$gettext = "";
		   $data = "";
		   $sentences = "";

		

if( !isset($snippet)){
	 	$snippet = "";
	 }
	
                $size = $doc->stream_size;
                $size = intval($size/1024)."kb";
		$date1 = $doc->pubdate;
		$date2 = substr($date1,0,10);

            ?>
             
            
            <a href="<?php echo $url; ?>">Link to Page</a>
            <b>Title :</b> <?php echo $doc->title ? (is_array($doc->title) ? $doc->title[0] : $doc->title) : "None"; ?> | <b>URL : </b> <?php echo $url ? $url : "None"; ?>
            <p>
                Author: <?php echo $doc->author ? $doc->author : "None"; ?> | Size: <?php echo $size ? $size : "None"; ?> | Date: <?php echo $date2 ? $date2 : "None"; ?>
            </p>
	<p> Snippet: <?php echo $snippet?> <br><br> </p>



           <hr>
        <?php endforeach; ?>
    <?php endif; ?>

 


</body>
</html>
