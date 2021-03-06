<?php
require_once("conn.php");
class CRUD{

		public static function getRow($tableName, $idFieldName=false, $idValue=false, $data=false, $limit=false)
		{
			$db = Database::getInstance();
			$conn = $db->getConnection(); 
			$return = false;
			$sql="";
			$sqlRetriveField="";
			$sqlRetriveField=($data!=false) ? CRUD::sqlFieldCreator($data) : "*";	
			$sqlWhere=($idFieldName==false || $idValue==false) ? "" : "WHERE ".$idFieldName." = :id";
			$sql=($limit==false) ? "SELECT ".$sqlRetriveField." FROM ".$tableName." ".$sqlWhere : "SELECT ".$sqlRetriveField." FROM ".$tableName." ".$sqlWhere." ".$limit;
			$st = $conn->prepare ( $sql );
			if($idFieldName!=false || $idValue!=false)
			{
				$pdoParam=CRUD::getPDOConstantType($idValue);
				$st->bindValue( ":id", $idValue, $pdoParam );
			}
			$st->execute();
			$return=$st->fetchAll();
			$conn=null;
			return $return;
		} 
		
		public static function addRow($tableName, $data)
		{
			$db = Database::getInstance();
			$conn = $db->getConnection(); 
			$sql="";
			$insertPartQuery=CRUD::sqlFieldCreator($data,"insert");
			$sql="INSERT INTO ".$tableName." ".$insertPartQuery;
			$st = $conn->prepare ( $sql );
			$conn->beginTransaction();
			foreach($data as $fKey=>$field)
			{
				$escapeF=":".$fKey;
				$escapeV=$field;
				$pdoParam=CRUD::getPDOConstantType($field);
				$st->bindValue($escapeF , $escapeV , $pdoParam);
			}
			$st->execute();
			$return = (($conn->lastInsertId())!=0) ? $conn->lastInsertId() : false; 
			$conn->commit();
			$conn=null;
			return $return;
		}
		
		public static function deleteRow($tableName, $idFieldName, $idValue)
		{
			$db = Database::getInstance();
			$conn = $db->getConnection(); 
			$sql="DELETE FROM ".$tableName." WHERE ".$idFieldName."=:id";
			$st = $conn->prepare ( $sql );
			$pdoParam=CRUD::getPDOConstantType($idValue);
			$st->bindValue( ":id", $idValue, $pdoParam );
			$st->execute();
			
			//checking wheather deleted or not
			$sql="SELECT 1 FROM ".$tableName." WHERE ".$idFieldName."=:id";
			$pdoParam=CRUD::getPDOConstantType($idValue);
			$st->bindValue( ":id", $idValue, $pdoParam );
			$st->execute();
			$return=($row=$st->fetch()) ? false : true;
			$conn=null;
			return $return;
		}
		
		public static function updateRow($tableName,$idFieldName, $idValue, $data)
		{
			$db = Database::getInstance();
			$conn = $db->getConnection(); 
			$updateQueryPart=CRUD::sqlFieldCreator($data,"update");
			$sql="UPDATE ".$tableName." ".$updateQueryPart." WHERE ".$idFieldName."=:id";
			
			$st = $conn->prepare ( $sql );
			$pdoParam=CRUD::getPDOConstantType($idValue);
			$st->bindValue( ":id", $idValue, $pdoParam );
			$st->execute();
			$return=(($st->rowCount())!=0) ? true : false;
			$conn=null;
			return $return;
		}
		
		public static function customSql($sql)
		{
			$db = Database::getInstance();
			$conn = $db->getConnection(); 
			$st = $conn->prepare ( $sql );
			$st->execute();
			$return=$st->fetchAll();
			return $return;
		}
		
		public static function sqlFieldCreator($dataArr,$FLAG=FALSE)
		{	
			$sqlFieldString="";
			$sqlValueString="";
			$insertString="";
			$updateString="";
			
			$count=(is_array($dataArr)) ? count($dataArr) : FALSE;
			
			if($FLAG==FALSE)
			{
				foreach($dataArr as $fKey=>$field)
				{
					$sqlFieldString.=(($count-1)==$fKey)? $field : $field.", ";
				}				
			}
			
			if($FLAG=="insert")
			{
				$counterFront=0;
				$counterBack=0;
				foreach($dataArr as $fKey=>$field)
				{
					$sqlFieldString.=(($count-1)==$counterFront)? $fKey : $fKey.", ";
					$sqlValueString.=(($count-1)==$counterFront)? ":".$fKey : ":".$fKey.", ";
					$counterFront++;
				}
				$insertString.="(".$sqlFieldString.") VALUES (".$sqlValueString.")";
			}
			
			if($FLAG=="update")
			{
				$counterFront=0;
				foreach($dataArr as $fKey=>$field)
				{	
					$fieldV=(is_int($field)) ? $field : "'".$field."'";
					$sqlFieldString.=(($count-1)==$counterFront)? $fKey." = ".$fieldV :  $fKey." = '".$field."', " ;
					$counterFront++;
				}
				$updateString.="SET ".$sqlFieldString;
			}
			
			if($FLAG==FALSE)
			return $sqlFieldString;
			if($FLAG=='insert')
			return $insertString;
			if($FLAG=='update')
			return $updateString;
		}
		
		public static function getPDOConstantType( $var )
		{
		  if( is_int( $var ) )
			return PDO::PARAM_INT;
		  //Default 
		  return PDO::PARAM_STR;
		}

}

?>