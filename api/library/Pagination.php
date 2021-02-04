<?php
class Pagination extends DB
{
    private static $table = null;
    private static $limit = 15;
    private static $offset = 0;
    private static $page = 1;
    private static $condition = null;
    private static $query = null;
    private static  $start, $end, $total, $hasNext, $hasPrevious, $pages, $haspages;

    /* 
    paging method
    @param $table -> the table to get data from
    @param $limit -> How many items to list per page
    */
    
    function __construct() {
        parent::__construct();
    }

    public function table($table){
        self::$table = $table;
    }

    public function result_per_page($limit){
        self::$limit = $limit;
    }

    public function pageNum($page){
        self::$page = $page;
    }

    public function lastQuery(){
        return self::$query;
    }

    public function rawQuery($query){
        self::$query = $query;
    }

    public function condition($condition){
        self::$condition = $condition;
    }
    
    public function reset(){
        self::$table = null;
        self::$limit = null;
        self::$condition = null;
    }

    public function paging(){
        return array(
            "haspages" => self::$haspages, //has pages
            "start" => self::$start, // record starts from 
            "end" => self::$end, //records ends in
            "total" => self::$total, // total record in system
            "page" => self::$page, //current page number
            "pages" => self::$pages, //total pages in the system
            "hasNext" => self::$hasNext, // has next page?
            "hasPrevious" => self::$hasPrevious, //has previous page?
        );
    }

    public function execute(){
        if (self::$table === null && self::$query===null) {
            throw new Exception("Please provide query or table name", 1);
        }

        try {
            if (self::$page>1) {
                self::$offset = ((int)self::$page-1) * (int)self::$limit;
            }
            $condition = (self::$condition)?self::$condition:"";
            // Find out how many items are in the table
            
            if (self::$query!==null) {
                $this->query(self::$query);
            }else{
                $this->query("SELECT * FROM ".self::$table." $condition");
            }
            $total = $this->count;
            // How many pages will there be
            $pages = ceil($total / self::$limit);

            // What page are we currently on?
            $page = (self::$page<$pages)?self::$page:$pages;

            // Some information to display to the user
            $start = self::$offset + 1;
            $end = min((self::$offset + self::$limit), $total);
            
			/*************************************************************************************************/
            self::$haspages = ($page < $pages) || ($page > 1)?true:false;
            self::$start = $start;
            self::$end = $end;
            self::$total = $total;
            self::$page = (int)($page);
            self::$pages = $pages;
            self::$hasNext = $page < $pages;
            self::$hasPrevious = $page > 1;

            if (self::$query!==null){
                $this->query(self::$query.' LIMIT '.self::$limit.' OFFSET '.self::$offset.'' );
            }else{
                // Prepare the paged query
                if (self::$condition!==null) {
                    self::$query = 'SELECT  * FROM  '.self::$table.' '.self::$condition.' LIMIT '.self::$limit.' OFFSET '.self::$offset.'' ;
                    $this->query(self::$query);
                }else{
                    self::$query = 'SELECT  * FROM  '.self::$table.' LIMIT '.self::$limit.' OFFSET '.self::$offset.'' ;
                    $this->query(self::$query);
                }
            }
            
        } catch (Exception $e) {
			catch_sql_error($e->getMessage());
        }
    }
}