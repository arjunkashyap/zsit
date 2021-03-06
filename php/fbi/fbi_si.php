<!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Zoological Survey of India | Digital archives of their Publications</title>
<link href="../style/reset.css" media="screen" rel="stylesheet" type="text/css" />
<link href="../style/indexstyle.css" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../js/jquery-2.0.0.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/treeview.js"></script>
</head>

<body>
<div class="page">
	<div class="header">
		<div class="zsi_logo"><img src="../images/logo.png" alt="ZSI Logo" /></div>
		<div class="gov_logo"><img src="../images/gov_logo.png" alt="Government of India Logo" /></div>
		<div class="title">
			<p class="eng">
				<span class="big">भारत सरकार</span><br />
				पर्यावरण, वन और जलवायु परिवर्तन मंत्रालय<br />
				<span class="big">Government of India</span><br />
				Ministry of Environment, Forest and<br />Climate Change
			</p>
			<div class="full">
				<p class="small">भारतीय प्राणी सर्वेक्षण</p>
				<p class="vbig">Zoological Survey of India</p>
			</div>
		</div>
<?php include("include_nav.php");?>
	</div>
	<div class="mainpage">
		<div class="nav">
			<div class="archive_title">Fauna of<br />British India</div>
			<ul class="menu">
				<li><a class="active" href="../fbi_books_list.php">Books</a></li>
				<li><a href="authors.php">Authors</a></li>
				<li class="gap_below"><a href="../search.php">Search</a></li>
				<li><a title="Click to download DjVu plugin" href="https://www.cuminas.jp/en/downloads/download_en/?pid=1" target="_blank">Get DjVu</a></li>
			</ul>
		</div>
		<div class="archive_holder">

<?php
include("connect.php");
require_once("../common.php");

if(isset($_GET['book_id'])){$book_id = $_GET['book_id'];}else{$book_id = '';}
if(isset($_GET['type'])){$type = $_GET['type'];}else{$type = '';}
if(isset($_GET['book_title'])){$book_title = $_GET['book_title'];}else{$book_title = '';}

$book_title = entityReferenceReplace($book_title);

if(!(isValidId($book_id) && isValidType($type) && isValidTitle($book_title)))
{
	echo "Invalid URL";
	
	echo "</div></div>";
	include("include_footer.php");
	echo "<div class=\"clearfix\"></div></div>";
	include("include_footer_out.php");
	echo "</body></html>";
	exit(1);
}

$db = @new mysqli("$host", "$user", "$password", "$database", "$port");
if($db->connect_errno > 0)
{
	echo 'Not connected to the database [' . $db->connect_errno . ']';
	echo "</div></div>";
	include("include_footer.php");
	echo "<div class=\"clearfix\"></div></div>";
	include("include_footer_out.php");
	echo "</body></html>";
	exit(1);
}

//~ $db = mysql_connect("localhost",$user,$password) or die("Not connected to database");
//~ $rs = mysql_select_db($database,$db) or die("No Database");

$query = "select * from fbi_systematic_index where book_id=$book_id and type='$type' order by slno";

//~ $result = mysql_query($query);
//~ $num_rows = mysql_num_rows($result);

$result = $db->query($query); 
$num_rows = $result ? $result->num_rows : 0;

$stack = array();
$p_stack = array();
$first = 1;

$li_id = 0;
$ul_id = 0;

$plus_link = "<img class=\"bpointer\" title=\"Expand\" src=\"../images/plus.gif\" alt=\"Expand or Collapse\" onclick=\"display_block_inside(this)\" />";
//$plus_link = "<a href=\"#\" onclick=\"display_block(this)\"><img src=\"plus.gif\" alt=\"\"></a>";
$bullet = "<img class=\"bpointer\" src=\"../images/bullet_1.gif\" alt=\"Point\" />";

//~ $plus_link = "+";
//~ $bullet = ".";


$query_aux = "select * from fbi_books_list where book_id=$book_id and type='fbi'";

//~ $result_aux = mysql_query($query_aux);
//~ $num_rows_aux = mysql_num_rows($result_aux);

$result_aux = $db->query($query_aux); 
$num_rows_aux = $result_aux ? $result_aux->num_rows : 0;

//~ $row_aux=mysql_fetch_assoc($result_aux);
$row_aux = $result_aux->fetch_assoc();

$edition = $row_aux['edition'];
$volume = $row_aux['volume'];
$part = $row_aux['part'];
$authorname = $row_aux['authorname'];
$page = $row_aux['page'];
$page_end = $row_aux['page_end'];

if($result_aux){$result_aux->free();}

$anames = preg_replace("/;/", ",&nbsp;&nbsp;", $authorname);
$anames = preg_split("/;/", $authorname);

$daname = '';

if(sizeof($anames) > 1)
{
	for($i=0; $i<(sizeof($anames) - 1); $i++)
	{
		$daname = $daname . ",&nbsp;&nbsp;" . $anames[$i];
	}
	$daname = preg_replace("/^,&nbsp;&nbsp;/", "", $daname);
	$daname = $daname . "&nbsp;&nbsp;and&nbsp;&nbsp;" . $anames[sizeof($anames) - 1];
}
else
{
	$daname = $authorname;
}

echo "<div class=\"page_booktitle\"><span class=\"motif fbi_motif\"></span><span class=\"itl\">$book_title</span></div>";
echo "<div class=\"page_subtitle\"><span class=\"itl\">$daname</span></div>";
echo "<div class=\"page_other\">";

$book_info = '';
		
if($edition != '00')
{
	$book_info = $book_info . "Edition " . intval($edition);
}
if($volume != '00')
{
	$book_info = $book_info . " | Volume " . intval($volume);
}
if($part != '00')
{
	$book_info = $book_info . " | Part " . intval($part);
}
if(intval($page) != 0)
{
	$book_info = $book_info . " | pp " . intval($page) . " - " . intval($page_end);	
}

$book_info = preg_replace("/^ /", "", $book_info);
$book_info = preg_replace("/^\|/", "", $book_info);
$book_info = preg_replace("/^ /", "", $book_info);

$PDFUrl = '../../PDFVolumes/' . $type . '/' . $book_id . '/index.pdf';
if (file_exists($PDFUrl)) $book_info .= '<br /><span class="downloadBook"><a target="_blank" href="' . $PDFUrl . '">Download Book (PDF)</a></span>';
echo "$book_info</div>";
if($num_rows > 0)
{
	echo "<div class=\"page_si\">Systematic Index</div>";
	echo "<div class=\"treeview\">";
	for($i=1;$i<=$num_rows;$i++)
	{
		//~ $row=mysql_fetch_assoc($result);
		$row = $result->fetch_assoc();
		
		$level = $row['level'];
		$title = $row['title'];
		$title = preg_replace('/!!(.*)!!/', "<i>$1</i>", $title);
		$page = $row['page'];
		$type = $row['type'];
		$slno = $row['slno'];
		$title = "<span class=\"titlespan\"><a target=\"_blank\" href=\"../../Volumes/$type/$book_id/index.djvu?djvuopts&amp;page=$page.djvu&amp;zoom=page\">$title</a></span>";
		
		if($first)
		{
			array_push($stack,$level);
			$ul_id++;
			echo "<ul id=\"ul_id$ul_id\">\n";
			array_push($p_stack,$ul_id);
			$li_id++;
			//echo "<li>$title(" . $stack[sizeof($stack)-1] . ")\n";
			//echo "<li>$title\n";
			$deffer = display_tabs($level) . "<li id=\"li_id$li_id\">:rep:$title";
			$first = 0;
		}
		elseif($level > $stack[sizeof($stack)-1])
		{
			//$parent_id = "ul_id" . $p_stack[sizeof($p_stack)-1];
			//$alt_link = $plus_link;
			//$alt_link = preg_replace('/#/',"#$parent_id",$alt_link);
			$deffer = preg_replace('/:rep:/',"$plus_link",$deffer);
			echo $deffer;			

			$ul_id++;			
			$li_id++;			
			array_push($stack,$level);
			array_push($p_stack,$ul_id);
			//echo "<ul>\n\t<li>$title(" . display_stack($stack) . ")\n";
			//echo "<ul>\n\t<li>$title\n";
			$deffer = "\n" . display_tabs(($level-1)) . "<ul class=\"dnone\" id=\"ul_id$ul_id\">\n";
			$deffer = $deffer . display_tabs($level) ."<li id=\"li_id$li_id\">:rep:$title";
		}
		elseif($level < $stack[sizeof($stack)-1])
		{
			$deffer = preg_replace('/:rep:/',"$bullet",$deffer);
			echo $deffer;
			
			for($k=sizeof($stack)-1;(($k>=0) && ($level != $stack[$k]));$k--)
			{
				echo "</li>\n". display_tabs($level) ."</ul>\n";
				$top = array_pop($stack);
				$top1 = array_pop($p_stack);
			}
			$li_id++;
			//echo "</li>\n<li>$title(" . display_stack($stack) . ")\n";
			$deffer = display_tabs($level) . "</li>\n";
			$deffer = $deffer . display_tabs($level) ."<li id=\"li_id$li_id\">:rep:$title";
		}
		elseif($level == $stack[sizeof($stack)-1])
		{
			$deffer = preg_replace('/:rep:/',"$bullet",$deffer);
			echo $deffer;
			$li_id++;
			//echo "</li>\n<li>$title(" . display_stack($stack) . ")\n";
			//echo "</li>\n<li>$title\n";
			$deffer = "</li>\n";
			$deffer = $deffer . display_tabs($level) ."<li id=\"li_id$li_id\">:rep:$title";
		}
	}

	$deffer = preg_replace('/:rep:/',"$bullet",$deffer);
	echo $deffer;

	for($i=0;$i<sizeof($stack);$i++)
	{
		echo "</li>\n". display_tabs($level) ."</ul>\n";
	}

	echo "</div>";
}
else
{
	echo "No data in the database";
}

if($result){$result->free();}
$db->close();

function display_stack($stack)
{
	for($j=0;$j<sizeof($stack);$j++)
	{
		$disp_array = $disp_array . $stack[$j] . ",";
	}
	return $disp_array;
}

function display_tabs($num)
{
	$str_tabs = "";
	
	if($num != 0)
	{
		for($tab=1;$tab<=$num;$tab++)
		{
			$str_tabs = $str_tabs . "\t";
		}
	}
	
	return $str_tabs;
}

?>

		</div>
	</div>
<?php include("include_footer.php");?>
	<div class="clearfix"></div>
</div>
<?php include("include_footer_out.php");?>
</body>

</html>
