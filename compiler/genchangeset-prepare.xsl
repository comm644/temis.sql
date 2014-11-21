<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="xml"
              indent="yes"
      />

  <xsl:template match="/">
    <xsl:apply-templates select="*|node()|@*|comment()|text()"/>
  </xsl:template>

  <xsl:template match="include">
    <xsl:apply-templates select="document(@href)/database/*"/>
  </xsl:template>

  <xsl:template match="*|@*|text()|comment()" >
    <xsl:copy>
      <xsl:apply-templates select="*|@*|text()|comment()" />
    </xsl:copy>
  </xsl:template>

</xsl:stylesheet>
