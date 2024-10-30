<?php
/*
Plugin Name: Blogmarking
Plugin URI: http://reitor.org/wp-plugins/blogmarking/
Description: Blogmarking &eacute; plugin que exibe abaixo das postagens do blog um social bookmark e também exibe a quantidade de assinantes do feed, assim possibilitando para aqueles que já tem uma boa quantidade de feeds a acariciar novos assinantes e os que não tem a ganhar os primeiros.
Version: 2.1
Author: Ronis Reitor
Author URI: http://www.reitor.org
*/
$bmver = '2.1';
$wpurl = get_bloginfo('wpurl');
$bmurl = WP_PLUGIN_URL . "/blogmarking/";
$bmOpt = get_option('bmOpt');
if(!is_array($bmOpt)){
	$bmOpt = array(
		'api'			=> 'http://migre.me/api.txt?url=%url%',
		'feed'			=> 'http://feeds.feedburner.com/yourfeed',
		'twitter'		=> 'reitor',
		'rssOpt'		=> '',
		'twitterOpt'	=> '',
		'espalhe'		=> ''
	);
	add_action('admin_notices', 'bm_aviso');
}
function bm_aviso(){
	echo '<div class="error"><p><a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=blogmarking">Por favor, configure o <strong>Blogmarking</strong></a></p></div>';
}
$bmSave = get_option('bmSave');
if(!is_array($bmSave)){
	$bmSave = array('data' => '0','fburi' => '?','fbcount' => '?','twtcount' => '?');
}
$bmMarks = get_option('bmMarks');
if(!is_array($bmMarks)){
	$bmMarks = array('twitter', 'facebook', 'delicious', 'digg', 'orkut', 'ueba', 'linkninja', 'ocioso', 'linkk', 'colmeia', 'domelhor', 'rec6', 'dihitt', 'bombanet', 'linklog', 'linkloko', 'linkirado', 'linkame');
}
$bmBookmarks = array(
	'twitter'		=> array(
		'Twitter'	=> 'http://twitter.com/home?status=%titulo%+-+%short%'),
	'facebook'		=> array(
		'Facebook'	=> 'http://www.facebook.com/sharer.php?u=%link%&amp;t=%titulo%'),
	'delicious'		=> array(
		'Delicious'	=> 'http://delicious.com/post?url=%link%&amp;title=%titulo%'),
	'digg'			=> array(
		'Digg'		=> 'http://digg.com/submit?phase=2&amp;url=%link%&amp;title=%titulo%'),
	'orkut'			=> array(
		'Orkut'		=> 'http://promote.orkut.com/preview?nt=orkut.com&amp;tt=%titulo%&amp;du=%link%&amp;cn=%resumo%'),
	'ueba'			=> array(
		'Ueba'		=> 'http://ueba.com.br/NovoLink?url=%link%&amp;titulo=%titulo%&amp;origem=%blog%'),
	'linkninja'		=> array(
		'LinkNinja'	=> 'http://linkninja.com.br/enviar_link.php?story_url=%link%'),
	'ocioso'		=> array(
		'Ocioso'	=> 'http://ocioso.com.br/cadastre_enviarlink.php?titulo=%titulo%&amp;site=%link%'),
	'linkk'			=> array(
		'Linkk'		=> 'http://www.linkk.com.br/submit.php?url=%link%&amp;title=%titulo%'),
	'colmeia'		=> array(
		'Colmeia'	=> 'http://www.colmeia.blog.br/submit?url=%link%&amp;title=%titulo%'),
	'domelhor'		=> array(
		'doMelhor'	=> 'http://domelhor.net/submit.php?url=%link%&titulo=%titulo%'),
	'rec6'			=> array(
		'Rec6'		=> 'http://www.via6.com/rec6/link.php?url=%link%'),
	'dihitt'		=> array(
		'diHITT'	=> 'http://www.dihitt.com.br/submit?url=%link%'),
	'bombanet'		=> array(
		'BombaNet'	=> 'http://www.bombanet.com.br/sugerir-um-link'),
	'linklog'		=> array(
		'LinkLog'	=> 'http://www.linklog.com.br/index.php?pageo=enviarlink'),
	'linkloko'		=> array(
		'LinkLoko'	=> 'http://www.linkloko.com.br/submit?url=%link%'),
	'linkirado'		=> array(
		'LinkIrado'	=> 'http://www.linkirado.net/envie.php'),
	'linkame'		=> array(
		'Linka-Me'	=> 'http://www.linka-me.com/submit.php?url=%link%')
);
function bm_curl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	$result	= curl_exec($ch);
	curl_close($ch);
	return $result;
}
function bmConfig() {
	global $bmOpt,$bmBookmarks,$bmurl,$bmMarks, $bmver;
	echo "<div class=\"wrap\">
		<div id=\"icon-blogmarking\" class=\"icon28\"><br /></div>
		<h2>Blogmarking {$bmver}</h2>\n		";
	if(isset($_POST['submit'])){
		$bmOpt = array(
		'api'			=> stripslashes(trim($_POST['api_bm'])),
		'feed'			=> stripslashes(trim($_POST['feed_bm'])),
		'twitter'		=> stripslashes(trim($_POST['twitter_bm'])),
		'rssOpt'		=> stripslashes(trim($_POST['rssOpt_bm'])),
		'twitterOpt'	=> stripslashes(trim($_POST['twitterOpt_bm'])),
		'espalhe'		=> stripslashes(trim($_POST['espalhe_bm']))
		);
		update_option('bmOpt', $bmOpt);
		update_option('bmMarks', $_POST['bmarks']);
		bm_save();
		echo "<div class=\"updated\"><p><strong>Plugin atualizado com sucesso!</strong></p></div>\n		";
	}
	echo "<table border=\"0\" cellpadding=\"5\" cellspacing=\"10\" width=\"810\">
		<tr>
		<td valign=\"top\" width=\"85%\">
		<form method=\"post\">
		<div id=\"poststuff\">
		<div class=\"postbox\">
		<h3 class=\"hndle\">Configuração</h3>
		<div class=\"inside\">
		<p><input type=\"checkbox\" id=\"espalhe_bm\" name=\"espalhe_bm\" value=\"checked\" {$bmOpt['espalhe']}/>
		<label for=\"espalhe_bm\">Ativar o plugin</label></p>
		<p>Twitter ID: <br>
		<input type=\"text\" name=\"twitter_bm\" size=\"60\" value=\"{$bmOpt['twitter']}\" /></p>
		<p>Feedburner URL: <br>
		<input type=\"text\" name=\"feed_bm\" size=\"60\" value=\"{$bmOpt['feed']}\" /></p>
		<p>API Encurtador: <br>
		<input type=\"text\" name=\"api_bm\" size=\"60\" value=\"{$bmOpt['api']}\" /></p>
		<p><input type=\"checkbox\" id=\"rssOpt_bm\" name=\"rssOpt_bm\" value=\"checked\" {$bmOpt['rssOpt']}/>
		<label for=\"rssOpt_bm\">Exibir número de assinantes</label></p>
		<p><input type=\"checkbox\" id=\"twitterOpt_bm\" name=\"twitterOpt_bm\" value=\"checked\" {$bmOpt['twitterOpt']}/>
		<label for=\"twitterOpt_bm\">Exibir número de seguidores</label></p>
		</div>
		</div>
		</div>
		<div id=\"poststuff\">
		<div class=\"postbox\">
		<h3 class=\"hndle\">Bookmarks</h3>
		<div class=\"inside\">
		<p>\n		";
		$n = 0;
		foreach($bmBookmarks as $class => $list){
			foreach($list as $nome => $url){
				$n++;
				echo"<input type=\"checkbox\" id=\"bm_$class\" name=\"bmarks[]\" value=\"$class\" ";
				foreach($bmMarks as $bookmarks){
					if($class == $bookmarks){
						echo'checked';
					}
				}
				echo"/><label for=\"bm_$class\"> <img src=\"{$bmurl}images/{$class}.png\" alt=\"$nome\" title=\"$nome\"/></label>\n		";
				while($n == 9) {
					echo"<br />\n		";
					$n = 0;
				}
			}
		}
		echo"</p>
		</div>
		</div>
		</div>
		<div align=\"right\">
		<input type=\"submit\" name=\"submit\" class=\"button-primary\" value=\"Salvar\" />
		</div>
		</form>
		</td>
		<td valign=\"top\" width=\"15%\">
		<div id=\"poststuff\">
		<div class=\"postbox\">
		<h3 class=\"hndle\">Faça uma doação</h3>
		<div class=\"inside\">
		<p><form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">
		<input type=\"hidden\" name=\"hosted_button_id\" value=\"BAUHVBXZACWCQ\">
		<input type=\"image\" src=\"https://www.paypal.com/pt_BR/i/btn/btn_donateCC_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\">
		<img alt=\"\" border=\"0\" src=\"https://www.paypal.com/pt_BR/i/scr/pixel.gif\" width=\"1\" height=\"1\">
		</form></p>
		</div>
		</div>
		</div>
		<div id=\"poststuff\">
		<div class=\"postbox\">
		<h3 class=\"hndle\">Divulgue</h3>
		<div class=\"inside\">
		<p><a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-url=\"http://reitor.org/wp-plugins/blogmarking/\" data-text=\"Eu indico o Blogmarking #Wp #Plugin\" data-count=\"horizontal\" data-via=\"Reitor\">Tweet</a><script type=\"text/javascript\" src=\"http://platform.twitter.com/widgets.js\"></script>
		<iframe src=\"http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Freitor.org%2Fwp-plugins%2Fblogmarking%2F&amp;layout=button_count&amp;show_faces=false&amp;width=120&amp;action=like&amp;colorscheme=light&amp;height=21\" scrolling=\"no\" frameborder=\"0\" style=\"border:none; overflow:hidden; width:120px; height:21px;\" allowTransparency=\"true\"></iframe>
		<br /><a href=\"http://reitor.org/wp-plugins/blogmarking/\" target=\"_blank\">Feedback</a> | <a href=\"http://twitter.com/Reitor\" target=\"_blank\">@Reitor</a> | <a href=\"http://reitor.org/\" target=\"_blank\">Reitor.Org</a>
		</p>
		</div>
		</div>
		</div>
		</td>
		</tr>
		</table>
		</div>";
}
function bm_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page('Blogmarking &lsaquo; Configuração', 'Blogmarking', 'manage_options', 'blogmarking', 'bmConfig');
    }
}
function bm_init(){
	global $bmurl;
	wp_register_style('bmStyle', $bmurl . 'css/blogmarking.css');
}
function bm_style(){
	wp_enqueue_style('bmStyle');
}
function bm_css() {
	global $bmurl;
	echo "\n<!-- Blogmarking// -->\n";
	echo "<link rel='stylesheet' href='{$bmurl}css/style.css' type='text/css' />\n";
	echo "<!-- \\\\Blogmarking -->\n";
}
function blogmarking($args) {
	global $bmOpt, $post, $bmBookmarks, $bmurl, $bmMarks;
	$bmSave = get_option('bmSave');
	if(is_single()){
		$titulo = $post->post_title;
		$titulo = str_replace(' ','+',$titulo);
		$resumo = $post->post_excerpt;
		$link = trim(urlencode(get_permalink($post->ID)));
		$blog = get_option('blogname');
		$s_url = str_replace('%url%',$link,$bmOpt['api']);
		$short = trim(bm_curl($s_url));
		$template = '<div class="bmcfix"></div>
		<div id="bmcss">
		<div class="bm"><span class="counters">';
		$data = strtotime(date('Y-m-d H:m:s'));
		if(!is_array($bmSave)){
			bm_save();
		}
		if($data >= $bmSave['data']){
			bm_save();
		}
		if ($bmOpt['rssOpt'] == 'checked'){
		   $template .='<a href="http://feeds.feedburner.com/'.$bmSave['fburi'].'" target="_blank" alt="Feed RSS" title="Assine você também..." class="fbcount">'.$bmSave['fbcount'].' Assinantes</a>';
		}
		if (($bmOpt['rssOpt'] == 'checked')AND($bmOpt['twitterOpt'] == 'checked')){
		   $template .=' | ';
		}		
		if ($bmOpt['twitterOpt'] == 'checked'){
		   $template .= '<a href="http://www.twitter.com/'.$bmOpt['twitter'].'" target="_blank" alt="Twitter" title="Siga-me também..." class="twtcount">'.$bmSave['twtcount'].' Seguidores</a><br />';
		}
		$template .= '</span><span class="espalhe"></span><div class="bmcfix"></div></div>
		<div class="bmcfix"></div>
		<span class="bmk">';
		foreach($bmBookmarks as $class => $list){
			foreach($list as $nome => $url){
				foreach($bmMarks as $bookmarks){
					if($class == $bookmarks){
						$template .= "<a href=\"$url\" class=\"$class\" target=\"_blank\"><img src=\"{$bmurl}images/{$class}.png\" alt=\"$nome\" title=\"$nome\"/></a>\n";
					}
				}
			}
		}
		$template .= '</span>
		</div><div class="bmcfix"></div>';
		$search = array('%titulo%','%resumo%','%link%','%short%','%blog%');
		$now = array($titulo,$resumo,$link,$short,$blog);
		$template = str_replace($search,$now,$template);
		$args = "$args \n $template \n";
	}
	return $args;
}
function bm_save() {
	global $bmOpt,$bmSave;
	$data		= strtotime(date('Y-m-d H:m:s'));
	$data		= strtotime('+6 hour',$data);
	$feedburner	= "http://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=".$bmOpt['feed'];
	$fbxml		= bm_curl($feedburner);
	$fbxml		= new SimpleXMLElement($fbxml);
	$fburi		= str_replace('SimpleXMLElement Object ( ','',$fbxml->feed['uri']);
	$fbcount	= str_replace('SimpleXMLElement Object ( ','',$fbxml->feed->entry['circulation']);
	$twitter	= "http://twitter.com/users/show/".$bmOpt['twitter'];
	$twtxml		= bm_curl($twitter);
	$twtxml		= new SimpleXMLElement($twtxml);
	$twtcount	= str_replace('SimpleXMLElement Object ( ','',$twtxml->followers_count);
	$bm_save	= array('data' => $data,'fburi' => $fburi,'fbcount' => $fbcount,'twtcount' => $twtcount);
	if((trim($fbcount) != 0)OR($bmSave['fbcount'] == '?')){
		update_option('bmSave',$bm_save);
	}
}
function feedcount_func() {
	global $bmSave;
	$count = $bmSave['fbcount'];
	return $count;
}
function twittercount_func() {
	global $bmSave;
	$count = $bmSave['twtcount'];
	return $count;
}
add_shortcode('feedcount', 'feedcount_func');
add_shortcode('twittercount', 'twittercount_func');
add_filter('widget_text', 'do_shortcode');
add_action('admin_print_styles', 'bm_style');
add_action('admin_init', 'bm_init');
add_action('admin_menu', 'bm_menu');
if($bmOpt['espalhe'] == 'checked') {
	add_action('wp_head', 'bm_css');
	add_filter('the_content','blogmarking');
}
?>