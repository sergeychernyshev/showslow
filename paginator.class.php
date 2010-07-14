<?php

class Paginator{
	var $items_per_page;
	var $items_total;
	var $current_page;
	var $num_pages;
	var $mid_range;
	var $return;
	var $default_ipp = 50;
	var $querystring;

	function Paginator()
	{
		$this->current_page = 1;
		$this->mid_range = 7;
		$this->items_per_page = $this->default_ipp;
	}

	function paginate($base)
	{
		$this->num_pages = ceil($this->items_total/$this->items_per_page);
		$this->current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // must be numeric > 0
		if($this->current_page < 1 Or !is_numeric($this->current_page)) $this->current_page = 1;
		if($this->current_page > $this->num_pages) $this->current_page = $this->num_pages;
		$prev_page = $this->current_page-1;
		$next_page = $this->current_page+1;

		if($_GET)
		{
			foreach($_GET as $key => $value)
			{
				if ($key != "page") {
					$this->querystring .= '&' . urlencode($key) . '=' . urlencode($value);
				}
			}
		}

		if($this->num_pages > 10)
		{
			$this->return = ($this->current_page != 1 And $this->items_total >= 10)
				? '<a class="paginate" href="' . htmlentities($base) .
					'?page='.urlencode($prev_page) .
					$this->querystring . '">&laquo; Previous</a> '
				: '<span class="inactive" href="#">&laquo; Previous</span> ';

			$this->start_range = $this->current_page - floor($this->mid_range/2);
			$this->end_range = $this->current_page + floor($this->mid_range/2);

			if($this->start_range <= 0)
			{
				$this->end_range += abs($this->start_range)+1;
				$this->start_range = 1;
			}
			if($this->end_range > $this->num_pages)
			{
				$this->start_range -= $this->end_range-$this->num_pages;
				$this->end_range = $this->num_pages;
			}
			$this->range = range($this->start_range,$this->end_range);

			for($i=1;$i<=$this->num_pages;$i++)
			{
				if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= " ... ";
				// loop through all pages. if first, last, or in range, display
				if($i==1 Or $i==$this->num_pages Or in_array($i,$this->range))
				{
					$this->return .= ($i == $this->current_page
						? '<a title="Go to page '.htmlentities($i).' of $this->num_pages" class="current" href="#">'.htmlentities($i).'</a> '
						: '<a class="paginate" title="Go to page '.htmlentities($i).' of '.htmlentities($this->num_pages).'" href="'.$base.'?page='.htmlentities($i).$this->querystring.'">'.htmlentities($i).'</a> ');
				}
				if($this->range[$this->mid_range-1] < $this->num_pages-1 And $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
			}
			$this->return .= (($this->current_page != $this->num_pages And $this->items_total >= 10))
				? '<a class="paginate" href="'.$base.'?page='.htmlentities($next_page).$this->querystring.'">Next &raquo;</a>'."\n"
				: '<span class="inactive" href="#">&raquo; Next</span>'."\n";
		}
		else
		{
			for($i=1;$i<=$this->num_pages;$i++)
			{
				$this->return .= ($i == $this->current_page)
					? '<a class="current" href="#">'.htmlentities($i).'</a> '
					: '<a class="paginate" href="'.$base.'?page='.htmlentities($i).$this->querystring.'">'.htmlentities($i).'</a> ';
			}
		}
	}

	function display_pages()
	{
		if ($this->num_pages <= 1) {
			return '';
		}
		return $this->return;
	}
}
