<?php
/*
 *
 *  DONT EDIT THIS FILE. AUTO CREATED BY mkobject.xsl
 *
 *  File: 
 */
require_once( DIR_MODULES . "/ADO/DBRelationAdapter.php" );
require_once( DIR_MODULES . "/ADO/DBObject.php" );

  /** \ingroup table_objects

  Class describes object-table mapping information for table t_data
  */
class Data extends DBObject
{
	
	var $data_id;
	var $date;
	var $value;
	var $string;
	var $text;
	var $enum;
	var $blob;
	var $real;
	var $dictionary_id;
    
	/** construct object
	*/
	function Data()
	{
		
	  	$this->data_id =  0 ;
	  	$this->date =  NULL ;
	  	$this->value =  NULL ;
	  	$this->string =  NULL ;
	  	$this->text =  NULL ;
	  	$this->enum =  'red';
	  	$this->blob =  NULL ;
	  	$this->real =  NULL ;
	  	$this->dictionary_id =  NULL ;
	}

	/** get primary key name (obsolete/internal use only)
	 @returns primary key column name as \b string , for this object it will be \a data_id
	*/
	function primary_key()
	{
		return( "data_id" );
	}
        
	/** get primary key value
	 @returns primary key value with type as defined in database (value of \a data_id )
	*/
	function primary_key_value()
	{
		return( $this->data_id );
		
	}

	/** always contains \a "t_data" 
	 */
	function table_name()
	{
		return( "t_data" );
	}

	/** return DBColumnDefinition \b array
	 @return \b array of DBColumnDefinition items - object relation scheme
	 */
	function getColumnDefinition()
	{
		$columnDefinition = array();
        		$columnDefinition[ "data_id" ] = $this->tag_data_id();
		$columnDefinition[ "date" ] = $this->tag_date();
		$columnDefinition[ "value" ] = $this->tag_value();
		$columnDefinition[ "string" ] = $this->tag_string();
		$columnDefinition[ "text" ] = $this->tag_text();
		$columnDefinition[ "enum" ] = $this->tag_enum();
		$columnDefinition[ "blob" ] = $this->tag_blob();
		$columnDefinition[ "real" ] = $this->tag_real();
		$columnDefinition[ "dictionary_id" ] = $this->tag_dictionary_id();

		return( $columnDefinition );
	}

	/** get colum definitions for forein keys
		@return array of DBColumnDefinition
	 */
	function getForeignKeys()
	{
		$keyDefs = array();
		$keyDefs[ "dictionary_id" ] = $this->key_dictionary_id();
  
		return( $keyDefs );
	}

	/** returns \b true if object is newly created
	@return bool 
	*/
	function isNew()
	{
		$val = $this->primary_key_value() ;
		return( $val === 0 || $val === -1 );
	}
        
	// Set methods

    
	/** set value to data_id  column */
	function set_data_id( $value )
	{
		return( $this->setValue( "data_id", $value ));
	}
	
	/** set value to date  column */
	function set_date( $value )
	{
		return( $this->setValue( "date", $value ));
	}
	
	/** set value to value  column */
	function set_value( $value )
	{
		return( $this->setValue( "value", $value ));
	}
	
	/** set value to string  column */
	function set_string( $value )
	{
		return( $this->setValue( "string", $value ));
	}
	
	/** set value to text  column */
	function set_text( $value )
	{
		return( $this->setValue( "text", $value ));
	}
	
	/** set value to enum  column */
	function set_enum( $value )
	{
		return( $this->setValue( "enum", $value ));
	}
	
	/** set value to blob  column */
	function set_blob( $value )
	{
		return( $this->setValue( "blob", $value ));
	}
	
	/** set value to real  column */
	function set_real( $value )
	{
		return( $this->setValue( "real", $value ));
	}
	
	/** set value to dictionary_id  column */
	function set_dictionary_id( $value )
	{
		return( $this->setValue( "dictionary_id", $value ));
	}
	

	//Get methods

    
	/** get value from \a data_id  column 
	@return int value
	*/
	function get_data_id()
	{
		return( $this->data_id );
	}
	
	/** get value from \a date  column 
	@return datetime value
	*/
	function get_date()
	{
		return( $this->date );
	}
	
	/** get value from \a value  column 
	@return int value
	*/
	function get_value()
	{
		return( $this->value );
	}
	
	/** get value from \a string  column 
	@return varchar value
	*/
	function get_string()
	{
		return( $this->string );
	}
	
	/** get value from \a text  column 
	@return text value
	*/
	function get_text()
	{
		return( $this->text );
	}
	
	/** get value from \a enum  column 
	@return enum('red','black') value
	*/
	function get_enum()
	{
		return( $this->enum );
	}
	
	/** get value from \a blob  column 
	@return blob value
	*/
	function get_blob()
	{
		return( $this->blob );
	}
	
	/** get value from \a real  column 
	@return float value
	*/
	function get_real()
	{
		return( $this->real );
	}
	
	/** get value from \a dictionary_id  column 
	@return int value
	*/
	function get_dictionary_id()
	{
		return( $this->dictionary_id );
	}
	

	//Tags
    
	/** get column defintion for \a data_id column
	 @param $alias \b string  alias for \a data_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_data_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "data_id", "int",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a date column
	 @param $alias \b string  alias for \a date column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_date( $alias=null )
	{
	    $def = new DBColumnDefinition( "date", "datetime",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a value column
	 @param $alias \b string  alias for \a value column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_value( $alias=null )
	{
	    $def = new DBColumnDefinition( "value", "int",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a string column
	 @param $alias \b string  alias for \a string column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_string( $alias=null )
	{
	    $def = new DBColumnDefinition( "string", "varchar",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a text column
	 @param $alias \b string  alias for \a text column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_text( $alias=null )
	{
	    $def = new DBColumnDefinition( "text", "text",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a enum column
	 @param $alias \b string  alias for \a enum column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_enum( $alias=null )
	{
	    $def = new DBColumnDefinition( "enum", "enum",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a blob column
	 @param $alias \b string  alias for \a blob column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_blob( $alias=null )
	{
	    $def = new DBColumnDefinition( "blob", "blob",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a real column
	 @param $alias \b string  alias for \a real column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_real( $alias=null )
	{
	    $def = new DBColumnDefinition( "real", "float",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a dictionary_id column
	 @param $alias \b string  alias for \a dictionary_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_dictionary_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "dictionary_id", "int",$alias,$this);
  
		return( $def );
	}
	

	//Foreign keys
    
            
	/** Foreign key for tag_dictionary_id() as link to Dictionary::tag_dictionary_id()
         *
         * @return DBForeignKey
         */
	function key_dictionary_id($proto=null)
	{
		if ( is_null($proto) ) $proto = new Dictionary();
		$def = new DBForeignKey( $this->tag_dictionary_id(), $proto->tag_dictionary_id() );
		return( $def );
	}
      
    
    
    
	// Loaders
        
	/** load dictionary specified by foreign key dictionary_id */
	function load_dictionary( $ds )
	{
		$dba = new DBObjectAdapter( $ds, new Dictionary );
		$this->dictionary = $dba->getByPrimaryKey( $this->dictionary_id );
	}
	
}
  
  /** \ingroup table_objects

  Class describes object-table mapping information for table t_dictionary
  */
class Dictionary extends DBObject
{
	
	var $dictionary_id;
	var $text;
    
	/** construct object
	*/
	function Dictionary()
	{
		
	  	$this->dictionary_id =  0 ;
	  	$this->text =  NULL ;
	}

	/** get primary key name (obsolete/internal use only)
	 @returns primary key column name as \b string , for this object it will be \a dictionary_id
	*/
	function primary_key()
	{
		return( "dictionary_id" );
	}
        
	/** get primary key value
	 @returns primary key value with type as defined in database (value of \a dictionary_id )
	*/
	function primary_key_value()
	{
		return( $this->dictionary_id );
		
	}

	/** always contains \a "t_dictionary" 
	 */
	function table_name()
	{
		return( "t_dictionary" );
	}

	/** return DBColumnDefinition \b array
	 @return \b array of DBColumnDefinition items - object relation scheme
	 */
	function getColumnDefinition()
	{
		$columnDefinition = array();
        		$columnDefinition[ "dictionary_id" ] = $this->tag_dictionary_id();
		$columnDefinition[ "text" ] = $this->tag_text();

		return( $columnDefinition );
	}

	/** get colum definitions for forein keys
		@return array of DBColumnDefinition
	 */
	function getForeignKeys()
	{
		$keyDefs = array();
		
		return( $keyDefs );
	}

	/** returns \b true if object is newly created
	@return bool 
	*/
	function isNew()
	{
		$val = $this->primary_key_value() ;
		return( $val === 0 || $val === -1 );
	}
        
	// Set methods

    
	/** set value to dictionary_id  column */
	function set_dictionary_id( $value )
	{
		return( $this->setValue( "dictionary_id", $value ));
	}
	
	/** set value to text  column */
	function set_text( $value )
	{
		return( $this->setValue( "text", $value ));
	}
	

	//Get methods

    
	/** get value from \a dictionary_id  column 
	@return int value
	*/
	function get_dictionary_id()
	{
		return( $this->dictionary_id );
	}
	
	/** get value from \a text  column 
	@return varchar value
	*/
	function get_text()
	{
		return( $this->text );
	}
	

	//Tags
    
	/** get column defintion for \a dictionary_id column
	 @param $alias \b string  alias for \a dictionary_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_dictionary_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "dictionary_id", "int",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a text column
	 @param $alias \b string  alias for \a text column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_text( $alias=null )
	{
	    $def = new DBColumnDefinition( "text", "varchar",$alias,$this);
  
		return( $def );
	}
	

	//Foreign keys
    
    
    
	// Loaders
        
}
  
  /** \ingroup table_objects

  Class describes object-table mapping information for table t_link
  */
class table_t_link extends DBObject
{
	
	var $link_id;
	var $data_id;
	var $dictionary_id;
    
	/** construct object
	*/
	function table_t_link()
	{
		
	  	$this->link_id =  0 ;
	  	$this->data_id =  -1 ;
	  	$this->dictionary_id =  -1 ;
	}

	/** get primary key name (obsolete/internal use only)
	 @returns primary key column name as \b string , for this object it will be \a link_id
	*/
	function primary_key()
	{
		return( "link_id" );
	}
        
	/** get primary key value
	 @returns primary key value with type as defined in database (value of \a link_id )
	*/
	function primary_key_value()
	{
		return( $this->link_id );
		
	}

	/** always contains \a "t_link" 
	 */
	function table_name()
	{
		return( "t_link" );
	}

	/** return DBColumnDefinition \b array
	 @return \b array of DBColumnDefinition items - object relation scheme
	 */
	function getColumnDefinition()
	{
		$columnDefinition = array();
        		$columnDefinition[ "link_id" ] = $this->tag_link_id();
		$columnDefinition[ "data_id" ] = $this->tag_data_id();
		$columnDefinition[ "dictionary_id" ] = $this->tag_dictionary_id();

		return( $columnDefinition );
	}

	/** get colum definitions for forein keys
		@return array of DBColumnDefinition
	 */
	function getForeignKeys()
	{
		$keyDefs = array();
		$keyDefs[ "data_id" ] = $this->key_data_id();
  $keyDefs[ "dictionary_id" ] = $this->key_dictionary_id();
  
		return( $keyDefs );
	}

	/** returns \b true if object is newly created
	@return bool 
	*/
	function isNew()
	{
		$val = $this->primary_key_value() ;
		return( $val === 0 || $val === -1 );
	}
        
	// Set methods

    
	/** set value to link_id  column */
	function set_link_id( $value )
	{
		return( $this->setValue( "link_id", $value ));
	}
	
	/** set value to data_id  column */
	function set_data_id( $value )
	{
		return( $this->setValue( "data_id", $value ));
	}
	
	/** set value to dictionary_id  column */
	function set_dictionary_id( $value )
	{
		return( $this->setValue( "dictionary_id", $value ));
	}
	

	//Get methods

    
	/** get value from \a link_id  column 
	@return int value
	*/
	function get_link_id()
	{
		return( $this->link_id );
	}
	
	/** get value from \a data_id  column 
	@return int value
	*/
	function get_data_id()
	{
		return( $this->data_id );
	}
	
	/** get value from \a dictionary_id  column 
	@return int value
	*/
	function get_dictionary_id()
	{
		return( $this->dictionary_id );
	}
	

	//Tags
    
	/** get column defintion for \a link_id column
	 @param $alias \b string  alias for \a link_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_link_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "link_id", "int",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a data_id column
	 @param $alias \b string  alias for \a data_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_data_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "data_id", "int",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a dictionary_id column
	 @param $alias \b string  alias for \a dictionary_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_dictionary_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "dictionary_id", "int",$alias,$this);
  
		return( $def );
	}
	

	//Foreign keys
    
            
	/** Foreign key for tag_data_id() as link to Data::tag_data_id()
         *
         * @return DBForeignKey
         */
	function key_data_id($proto=null)
	{
		if ( is_null($proto) ) $proto = new Data();
		$def = new DBForeignKey( $this->tag_data_id(), $proto->tag_data_id() );
		return( $def );
	}
      
    
            
	/** Foreign key for tag_dictionary_id() as link to Dictionary::tag_dictionary_id()
         *
         * @return DBForeignKey
         */
	function key_dictionary_id($proto=null)
	{
		if ( is_null($proto) ) $proto = new Dictionary();
		$def = new DBForeignKey( $this->tag_dictionary_id(), $proto->tag_dictionary_id() );
		return( $def );
	}
      
    
    
    
	// Loaders
        
	/** load data specified by foreign key data_id */
	function load_data( $ds )
	{
		$dba = new DBObjectAdapter( $ds, new Data );
		$this->data = $dba->getByPrimaryKey( $this->data_id );
	}
	
	/** load dictionary specified by foreign key dictionary_id */
	function load_dictionary( $ds )
	{
		$dba = new DBObjectAdapter( $ds, new Dictionary );
		$this->dictionary = $dba->getByPrimaryKey( $this->dictionary_id );
	}
	
}
  
  /** \ingroup table_objects

  Class describes object-table mapping information for table t_another_link
  */
class Another extends DBObject
{
	
	var $another_link_id;
	var $owner_id;
	var $child_id;
    
	/** construct object
	*/
	function Another()
	{
		
	  	$this->another_link_id =  0 ;
	  	$this->owner_id =  -1 ;
	  	$this->child_id =  -1 ;
	}

	/** get primary key name (obsolete/internal use only)
	 @returns primary key column name as \b string , for this object it will be \a another_link_id
	*/
	function primary_key()
	{
		return( "another_link_id" );
	}
        
	/** get primary key value
	 @returns primary key value with type as defined in database (value of \a another_link_id )
	*/
	function primary_key_value()
	{
		return( $this->another_link_id );
		
	}

	/** always contains \a "t_another_link" 
	 */
	function table_name()
	{
		return( "t_another_link" );
	}

	/** return DBColumnDefinition \b array
	 @return \b array of DBColumnDefinition items - object relation scheme
	 */
	function getColumnDefinition()
	{
		$columnDefinition = array();
        		$columnDefinition[ "another_link_id" ] = $this->tag_another_link_id();
		$columnDefinition[ "owner_id" ] = $this->tag_owner_id();
		$columnDefinition[ "child_id" ] = $this->tag_child_id();

		return( $columnDefinition );
	}

	/** get colum definitions for forein keys
		@return array of DBColumnDefinition
	 */
	function getForeignKeys()
	{
		$keyDefs = array();
		$keyDefs[ "owner_id" ] = $this->key_owner_id();
  $keyDefs[ "child_id" ] = $this->key_child_id();
  
		return( $keyDefs );
	}

	/** returns \b true if object is newly created
	@return bool 
	*/
	function isNew()
	{
		$val = $this->primary_key_value() ;
		return( $val === 0 || $val === -1 );
	}
        
	// Set methods

    
	/** set value to another_link_id  column */
	function set_another_link_id( $value )
	{
		return( $this->setValue( "another_link_id", $value ));
	}
	
	/** set value to owner_id  column */
	function set_owner_id( $value )
	{
		return( $this->setValue( "owner_id", $value ));
	}
	
	/** set value to child_id  column */
	function set_child_id( $value )
	{
		return( $this->setValue( "child_id", $value ));
	}
	

	//Get methods

    
	/** get value from \a another_link_id  column 
	@return int value
	*/
	function get_another_link_id()
	{
		return( $this->another_link_id );
	}
	
	/** get value from \a owner_id  column 
	@return int value
	*/
	function get_owner_id()
	{
		return( $this->owner_id );
	}
	
	/** get value from \a child_id  column 
	@return int value
	*/
	function get_child_id()
	{
		return( $this->child_id );
	}
	

	//Tags
    
	/** get column defintion for \a another_link_id column
	 @param $alias \b string  alias for \a another_link_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_another_link_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "another_link_id", "int",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a owner_id column
	 @param $alias \b string  alias for \a owner_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_owner_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "owner_id", "int",$alias,$this);
  
		return( $def );
	}
	
	/** get column defintion for \a child_id column
	 @param $alias \b string  alias for \a child_id column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_child_id( $alias=null )
	{
	    $def = new DBColumnDefinition( "child_id", "int",$alias,$this);
  
		return( $def );
	}
	

	//Foreign keys
    
            
	/** Foreign key for tag_owner_id() as link to Data::tag_data_id()
         *
         * @return DBForeignKey
         */
	function key_owner_id($proto=null)
	{
		if ( is_null($proto) ) $proto = new Data();
		$def = new DBForeignKey( $this->tag_owner_id(), $proto->tag_data_id() );
		return( $def );
	}
      
    
            
	/** Foreign key for tag_child_id() as link to Dictionary::tag_dictionary_id()
         *
         * @return DBForeignKey
         */
	function key_child_id($proto=null)
	{
		if ( is_null($proto) ) $proto = new Dictionary();
		$def = new DBForeignKey( $this->tag_child_id(), $proto->tag_dictionary_id() );
		return( $def );
	}
      
    
    
    
	// Loaders
        
	/** load data specified by foreign key owner_id */
	function load_data( $ds )
	{
		$dba = new DBObjectAdapter( $ds, new Data );
		$this->data = $dba->getByPrimaryKey( $this->owner_id );
	}
	
	/** load dictionary specified by foreign key child_id */
	function load_dictionary( $ds )
	{
		$dba = new DBObjectAdapter( $ds, new Dictionary );
		$this->dictionary = $dba->getByPrimaryKey( $this->child_id );
	}
	
}
  

    
/** \ingroup table_relations
   Relation adapter for loading Dictionary
   objects as members of  Data
*/
class DataDictionaryRelation extends DBRelationAdapter
{
	/** creates relation object for searching in data source

	@param $objectID \b integer primary key of Data
	@param $memberID \b integer primary key of Dictionary
	 */
	protected function getObject( $objectID, $memberID )
	{
		$obj = new table_t_link;
		
		$obj->data_id = $objectID;
		$obj->dictionary_id = $memberID;
		return( $obj );
	}

	/** returns master object prototype of Data class
	 @param $objectID \b integer assigned primary key of Data
	 */
	protected function getDataObject( $objectID )
	{
		$obj = new Data();
		$obj->data_id = $objectID;
		return( $obj );
	}
	/** returns memebr object prototype of Dictionary class
	 @param $memberID \b integer assigned primary key of Dictionary
	 */
	protected function getMemberObject( $memberID )
	{
		$obj = new Dictionary();
		$obj->dictionary_id = $memberID;
		return( $obj );
	}
	/** returns foreing keys for linking 
	 */
	protected function getForeignKeys()
	{
		return( array( "data_id", "dictionary_id" ) );

	}
        /** select Dictionary objects by Data primary key ID.
         * @param DBDataSource $ds  connection to data source.
         * @param integer $objectID  primary key of Data.
         * @return array|Dictionary  collection ob member objects.
         */
	public function selectDictionarys( $ds, $objectID )
	{
		return $this->select( $ds, $objectID );
	}
}      
  
/** \ingroup table_relations
   Relation adapter for loading Data
   objects as members of  Dictionary
*/
class DictionaryDataRelation extends DBRelationAdapter
{
	/** creates relation object for searching in data source

	@param $objectID \b integer primary key of Dictionary
	@param $memberID \b integer primary key of Data
	 */
	protected function getObject( $objectID, $memberID )
	{
		$obj = new table_t_link;
		
		$obj->dictionary_id = $objectID;
		$obj->data_id = $memberID;
		return( $obj );
	}

	/** returns master object prototype of Dictionary class
	 @param $objectID \b integer assigned primary key of Dictionary
	 */
	protected function getDataObject( $objectID )
	{
		$obj = new Dictionary();
		$obj->dictionary_id = $objectID;
		return( $obj );
	}
	/** returns memebr object prototype of Data class
	 @param $memberID \b integer assigned primary key of Data
	 */
	protected function getMemberObject( $memberID )
	{
		$obj = new Data();
		$obj->data_id = $memberID;
		return( $obj );
	}
	/** returns foreing keys for linking 
	 */
	protected function getForeignKeys()
	{
		return( array( "dictionary_id", "data_id" ) );

	}
        /** select Data objects by Dictionary primary key ID.
         * @param DBDataSource $ds  connection to data source.
         * @param integer $objectID  primary key of Dictionary.
         * @return array|Data  collection ob member objects.
         */
	public function selectDatas( $ds, $objectID )
	{
		return $this->select( $ds, $objectID );
	}
}      
  

    
/** \ingroup table_relations
   Relation adapter for loading Dictionary
   objects as members of  Data
*/
class AnotherDataDictionaryRelation extends DBRelationAdapter
{
	/** creates relation object for searching in data source

	@param $objectID \b integer primary key of Data
	@param $memberID \b integer primary key of Dictionary
	 */
	protected function getObject( $objectID, $memberID )
	{
		$obj = new Another;
		
		$obj->owner_id = $objectID;
		$obj->child_id = $memberID;
		return( $obj );
	}

	/** returns master object prototype of Data class
	 @param $objectID \b integer assigned primary key of Data
	 */
	protected function getDataObject( $objectID )
	{
		$obj = new Data();
		$obj->owner_id = $objectID;
		return( $obj );
	}
	/** returns memebr object prototype of Dictionary class
	 @param $memberID \b integer assigned primary key of Dictionary
	 */
	protected function getMemberObject( $memberID )
	{
		$obj = new Dictionary();
		$obj->child_id = $memberID;
		return( $obj );
	}
	/** returns foreing keys for linking 
	 */
	protected function getForeignKeys()
	{
		return( array( "owner_id", "child_id" ) );

	}
        /** select Dictionary objects by Data primary key ID.
         * @param DBDataSource $ds  connection to data source.
         * @param integer $objectID  primary key of Data.
         * @return array|Dictionary  collection ob member objects.
         */
	public function selectDictionarys( $ds, $objectID )
	{
		return $this->select( $ds, $objectID );
	}
}      
  
  
?>