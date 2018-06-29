<?php
namespace helper;

class Page{

	static public function set($total, $num, $page, $url, $mod=0, $span='6', $prev='|<', $next='>|') {
		$page = $page ? $page : 1;
		$tal = ceil($total/$num);
		if($tal<=1) return;
		$sep = (preg_match('/\?/',$url)) ? '&' : '?';
		$url = $url.$sep;
		$ret = '';
		if ($page>1) $ret.= ' <li class="paginate_button"><a href="'.$url.'page='.($page-1).'" class="prev">'.$prev.'</a></li> ';
		$ns = '';
		if ($page > $span) $ns.= ' <li><a href="'.$url.'">1 ...</a></li> ';
		for($i=$page-$span+1; $i<$page+$span; $i++) {
			if ($i<=0 || $i>$tal) continue;
			if ($page == $i)
				$ns.= ' <li class="paginate_button active"><a href="javascript:;" >'.$i.'</a></li> ';
			else
				$ns.= ' <li class="paginate_button"><a href="'.$url.'page='.$i.'">'.$i.'</a></li> ';
		}
		if ($tal-$page>$span-1) $ns.= ' <li class="paginate_button"><a href="'.$url.'page='.$tal.'">... '.$tal.'</a></li> ';
		$ret.= $ns;
		if ($page<$tal) $ret.= ' <li class="paginate_button"><a href="'.$url.'page='.($page+1).'" class="nxt">'.$next.'</a></li> ';
		if($mod=='2' || $mod=='3') $ret .= '<input type="text" name="custompage" class="txt-input" size="3" onkeydown="if(event.keyCode==13) {window.location=\''.$url.'page=\'+this.value; doane(event);}">';
		$ret.='';
		return $ret;
	}
}