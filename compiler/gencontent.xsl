<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

  <xsl:output method="text"
    encoding="utf-8"
    />

  <xsl:variable name="SqlEngineTag40">TYPE</xsl:variable> <!-- Mysql.40 -->
  <xsl:variable name="SqlEngineTag41">ENGINE</xsl:variable> <!-- Mysql.41 -->
  <xsl:variable name="SqlEngineTag" select="$SqlEngineTag41"/>

  <xsl:variable name="SqlDefaultEngine">InnoDB</xsl:variable>

    <!-- TODO customize transformation rules 
         syntax recommendation http://www.w3.org/TR/xslt 
    -->
    <xsl:template match="/">
        <xsl:apply-templates select="/database/table"/>
    </xsl:template>

    <xsl:template match="table"/>
    <xsl:template match="table[count(item) != 0]">
        <xsl:variable name="table" select="."/>
--- data for table <xsl:value-of select="$table/@name"/>.

        <xsl:for-each select="item">
            INSERT INTO `<xsl:value-of select="$table/@name"/>` (<xsl:text/>
            <xsl:apply-templates select="." mode="insert-column-names"/>
            <xsl:text>)</xsl:text>
            VALUES (<xsl:text/>
            <xsl:apply-templates select="." mode="insert-column-values" >
                  <xsl:with-param name="table" select="$table"/>
             </xsl:apply-templates>
            <xsl:text/> );
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="item" mode="insert-column-names">
        <xsl:for-each select="value">
            <xsl:if test="position() != 1">,</xsl:if>
            <xsl:text/>`<xsl:value-of select="@name"/>`<xsl:text/>
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="item" mode="insert-column-values">
        <xsl:param name="table"/>
        <xsl:for-each select="value">
            <xsl:if test="position() != 1">,</xsl:if>
            <xsl:variable name="name" select="@name"/>
            <xsl:variable name="column" select="$table/column[@name=$name]"/>
            
            <xsl:choose>
                <xsl:when test="$column/@type = 'varchar' or column/@type='text'">
                    <xsl:text/>"<xsl:value-of select="@value"/>"<xsl:text/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@value"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
    </xsl:template>
    
</xsl:stylesheet>
