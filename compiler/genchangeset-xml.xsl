<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml"
					indent = "yes" 
	/>

	<xsl:template match="/">
		<changeset >
			<xsl:attribute name="previous">
				<xsl:value-of select="document(//previous/@href)//version/@major"/>
			</xsl:attribute>
			<xsl:attribute name="current">
				<xsl:value-of select="document(//current/@href)//version/@major"/>
			</xsl:attribute> 
			
			<xsl:variable name="doc" select="document(//current/@href)//table"/>
			<xsl:apply-templates select="$doc" mode="add">
				<xsl:with-param name="otherdoc" select="document(//previous/@href)"/>
				<xsl:with-param name="mode" select="'add'"/> 
			</xsl:apply-templates>
			<xsl:apply-templates select="document(//previous/@href)//table" mode="remove">
				<xsl:with-param name="otherdoc" select="document(//current/@href)"/>
				<xsl:with-param name="mode" select="'remove'"/> 
			</xsl:apply-templates>
		</changeset>
		
	</xsl:template>
	
	

	<xsl:template match="table" mode="add">
		<xsl:param name="otherdoc"/>
		<xsl:param name="mode"/>
		
		<xsl:variable name="name" select="@name"/>
		<xsl:variable name="other" select="$otherdoc//table[@name = $name]"/>
			
		<xsl:choose>
			<xsl:when test="count($other) = 0 ">
				<add-table mode="{$mode}-table" table="{@name}">
					<xsl:apply-templates select="." mode="copy"/>
				</add-table>				
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="column" mode="add">
					<xsl:with-param name="otherdoc" select="$other/column"/>
					<xsl:with-param name="mode" select="add"/> 
				</xsl:apply-templates>
			
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="table" mode="remove">
		<xsl:param name="otherdoc"/>
		<xsl:param name="mode"/>
		
		<xsl:variable name="name" select="@name"/>
		<xsl:variable name="other" select="$otherdoc//table[@name = $name]"/>
			
		<xsl:choose>
			<xsl:when test="count($other) = 0 ">
				<remove-table  mode="{$mode}-table" table="{@name}"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="column" mode="remove">
					<xsl:with-param name="otherdoc" select="$other/column"/>
					<xsl:with-param name="mode" select="$mode"/> 
				</xsl:apply-templates>
			
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="column" mode="add"> 
		<xsl:param name="otherdoc"/>
		<xsl:param name="mode"/>
		
		<xsl:variable name="name" select="@name"/>
		<xsl:variable name="other" select="$otherdoc[@name = $name]"/>
		
		<xsl:if test="count($other) = 0 ">
			<add-column name="{@name}" table="{../@name}">
				<xsl:copy-of select="."/>
			</add-column>
		</xsl:if>
	</xsl:template>

	<xsl:template match="column" mode="remove"> 
		<xsl:param name="otherdoc"/>
		<xsl:param name="mode"/>
		
		<xsl:variable name="name" select="@name"/>
		<xsl:variable name="other" select="$otherdoc[@name = $name]"/>
		
		<xsl:if test="count($other) = 0 ">
			<remove-column mode="{$mode}-column" name="{@name}" table="{../@name}"/>
		</xsl:if>
	</xsl:template>

<xsl:template match="*|@*" mode="copy">
	<xsl:copy>
		<xsl:apply-templates select="*|@*" mode="copy"/>
	</xsl:copy>
</xsl:template>

</xsl:stylesheet>
