<?php
/*
 * Author : 
 * Date   : 
 */
class mysql
{
	protected $connection;
	protected $hostname;
	protected $database;
	protected $username;
	protected $password;
	function __construct($dbhost=null, $dbname=null, $dbuser=null, $dbpass=null) 
	{
		$this->database = MYSQL_DB_NAME;
		$this->hostname = MYSQL_DB_HOST;
		$this->username = MYSQL_DB_USER;
		$this->password = MYSQL_DB_PASSWORD;
		
	}
	public function open() 
	{
		if(is_null($this->database)) 
			die("MySQL database not selected");
		if(is_null($this->hostname)) 
			die("MySQL hostname not set");
		$this->connection = @mysqli_connect($this->hostname, $this->username, $this->password);
                if($this->connection === false) 
			die("Could not connect to database. Check your username and password then try again.\n");
		if(!mysqli_select_db($this->connection, $this->database))
			die("Could not select database");
                
	}
        public function getRealExcapeString($value){
           return mysqli_real_escape_string($this->connection,$value);
        }
	public function close() 
	{
		if($this->connection)
		{
			mysqli_close($this->connection);
			$this->connection = null;
		}
	}
	public function affectedRows() 
	{
		return mysqli_affected_rows($this->connection);
	}
	public function insertId() 
	{
		return mysqli_insert_id($this->connection);
	}
	public function numRows($result) 
	{
		return mysqli_num_rows($result);
	}
	public function insert($sql) 
	{
		if($this->connection === false) 
		{
			die('No Database Connection Found.');
		}
		$result=@mysqli_query($sql,$this->connection);
		if($result === false) 
		{
			die(mysqli_error());
		}
	}
	public function query($sql) 
	{
		if($this->connection === false) 
		{
			die('No Database Connection Found.');
		}
		$result = @mysqli_query($this->connection,$sql);
		if($result === false) 
		{
			die(mysqli_error());
		}
		return $result;
	}
	public function fetchArray($result) 
	{

		if ($this->connection === false) 
		{
			die('No Database Connection Found.');
		}
		$i=0;
		$temp=array();
		while($data = @mysqli_fetch_array($result))
		{
			$temp[$i]=$data;
			$i++;
		}
		if (!is_array($temp)) 
		{
			die(mysqli_error());
		}
		return $temp;
	}
	function selectQuery($sql) 
	{
		$this->open();
		$results = $this->fetchArray($this->query($sql));
		$this->close();
		return $results;
	}
	function insertQuery($sql) 
	{
		$this->open();
		$this->query($sql);
		$primary_key = $this->insertId();
		$this->close();
		return $primary_key;
	}
	function executeQuery($sql) 
	{
		$this->open();
		$results = $this->query($sql);
		$this->close();
		return $results;
	}
	
	/**
	 * Function inserts a record into a table.
	 * Input @param: Table name, Associative array of record [Field name as array
	 * key & field value as array value].
	 * @return ID of record inserted if it succeeds, else returns FALSE
	 */
    function insertRecord($tableName, $arrRecord)
    {
        $tableName = trim($tableName);

        if (empty($tableName) || empty($arrRecord) || !is_array($arrRecord))
            return FALSE;

		$this->open();
        
        $fieldList = $valueList = '';
        foreach ($arrRecord as $fieldName => $fieldValue) {
            $fieldList .= $fieldName . ',';
            if (is_string($fieldValue))
                $valueList .= "'" . mysqli_real_escape_string($this->connection,$fieldValue) . "',";
            else
                $valueList .= $fieldValue . ',';
        }
        $fieldList = substr($fieldList, 0, -1);
        $valueList = substr($valueList, 0, -1);
        
        $insertQuery = 'INSERT INTO ' . $tableName .  ' (' . $fieldList . ') '.
        'VALUE (' . $valueList . ');';
        $this->query($insertQuery);
        
        $primary_key = $this->insertId();
		$this->close();
		if($primary_key)
			return $primary_key;
        return FALSE;
    
	
    
      /*  if (is_array($dataArray)) {
            
                $query                       = "INSERT INTO $tablename SET ";
                $arrayCount                = sizeof($dataArray);
                $count                    = 1;
            while (list($key,$val)   = each($dataArray)) {
                        
                if ($count==$arrayCount) {
                        $query .=" $key='$val'";
                } else {
                        $query .="$key='$val', ";
                }
                        $count ++;    
            }//End Of while loop 

			//echo $query."<br>";       
			
			$this->open();
			mysqli_query($query);//Calling the execute query 
			//echo $query;exit;
			$insid=mysqli_insert_id();
			$this->close();
			return $insid;
                        
        }//end Of if loop*/
    
	}
	
	/**
     * Parses MySQL resultset to record set in selected format.
     * @param MySQL resultset $resultSet
     * @param Const $outputFormat
     * @return record set | FALSE on error
     */
    public static function parseResultSet($resultSet, $outputFormat = self::OBJ)
    {$this->open();
        if (!is_object($resultSet) || $resultSet->num_rows == 0) {
            if (is_object($resultSet)) $resultSet->close();
            return FALSE;
        }
        
        switch ($outputFormat) {
            case self::ARR:
                $recordSet = array();
                while ($row = $resultSet->fetch_assoc()) {
                    $recordSet[] = $row;
                }
                break;
            case self::OBJ:
                $recordSet = array();
                while ($obj = $resultSet->fetch_object()) {
                    $recordSet[] = $obj;
                }
                break;
            case self::VAL:
                $row = $resultSet->fetch_row();
                $recordSet = $row[0];
        }
        
        $resultSet->close();
        $this->close();
        return $recordSet;
		
    }
	function getComboValues($name,$value,$curvalue,$table,$condition="",$caption="") 
	{

        $query    = "SELECT DISTINCT $name as f1,$value as f2 
        FROM $table $condition";
        //echo     $query;
        $this->open();
        $result    = $this->executeQuery($query);
        $option ="";    
        if (mysqli_num_rows($result)) {
            
            if ($caption<>1) {
                if ($caption=="nocaption") {
                        $option ="";
                } elseif ($caption!="") {
                        $option ="<option value='0'>$caption</option>";
                } else {
                     
                        $option ="<option value=''>Select one</option>";
                }
            }
            while ($row=mysqli_fetch_object($result)) {
                if ($curvalue==$row->f2) {
                    //echo "<br>$curvalue:" . $row->f2;
                    $option .= "<option value='$row->f2' selected>". 
                    ($row->f1) . "</option>";    //strtolower//ucwords(
                } else {
                    $option .= "<option value='$row->f2'>". ($row->f1) .
                     "</option>";    //strtolower    
                }
            }
        }
		$this->close();
         return $option;
    }
	function getTableValue($field,$table,$condition="")
    {
        $query = "SELECT $field as f1 FROM $table ";
		 $this->open();
        if ($condition<>"") {
            $query.=" WHERE $condition";
        }
            //echo "<BR>" . $query;
        $result    = $this->executeQuery($query);
        if (mysqli_num_rows($result)) {
            $row    = mysqli_fetch_object($result);
            $f1        = $row->f1;
        } else {
            $f1    =    "";
        } 
		$this->close();
        return stripslashes($f1);
		  
    }
	
	    function getTableValuesArray($table,$condition="")
    	{
			$query    = "SELECT * FROM $table ";
			if ($condition<>"") {
				$query .=" WHERE $condition";  
        	}  
               //echo "<BR>" . $query;    exit;
            $this->open();
			$result    = $this->executeQuery($query);
			if (mysqli_num_rows($result)) {
				$row1    = mysqli_fetch_array($result);
			}
				return $row1;
            $this->close();
    	}

	 function countRows($result)
     {$this->open();
        if (is_resource($result)) {
		$this->close();
                 return mysqli_num_rows($result);
        }
     }
	    function readValues($qry)
   		 {//echo $qry;exit;
			$ary = array();
			$i = 0;
			$this->open();
			$q = mysqli_query($qry) or $q="";
			if ($q!="") {
				while ($row = mysqli_fetch_array($q)) {
					$ary[$i] = $row;
					$i++;
				}
			}
			$this->close();
			return $ary;
			
			
   	 }
	 /** Query to update table data     
    *
    * @param tablename $tablename tablename
    * @param dataArray $dataArray dataarray 
    * @param condition $condition condition
    *
    * @return string
    */
    function updateQuery($tablename,$dataArray,$condition="")
    {
    $this->open();
        if (is_array($dataArray)) {
                $query                 = "UPDATE  $tablename SET ";
                $arrayCount            = sizeof($dataArray);
                $count                = 1;
            while (list($key,$val)  = each($dataArray)) {
                
                if ($count==$arrayCount) {
                    $query .=" $key='$val'";
                } else {
                    $query .=" $key='$val', ";
                }
                    $count ++;    
            }//End Of while loop        
                
            if ($condition!="") {
                $query   .= " WHERE  $condition ";
            }//end of if 
                        
             $result = $this->executeQuery($query);//Calling the execute query 
                        
        }
		$this->close();
		if ($result) {
		
		//return mysqli_affected_rows();	
		return $result;	
		}
		
		 
    }
//additional
 function getTableValuesArray1($table,$condition="")
    {
        $query    = "SELECT * FROM $table ";
        if ($condition<>"") {
            $query .=" WHERE $condition";  
        }  
               //echo "<BR>" . $query;    exit;
        $this->open();
        $result    = $this->query($query);
		
        if (mysqli_num_rows($result)) {
            $row1    = $this->fetchArray($result);
        }
		$this->close();
        return $row1;
        
    }
 function readValuesAssoc($qry)
    {//echo $qry;exit;
        $ary = array();
        $i = 0;
		$this->open();
        $q = mysqli_query($qry) or $q="";
        if ($q!="") {
            while ($row = mysqli_fetch_assoc($q)) {
                $ary[$i] = $row;
                $i++;
            }
        }
		$this->close();
        return $ary;
    }	
 function getTableValuesMultiArray($table,$condition="")
    {
         $ary = array();
        $i     = 0;
        $query    = "SELECT * FROM $table ";
        if ($condition<>"") {
            $query .=" WHERE $condition";    
        }
          //   echo "<BR>" . $query;    
        $this->open();
        $result    = $this->query($query);
		
        if (mysqli_num_rows($result)) {
            while ($row = mysqli_fetch_array($result)) {
                $ary[$i] = $row;
                $i++;
            }
        
        }
		$this->close();
        return $ary;
		//print_r($ary);
        
    }
	function readValue($Query)
	{
		$ResultData		=	array();
		$this->open();
		$ResultSet		=	mysqli_query($Query);
		
		if($ResultSet) {
			$ResultData[0]	=	mysqli_fetch_array($ResultSet); 	
			mysqli_free_result($ResultSet);
			$this->close();	
			return $ResultData[0];
		} else {
			$this->ErrorInfo	=	mysqli_error();
			$this->close();	
			return $ResultData;
		}	
		
	}
public function getRowsCount($tablename, $condition="")
	{
	  $this->open();
		$result = $this->SelectQuery("SELECT COUNT(*) AS rows FROM `".$tablename."` WHERE $condition");
		$resCount=(int) $result[0]['rows'];
                $this->close();	
		return $resCount;
		
	}
 function numberOfRecords($Query)
    {
        $RowCount    =    0;
		
		$this->open();
        $ResultSet    =   mysqli_query($Query);
		
        if ($ResultSet) {
            $RowCount    =     mysqli_num_rows($ResultSet);
            mysqli_free_result($ResultSet);
			$this->close();
            return $RowCount;
        } else {
            $this->ErrorInfo    =    mysqli_error();
			$this->close();
            return $RowCount;
        }
    } 
 function insert_query($tablename,$dataArray)
    {if (is_array($dataArray)) {
            
                $query                       = "INSERT INTO $tablename SET ";
                $arrayCount                = sizeof($dataArray);
                $count                    = 1;
            while (list($key,$val)   = each($dataArray)) {
                        
                if ($count==$arrayCount) {
                        $query .=" $key='$val'";
                } else {
                        $query .="$key='$val', ";
                }
                        $count ++;    
            }//End Of while loop 

			//echo $query."<br>";       
			
			$this->open();
			mysqli_query($query);//Calling the execute query 
			//echo $query;exit;
			$insid=mysqli_insert_id();
			$this->close();
			return $insid;
                        
        }//end Of if loop*/
		}
	 function deleteQuery($tablename,$condition="")
    {
        $query = "DELETE FROM $tablename ";
        $this->open();
        if ($condition!="") {
            $query       .= " WHERE $condition";
        }
		
        $this->executeQuery($query);//Calling the execute query 
    	$this->close();
    } 
	function getComboValuesWithQuery($query,$curvalue,$caption="")
    {
        
        $this->open();
		
		$result    = mysqli_query($query, $this->connection);
        if ($caption) {
                $option = "<option value=''>$caption</option>";
        } else {
            $option = "";
        }
        
        if (mysqli_num_rows($result)) {        
            while ($row=mysqli_fetch_object($result)) {
                if ($curvalue==$row->f2) {
                    
                    $option .= "<option value='$row->f2' selected=\"selected\">". 
                    stripslashes($row->f1)."</option>";   
					/* $option .= "<option value='$row->f2' selected=\"selected\"><strong>". 
                    stripslashes($row->f1)."</strong></option>";   */  
                } else {
                    $option .= "<option value='$row->f2'>". 
                    stripslashes($row->f1)."</option>";
                }
            }
        }
		$this->close();
         return $option;
    }
}
?>
