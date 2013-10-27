<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

  <xsl:output method="text"
    encoding="windows-1251"
    />
  <xsl:variable name="baseclass" select="//baseclass/@name"/>
  <xsl:variable name="prefix" select="//prefix/@name"/>

  <xsl:variable name="eol">
    <xsl:text>
</xsl:text>
</xsl:variable>
  

  <xsl:template match="/">
    <xsl:apply-templates select="database"/>
    <xsl:apply-templates select="database/include"/>
  </xsl:template>
  
  <xsl:template match="database">
<xsl:text>&lt;?php</xsl:text>
/*
 *
 *  DONT EDIT THIS FILE. AUTO CREATED BY mkobject.xsl
 *
 *  File: <!-- <xsl:value-of select="$file"/> -->
 */
require_once( DIR_MODULES . "/ADO/DBRelationAdapter.php" );
require_once( DIR_MODULES . "/ADO/DBObject.php" );
<xsl:for-each select="//external">
  <xsl:variable name="section">
    <xsl:choose>
      <xsl:when test="@section = 'objects'">DIR_OBJECTS</xsl:when>
      <xsl:when test="@section = 'modules'">DIR_MODULES</xsl:when>
      <xsl:otherwise><xsl:value-of select="@section"/></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:text>require_once( </xsl:text><xsl:value-of select="$section"/> . "<xsl:value-of select="@file"/>" );
</xsl:for-each>

  <xsl:apply-templates select="database/include"/>
  
  <xsl:apply-templates select="table"/>
  <xsl:apply-templates select="table[@type='relation']" mode="relation"/>
  <xsl:apply-templates select="table[count(@enum)!=0]" mode="enum"/>
  
?></xsl:template>

  <xsl:template match="table" >
  <xsl:variable name="class">
    <xsl:choose>
      <xsl:when test="count( @class ) != 0"><xsl:value-of select="@class"/></xsl:when>
      <xsl:otherwise><xsl:value-of select="$prefix"/><xsl:value-of select="@name"/></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:variable name="pkname" select="column[@primary-key='yes']/@name"/>
  <xsl:variable name="table-name" select="@name"/>

  <xsl:variable name="foreign-keys" select="column[count(@foreign-key) !=0]"/>
  /** \ingroup table_objects

  Class describes object-table mapping information for table <xsl:value-of select="@name"/>
  */
class <xsl:value-of select="$class"/> extends <xsl:value-of select="$baseclass"/>
{
	<xsl:apply-templates select="column" mode="create"/>
    
	/** construct object
	*/
	function <xsl:value-of select="$class"/>()
	{
		<xsl:apply-templates select="column" mode="init"/>
	}

	/** get primary key name (obsolete/internal use only)
	 @returns primary key column name as \b string , for this object it will be \a <xsl:value-of select="$pkname"/>
	*/
	function primary_key()
	{
		return( "<xsl:value-of select="$pkname"/>" );
	}
        
	/** get primary key value
	 @returns primary key value with type as defined in database (value of \a <xsl:value-of select="$pkname"/> )
	*/
	function primary_key_value()
	{
		<xsl:choose>
		<xsl:when test="count( column[@primary-key='yes'] ) != 0" >
		<xsl:text>return( $this-&gt;</xsl:text><xsl:value-of select="column[@primary-key='yes']/@name"/> );
		</xsl:when>
		<xsl:otherwise>return( 0 );</xsl:otherwise>
		</xsl:choose>
	}

	/** always contains \a "<xsl:value-of select="@name"/>" 
	 */
	function table_name()
	{
		return( "<xsl:value-of select="@name"/>" );
	}

	/** return DBColumnDefinition \b array
	 @return \b array of DBColumnDefinition items - object relation scheme
	 */
	function getColumnDefinition()
	{
		$columnDefinition = array();
        <xsl:text/>
		<xsl:apply-templates select="column" mode="define">
          <xsl:with-param name="table" select="@name"/>
        </xsl:apply-templates>
		return( $columnDefinition );
	}

	/** get colum definitions for forein keys
		@return array of DBColumnDefinition
	 */
	function getForeignKeys()
	{
		$keyDefs = array();
		<xsl:apply-templates select="$foreign-keys" mode="define-fk"/>
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

    <xsl:for-each select="column">
	/** set value to <xsl:value-of select="@name"/>  column */
	function set_<xsl:value-of select="@name"/>( $value )
	{
		return( $this->setValue( "<xsl:value-of select="@name"/>", $value ));
	}
	</xsl:for-each>

	//Get methods

    <xsl:for-each select="column">
	/** get value from \a <xsl:value-of select="@name"/>  column 
	@return <xsl:value-of select="@type"/> value
	*/
	function get_<xsl:value-of select="@name"/>()
	{
		return( $this-><xsl:value-of select="@name"/> );
	}
	</xsl:for-each>

	//Tags
    <xsl:for-each select="column">
	/** get column defintion for \a <xsl:value-of select="@name"/> column
	 @param $alias \b string  alias for \a <xsl:value-of select="@name"/> column which will be used for on SQL query generation stage
     @return DBColumnDefinition
	 */
	function tag_<xsl:value-of select="@name"/>( $alias=null )
	{
	    $def = new DBColumnDefinition( "<xsl:text/>
    <xsl:value-of select="@name"/>", "<xsl:apply-templates select="@type" mode="get-type"/>
    <xsl:text>",$alias,$this);</xsl:text>
  
		return( $def );
	}
	</xsl:for-each>

	//Foreign keys
    <xsl:for-each select="$foreign-keys">
      <xsl:variable name="owner-tag">$this->tag_<xsl:value-of select="@name"/>()</xsl:variable>
      <xsl:variable name="foreign-tag">$proto->tag_<xsl:value-of select="@foreign-key"/>()</xsl:variable>
      <xsl:variable name="foreign-class">
        <xsl:apply-templates select="." mode="get-foreign-class">
          <xsl:with-param name="prefix" select="$prefix"/>
        </xsl:apply-templates>
       </xsl:variable>
            
	/** Foreign key for tag_<xsl:value-of select="@name"/>() as link to <xsl:value-of select="$foreign-class"/>::tag_<xsl:value-of select="@foreign-key"/>()
         *
         * @return DBForeignKey
         */
	function key_<xsl:value-of select="@name"/>($proto=null)
	{
		if ( is_null($proto) ) $proto = new <xsl:value-of select="$foreign-class"/>();
		$def = new DBForeignKey( <xsl:value-of select="$owner-tag"/>, <xsl:value-of select="$foreign-tag"/> );
		return( $def );
	}
      
    </xsl:for-each>
    
    
	// Loaders
        <xsl:for-each select="$foreign-keys[count(@member) != 0 and  count( @class ) != 0]">
	/** load <xsl:value-of select="@member"/> specified by foreign key <xsl:value-of select="@name"/> */
	function load_<xsl:value-of select="@member"/>( $ds )
	{
		$dba = new DBObjectAdapter( $ds, new <xsl:value-of select="@class"/> );
		$this-><xsl:value-of select="@member"/> = $dba->getByPrimaryKey( $this-><xsl:value-of select="@name"/> );
	}
	</xsl:for-each>
}
  </xsl:template>

  <xsl:template match="column" mode="define">
    <xsl:param name="table"/>
    <xsl:text>		</xsl:text>$columnDefinition[ "<xsl:value-of select="@name"/>" ] = $this->tag_<xsl:value-of select="@name"/>();<xsl:text>
</xsl:text>
  </xsl:template>

  <xsl:template match="column" mode="get-foreign-class">
    <xsl:param name="prefix"/>
    <xsl:variable name="foreign-table" select="@foreign-table"/>
    <xsl:choose>
      <xsl:when test="count(@class) != 0">
        <xsl:value-of select="@class"/>
      </xsl:when>
      <xsl:when test="count(/database/table[@name=$foreign-table]/@class) != 0">
            <!-- use class defined in foreigh table -->
        <xsl:value-of select="/database/table[@name=$foreign-table]/@class"/>
      </xsl:when>
      <xsl:when test="count(/database/table[@name=$foreign-table]) != 0">
            <!-- use table name as class name defined in foreigh table -->
        <xsl:value-of select="$prefix"/>
        <xsl:value-of select="/database/table[@name=$foreign-table]/@name"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:message terminate="yes">
          <xsl:text/>GenPhp Error: Can't generate foreign key for <xsl:value-of select="$table-name"/>::<xsl:value-of select="@name"/>
          <xsl:text/> as <xsl:value-of select="$foreign-table"/>::<xsl:value-of select="@foreign-key"/> because @class not defined and foreign table is not accessible.
        </xsl:message>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="column" mode="define-fk">
    <xsl:text/>$keyDefs[ "<xsl:value-of select="@name"/>" ] = $this->key_<xsl:value-of select="@name"/>();
  </xsl:template>


  <xsl:template match="column" mode="create">
    <xsl:if test="count(@description) !=0 "><xsl:text>
	/** </xsl:text><xsl:value-of select="@description"/> 
	   @access private	
	 */</xsl:if>
	var $<xsl:value-of select="@name"/><xsl:text>;</xsl:text>
  </xsl:template>


  
  <xsl:template match="column" mode="init">
    <xsl:variable name="default">
      <xsl:choose>
        <xsl:when test="@default = 'null'"> NULL </xsl:when>
        <xsl:when test="( @default = 'not null' or  @default = 'NOT NULL') and @type != 'varchar'"> -1 </xsl:when>
        <xsl:when test="( @default = 'not null' or  @default = 'NOT NULL') and @type = 'varchar'"> '' </xsl:when>
        <xsl:when test="@type = 'blob'"> NULL </xsl:when>
        <xsl:when test="count(@default) != 0"> '<xsl:value-of select="@default"/>'</xsl:when>
        <xsl:when test="count(@auto-increment) != 0"> 0 </xsl:when>
        <xsl:otherwise> NULL</xsl:otherwise>
      </xsl:choose>
	  </xsl:variable>
	  	$this-><xsl:value-of select="@name"/> = <xsl:value-of select="$default"/><xsl:text>;</xsl:text>
  </xsl:template>

  <!--   RELATIONS -->
  
  <xsl:template match="table" mode="relation">
    <xsl:text>

    </xsl:text>

    <xsl:variable name="pair" select="column[count(@foreign-key)!=0]"/>

    <xsl:variable name="first-class">
      <xsl:apply-templates select="$pair[position() =1]" mode="get-foreign-class">
        <xsl:with-param name="prefix" select="$prefix"/>
      </xsl:apply-templates>
    </xsl:variable>
    <xsl:variable name="second-class">
      <xsl:apply-templates select="$pair[position() =2]" mode="get-foreign-class">
        <xsl:with-param name="prefix" select="$prefix"/>
      </xsl:apply-templates>
    </xsl:variable>

    <xsl:choose>
      <xsl:when test="count(@class)!=0">
        <xsl:call-template name="make-class">
          <xsl:with-param name="adapter" select="."/>
          <xsl:with-param name="master" select="$pair[position() =1]"/>
          <xsl:with-param name="member" select="$pair[position() =2]"/>
          <xsl:with-param name="class-prefix" select="@class"/>
        </xsl:call-template>
        
      </xsl:when>
      <xsl:when test="$first-class != '' and $second-class != ''">
        <xsl:call-template name="make-class">
          <xsl:with-param name="adapter" select="."/>
          <xsl:with-param name="master" select="$pair[position() =1]"/>
          <xsl:with-param name="member" select="$pair[position() =2]"/>
        </xsl:call-template>

        <xsl:if test="$second-class != $first-class">
          <xsl:call-template name="make-class">
            <xsl:with-param name="adapter" select="."/>
            <xsl:with-param name="master" select="$pair[position() =2]"/>
            <xsl:with-param name="member" select="$pair[position() =1]"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:when>
    </xsl:choose>
</xsl:template>

  <xsl:template name="make-class">
    <xsl:param name="adapter"/>
    <xsl:param name="master"/>
    <xsl:param name="member"/>
    <xsl:param name="class-prefix"/>
    <xsl:variable name="adapter-class">
      <xsl:choose>
        <xsl:when test="count( $adapter/@class ) != 0"><xsl:value-of select="$adapter/@class"/></xsl:when>
        <xsl:otherwise><xsl:value-of select="$prefix"/><xsl:value-of select="$adapter/@name"/></xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="master-class">
      <xsl:apply-templates select="$master" mode="get-foreign-class">
        <xsl:with-param name="prefix" select="$prefix"/>
      </xsl:apply-templates>
    </xsl:variable>
    <xsl:variable name="member-class">
      <xsl:apply-templates select="$member" mode="get-foreign-class">
        <xsl:with-param name="prefix" select="$prefix"/>
      </xsl:apply-templates>
    </xsl:variable>


    <xsl:variable name="class-name">
      <xsl:value-of select="$class-prefix"/>
      <xsl:value-of select="$master-class"/>
      <xsl:value-of select="$member-class"/>
      <xsl:text>Relation</xsl:text>
    </xsl:variable>
/** \ingroup table_relations
   Relation adapter for loading <xsl:value-of select="$member/@class"/>
   objects as members of  <xsl:value-of select="$master/@class"/>
*/
class <xsl:value-of select="$class-name"/> extends DBRelationAdapter
{
	/** creates relation object for searching in data source

	@param $objectID \b integer primary key of <xsl:value-of select="$master-class"/>
	@param $memberID \b integer primary key of <xsl:value-of select="$member-class"/>
	 */
	protected function getObject( $objectID, $memberID )
	{
		$obj = new <xsl:value-of select="$adapter-class"/>;
		
		$obj-><xsl:value-of select="$master/@name"/> = $objectID;
		$obj-><xsl:value-of select="$member/@name"/> = $memberID;
		return( $obj );
	}

	/** returns master object prototype of <xsl:value-of select="$master-class"/> class
	 @param $objectID \b integer assigned primary key of <xsl:value-of select="$master-class"/>
	 */
	protected function getDataObject( $objectID )
	{
		$obj = new <xsl:value-of select="$master-class"/>();
		$obj-><xsl:value-of select="$master/@name"/> = $objectID;
		return( $obj );
	}
	/** returns memebr object prototype of <xsl:value-of select="$member-class"/> class
	 @param $memberID \b integer assigned primary key of <xsl:value-of select="$member-class"/>
	 */
	protected function getMemberObject( $memberID )
	{
		$obj = new <xsl:value-of select="$member-class"/>();
		$obj-><xsl:value-of select="$member/@name"/> = $memberID;
		return( $obj );
	}
	/** returns foreing keys for linking 
	 */
	protected function getForeignKeys()
	{
		return( array( "<xsl:value-of select="$master/@name"/>", "<xsl:value-of select="$member/@name"/>" ) );

	}
        /** select <xsl:value-of select="$member-class"/> objects by <xsl:value-of select="$master-class"/> primary key ID.
         * @param DBDataSource $ds  connection to data source.
         * @param integer $objectID  primary key of <xsl:value-of select="$master-class"/>.
         * @return array|<xsl:value-of select="$member-class"/>  collection ob member objects.
         */
	public function select<xsl:value-of select="$member-class"/>s( $ds, $objectID )
	{
		return $this->select( $ds, $objectID );
	}
}      
  </xsl:template>

  <xsl:template match="include">
    <xsl:apply-templates select="document(@href)/database"/>
  </xsl:template>

  <xsl:template match="@*|node()" mode="get-type">
    <xsl:param name="value"/>
    <xsl:choose>
      <xsl:when test="starts-with(.,'enum')">enum</xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="."/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="table" mode="enum">
    <xsl:variable name="table" select="."/>
    <xsl:value-of select="$eol"/>
    <xsl:text/>//Enum for <xsl:value-of select="@class"/>/<xsl:value-of select="@name"/> /  <xsl:value-of select="@description"/>
      
    <xsl:variable name="prefix" select="@enum"/>
    <xsl:variable name="value" select="$table/column[@primary-key='yes']/@name"/>
    <xsl:variable name="name" select="@enum-name"/>
    <xsl:variable name="comment" select="@enum-comment"/>
    
    <xsl:for-each select="data/item">
      <xsl:value-of select="$eol"/>define( "<xsl:value-of select="$prefix"/><xsl:value-of select="@*[name()=$name]"/>", <xsl:text/>
      <xsl:value-of select="@*[name()=$value]"/>); // <xsl:value-of select="@*[name()=$comment]"/>
    </xsl:for-each>
  </xsl:template>
    
</xsl:stylesheet>
