<?php
class DatabaseAccessObject{
    private $mysql_address = "";
    private $mysql_username = "";
    private $mysql_password = "";
    private $mysql_database = "";
    private $link;
    private $last_sql = "";
    private $last_id = 0;
    private $last_num_rows = 0;
    private $error_message = "";

    function __construct($mysql_address, $mysql_username, $mysql_password, $mysql_database)
    {
        $this->mysql_address=$mysql_address;
        $this->mysql_username=$mysql_username;
        $this->mysql_password=$mysql_password;
        $this->mysql_database=$mysql_database;

        $this->link=$GLOBALS["___mysqli_ston"]=mysqli_connect($this->mysql_address,$this->mysql_username,
                                                                $this->mysql_password,$this->mysql_database);
        
        if(mysqli_connect_errno())
        {
            $this->error_message="Failed to connect to MySQL: " . mysqli_connect_error();
            echo $this->error_message;
            return false;
        }

        mysqli_query($GLOBALS["___mysqli_ston"], "SET NAMES utf8");
        mysqli_query($this->link, "SET NAMES utf8");
        // if(!(bool)mysqli_query($this->link, "USE ".$this->mysql_database))
        // {
        //     $this->error_message = 'Database '.$this->mysql_database.' does not exist!';
        // }
    }

    public function __destruct() {
        mysqli_close($this->link);
    }

    public function execute($sql=null) {
        if($sql===null) return false;
        $this->last_sql=str_ireplace("DROP","",$sql);
        $result=mysqli_query($this->link,$this->last_sql);
        $result_set = array();

        if($result)
        {
            echo $sql."<br>"."成功";
            $this->last_num_rows=@mysqli_num_rows($result);
            while($row = @mysqli_fetch_assoc($result))
            {
                $result_set[]=$row;
            }
            return $result_set;
        }
        else
        {
            echo $sql."<br>"."語法有誤";
            echo "<br>";
            echo "<br>";
            echo mysqli_error($this->link);
        }
        // if裡面如果成功連線$this->link就是物件就會印mysqli_error($this->link)，因為成功連上所以沒錯誤會直接跳else，
        // 如果沒有成功連上會印$___mysqli_res = mysqli_connect_error()，有錯誤訊息就會印$___mysqli_res自己，
        // 沒有就回傳false，if一樣會跳else
        // if (((is_object($this->link)) ?
        //      mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? 
        //      $___mysqli_res : false)))

        // {
        //     echo $___mysqli_res;
        // }
        // else
        // {
        //     $this->last_num_rows=@mysqli_num_rows($result);
        //     for($xx=0 ; $xx < $this->last_num_rows ; $xx++)
        //     {
        //         $result_set[$xx] = mysqli_fetch_assoc($result);
        //     }

        //     if(isset($result_set))
        //     {
        //         return $result_set;
        //     }
        //     else{
        //         $this->error_message = "result: zero";
        //     }
            
        // }
    }

    //搜尋
    public function select($table = null, $condition = "1", $order_by = "1", $fields = "*", $limit = "")
    {
        $sql = "SELECT {$fields} FROM {$table} WHERE {$condition} ORDER BY {$order_by} {$limit}";
        return $this->execute($sql);
    }

    public function getHeroId($table,$name)
    {
    $sql = "SELECT * FROM {$table} WHERE `hero_name` = '{$name}'";
    $hero=$this->execute($sql);
    return $hero[0]["id"];
    }
    //新增
    public function insert($table=null,$data_array = array())
    {
        if( $table === null ) return false;
        if( count($data_array)==0) return false;

        $tmp_col=array();
        $tmp_dat=array();

        foreach ($data_array as $key => $value) {
            $tmp_col[]="`{$key}`";
            $tmp_dat[]="'{$value}'";
        }
        $columns = join(",", $tmp_col);
        $data = join(",", $tmp_dat);

        $this->last_sql="INSERT INTO `{$table}` ($columns) VALUE ($data)";
        $result=mysqli_query($this->link,$this->last_sql);

        if($result)
        {
            echo $this->last_sql."<br>"."成功";
        }
        else
        {
            echo $this->last_sql."<br>"."語法有誤";
            echo "<br>";
            echo "<br>";
            echo mysqli_error($this->link);
        }

    }

    public function update($table = null, $data_array = null, $key_column = null, $id = null)
    {
        if( $table === null || $id === null || $key_column === null) return false;
        if( count($data_array) ==0 || count($key_column) ==0) return false;
 
        $x=0;
        $setting_list='';
        foreach ($data_array as $key => $value) {
            $x = $x+1;
            $setting_list .= "`{$key}` = '{$value}'";
            if($x < count($data_array) -1)
            {
                $setting_list .= ",";
            }
        }

     
        $this->last_sql="UPDATE `{$table}` SET {$setting_list}
        WHERE `{$key_column}` = {$id};";
        $result=mysqli_query($this->link,$this->last_sql);
        if($result)
        {
            echo $this->last_sql."<br>"."成功";
        }
        else
        {
            echo $this->last_sql."<br>"."語法有誤";
            echo "<br>";
            echo "<br>";
            echo mysqli_error($this->link);
        }

    }

    public function delete($table = null, $key_column = null, $id = null) {
        if ($table===null) return false;
        if($id===null) return false;
        if($key_column===null) return false;

        return $this->execute("DELETE FROM $table WHERE `{$key_column}` = $id ");
    }

        /**
     * @return string
     * 這段會把最後執行的語法回傳給你
     */
    public function getLastSql() {
        return $this->last_sql;
    }

    /**
     * @param string $last_sql
     * 這段是把執行的語法存到變數裡，設定成 private 只有內部可以使用，外部無法呼叫
     */
    private function setLastSql($last_sql) {
        $this->last_sql = $last_sql;
    }

    /**
     * @return int
     * 主要功能是把新增的 ID 傳到物件外面
     */
    public function getLastId() {
        return $this->last_id;
    }

    /**
     * @param int $last_id
     * 把這個 $last_id 存到物件內的變數
     */
    private function setLastId($last_id) {
        $this->last_id = $last_id;
    }

    /**
     * @return int
     */
    public function getLastNumRows() {
        return $this->last_num_rows;
    }

    /**
     * @param int $last_num_rows
     */
    private function setLastNumRows($last_num_rows) {
        $this->last_num_rows = $last_num_rows;
    }

    /**
     * @return string
     * 取出物件內的錯誤訊息
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @param string $error_message
     * 記下錯誤訊息到物件變數內
     */
    private function setErrorMessage($error_message)
    {
        $this->error_message = $error_message;
    }


}
