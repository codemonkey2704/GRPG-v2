<?php
declare(strict_types=1);
/*
 * PHP Pagination Class.
 *
 * @author admin@catchmyfame.com - https://www.catchmyfame.com
 *
 * @version 3.0.0
 * @date February 6, 2014
 *
 * @copyright (c) admin@catchmyfame.com (www.catchmyfame.com)
 * @license CC Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0) - https://creativecommons.org/licenses/by-sa/3.0/
*/
if (!defined('GRPG_INC')) {
    exit;
}
class Paginator
{
    public int $current_page;
    public int $items_per_page;
    public int $limit_end;
    public int $limit_start;
    public float $num_pages;
    public int $total_items;
    public string $limit;
    protected array $ipp_array;
    protected int $mid_range;
    protected string $querystring;
    protected string $return;
    protected int $default_ipp;
    protected int $start_range;
    protected int $end_range;
    protected array $range;

    public function __construct($total = 0, $mid_range = 5, $ipp_array = [10, 25, 50, 100, 'All'])
    {
        $this->total_items = (int) $total;
        if ($this->total_items <= 0) {
            $this->return = '';

            return;
        }
        $this->mid_range = (int) $mid_range; // midrange must be an odd int >= 1
        if ($this->mid_range % 2 === 0 or $this->mid_range < 1) {
            $this->return = '';

            return;
        }
        if (!is_array($ipp_array)) {
            $this->return = '';

            return;
        }
        $this->ipp_array = $ipp_array;
        $this->items_per_page = $_GET['ipp'] ?? $this->ipp_array[1];
        $this->default_ipp = $this->ipp_array[1];
        if ($this->items_per_page === 'All') {
            $this->num_pages = 1;
        } else {
            if (!is_numeric($this->items_per_page) or $this->items_per_page <= 0) {
                $this->items_per_page = $this->ipp_array[0];
            }
            $this->num_pages = (float)ceil($this->total_items / $this->items_per_page);
        }
        $this->current_page = array_key_exists('page', $_GET) && ctype_digit($_GET['page']) ? $_GET['page'] : 1; // must be numeric > 0
        if ($_GET) {
            $args = explode('&', $_SERVER['QUERY_STRING']);
            foreach ($args as $arg) {
                $keyval = (array)explode('=', $arg);
                if ($keyval[0] !== 'page' and $keyval[0] !== 'ipp') {
                    $this->querystring .= '&'.$arg;
                }
            }
        }
        if ($_POST) {
            foreach ($_POST as $key => $val) {
                if (!is_array($val) && 'page' !== $key and 'ipp' !== $key) {
                    $this->querystring .= '&'.$key.'='.$val;
                }
            }
        }
        if ($this->num_pages > 10) {
            $this->return .= ($this->current_page > 1 and $this->total_items >= 10) ? '<div class="pure-u-1-12"><a class="pure-button button-xsmall pure-button-disabled" href="'.$_SERVER['PHP_SELF'].'?page='.($this->current_page - 1).'&ipp='.$this->items_per_page.$this->querystring.'" disabled>Previous</a></div>' : '<div class="pure-u-1-12 disabled"><a class="pure-button button-xsmall pure-button-disabled" href="#" disabled>Previous</div> ';
            $this->start_range = $this->current_page - floor($this->mid_range / 2);
            $this->end_range = $this->current_page + floor($this->mid_range / 2);
            if ($this->start_range <= 0) {
                $this->end_range += abs($this->start_range) + 1;
                $this->start_range = 1;
            }
            if ($this->end_range > $this->num_pages) {
                $this->start_range -= $this->end_range - $this->num_pages;
                $this->end_range = $this->num_pages;
            }
            $this->range = range($this->start_range, $this->end_range);
            for ($i = 1; $i <= $this->num_pages; ++$i) {
                if ($this->range[0] > 2 && $i == $this->range[0]) { // loop through all pages. if first, last, or in range, display
                    $this->return .= ' ... ';
                }
                if (in_array($i, [1, $this->num_pages, $this->range])) {
                    $this->return .= ($i == $this->current_page && $this->items_per_page !== 'All') ? '<div class="pure-u-1-24 disabled"><a class="pure-button button-xsmall pure-button-disabled" title="Go to page '.$i.' of '.$this->num_pages.'" href="#" disabled>'.$i.'</a></div>'."\r\n" : '<div class="pure-u-1-24"><a class="pure-button button-xsmall" title="Go to page '.$i.' of '.$this->num_pages.'" href="'.$_SERVER['PHP_SELF'].'?page='.$i.'&ipp='.$this->items_per_page.$this->querystring.'">'.$i.'</a></div>'."\r\n";
                }
                if ($this->range[$this->mid_range - 1] < $this->num_pages - 1 && $i == $this->range[$this->mid_range - 1]) {
                    $this->return .= '<div class="pure-u-1-24 disabled"><a href="#" class="pure-button button-xsmall pure-button-disabled" disabled>...</a></div>';
                }
            }
            $this->return .= (($this->current_page < $this->num_pages && $this->total_items >= 10) && ($this->items_per_page !== 'All') && $this->current_page > 0) ? '<div class="pure-u-1-12"><a class="pure-button button-xsmall" href="'.$_SERVER['PHP_SELF'].'?page='.($this->current_page + 1).'&ipp='.$this->items_per_page.$this->querystring.'">Next</a></div>'."\r\n" : '<div class="pure-u-1-12 disabled"><a class="pure-button button-xsmall pure-button-disabled" href="#" disabled>Next</a></div>'."\r\n";
            $this->return .= ($this->items_per_page === 'All') ? '<div class="pure-u-1-12 disabled"><a class="pure-button button-xsmall pure-button-disabled" href="#" disabled>All</a></div>'."\r\n" : '<div class="pure-u-1-12"><a class="pure-button button-xsmall" href="'.$_SERVER['PHP_SELF'].'?page=1&ipp=All'.$this->querystring.'">All</a></div>'."\r\n";
        } else {
            for ($i = 1; $i <= $this->num_pages; ++$i) {
                $this->return .= ($i == $this->current_page) ? '<div class="pure-u-1-24 disabled"><a class="pure-button button-xsmall pure-button-disabled" href="#" disabled>'.$i.'</a></div>' : '<div class="pure-u-1-24"><a class="pure-button button-xsmall" href="'.$_SERVER['PHP_SELF'].'?page='.$i.'&ipp='.$this->items_per_page.$this->querystring.'">'.$i.'</a></div>'."\r\n";
            }
            $this->return .= '<div class="pure-u-1-12"><a class="pure-button button-xsmall'.($this->items_per_page === 'All' ? ' pure-button-disabled' : '').'" href="'.$_SERVER['PHP_SELF'].'?page=1&ipp=All'.$this->querystring.'">All</a></div>'."\r\n";
        }
        $this->return = str_replace('&', '&amp;', $this->return);
        if ($this->current_page < 1) {
            $this->items_per_page = 0;
        }
        $this->limit_start = $this->current_page > 0 ? ($this->current_page - 1) * (is_numeric($this->items_per_page) ? $this->items_per_page : 0) : 0;
        $this->limit_end = ($this->items_per_page === 'All') ? (int) $this->total_items : (int) $this->items_per_page;
        $this->limit = ' LIMIT '.$this->limit_start.', '.$this->limit_end;
    }

    public function display_items_per_page(): string
    {
        $items = null;
        natsort($this->ipp_array); // This sorts the drop down menu options array in numeric order (with 'all' last after the default value is picked up from the first slot
        foreach ($this->ipp_array as $ipp_opt) {
            $items .= ($ipp_opt == $this->items_per_page) ? '<option selected value="'.$ipp_opt.'">'.$ipp_opt.'</option>'."\r\n" : '<option value="'.$ipp_opt.'">'.$ipp_opt.'</option>'."\r\n";
        }

        return '<div class="pure-u-1-6">Items per page:
            <select onchange="window.location=\''.$_SERVER['PHP_SELF'].'?page=1&amp;ipp=\'+this[this.selectedIndex].value+\''.$this->querystring.'\';return false">'.$items.'</select>
        </div>'."\r\n";
    }

    public function display_jump_menu(): string
    {
        $option = null;
        for ($i = 1; $i <= $this->num_pages; ++$i) {
            $option .= $i == $this->current_page ? '<option value="'.$i.'" selected>'.$i.'</option>'."\r\n" : '<option value="'.$i.'">'.$i.'</option>'."\r\n";
        }

        return '<div class="pure-u-1-6">Page:
            <select onchange="window.location=\''.$_SERVER['PHP_SELF'].'?page=\'+this[this.selectedIndex].value+\'&amp;ipp='.$this->items_per_page.$this->querystring.'\';return false">'.$option.'</select>
        </div>'."\r\n";
    }

    public function display_pages(): string
    {
        return '<div class="pure-g">'.$this->return.'</div>';
    }
}
