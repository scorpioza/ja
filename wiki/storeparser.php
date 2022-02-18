<?php
/***
! User settings
Edit these lines according to your need
***/
//{{{

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Europe/Moscow');

$AUTHENTICATE_USER = true;	// true | false
$USERS = array(
	'33greencats'=>'xantheose74', 
	'UserName2'=>'Password2', 
	'UserName3'=>'Password3'); // set usernames and strong passwords
$DEBUG = false;				// true | false
$CLEAN_BACKUP = true; 		// during backuping a file, remove overmuch backups
$FOLD_JS = true; 			// if javascript files have been expanded during download the fold them
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//}}}


/***************************************************************/
/***************************** PARSER ***************************/
/***************************************************************/

$PSY = new stdClass();
$PSY->livebase = "http://www.psytronica.ru/";
$PSY->base = "/home/psytronica/web/psytronica.ru/public_html/";
$PSY->site_dirname = "psysite";
$PSY->wiki_dirname = "psywiki";

// Без htaccess-редиректа поддиректории
//$PSY->livesite = $PSY->livebase.$PSY->site_dirname."/";

// С htaccess-редиректом поддиректории
$PSY->livesite = $PSY->livebase;

$PSY->root  = $PSY->base.$PSY->site_dirname."/";
$PSY->wikidir = $PSY->base.$PSY->wiki_dirname."/";
$PSY->wikifile = $PSY->wikidir."xedni.php";

$PSY->countBackups = 8;
$PSY->useCleanBCP = true;

$PSY->filename = array(
    "bad_endings" => array(".php"),
    "bad_beginnings" => array("."),
    "bad_fragments" => array("../"),
    "replaces" => array("\\" => "/"),
    "allowed_regexp" => "/[^A-Za-z\p{Cyrillic}0-9\s\-\_\/\+\.]/u"
);

$PSY->modules = array();
$PSY->tags = array();
$PSY->blogContent = array();
$PSY->pageContent = array();

$PSY->classes = array();

$PSY->tagColors = array();

$PSY->metas = array("title" => array(), "keywords" => array(), "description" => array());

$PSY->cfg = array();

/* Значения по умолчанию. Переопределяются в asset=cfg */
$PSY->cfg['perpage'] = 5;

$PSY->cfg['lastposts_length'] = 200;
$PSY->cfg['lastpages_length'] = 200;
$PSY->cfg['lastposts_count'] = 3;
$PSY->cfg['lastpages_count'] = 3;
$PSY->cfg['tag_min_size'] = 14;
$PSY->cfg['tag_max_size'] = 32;

$PSY->cfg['tag_min_color'] = "255,80,80";
$PSY->cfg['tag_mid_color'] = "255,220,80";
$PSY->cfg['tag_max_color'] = "80,255,80" ;

$PSY->cfg['description_length'] = 200;
$PSY->cfg['more_separator'] = "<!--more-->";
$PSY->cfg['more_text'] = "Дальше >>";

$PSY->cfg['archive_open'] = "&#9660;&#160;";
$PSY->cfg['archive_closed'] = "&#9658;&#160;";  

$PSY->cfg['title_posts'] = "Материалы";
$PSY->cfg['title_pages'] = "Страницы";
$PSY->cfg['title_tags'] = "Теги";
$PSY->cfg['title_sections'] = "Разделы";

/***************************************************************/
/*************************** / PARSER ****************************/
/***************************************************************/



/***
!Code
No change needed under
***/
//{{{

/***
 * store.php - upload a file in this directory
 * version :1.6.1 - 2007/08/01 - BidiX@BidiX.info
 * 
 * see : 
 *	http://tiddlywiki.bidi.info/#UploadPlugin for usage
 *	http://www.php.net/manual/en/features.file-upload.php 
 *		for details on uploading files
 * usage : 
 *	POST  
 *		UploadPlugin[backupDir=<backupdir>;user=<user>;password=<password>;uploadir=<uploaddir>;[debug=1];;]
 *		userfile <file>
 *	GET
 *
 * each external javascript file included by download.php is change by a reference (src=...)
 *
 * Revision history
 * V1.6.1 - 2007/08/01
 * Enhancement: Add javascript folding
 * V1.6.0 - 2007/05/17
 * Enhancement: Add backup management
 * V1.5.2 - 2007/02/13
 * Enhancement: Add optional debug option in client parameters
 * V1.5.1 - 2007/02/01
 * Enhancement: Check value of file_uploads in php.ini. Thanks to Didier Corbière
 * V1.5.0 - 2007/01/15
 * Correct: a bug in moving uploadFile in uploadDir thanks to DaniGutiérrez for reporting
 * Refactoring
 * V 1.4.3 - 2006/10/17 
 * Test if $filename.lock exists for GroupAuthoring compatibility
 * return mtime, destfile and backupfile after the message line
 * V 1.4.2 - 2006/10/12
 *  add error_reporting(E_PARSE);
 * v 1.4.1 - 2006/03/15
 *	add chmo 0664 on the uploadedFile
 * v 1.4 - 2006/02/23
 * 	add uploaddir option :  a path for the uploaded file relative to the current directory
 *	backupdir is a relative path
 *	make recusively directories if necessary for backupDir and uploadDir
 * v 1.3 - 2006/02/17
 *	presence and value of user are checked with $USERS Array (thanks to PauloSoares)
 * v 1.2 - 2006/02/12 
  *	POST  
 *		UploadPlugin[backupDir=<backupdir>;user=<user>;password=<password>;]
 *		userfile <file>
*	if $AUTHENTICATE_USER
 *		presence and value of user and password are checked with 
 *		$USER and $PASSWORD
 * v 1.1 - 2005/12/23 
 *	POST  UploadPlugin[backupDir=<backupdir>]  userfile <file>
 * v 1.0 - 2005/12/12 
 *	POST userfile <file>
 *
 * Copyright (c) BidiX@BidiX.info 2005-2007
 ***/
//}}}

//{{{

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	/*
	 * GET Request
	 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
		<title>BidiX.info - TiddlyWiki UploadPlugin - Store script</title>
	</head>
	<body>
		<p>
		<p>store.php V 1.6.1
		<p>BidiX@BidiX.info
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p align="center">This page is designed to upload a <a href="http://www.tiddlywiki.com/">TiddlyWiki<a>.</p>
		<p align="center">for details see : <a href="http://TiddlyWiki.bidix.info/#HowToUpload">TiddlyWiki.bidix.info/#HowToUpload<a>.</p>	
	</body>
</html>
<?php
exit;
}

/*
 * POST Request
 */
	 
// Recursive mkdir
function mkdirs($dir) {
	if( is_null($dir) || $dir === "" ){
		return false;
	}
	if( is_dir($dir) || $dir === "/" ){
		return true;
	}
	if( mkdirs(dirname($dir)) ){
		return mkdir($dir);
	}
	return false;
}

function toExit() {
	global $DEBUG, $filename, $backupFilename, $options;
	if ($DEBUG) {
		echo ("\nHere is some debugging info : \n");
		echo("\$filename : $filename \n");
		echo("\$backupFilename : $backupFilename \n");
		print ("\$_FILES : \n");
		print_r($_FILES);
		print ("\$options : \n");
		print_r($options);
}
exit;
}

function ParseTWFileDate($s) {
	// parse date element
	preg_match ( '/^(\d\d\d\d)(\d\d)(\d\d)\.(\d\d)(\d\d)(\d\d)/', $s , $m );
	// make a date object
	$d = mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
	// get the week number
	$w = date("W",$d);

	return array(
		'year' => $m[1], 
		'mon' => $m[2], 
		'mday' => $m[3], 
		'hours' => $m[4], 
		'minutes' => $m[5], 
		'seconds' => $m[6], 
		'week' => $w);
}

//SherZa
function cleanBCP($dirname, $prefix){

    global $PSY;
    $files = glob($dirname.'/'.$prefix.'.*.php');

    if(count($files) <= (int)$PSY->countBackups) return array();

    usort($files, function($a, $b) {
        return filemtime($a) < filemtime($b);
    });
    return array_slice($files, $PSY->countBackups);
}

function cleanFiles($dirname, $prefix) {
	$now = getdate();
	$now['week'] = date("W");

	$hours = Array();
	$mday = Array();
	$year = Array();
	
	$toDelete = Array();

	// need files recent first
	$files = Array();
	($dir = opendir($dirname)) || die ("can't open dir '$dirname'");
	while (false !== ($file = readdir($dir))) {
		if (preg_match("/^$prefix/", $file))
        array_push($files, $file);
    }
	$files = array_reverse($files);
	
	// decides for each file
	foreach ($files as $file) {
		$fileTime = ParseTWFileDate(substr($file,strpos($file, '.')+1,strrpos($file,'.') - strpos($file, '.') -1));
		if (($now['year'] == $fileTime['year']) &&
			($now['mon'] == $fileTime['mon']) &&
			($now['mday'] == $fileTime['mday']) &&
			($now['hours'] == $fileTime['hours']))
				continue;
		elseif (($now['year'] == $fileTime['year']) &&
			($now['mon'] == $fileTime['mon']) &&
			($now['mday'] == $fileTime['mday'])) {
				if (isset($hours[$fileTime['hours']]))
					array_push($toDelete, $file);
				else 
					$hours[$fileTime['hours']] = true;
			}
		elseif 	(($now['year'] == $fileTime['year']) &&
			($now['mon'] == $fileTime['mon'])) {
				if (isset($mday[$fileTime['mday']]))
					array_push($toDelete, $file);
				else
					$mday[$fileTime['mday']] = true;
			}
		else {
			if (isset($year[$fileTime['year']][$fileTime['mon']]))
				array_push($toDelete, $file);
			else
				$year[$fileTime['year']][$fileTime['mon']] = true;
		}
	}
	return $toDelete;
}

function replaceJSContentIn($content) {
	if (preg_match ("/(.*?)<!--DOWNLOAD-INSERT-FILE:\"(.*?)\"--><script\s+type=\"text\/javascript\">(.*)/ms", $content,$matches)) {
		$front = $matches[1];
		$js = $matches[2];
		$tail = $matches[3];
		if (preg_match ("/<\/script>(.*)/ms", $tail,$matches2)) {		
			$tail = $matches2[1];
		}
		$jsContent = "<script type=\"text/javascript\" src=\"$js\"></script>";
		$tail = replaceJSContentIn($tail);
		return($front.$jsContent.$tail);
	}
	else
		return $content;
}

// Check if file_uploads is active in php config
if (ini_get('file_uploads') != '1') {
   echo "Error : File upload is not active in php.ini\n";
   toExit();
}

// var definitions
$uploadDir = './';
$uploadDirError = false;
$backupError = false;
$optionStr = $_POST['UploadPlugin'];
$optionArr=explode(';',$optionStr);
$options = array();
$backupFilename = '';
$filename = $_FILES['userfile']['name'];
$destfile = $filename;

// get options
foreach($optionArr as $o) {
	if(!trim($o)) continue;	
	list($key, $value) = explode('=', $o);
	$options[$key] = $value;
}

// debug activated by client
if (isset($options['debug']) && $options['debug'] == 1) {
	$DEBUG = true;
}

// authenticate User
if (($AUTHENTICATE_USER)
	&& ((!$options['user']) || (!$options['password']) || ($USERS[$options['user']] != $options['password']))) {
	echo "Error : UserName or Password do not match \n";
	echo "UserName : [".$options['user']. "] Password : [". $options['password'] . "]\n";
	toExit();
}



// make uploadDir
if ($options['uploaddir']) {
	$uploadDir = $options['uploaddir'];
	// path control for uploadDir   
    if (!(strpos($uploadDir, "../") === false)) {
        echo "Error: directory to upload specifies a parent folder";
        toExit();
	}
	if (! is_dir($uploadDir)) {
		mkdirs($uploadDir);
	}
	if (! is_dir($uploadDir)) {
		echo "UploadDirError : $uploadDirError - File NOT uploaded !\n";
		toExit();
	}
	if ($uploadDir{strlen($uploadDir)-1} != '/') {
		$uploadDir = $uploadDir . '/';
	}
}
$destfile = $uploadDir . $filename;

// backup existing file
if (file_exists($destfile) && ($options['backupDir'])) {
	if (! is_dir($options['backupDir'])) {
		mkdirs($options['backupDir']);
		if (! is_dir($options['backupDir'])) {
			$backupError = "backup mkdir error";
		}
	}
	$backupFilename = $options['backupDir'].'/'.substr($filename, 0, strrpos($filename, '.'))
				.date('.Ymd.His').substr($filename,strrpos($filename,'.'));
	rename($destfile, $backupFilename) or ($backupError = "rename error");
	// remove overmuch backup
	if ($CLEAN_BACKUP) {
		$toDelete = ($PSY->useCleanBCP)?
            cleanBCP($options['backupDir'], substr($filename, 0, strrpos($filename, '.'))) : 
            cleanFiles($options['backupDir'], substr($filename, 0, strrpos($filename, '.')));
		foreach ($toDelete as $file) {
			$f = $options['backupDir'].'/'.$file;
			if($DEBUG) {
				echo "delete : ".$options['backupDir'].'/'.$file."\n";
			}
			unlink($options['backupDir'].'/'.$file);
		}
	}
}

// move uploaded file to uploadDir
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $destfile)) {
	if ($FOLD_JS) {
		// rewrite the file to replace JS content
		$fileContent = file_get_contents ($destfile);
		$fileContent = replaceJSContentIn($fileContent);
		if (!$handle = fopen($destfile, 'w')) {
	         echo "Cannot open file ($destfile)";
	         exit;
	    }
	    if (fwrite($handle, $fileContent) === FALSE) {
	        echo "Cannot write to file ($destfile)";
	        exit;
	    }
	    fclose($handle);
	}
    
	chmod($destfile, 0644);
	if($DEBUG) {
		echo "Debug mode \n\n";
	}
	if (!$backupError) {		
		echo "0 - File successfully loaded in " .$destfile. "\n";
		main();

	} else {
		echo "BackupError : $backupError - File successfully loaded in " .$destfile. "\n";
	}
	echo("destfile:$destfile \n");
	if (($backupFilename) && (!$backupError)) {
		echo "backupfile:$backupFilename\n";
	}
	$mtime = filemtime($destfile);
	echo("mtime:$mtime");
} 
else {
	echo "Error : " . $_FILES['error']." - File NOT uploaded !\n";

}
toExit();
//}}}


/***************************************************************/
/***************************** PARSER ***************************/
/***************************************************************/



function psyError($txt){
    echo $txt;
    die();
}

function parseWiki(){
    global $PSY;

    if(!file_exists($PSY->wikifile)) psyError($txt);

    $dom = new DOMDocument;
    @$dom->loadHTMLFile($PSY->wikifile);

    $divs = $dom->getElementsByTagName('div');

    $content = array();

    foreach ($divs as $div) {
        $title = trim($div->getAttribute('title'));
        if($title && substr($title,0,1)!=="$" && substr($title,0,1)!=="#"){

            $created = $div->getAttribute('created');
            $date = $div->getAttribute('date');

            if($date) $created = $date.substr($created, strlen($date));

            $div->setAttribute('created', $created);

            $content[$created] = $div;
        }
    }

    krsort($content);
    return $content;
}

function makeLink($link, $ext="html"){
    global $PSY;

    if($link=="index" && $ext=="html") return $PSY->livesite;

    return ($ext != "*")? $PSY->livesite.$link.".".$ext : $PSY->livesite.$link;
}

function getPostUrl($div){

    $created = $div->getAttribute('created');
    $year = substr($created,0,4);
    $mon = substr($created,4,2);
    $url = ($div->getAttribute('url'))? $div->getAttribute('url') : substr($created,5);
    return $year."/".$mon."/".$url;
}
function getTagUrl($tag){
    return "search/label/".str_replace(" ", "_", $tag);
}
function getSectionUrl($section){
    return "section/".str_replace(" ", "_", $section);
}
function getAjaxUrl($ajax){
    return "search/ajax/".$ajax;
}
function getImgUrl($img){
    return "images/".$img;
}

function getPage($count){
    global $PSY;
    return floor($count/($PSY->cfg['perpage'])) +1;
}

function getPostTags($div){
    global $PSY;

    $tags = array();

    if(!trim($div->getAttribute('tags'))) return $tags;

    $tagsArray = explode(" ", $div->getAttribute('tags'));
    foreach($tagsArray as $tag){
        $tag = trim($tag);
        $tag = str_replace("+", " ", $tag);

        if(substr($tag, 0,1)==="$" || substr($tag, 0,1)==="#") continue;

        if(!$div->getAttribute('template')){

            if(!isset($PSY->tags[$tag])) $PSY->tags[$tag]=0;
            $PSY->tags[$tag]++;
        }

        $tags[] = $tag;
    }

    return $tags;
}

function prepareTagsInContent($HTML, $tags_name, $tags_count_name, $attrs, $div){

    global $PSY;
    static $odd_even = array("tags"=>array(), "sections"=>array());
    static $odd_even_counter = array("tags"=>array(), "sections"=>array());

    if(!isset($attrs["tags"] )) return $HTML;

    foreach($attrs["tags"] as $tag){

        if(!isset($HTML[$tags_count_name][$tag])) $HTML[$tags_count_name][$tag] = 0;

        $tag_page = getPage($HTML[$tags_count_name][$tag]);

        if(!isset($HTML[$tags_name][$tag])){
            $HTML[$tags_name][$tag] = array();
        }

        if(!isset($HTML[$tags_name][$tag][$tag_page])){
            $HTML[$tags_name][$tag][$tag_page] = array();
        }

        if(!isset($odd_even[$tags_name][$tag])) $odd_even[$tags_name][$tag] = 1;
        if(!isset($odd_even_counter[$tags_name][$tag])) $odd_even_counter[$tags_name][$tag] = 0;

        $odd_even[$tags_name][$tag] = 1 - $odd_even[$tags_name][$tag];
        $odd_even_counter[$tags_name][$tag]++;

        if($odd_even_counter[$tags_name][$tag] == $PSY->cfg['perpage']-1){
            $odd_even_counter[$tags_name][$tag]=0;
            $odd_even[$tags_name][$tag] = 0;
        }

        $HTML[$tags_name][$tag][$tag_page][] =getPageData($tags_name, $div, $attrs, $odd_even[$tags_name][$tag]);
        

        $HTML[$tags_count_name][$tag]++;

        if(isset($PSY->cfg["json"]->$tags_name->$tag->classes_inner)){

            $classes = $PSY->cfg["json"]->$tags_name->$tag->classes_inner;
            $classes = explode(",",$classes);
            $classes=array_map('trim',$classes);

            addClassesUrl($classes, $attrs["url"]);
        }
        
    }
    return $HTML;

}

function addClassesUrl($classes, $url){
    global $PSY;
    if(!$classes || empty($classes)) return;

    foreach($classes as $class){
        if(!isset($PSY->classes[$class])) $PSY->classes[$class] = array();

        $PSY->classes[$class][] = $url;
    }

}

function prepareClassesUrlsInAsset($a){

    global $PSY;

    if(!isset($a["classes"]) || !$a["classes"] || empty($a["classes"])) return $a;

    if(!$a["urls"]) $a["urls"] =array();

    foreach($a["classes"] as $class){
        if(isset($PSY->classes[$class])){
            foreach($PSY->classes[$class] as $url){
                if(!in_array($url, $a["urls"])){
                    $a["urls"][] = $url;
                }
            }
        }
    }

    return $a;
}
function prepareClassesUrls($assets){

    //$HTML["assets"][$asset][$ordering."_".$created] 
   // $HTML["assets"]["tmpl"][$template][$created] 

    global $PSY;   

    if(!$PSY->classes || empty($PSY->classes)) return $assets;
    foreach($assets as $asset_name=>$asset){

        if (!in_array($asset_name, array("tmpl", "js", "css"))) continue;

        if($asset_name == "tmpl"){
            foreach($asset as $template=>$obj){
                foreach($obj as $created=>$a){
                    $assets[$asset_name][$template][$created] = prepareClassesUrlsInAsset($a);
                }
            }
        }else{
             foreach($asset as $ordering=>$a){
                 $assets[$asset_name][$ordering] = prepareClassesUrlsInAsset($a);
             }
        }
    }
    return $assets;
}

function prepareContent($content){

    global $PSY;

    $HTML = array(
        "blog" => array(),
        "posts" => array(),
        "tags" => array(),
        "pages" => array(),
        "sections" => array(),

        "ajax" => array(),
        "other" => array(),
        "search" => "",
        
        "tags_count" => array(),
        "pcount" => 0,
        "scount" => array() ,

        "assets" => array(
            "tmpl" => array(),
            "js" => array(),
            "css" => array()
        ) 
    );


    $pcount = 0;
    $search = array();

    $classes_inner = array("tags" => array(), "sections" => array());
    $odd_even = 0;

    foreach ($content as $created=>$div) {

        static $img_urls = array();

        $attrs = array();

        if($div->getAttribute('draft')) continue;        


        $classes=NULL;
        if(trim($div->getAttribute('classes'))){
            $classes = explode(",",$div->getAttribute('classes'));
            $classes=array_map('trim',$classes);
        }            

        if($div->getAttribute('asset')){

            $asset = $div->getAttribute('asset');

            if($asset == "img"){
                prepareNodeValue($div->nodeValue, $div->getAttribute('url'));
                 continue;
            }else if($asset == "cfg"){
                foreach ( $div->attributes as $attrName => $attrNode) {
                    if($attrName=='asset' || !trim($attrNode->value)) continue;
                    $PSY->cfg[$attrName] = trim($attrNode->value);
                }
                $PSY->cfg["json"] = json_decode($div->nodeValue);
                if(!empty($PSY->cfg["json"])){

                    foreach($PSY->cfg["json"] as $tagsec=>$tselem){

                        if(!in_array($tagsec, array('tags', 'sections'))) continue;

                        foreach($tselem as $tag_sec=>$tsprops){
                            if(!isset($tsprops->classes) || empty($tsprops->classes)) continue;
                            
                            $tsurl = ($tagsec == "tags")?  getTagUrl($tag_sec) : getSectionUrl($tag_sec);

                            $tsclasses = explode(",",$tsprops->classes);
                            $tsclasses=array_map('trim',$tsclasses);

                            addClassesUrl($tsclasses, $tsurl);
                        }
                    }                 
                }
                
            }

            if (!in_array($asset, array("tmpl", "js", "css"))) continue;

            $urls=NULL;
            if(trim($div->getAttribute('url'))){
                $urls = explode(",",$div->getAttribute('url'));
                $urls=array_map('trim',$urls);
            }

            if($asset == "tmpl"){
                
                $template = $div->getAttribute('template');

                if(!$template) continue;

                if(!isset($HTML["assets"]["tmpl"][$template])){
                    $HTML["assets"]["tmpl"][$template] = array();
                }

                $HTML["assets"]["tmpl"][$template][$created] = array(
                    "urls" => $urls,
                    "classes" => $classes,
                    "content" => $div->nodeValue
                );

            }else{
                $ordering = $div->getAttribute('ordering');
                $HTML["assets"][$asset][$ordering."_".$created] = array(
                    "urls" => $urls,
                    "classes" => $classes,
                    "content" => $div->nodeValue,
                    "title" => $div->getAttribute('title')
                );

            }

            continue;    
        }

        $attrs["tags"] = getPostTags($div);

        if($div->getAttribute('template')){
            $tmpl = $div->getAttribute('template');

            if($tmpl == "ajax"){

                $HTML["ajax"][$div->getAttribute('url')]=prepareNodeValue($div->nodeValue);

            }else if($tmpl == "other"){

                $HTML["other"][$div->getAttribute('url')]=$div->nodeValue;

            }else if($tmpl == "page"){

                $HTML["pages"][] = getTmplData($tmpl, $div);
                $attrs["url"] = $div->getAttribute('url');

                if(!$div->getAttribute('private')){
                        $search[] = array(
                            "title" => $div->getAttribute('title'),
                            "text" => $div->nodeValue,
                            "url"  => $attrs["url"]
                        );          

                        $PSY->pageContent[$created] = array($div, $attrs);   
                        prepareMetas($created, "page");   
                }    
                
                $HTML = prepareTagsInContent($HTML, "sections", "scount", $attrs, $div);

                 addClassesUrl($classes, $attrs["url"]);
            }
            continue;
        }
        $attrs["url"] = getPostUrl($div);

        addClassesUrl($classes, $attrs["url"]);

        $PSY->blogContent[$created] = array($div, $attrs);
        prepareMetas($created, "post");

        $search[] = array(
            "title" => $div->getAttribute('title'),
            "text" => $div->nodeValue,
            "url"  => $attrs["url"]
        );

        // render blog posts
        $page = getPage($pcount);

        if(!isset($HTML["blog"][$page])){
            $HTML["blog"][$page] = array();
        }
        $HTML["blog"][$page][$attrs["url"]]=getPageData("blogpost", $div, $attrs, $odd_even);
        $odd_even = 1 - $odd_even;

        // render  posts inside
        $HTML["posts"][$attrs["url"]]=getPageData("post", $div, $attrs);

        // render  tags

        $HTML = prepareTagsInContent($HTML, "tags", "tags_count", $attrs, $div);

        $pcount++;

    }
    $HTML["pcount"] = $pcount;

    $HTML["search"] =json_encode($search);

    foreach($HTML["assets"] as $type=>$assetinfo){
        ksort($HTML["assets"][$type]);
    }

    $HTML["assets"] = prepareClassesUrls($HTML["assets"]);

    getTagColors();

    return $HTML;

}

function getTagColors(){
    global $PSY;

    if(!isset($PSY->cfg["json"]->tagcolors) || empty($PSY->cfg["json"]->tagcolors)) return;

    foreach($PSY->cfg["json"]->tagcolors as $color=>$tagsraw){
        $tags = explode(",",$tagsraw);
        foreach($tags as $tag){
            $tag = trim($tag);
            if(!isset($PSY->tagColors[$tag])) $PSY->tagColors[$tag] = array();
            $PSY->tagColors[$tag][] = $color;
        }
    }

}


function prepareMetas($val="", $type=""){

    global $PSY;

    $title = (isset($PSY->cfg['sitetitle']) && trim($PSY->cfg['sitetitle']))? $PSY->cfg['sitetitle'] : "";
    $description = (isset($PSY->cfg['description']) && trim($PSY->cfg['description']))? $PSY->cfg['description'] : "";
    $keywords = (isset($PSY->cfg['keywords']) && trim($PSY->cfg['keywords']))? $PSY->cfg['keywords'] : "";
    $url = "";

    if($type == "blog"){

        $url = getLink($val);

    }else if($type == "tags" || $type == "sections"){

        $url = ($type == "tags" )? getTagUrl($val) : getSectionUrl($val);

        $title = ($title)? $title ." - ".$val : $val;
        $keywords =  ($keywords)?  $keywords.", ".$val : $val;

        if(isset($PSY->cfg["json"]->$type->$val) && !empty($PSY->cfg["json"]->$type->$val)){
            $cinfo = $PSY->cfg["json"]->$type->$val;
            if(isset($cinfo->title) && trim($cinfo->title))  $title = $cinfo->title;
            if(isset($cinfo->keywords) && trim($cinfo->keywords))  $keywords = $cinfo->keywords;
            if(isset($cinfo->description) && trim($cinfo->description))  $description = $cinfo->description;
        }        

    }else if($type == "post" || $type == "page"){
        $created = $val;
        $info = ($type == "post")? $PSY->blogContent[$created] : $PSY->pageContent[$created];  

        $div = $info[0];  $attrs = $info[1]; $url =$attrs["url"];

        if(trim($div->getAttribute('sitetitle'))) $title = trim($div->getAttribute('sitetitle'));
        if(trim($div->getAttribute('description'))){
            $description = trim($div->getAttribute('description'));
        }else{
             $desc = trimByWords($div->nodeValue, $PSY->cfg['description_length']);
             $desc = trim(str_replace("\n", "", $desc));
             if($desc) $description =$desc;
        }
        if(trim($div->getAttribute('keywords'))) $keywords = trim($div->getAttribute('keywords'));

    }

    $PSY->metas['title'][$url] = $title;
    $PSY->metas['description'][$url] = $description;
    $PSY->metas['keywords'][$url] = $keywords;

}


function prepareTags($url, $tmpl){
    global $PSY;

    if(empty($PSY->tags))  return;

    ksort($PSY->tags);

    $html = "";

    $html .=  "<div class='psy_tags'><ul>";
    foreach($PSY->tags as $tag=>$tcount){
        $html .= "<li><a href='".makeLink(getTagUrl($tag))."'>".$tag." (".$tcount.")</a></li>";
    }
    $html .=  "</ul></div>";

    $PSY->modules[$url]['tags'] =  $html;
}

function prepareTags2($url, $tmpl){
    global $PSY;

    if(empty($PSY->tags))  return;

    ksort($PSY->tags);

    $html = "";

    $html .=  "<div class='psy_tags2'>";

    $min_tcount = 0;
    $max_tcount = 0;

    $min_size = (int)$PSY->cfg['tag_min_size'];
    $max_size = (int)$PSY->cfg['tag_max_size'];

    $min_color = explode(",", $PSY->cfg['tag_min_color']);
    $mid_color = explode(",", $PSY->cfg['tag_mid_color']); 
    $max_color = explode(",", $PSY->cfg['tag_max_color']);


    foreach($PSY->tags as $tag=>$tcount){
        if(!$min_tcount || $min_tcount > $tcount) $min_tcount = $tcount;
        if($max_tcount < $tcount) $max_tcount = $tcount;
    }    

    $mid_tcount = (int) ( $min_tcount +  ($max_tcount - $min_tcount)/2);

    $x1 = $min_tcount; $x2 = $mid_tcount; $x3 = $max_tcount;

    $a = array(); $b = array(); $c = array();

    for($i=0; $i<3; $i++){
        $y1 = (int)trim($min_color[$i]); $y2 = (int)trim($mid_color[$i]); $y3 = (int)trim($max_color[$i]);

        $a[$i] = ($y3 - ($x3*($y2-$y1) + $x2*$y1 - $x1*$y2)/($x2- $x1)) / ($x3*($x3-$x1-$x2)+$x1*$x2);
        
        $b[$i] = ($y2-$y1)/($x2-$x1) - $a[$i]*($x1+$x2);
        
        $c[$i] = ($x2*$y1 - $x1*$y2)/($x2 - $x1) + $a[$i]*$x1*$x2;

        #echo $a[$i]." | ".$b[$i]." | ".$c[$i]." <br />";
        #var_dump(array($y1, $y2, $y3)); echo " <br />";
        #var_dump(array($x1, $x2, $x3)); echo " <br />";
    }
    #echo "<hr />";

    foreach($PSY->tags as $tag=>$tcount){
        $size = (int)(($max_size - $min_size)/( $max_tcount - $min_tcount) * ($tcount - $min_tcount) + $min_size);

        $color = array();

        #echo $tcount."<br />";

        for($i=0; $i<3; $i++){
            
            /*$color[$i] = (int)( ((int)$max_color[$i] - (int)$min_color[$i])/( $max_tcount - $min_tcount) *  
                ($tcount - $min_tcount) + (int)$min_color[$i]);
            echo (int)$max_color[$i].' - '.(int)$min_color[$i].'; color = '.$color[$i].";&nbsp;&nbsp;&nbsp; tk = ". $tcount."<br />";*/

            $x = $tcount;
            $y1 = (int)$min_color[$i];
            $color[$i] =(int)($a[$i]*$x*$x + $b[$i]*$x +$c[$i]);
            #echo ($a[$i]." * ".$x." * ".$x ." + ". $b[$i]." * ".$x ." + ".$c[$i]." = ".$color[$i]); echo " <br />";

            if($color[$i] > 255) $color[$i] = 255;
            if($color[$i] < 0) $color[$i] = 0;
        }
         #echo "<hr />";

        $html .= "<a class='psy_tag_calc_link' tagcount='".$tcount."' style='font-size:".$size.
                    "px; color:rgba(".$color[0].", ".$color[1].", ".$color[2].", 1)' href='".
                    makeLink(getTagUrl($tag))."'>".$tag."<span>&nbsp;(".$tcount.")</span></a> ";
    }
    $html .=  "</div>";

    $PSY->modules[$url]['tags2'] =  $html;
}

function prepareArchive($url, $tmpl){
    global $PSY;
    $html = "";
    
    $year_prev = "";
    $mon_prev = "";

    $open_closed = array("open"=>$PSY->cfg['archive_open'], "closed"=>$PSY->cfg['archive_closed']);

    $html .=  "<div class='psy_archive'>
    <div class='psy_archive_elems' style='display:none'>
            <span class='el_open'>".$PSY->cfg['archive_open']."</span>
            <span class='el_closed'>".$PSY->cfg['archive_closed']."</span>
    </div>
    <ul class='psy_archive_years'>";
    $last_month = false;
    $last_year = false;

        foreach ($PSY->blogContent as $created=>$obj) {

                $div = $obj[0]; $attrs = $obj[1];

                $year = substr($created,0,4);
                $mon = substr($created,4,2);

                 if($mon != $mon_prev || $year != $year_prev){
                     if($year_prev) $html .= "</ul></li>";
                 }

                 $addclass= "";

                if($year != $year_prev){
                    if($year_prev) $html .= "</ul></li>";

                    $opclose = "closed";
                    if(!$last_year ){
                        $addclass= " class='last_year' ";
                        $last_year = true;
                        $opclose = "open";
                    }

                    $year_html = "
                    <a class='toggle' href='javascript:void(0)'>
                        <span class='zippy toggle-".$opclose."'>".$open_closed[$opclose]."</span>
                    </a>
                    <a class='psy_year_title' href='#'>".$year."</a>";

                    $html .= "<li".$addclass.">".$year_html."<ul class='psy_archive_mons'>";
                }

                if($mon != $mon_prev || $year != $year_prev){

                    $opclose = "closed";                   
                    if(!$last_month ){
                        $addclass= " class='last_month' ";
                        $last_month = true;
                        $opclose = "open";
                    }

                    $mon_html = "
                    <a class='toggle' href='javascript:void(0)'>
                        <span class='zippy toggle-".$opclose."'>".$open_closed[$opclose]."</span>
                    </a>
                    <a class='psy_mon_title' href='#'>".$mon."</a>";                    

                    $html .= "<li".$addclass.">".$mon_html."<ul class='psy_archive_days'>";
                }

                $html .= "<li><a href='".makeLink(getPostUrl($div))."'>".$div->getAttribute('title')."</a></li>";

                $year_prev = $year;
                $mon_prev = $mon;


        }


    $html .=  "</li></ul></li></ul></div>";

    $PSY->modules[$url]['archive'] =  $html;    
}

function trimByWords($txt, $length){

    $txt =preg_replace('/<[^>]*>/', '',  $txt);
    if(strlen($txt) > $length){
        $title_array=explode(' ',$txt);
        $j=0; $title_new='';
        while((strlen($title_new) < $length) && isset($title_array[$j])){
            $title_new.=$title_array[$j].' ';
            $j++;
        }
        return $title_new;
    }else return $txt;
}

function prepareLastPosts($url, $tmpl){
    global $PSY;

    $html  =  "";

    $k=0;
    foreach ($PSY->blogContent as $created=>$obj) {

            $div = $obj[0]; $attrs = $obj[1];

            $year = substr($created,0,4);
            $mon = substr($created,4,2);
            $day = substr($created,6,2);

            $last_post = getTemplatePerPage('last_post', $url, $tmpl);   

            $lrepl = array(
                "%lastpost_date%" => $day.".".$mon.".".$year,
                "%lastpost_title%" => $div->getAttribute('title'),
                "%lastpost_url%" => makeLink(getPostUrl($div)),
                "%lastpost_content%" => trimByWords($div->nodeValue, $PSY->cfg['lastposts_length'])

            );
            $last_post = str_replace(array_keys($lrepl), array_values($lrepl), $last_post); 
            $html .= $last_post;

            $k++;

            if($k==$PSY->cfg['lastposts_count']) break;
    }

    $PSY->modules[$url]['lastposts'] =  $html;
}

function prepareLastPages($url, $tmpl){
    global $PSY;

    $html  =  "<div class='psy_lastpages'>";

    $k=0;
    foreach ($PSY->pageContent as $created=>$obj) {

            $div = $obj[0]; $attrs = $obj[1];

            $year = substr($created,0,4);
            $mon = substr($created,4,2);
            $day = substr($created,6,2);

            $last_page = getTemplatePerPage('last_page', $url, $tmpl);   

            $lrepl = array(
                "%lastpage_date%" => $day.".".$mon.".".$year,
                "%lastpage_title%" => $div->getAttribute('title'),
                "%lastpage_url%" => makeLink(trim($div->getAttribute('url'))),
                "%lastpage_content%" => trimByWords($div->nodeValue, $PSY->cfg['lastpages_length'])

            );
            $last_page = str_replace(array_keys($lrepl), array_values($lrepl), $last_page); 
            $html .= $last_page;


            $k++;

            if($k==$PSY->cfg['lastpages_count']) break;
    }


    $html .=  "</div>";


    $PSY->modules[$url]['lastpages'] =  $html;
}

function prepareModules($url, $tmpl){

    global $PSY;
    $PSY->modules[$url] = array();

    prepareTags($url, $tmpl);
    prepareTags2($url, $tmpl);
    prepareArchive($url, $tmpl);
    prepareLastPosts($url, $tmpl);
    prepareLastPages($url, $tmpl);    
}

function transliterate($string)
{

    $nonaskiireplace= 'Á|A, Â|A, Å|A, Ă|A, Ä|A, À|A, Ć|C, Ç|C, Č|C, Ď|D, É|E, È|E, Ë|E, Ě|E, Ì|I, Í|I, Î|I, Ï|I, Ĺ|L, Ń|N, Ň|N, Ñ|N, Ò|O, Ó|O, Ô|O, Õ|O, Ö|O, Ŕ|R, Ř|R, Š|S, Ś|O, Ť|T, Ů|U, Ú|U, Ű|U, Ü|U, Ý|Y, Ž|Z, Ź|Z, á|a, â|a, å|a, ä|a, à|a, ć|c, ç|c, č|c, ď|d, đ|d, é|e, ę|e, ë|e, ě|e, è|e, ì|i, í|i, î|i, ï|i, ĺ|l, ń|n, ň|n, ñ|n, ò|o, ó|o, ô|o, ő|o, ö|o, š|s, ś|s, ř|r, ŕ|r, ť|t, ů|u, ú|u, ű|u, ü|u, ý|y, ž|z, ź|z, ˙|-, ß|ss, Ą|A, µ|u, Ą|A, µ|u, ą|a, Ą|A, ę|e, Ę|E, ś|s, Ś|S, ż|z, Ż|Z, ź|z, Ź|Z, ć|c, Ć|C, ł|l, Ł|L, ó|o, Ó|O, ń|n, Ń|N, А|A, а|a, Б|B, б|b, В|V, в|v, Г|G, г|g, Д|D, д|d, Е|E, е|e, Ж|ZH, ж|zh, З|Z, з|z, И|I, и|i, Й|Y, й|y, К|K, к|k, Л|L, л|l, М|M, м|m, Н|N, н|n, О|O, о|o, П|P, п|p, Р|R, р|r, С|S, с|s, Т|T, т|t, У|U, у|u, Ф|F, ф|f, Х|H, х|h, Ц|TS, ц|ts, Ч|CH, ч|ch, Ш|SH, ш|sh, Щ|SCH, щ|sch, Ы|YI, ы|yi, Э|E, э|e, Ю|YU, ю|yu, Я|YA, я|ya, Ъ| , ъ| , Ь| , ь|';

    $naskr = explode(',', $nonaskiireplace);
    $tr = array();
    foreach($naskr as $na){
        $na_array=explode('|', trim($na));
        if(!isset($na_array[1])) $na_array[1]='';
        $tr[$na_array[0]]=$na_array[1];
    }

    $string = strtr($string,$tr);

    return $string;
}



function renderTags($tags){

    global $PSY;

    $html = "<ul class='tags'>";
    foreach($tags as $tag){
        $lic = (isset($PSY->tagColors[$tag]))? 
                " class='".implode(" ", $PSY->tagColors[$tag])."' " : "";

        $html .= "<li".$lic."><a href='".makeLink(getTagUrl($tag))."'>".$tag." <span>%tagcount|".$tag."%</span></a></li> ";
    }
    $html .="</ul>";
    return $html;
}

function prepareNodeValue($txt, $url_attr = ''){

    static $img_urls = array();

    $dom = new DOMDocument;
    @$dom->loadHTML($txt);

    $imgs = $dom->getElementsByTagName('img');

    $content = array();

    foreach ($imgs as $img) {
        $url = ($url_attr)? $url_attr: trim($img->getAttribute('psy_url'));
        if(!$url) continue;

        $base64_string = trim($img->getAttribute('src'));
        if(!$base64_string) continue;
        
        $data = explode(',', $base64_string);
        $data = base64_decode($data[1]);
        if(!$data) continue;

        if(!in_array($url, $img_urls)){
            generatePage ($data, getImgUrl($url), "*", "b");
            $img_urls[]=$url;
        }
        $txt = str_replace($img->getAttribute('src'), makeLink(getImgUrl($url), "*"), $txt);
    }

    return $txt;
}

function renderPage($post_data, $type, $url, $tmpl){

    $pg = getTemplatePerPage($type, $url, $tmpl); 

    foreach($post_data as $field=>$finfo){
        if(strpos($pg, "%".$field."%")===FALSE) continue;

        if($field == "post_tags") $finfo = "<div class='psy_page_tags'>".renderTags($finfo)."</div>";

        $pg = str_replace("%".$field."%", $finfo, $pg);
    }

    $com_add = ($type=="post")? "" : "_blog";

    if(strpos($pg, "%comments".$com_add."%")!==FALSE && !$post_data["post_nocomments"]){

        $comments = getTemplatePerPage("comments".$com_add, $url, $tmpl); 
        foreach($post_data as $field=>$finfo){
            $cfield = str_replace("post_", "com_", $field);
            if(strpos($comments, "%".$cfield."%")===FALSE) continue;
            $comments = str_replace("%".$cfield."%", $finfo, $comments);
        }
        $pg = str_replace("%comments".$com_add."%", $comments, $pg);
    }else{
        $pg = str_replace("%comments".$com_add."%", "", $pg);
    }

    return $pg;
}

function getPageData($type, $div, $attrs, $odd_even=0){

        global $PSY;
        
        $oddeven = ($odd_even)? "odd" : "even";

        $text = $div->nodeValue;

        $post_readmore = "";

        if(in_array($type, array("blogpost", "tags", "sections"))){
            if( strpos($text, $PSY->cfg['more_separator']) !== FALSE){
                $text = substr($text, 0, strpos($text, $PSY->cfg['more_separator']));

                $post_readmore = "<span class='readmore'><a class='psy_readmore' href='".makeLink($attrs["url"])."'>"
                     .$PSY->cfg['more_text']."</a></span>";
            }
        }

        $post_content  = prepareNodeValue($text);

        $created = $div->getAttribute('created');
        $date = substr($created, 6, 2).".".substr($created, 4, 2).".".substr($created, 0, 4);

        $post_data = array(
            "oddeven" => $oddeven,
            "post_type" => $type,
            "post_url_raw" => $attrs["url"],
            "post_url" => makeLink($attrs["url"]),
            "post_title" => trim($div->getAttribute('title')),
            "post_content" => $post_content,
            "post_readmore" => $post_readmore,
            "post_tags" => $attrs["tags"],
            "post_date" => "<div class='psy_page_date'>".$date."</div>",
            "post_nocomments" => trim($div->getAttribute('nocomments')),
            "created" => $created
        );

        return $post_data;

}

function getTmplData($tmpl, $div){

        global $PSY;

        $tmpl_content  = prepareNodeValue($div->nodeValue);
        $url = trim($div->getAttribute('url'));

        $created = $div->getAttribute('created');
        $date = substr($created, 6, 2).".".substr($created, 4, 2).".".substr($created, 0, 4);

        $tmpl_data = array(
            "tmpl_type" => $tmpl,
            "tmpl_url_raw" => $url,
            "tmpl_url" => makeLink($url),
            "tmpl_title" => trim($div->getAttribute('title')),
            "tmpl_content" => $tmpl_content,
            "tmpl_date" => "<div class='psy_page_date'>".$date."</div>",
            "tmpl_nocomments" => trim($div->getAttribute('nocomments')),
            "tmpl_private" => trim($div->getAttribute('private')),
            "created" => $created
        );

        return $tmpl_data;

}

function renderTmpl($tmpl_data, $tmpl, $asset_tmpl){

        $url = $tmpl_data["tmpl_url_raw"];
        $pg = getTemplatePerPage($tmpl, $url, $asset_tmpl); 
        foreach($tmpl_data as $field=>$finfo){
            if(strpos($pg, "%".$field."%")===FALSE) continue;
            $pg = str_replace("%".$field."%", $finfo, $pg);
        }

        if(strpos($pg, "%comments%")!==FALSE && !$tmpl_data["tmpl_nocomments"]){

            $comments = getTemplatePerPage("comments", $url, $asset_tmpl); 
            foreach($tmpl_data as $field=>$finfo){
                $cfield = str_replace("tmpl_", "com_", $field);
                if(strpos($comments, "%".$cfield."%")===FALSE) continue;
                $comments = str_replace("%".$cfield."%", $finfo, $comments);
            }
            $pg = str_replace("%comments%", $comments, $pg);
        }else{
            $pg = str_replace("%comments%", "", $pg);
        }

        return $pg;
}



function getLink($page, $url=""){
    if($url){
        return ($page==1)? $url : $url."/page".$page;
    }else{
        return ($page==1)? "index" : "page".$page;
    }    
}

function getPaginationLink($page, $url){
    return makeLink(getLink($page, $url));
}

function renderPagination($page, $count, $url=""){

    global $PSY;

    $limit = ceil($count/$PSY->cfg['perpage']);
    if($limit == 1) return "";

    $html = "<div class='psy_pagination'>";

    if($page>1){
        $html .= "<a class='psy_page_first' href='".getPaginationLink(1, $url)."'><span> &lt;&lt; </span></a>";
        $html .= "<a class='psy_page_prev' href='".getPaginationLink($page-1, $url)."'><span> &lt; </span></a>";
    }else{
        $html .= "<span class='psy_page_first'><span> &lt;&lt; </span></span>";
        $html .= "<span class='psy_page_prev'><span> &lt; </span></span>";       
    }

    for($i=1; $i<=$limit; $i++){

        if($i!=$page){
            $html .= "<a class='psy_page_other' href='".getPaginationLink($i, $url)."'><span>".$i."</span></a>";
        }else{
            $html .= "<span class='psy_page_cur' ><span>".$i."</span></span>";
        }
    }

    if($page<$limit){
        $html .= "<a class='psy_page_next' href='".getPaginationLink($page+1, $url)."'><span> &gt; </span></a>";
        $html .= "<a class='psy_page_end' href='".getPaginationLink($limit, $url)."'><span> &gt;&gt; </span></a>";
    }else{
        $html .= "<span class='psy_page_next'><span> &gt; </span></span>";
        $html .= "<span class='psy_page_end'><span> &gt;&gt; </span></span>";       
    }
    $html .="<div class='clearfix'></div>";
     $html .="</div>";
     return $html;
}

function beginsWith($str, $substr){
    if(strlen($substr) > strlen($str)) return false;
    return (substr($str, 0, strlen($substr)) === $substr);
}
function endsWith($str, $substr){
    if(strlen($substr) > strlen($str)) return false;
    return (substr($str, strlen($str) - strlen($substr)) === $substr);
}

function getAssetPerPage($assetObj, $url, $multiple=false){
    $content = ($multiple)? array() : "";
    $prev_turl = "";
    foreach($assetObj as $ordering=> $tobj){
        if($tobj['urls'] && in_array($url, $tobj['urls'])){
            if($multiple){
                $content[$ordering] = $tobj;
            }else{
                $content = $tobj['content'];
                break;
            }
        }else if(!$tobj['urls'] ){
            if($multiple){
                $content[$ordering] = $tobj;
            }else{
                if(!$content) $content = $tobj['content'];
            }
        }else if ($tobj['urls']){
            foreach($tobj['urls'] as $turl){
                if(endsWith($turl, "*")){
                    
                    $turl= substr($turl, 0, strlen($turl)-1);

                    if(beginsWith($url, $turl) && !beginsWith($prev_turl, $turl)){

                        if($multiple){
                            $content[$ordering] = $tobj;
                        }else{
                            $content = $tobj['content'];
                        }
                        $prev_turl = $turl;

                    }

                }
            }
        }
    }
    return $content;
}

function getTemplatePerPage($type, $url, $tmpls){

    $content = isset($tmpls[$type])? getAssetPerPage($tmpls[$type], $url) : "";

    preg_match_all("/%template\|([^%]*)%/",$content, $out);
    if(!empty($out) && !empty($out[0])){
        foreach($out[0] as $k=>$tmpl_raw){
            $new_content = "";
            if($tmpl_raw){
                    $new_content = getTemplatePerPage($out[1][$k], $url, $tmpls);
            }
            $content = str_replace($tmpl_raw, $new_content, $content );
        }
    }

    return $content;

}


function renderElements($html, $url){
    global $PSY;

    $elements = array("module", "link", "section", "tag", "tagcount", "cfg", "date", 
            "menulink", "url", "email");

    foreach($elements as $el){

        preg_match_all("/%".$el."\|([^%]*)%/",$html, $out);
        if(!empty($out) && !empty($out[0])){
            foreach($out[0] as $k=>$tmpl_raw){
                $new_content = "";
                $elem  = $out[1][$k];
                if($elem){

                    if($el == "module" && isset($PSY->modules[$url][$elem])){
                        
                        $new_content = $PSY->modules[$url][$elem];

                    }else if($el == "link"){

                        $new_content = makeLink($elem);

                    }else if($el == "menulink"){
                        
                        $elm = explode("|", $elem);
                        $hsh = (isset($elm[2]))? $elm[2] : "";
                        $new_content = ($url == $elm[0])? 
                            "<span class='psy_active'>".$elm[1]."</span>" : 
                            "<a href='".makeLink($elm[0]).$hsh."' class='psy_noactive'>".$elm[1]."</a>" ;

                    }else if($el == "section"){

                        $new_content = makeLink(getSectionUrl($elem));

                    }else if($el == "tag"){

                        $new_content = makeLink(getTagUrl($elem));
                        
                    }else if($el == "tagcount"){

                        $new_content = (isset($PSY->tags[$elem])) ? $PSY->tags[$elem] : "";

                    }else if($el == "cfg"){
                        $new_content = (isset($PSY->cfg[$elem])) ? $PSY->cfg[$elem] : "";

                    }else if($el == "date"){
                        $date = date($elem, time());
                        $new_content = $date;

                    }else if($el == "url"){
                        
                        $new_content = $url;

                        if(beginsWith($elem, "is_")){
                            $check_url = substr($elem, 3);
                            $new_content = ($check_url == $url)? 
                                str_replace("/", "_", transliterate($url)) : "other";

                        }else if($elem == "full"){

                            $new_content = makeLink($url);

                        }else if($elem == "stringify"){

                            $new_content = str_replace("/", "_", transliterate($url));
                        
                        }
                    }else if($el == "email"){
                        $new_content = hide_email($elem);
                    }

                }
                $html = str_replace($tmpl_raw, $new_content, $html );
            }
        }
    }

    return $html;
}

function afterGetTemplate($html, $content_html, $url, $assets){

    global $PSY;
    static $hashes = array("css"=>array(), "js"=>array());

    if(!is_array($content_html)) $content_html = array("content"=>$content_html);

    $content_elements = array("content", "pagination", "content_head");
    foreach($content_elements as $ce){

        $cerepl = (isset($content_html[$ce]))? $content_html[$ce] : "";
        $html = str_replace("%".$ce."%", $cerepl, $html);
    }


    $html = renderElements($html, $url);

    $index = getLink(1);
    $sitetitle = isset($PSY->metas['title'][$url])? $PSY->metas['title'][$url] : $PSY->metas['title'][$index];
    $description = isset($PSY->metas['description'][$url])? 
                $PSY->metas['description'][$url] : $PSY->metas['description'][$index];
    $keywords = isset($PSY->metas['keywords'][$url])? 
                $PSY->metas['keywords'][$url] : $PSY->metas['keywords'][$index];


    $replaces = array(
        "%livesite%" => $PSY->livesite,
        "%livebase%" => $PSY->livebase,
        "%trans_url%" => transliterate($url),
        "%body_class%" => str_replace("/", "_", transliterate($url)),
        "%title%" => $sitetitle,
        "%description%" => $description,
        "%keywords%" => $keywords
    );

    foreach(array("js", "css") as $jscss){

        $jscss_codes_obj = getAssetPerPage($assets[$jscss], $url, true);
        $jscss_codes  = "";

        if($jscss == "js"){
            $jscss_codes  .= 'var PSY_LIVE = "'.$PSY->livesite.'";'."\n\n";
        }

        foreach($jscss_codes_obj as $cobj){
            $jscss_codes  .= "/*********************************\n";
            $jscss_codes  .= "        ".$cobj["title"]."\n";
            $jscss_codes  .= "*********************************/\n\n";
            $jscss_codes  .= "        ".$cobj["content"]."\n\n";
        }

        $md5 = md5($jscss_codes);

        if(!in_array($md5, $hashes[$jscss])){
            $hashes[$jscss][] = $md5;    
            $link_num = sizeof($hashes[$jscss]);
            generatePage ($jscss_codes, $jscss."/psytronica".$link_num, $jscss);
        }else{
            $link_num = array_search($md5, $hashes[$jscss])+1;
        }

        $replaces['%'. $jscss.'%'] = makeLink($jscss."/psytronica".$link_num, $jscss);
    }

    $html = str_replace(array_keys($replaces), array_values($replaces), $html);

    return $html;
}

function render($content_html, $url, $assets){

    global $PSY;

    prepareModules($url, $assets['tmpl']);

    $html = getTemplatePerPage('site', $url, $assets['tmpl']);
    $html = afterGetTemplate($html, $content_html, $url, $assets);

    return $html;

}


function generatePage ($html, $url, $ext="html", $write_mode=""){ 
    // $write_mode b for binary
    
    global $PSY;

    $url = str_replace(array_keys($PSY->filename["replaces"]), 
            array_values($PSY->filename["replaces"]),  $url);

    $url = str_replace($PSY->filename["bad_fragments"],  "",  $url);

    $url = preg_replace($PSY->filename["allowed_regexp"],'',$url);

    $url_arr = explode("/", $url);

    foreach($url_arr as $k=>$u){

        foreach($PSY->filename["bad_endings"] as $bend){

            if(endsWith($u, $bend)) $url_arr[$k] = substr($u, 0, -1*strlen($bend));
        }

        foreach($PSY->filename["bad_beginnings"] as $bend){

            if(beginsWith($u, $bend))  $url_arr[$k] = substr($u, strlen($bend));
        }        
    }

    $url = implode("/", $url_arr);

    $url = ($ext!="*")?  $PSY->root.$url.".".$ext : $PSY->root.$url;

    if(is_dir($url)) psyError("Путь является директорией: ".$url);

    if(!file_exists(dirname($url))){
        mkdir(dirname($url), 0755, true);
    }
    if (!$handle = fopen($url, 'w'.$write_mode)) {
            echo "Cannot open file ($url)";
            exit;
    }
    if (fwrite($handle, $html) === FALSE) {
        echo "Cannot write to file ($url)";
        exit;
    }
    fclose($handle);
}


function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
           rrmdir($dir."/".$object);
         else
           unlink($dir."/".$object); 
       } 
     }
     rmdir($dir); 
   } 
}
function cleanFolder(){
    global $PSY;
    $files = glob($PSY->root.'*'); // get all file names
    foreach($files as $file){ // iterate files


        if(substr($file, 0, strlen($PSY->wikidir)) === $PSY->wikidir) continue;

        if(is_file($file)){
            unlink($file); // delete file
        }else{
            rrmdir($file);
        }
    }
}
function my_mb_ucfirst($str) {
    $fc = mb_strtoupper(mb_substr($str, 0, 1));
    return $fc.mb_substr($str, 1);
}

function hide_email($email) { 
    $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz'; 
    $key = str_shuffle($character_set); 
    $cipher_text = ''; 
    $id = 'e'.rand(1,999999999); 
    for ($i=0;$i<strlen($email);$i+=1) $cipher_text.= $key[strpos($character_set,$email[$i])]; 
    $script = 'var a="'.$key.'";var b=a.split("").sort().join("");var c="'.$cipher_text.'";var d="";'; 
    $script.= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));'; 
    $script.= 'document.getElementById("'.$id.'").innerHTML="<a href=\\"mailto:"+d+"\\">"+d+"</a>"'; 
    $script = "eval(\"".str_replace(array("\\",'"'),array("\\\\",'\"'), $script)."\")"; 
    $script = '<script type="text/javascript">/*<![CDATA[*/'.$script.'/*]]>*/</script>'; 
    return '<span id="'.$id.'" class ="esalgomuyraro">m<b>@</b>e@d<b>no</b>oma<b>.com</b>in.com</span>'.$script;
} 


function generate($HTML){

    $sitemap = array("posts"=> array(), "tags"=> array(), "sections"=>array(), "pages"=>array());

    foreach ($HTML["blog"] as $page => $bobj){

        $blog_html = array("content"=>"");
        $blog_html['pagination'] =  renderPagination($page, $HTML["pcount"]);

        foreach($bobj as $url => $post_data){
            $html = renderPage($post_data, "blogpost", getLink($page), $HTML['assets']['tmpl']);         
            $blog_html['content'] .= $html;
        }

        prepareMetas($page, "blog");

        $site_html = render($blog_html, getLink($page), $HTML['assets']);
        generatePage ($site_html, getLink($page));
    }



    foreach ($HTML["posts"] as $url => $post_data){

        $post_html = renderPage($post_data, "post", $url, $HTML['assets']['tmpl']);     
        $site_html = render($post_html, $url, $HTML['assets']);
        generatePage ($site_html, $url);
        $sitemap["posts"][$url] = array($post_data["post_title"], $post_data['created']);
    }    



    foreach ($HTML["tags"] as $tag => $tobj){

        foreach ($tobj as $tag_page => $htmls){

            $tag_html = array("content"=>"");

            
            $tag_html['content_head'] = 
                    getTemplatePerPage('content_head_tag', getTagUrl($tag), $HTML['assets']['tmpl']);
            $tag_html['content_head'] = str_replace("%content_head_tag_data%", my_mb_ucfirst($tag),  $tag_html['content_head']);

            $tag_html['pagination'] =  renderPagination($tag_page, $HTML["tags_count"][$tag], getTagUrl($tag));

            foreach($htmls as $post_data){
                $html = renderPage($post_data, "tags", getTagUrl($tag), $HTML['assets']['tmpl']);
                $tag_html["content"] .=$html;
            }

            prepareMetas($tag, "tags");

            $site_html = render($tag_html, getTagUrl($tag), $HTML['assets']);

            generatePage ($site_html, getLink($tag_page, getTagUrl($tag)));

        }
        $sitemap["tags"][getTagUrl($tag)] = array($tag, "");
    }

    foreach ($HTML["sections"] as $tag => $tobj){

        foreach ($tobj as $tag_page => $htmls){

            $tag_html = array("content"=>"");

            $tag_html['content_head'] = 
                    getTemplatePerPage('content_head_section', getSectionUrl($tag), $HTML['assets']['tmpl']);
            $tag_html['content_head'] = str_replace("%content_head_section_data%", my_mb_ucfirst($tag),  $tag_html['content_head']);            


            $tag_html['pagination'] =  renderPagination($tag_page, $HTML["scount"][$tag], getSectionUrl($tag));

            foreach($htmls as $post_data){
                $html = renderPage($post_data, "sections", getSectionUrl($tag), $HTML['assets']['tmpl']);                
                $tag_html["content"] .=$html;
            }

            prepareMetas($tag, "sections");

            $site_html = render($tag_html, getSectionUrl($tag), $HTML['assets']);

            generatePage ($site_html, getLink($tag_page, getSectionUrl($tag)));

        }
        $sitemap["sections"][getSectionUrl($tag)] = array($tag, "");
    }    

    foreach($HTML["pages"] as $page_data){

        $page_html = renderTmpl($page_data, "page", $HTML['assets']['tmpl']); 
        $url = $page_data['tmpl_url_raw'];
        $site_html = render($page_html, $url, $HTML['assets']);
        generatePage ($site_html, $url);       
        
        if(!$page_data["tmpl_private"]){
        	$sitemap["pages"][$url] = array($page_data["tmpl_title"], 
        			$page_data['created']); 
        }              
    }


    foreach ($HTML["ajax"] as $url => $ajax){
        generatePage ($ajax, getAjaxUrl($url));
    }    
    foreach ($HTML["other"] as $url => $other){
        generatePage ($other, $url, "*");
    }  

    generatePage ($HTML["search"], "search/index");   
    
    generateSiteMap($sitemap, $HTML['assets']);
    generateSiteMapXML($sitemap, $HTML['assets']);
}

function generateSiteMap($sitemap, $assets){
    global $PSY;

    $pg = getTemplatePerPage('sitemap', 'sitemap', $assets['tmpl']);
    $html = "";
    foreach($sitemap as $type=>$smobj){
        $html .= "<ul class='psy_sitemap_ul psy_sitemap_ul".$type
                    ."'><span class='psy_sitemap_title'>".$PSY->cfg['title_'.$type].'</span>';
        $oddeven = 0;
        foreach($smobj as $url=>$arr){
        	list($name, $created) = $arr;

            $liclass = ($oddeven)? "odd": "even";
            $html .= "<li class='".$liclass."'><a href='".makeLink($url)."'>".$name."</a></li>";
             $oddeven = 1 - $oddeven;
        }
        $html .= "</ul>";
    }

    $pg = str_replace("%sitemap%", $html, $pg);
    $pg = prepareNodeValue($pg);
    $site_html = render($pg, "sitemap", $assets);
    generatePage ($site_html, "sitemap");
}

function generateSiteMapXML($sitemap, $assets){
    global $PSY;

    $html = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

	/*$html .= "<?xml-stylesheet type=\"text/xsl\" href=\"".makeLink('sitemap', 'xsl')."\"?>\n";*/

	$html .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n\n";


	$html .= "<url>\n";
	$html .= '<loc>'.$PSY->livesite."</loc>\n";
	$html .= "<changefreq>weekly</changefreq>\n";
	$html .= "<priority>0.5</priority>\n";
	$html .= "</url>\n";

	$sitemap = array(
		"tags" => $sitemap["tags"],
		"sections" => $sitemap["sections"],
		"posts" => $sitemap["posts"],
		"pages" => $sitemap["pages"]
	);

    foreach($sitemap as $type=>$smobj){

        foreach($smobj as $url=>$arr){

        	list($name, $created) = $arr;

			$html .= "<url>\n";
			$html .= '<loc>'.makeLink($url)."</loc>\n";

			if($created){
				//2016-10-24T17:26:04Z
				$date = substr($created,0,4)."-".substr($created,4,2)
				."-".substr($created,6,2)."T".substr($created,8,2)
				.":".substr($created,10,2).":".substr($created,12,2)."Z";
				$html .= "<lastmod>".$date."</lastmod>\n";
			}

			$html .= "<changefreq>weekly</changefreq>\n";
			$html .= "<priority>0.5</priority>\n";
			$html .= "</url>\n";
        }
    }

    $html .= "</urlset>";

    generatePage ($html, "sitemap", "xml");
}

function main(){
	echo "Begin parsing content\n";	
    $content = parseWiki();
	echo "Cleaning folder\n";	
    cleanFolder();    
	echo "Preparing content\n";	
    $HTML = prepareContent($content);
	echo "Generating pages\n";	
    generate($HTML);
	echo "Finish parsing content\n";
}
