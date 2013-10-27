<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

  <xsl:output method="text"
    encoding="cp1251"
    />

  <xsl:variable name="SqlEngineTag40">TYPE</xsl:variable> <!-- Mysql.40 -->
  <xsl:variable name="SqlEngineTag41">ENGINE</xsl:variable> <!-- Mysql.41 -->
  <xsl:variable name="SqlEngineTag" select="$SqlEngineTag41"/>

  <xsl:variable name="SqlDefaultEngine">InnoDB</xsl:variable>
  
  <xsl:template match="/">
SET FOREIGN_KEY_CHECKS = 0;
    <xsl:apply-templates select="/database"/>
SET FOREIGN_KEY_CHECKS = 1;
  </xsl:template>

  <xsl:template match="database">
    <xsl:apply-templates select="table"/>
    <xsl:apply-templates select="include"/>
  </xsl:template>
  
  <xsl:template match="include">
    <xsl:apply-templates select="document(@href)/database"/>
  </xsl:template>


  
  <xsl:template match="table">
    <xsl:variable name="table" select="@name"/>
DROP TABLE IF EXISTS <xsl:value-of select="@name"/>;
CREATE TABLE <xsl:value-of select="@name"/> (
  <xsl:for-each select="column">
    <xsl:if test="position() > 1">
  ,</xsl:if>
    <xsl:apply-templates select="." mode="define"/>
  </xsl:for-each>
  <xsl:text>

  </xsl:text>

  <xsl:apply-templates select="column[count(@foreign-key) !=0 ]" mode="index">
    <xsl:with-param name="table" select="$table"/>
  </xsl:apply-templates>

  <xsl:apply-templates select="column[@index ='yes' ]" mode="index">
    <xsl:with-param name="table" select="$table"/>
  </xsl:apply-templates>

  <xsl:text>
  </xsl:text>    

  <xsl:apply-templates select="column[@primary-key ='yes' ]" mode="primary-key">
    <xsl:with-param name="table" select="$table"/>
  </xsl:apply-templates>

  <xsl:apply-templates select="column[@unique ='yes' ]" mode="unique">
    <xsl:with-param name="table" select="$table"/>
  </xsl:apply-templates>

  <xsl:apply-templates select="column[count(@foreign-key) !=0 ]" mode="foreign-key">
    <xsl:with-param name="table" select="$table"/>
  </xsl:apply-templates>

  ) <xsl:text/><xsl:value-of select="$SqlEngineTag"/>=<xsl:value-of select="$SqlDefaultEngine"/><xsl:text> </xsl:text>
  <xsl:if test="count( /database/@encoding ) =1">CHARSET <xsl:value-of select="/database/@encoding"/></xsl:if>
  <xsl:if test="count( /database/@collate ) = 1">COLLATE <xsl:value-of select="/database/@collate"/></xsl:if>;

  <xsl:apply-templates select="." mode="insert-data"/>
</xsl:template>


  
  <xsl:template match="column" mode="define">
    <xsl:text>`</xsl:text><xsl:value-of select="@name"/><xsl:text>` </xsl:text><xsl:value-of select="@type"/>
    <xsl:if test="@length != ''">
      <xsl:text>(</xsl:text><xsl:value-of select="@length"/><xsl:text>)</xsl:text>
    </xsl:if>
    <xsl:apply-templates select="." mode="default-value"/>
    
    <xsl:if test="@auto-increment='yes'">
      <xsl:text> auto_increment </xsl:text>
    </xsl:if>
    <!--
    <xsl:if test="@unique='yes'">
      <xsl:text> unique </xsl:text>
    </xsl:if>
    -->
  </xsl:template>

  <xsl:template match="column" mode="default-value">
    <xsl:choose>
      <xsl:when test="@default = 'null' or @default = 'NULL'"> DEFAULT NULL </xsl:when>
      <xsl:when test="@default = 'not null' or @default = 'NOT NULL'"> NOT NULL </xsl:when>
      <xsl:when test="@default = '0'"> DEFAULT 0</xsl:when>
      <xsl:when test="@default = '' and @type='int'">
        <xsl:message terminate="yes">
          <xsl:text>GenSql Error: Attribute @default for column '</xsl:text>
          <xsl:value-of select="../@name"/>::<xsl:value-of select="@name"/>
          
          <xsl:text>' is not defined correctly</xsl:text>
        </xsl:message>
      </xsl:when>
      <xsl:when test="@default = '' and @type='varchar'"> DEFAULT ''</xsl:when>
      <xsl:when test="@default = '' and count( @foreign-key ) != 0">
        <xsl:message terminate="yes">
          <xsl:text>GenSql Error: Attribute @default for column '</xsl:text>
          <xsl:value-of select="../@name"/>::<xsl:value-of select="@name"/>
          
          <xsl:text>' is not defined correctly</xsl:text>
        </xsl:message>
      </xsl:when>
      <xsl:when test="count(@default) != 0 and @type='int'"> DEFAULT <xsl:value-of select="@default"/></xsl:when>
      <xsl:when test="count(@default) != 0"> DEFAULT '<xsl:value-of select="@default"/>'</xsl:when>
      <xsl:when test="@primary-key='yes'"> NOT NULL </xsl:when>
      <xsl:otherwise> DEFAULT NULL</xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  

  <xsl:template match="column" mode="index">
    <xsl:param name="table"/>
    <xsl:text>,</xsl:text>INDEX ix_<xsl:value-of select="$table"/>_<xsl:value-of select="@name"/>(`<xsl:value-of select="@name"/>` <xsl:apply-templates select="." mode="index-length"/>)
  </xsl:template>

  <xsl:template match="column" mode="index-length"/>
  <xsl:template match="column[count(@length) != 0]" mode="index-length">
	<xsl:text/>(<xsl:value-of select="@length"/>)<xsl:text/>
  </xsl:template>
  
  <xsl:template match="column" mode="primary-key">
    <xsl:param name="table"/>
    <xsl:text>,</xsl:text>CONSTRAINT c_<xsl:value-of select="$table"/>_<xsl:value-of select="@name"/> PRIMARY KEY(`<xsl:value-of select="@name"/>`)
  </xsl:template>

  <xsl:template match="column" mode="unique">
    <xsl:param name="table"/>
    <xsl:text>,</xsl:text>CONSTRAINT c_<xsl:value-of select="$table"/>_<xsl:value-of select="@name"/> UNIQUE(`<xsl:value-of select="@name"/>`)
  </xsl:template>
  
  <xsl:template match="column" mode="on-delete"><xsl:value-of select="@on-delete"/></xsl:template>
  <xsl:template match="column[@on-delete='set-null']" mode="on-delete">SET NULL</xsl:template>
  <xsl:template match="column[@on-delete='cascade']" mode="on-delete">CASCADE</xsl:template>
  <xsl:template match="column[@on-delete='restrict']" mode="on-delete">RESTRICT</xsl:template>

  <xsl:template match="column" mode="foreign-key">
    <xsl:param name="table"/>
    <xsl:variable name="this" select="."/>

    <!-- verify tables if available -->
    <xsl:variable name="foreign-table" select="//table[@name = $this/@foreign-table]"/>
    <xsl:if test="count( $foreign-table ) = 0"> 
        <xsl:message terminate="yes">
          <xsl:text>GenSql Error: Foreign Key constaints fail in '</xsl:text>
          <xsl:value-of select="../@name"/>::<xsl:value-of select="@name"/>
          <xsl:text>'. The table '</xsl:text>
          <xsl:value-of select="@foreign-table"/>
          <xsl:text>' does not exists</xsl:text>
        </xsl:message>
    </xsl:if>
    <xsl:if test="count( $foreign-table ) = 1"> 
      <xsl:if test="count( $foreign-table/column[@name = $this/@foreign-key] ) = 0 ">
        <xsl:message terminate="yes">
          <xsl:text>GenSql Error: Foreign Key constaints fail in '</xsl:text>
          <xsl:value-of select="../@name"/>::<xsl:value-of select="@name"/>
          <xsl:text>'. The column '</xsl:text>
          <xsl:value-of select="@foreign-table"/>::<xsl:value-of select="@foreign-key"/>
          <xsl:text>' does not exists</xsl:text>
        </xsl:message>
      </xsl:if>
    </xsl:if>
    
    <xsl:text>
  ,</xsl:text>CONSTRAINT c_<xsl:value-of select="$table"/>_<xsl:value-of select="@name"/> FOREIGN KEY(`<xsl:value-of select="@name"/>`)
    REFERENCES <xsl:value-of select="@foreign-table"/>(`<xsl:value-of select="@foreign-key"/><xsl:text>`)</xsl:text>
    <xsl:if test="count(@on-delete)"> ON DELETE <xsl:apply-templates select="." mode="on-delete"/></xsl:if>
  </xsl:template>


  
  <xsl:template match="*" mode="insert-data">
    <xsl:variable name="table" select="."/>
    <xsl:for-each select="data/item">
      <xsl:text>INSERT INTO `</xsl:text>
      <xsl:value-of select="$table/@name"/><xsl:text>` SET </xsl:text>
      <xsl:for-each select="@*" >
        <xsl:if test="position() != 1">,</xsl:if>
        <xsl:text>`</xsl:text><xsl:value-of select="name()"/>
        <xsl:text>`="</xsl:text><xsl:value-of select="."/><xsl:text>"</xsl:text>
      </xsl:for-each>
      <xsl:text>;
</xsl:text>      
    </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>
