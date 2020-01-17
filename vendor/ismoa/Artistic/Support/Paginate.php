<?php

class Paginate
{
    private $page   = 0;
    private $start  = 0;
    private $end    = 0;
    private $option = array();
    private $solv   = array();
    private $ui     = array();
    private $qrst   = '';
    private $url    = '';

    use \Artistic\Traits\Singleton;

    private function __construct()
    {
        $this->qrst = $this->url = '';
        $this->page = isset($_GET['page']) ? $_GET['page'] : 1;

        $this->ui = array('first' => '','prev_block' => '', 'prev' => '', 'page' => '', 'next' => '', 'next_block' => '', 'last' => '');
        $this->loadOption();
        $this->parseUrl();
    }

    private function loadOption()
    {
        $option = config('paginate');

        $this->option['first_page'] = (isset($option['first_page']))    ? $option['first_page'] : true;
        $this->option['prev_block'] = (isset($option['prev_block']))    ? $option['prev_block'] : true;
        $this->option['prev_page']  = (isset($option['prev_page']))     ? $option['prev_page']  : true;
        $this->option['next_page']  = (isset($option['next_page']))     ? $option['next_page']  : true;
        $this->option['next_block'] = (isset($option['next_block']))    ? $option['next_block'] : true;
        $this->option['last_page']  = (isset($option['last_page']))     ? $option['last_page']  : true;
    }

    private function parseUrl()
    {
        $uri = parse_url($_SERVER['REQUEST_URI']);

        if (isset($uri['query'])) {
            parse_str($uri['query'], $qrst);

            if (isset($qrst['page'])) unset($qrst['page']);
            $this->qrst = (count($qrst) > 0) ? '&' . http_build_query($qrst) : '';
        }

        $this->url = $uri['path'].'?page=';
    }

    private function solvPaginate($tcount, $lcount, $bcount)
    {
        $this->page     = ($this->page < 1) ? 1 : $this->page;
        $block          = ceil($tcount / $lcount);
        $tblock         = ceil($block / $lcount);
        $current        = ceil($this->page / $bcount);
        $prev           = (($prev = $current - 1) < 1) ? 1 : $prev;
        $next           = (($next = $current + 1) > $block) ? $block : $next;
        $this->start    = (($current - 1) * $lcount) + 1;
        $this->end      = (($end = $this->start + $bcount - 1) > $block) ? $block: $end;
        $next_block     = (($next - 1) * $lcount) + 1;

        $this->solv['prev_block'] = (($prev - 1) * $lcount) + 1;
        $this->solv['next_block'] = ($next_block > $block) ? $block :$next_block;
        $this->solv['prev'] = (($prev = $this->page - 1) < 1) ? 1 : $prev;
        $this->solv['next'] = (($next = $this->page + 1) > $block) ? $block : $next;
        $this->solv['last'] = $block;

        $this->buildPaginate();
    }

    private function buildQueryString($querystring)
    {
        $this->qrst = (strlen($qrst = http_build_query($querystring)) > 0) ? '&' . $qrst : $this->qrst;
    }

    private function buildPaginate()
    {
        if (true == $this->option['first_page']) {
            $this->ui['first'] = '<a href="' . $this->url . '1' . $this->qrst  .'" class="ui-page page-first">First</a>';
        }

        if (true == $this->option['prev_block']) {
            $this->ui['prev_block'] = '<a href="'.$this->url . $this->solv['prev_block']. $this->qrst.'" class="ui-page page-prev-block">Previous Block</a>';
        }
        if (true == $this->option['prev_page']) {
            $this->ui['prev'] = '<a href="'.$this->url . $this->solv['prev']. $this->qrst.'" class="ui-page page-prev">Previous Page</a>';
        }

        if (true == $this->option['next_page']) {
            $this->ui['next'] = '<a href="'.$this->url . $this->solv['next']. $this->qrst.'" class="ui-page page-next">Next Page</a>';
        }

        if (true == $this->option['next_block']) {
            $this->ui['next_block'] = '<a href="'.$this->url . $this->solv['next_block']. $this->qrst.'" class="ui-page page-next-block">Next Block</a>';
        }

        if (true == $this->option['last_page']) {
            $this->ui['last'] = '<a href="'.$this->url . $this->solv['last']. $this->qrst.'" class="ui-page page-next-last">Last Page</a>';
        }

        for ($i = $this->start; $i <= $this->end; $i++) {
            $current = ($i == $this->page) ? ' page-current' : '';
            $this->ui['page'] .= '<a href="'.$this->url . $i. $this->qrst.'" class="ui-page' .$current. '"> '. $i . '</a>';
        }
    }

    public function paginate($tcount = 0, $lcount = 10, $bcount = 3, array $querystring)
    {
        if (count($querystring) > 0) $this->buildQueryString($querystring); 

        if ($tcount < 1) return '';
        $this->solvPaginate($tcount, $lcount, $bcount);

        return implode('', $this->ui);
    }
}